<?php

declare(strict_types=1);

namespace Tests\Command;

use App\Entity\ImportFile;
use App\Entity\ServiceLog;
use Symfony\Component\Console\Command\Command;
use Tests\BaseKernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ParseServiceLogCommandTest extends BaseKernelTestCase
{
    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->application = new Application($kernel);
    }

    public function testItCanProcessAServiceLog(): void
    {
        $command = $this->application->find('legalone:parse-service-log');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'filename' => __DIR__ . '/data/logs.txt',
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('File is successfully imported!', $output);

        $importFileRepository = $this->entityManager->getRepository(ImportFile::class);
        $importFile = $importFileRepository->findOneBy(['filename' => __DIR__ . '/data/logs.txt']);

        $serviceLogRepository = $this->entityManager->getRepository(ServiceLog::class);
        $serviceLogs = $serviceLogRepository->findAll();

        $this->assertEquals(21, $importFile->getParsedItemsCount());
        $this->assertEquals(20, $importFile->getImportedItemsCount());
        $this->assertEquals('success', $importFile->getStatus());

        $this->assertCount(20, $serviceLogs);
    }

    public function testItIsInvalidIfFileHasBeenPreviouslyProcessed(): void
    {
        $importFile = new ImportFile();
        $importFile->setStatus('success');
        $importFile->setFilename(__DIR__ . '/data/logs1.txt');
        $this->entityManager->persist($importFile);
        $this->entityManager->flush();

        $command = $this->application->find('legalone:parse-service-log');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'filename' => __DIR__ . '/data/logs1.txt',
        ]);

        $this->assertEquals(Command::INVALID, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('This file has already been imported from previous job!', $output);
    }

    public function testItFailsIfFileIsNotExisting(): void
    {
        $command = $this->application->find('legalone:parse-service-log');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'filename' => __DIR__ . '/data/logs',
        ]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('File cannot be opened!', $output);

        $commandTester->execute([
            'filename' => __DIR__ . '/data',
        ]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Filename provided is a directory!', $output);
    }
}
