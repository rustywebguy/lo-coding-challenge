<?php

declare(strict_types=1);

namespace App\Command;

use Generator;
use Monolog\Logger;
use App\Entity\ImportFile;
use Psr\Log\LoggerInterface;
use App\ValueObject\RequestLog;
use App\Modules\TextFileReader;
use App\Modules\RequestLogParser;
use App\Repository\ImportFileRepository;
use App\Repository\ServiceLogRepository;
use App\Interfaces\ReportableExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legalone:parse-service-log')]
final class ParseServiceLogCommand extends Command
{
    private const READ_BATCH_SIZE = 25000;

    protected static $defaultName = 'legalone:parse-service-log';

    private bool $shouldStop;

    public function __construct(
        public RequestLogParser $parser,
        public ServiceLogRepository $serviceRepository,
        public ImportFileRepository $importFileRepository,
        public LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->trapSignals();

        $filename = $input->getArgument('filename');

        $importFile = $this->importFileRepository->initiateOrContinueImport($filename);

        if ($importFile->isSuccessful()) {
            $this->log($output, 'This file has already been imported from previous job!', Logger::ERROR);
            return Command::INVALID;
        }

        try {
            $reader = TextFileReader::fromFilename(
                $filename,
                self::READ_BATCH_SIZE,
                $importFile->getParsedItemsCount()
            );

            $totalItemsCount = $reader->getRowsCount() - $importFile->getParsedItemsCount();
            $progressBar = $this->startProgress($output, $totalItemsCount);

            // since this is a console command we store the log by batch.
            // but if we can use queues we can split the file by batch
            // and publish a batch into a queue to be processed by a batch processor
            while (($batch = $reader->getNextBatch())->valid()) {
                if ($this->shouldStop) {
                    $status = ImportFile::INTERRUPTED;

                    $this->log($output, 'Received interrupt signal. Stopping import!', Logger::ERROR);
                    return Command::FAILURE;
                }

                $requestLogs = $this->parseBatch($batch);

                $importedCount = $this->serviceRepository->bulkInsertFromRequestLogs($requestLogs);

                $importFile = $this->importFileRepository->updateCount(
                    $importFile,
                    $reader->getLinesReadCount(),
                    $importedCount
                );

                $progressBar->advance($reader->getLinesReadCount());
            }

            $status = ImportFile::SUCCESS;

            $progressBar->finish();

            $this->log($output, 'File is successfully imported!');
            return Command::SUCCESS;
        } catch (ReportableExceptionInterface $exception) {
            $status = ImportFile::FAILED;

            $this->logger->error(__CLASS__.': '. $exception->getMessage(), $exception->getContext());

            $this->log($output, $exception->getMessage(), Logger::ERROR);
            return Command::FAILURE;
        } finally {
            $this->importFileRepository->setImportStatus($importFile, $status);
        }
    }

    protected function configure(): void
    {
        $this->setHelp('This command parses the service log file provided and stores it in the database');

        $this->addArgument(
            'filename',
            InputArgument::REQUIRED,
            'File should be stored in storage directory'
        );
    }

    private function startProgress(OutputInterface $output, int $count): ProgressBar
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->section();
        }

        $progressBar = new ProgressBar($output, $count);
        $progressBar->start();

        return $progressBar;
    }

    /**
     * @param Generator $batch
     * @return RequestLog[]
     */
    private function parseBatch(Generator $batch): array
    {
        $requestLogs = [];

        foreach ($batch as $row) {
            try {
                $requestLogs[] = $this->parser->parse($row);
            } catch (ReportableExceptionInterface $exception) {
                $this->logger->error(__CLASS__ . ': '. $exception->getMessage(), $exception->getContext());
            }
        }

        return $requestLogs;
    }

    private function trapSignals(): void
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGTERM, [$this, 'interrupt']);
        pcntl_signal(SIGINT, [$this, 'interrupt']);

        $this->shouldStop = false;
    }

    public function interrupt(): void
    {
        $this->shouldStop = true;
    }

    public function log(OutputInterface $output, string $message, int $level = Logger::INFO): void
    {
        $level = $level === Logger::INFO ? 'info': 'error';
        $message = sprintf('<%s> %s<%s>', $level, $message, $level);

        $output->writeln($message);
    }
}
