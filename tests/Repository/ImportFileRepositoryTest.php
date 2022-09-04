<?php

namespace Tests\Repository;

use App\Entity\ImportFile;
use App\Repository\ImportFileRepository;
use Tests\BaseKernelTestCase;

class ImportFileRepositoryTest extends BaseKernelTestCase
{
    public function testCanInitiateImportFile(): void
    {
        $importFileRepository = $this->entityManager->getRepository(ImportFile::class);
        /** @var ImportFileRepository $importFileRepository */
        $importFile = $importFileRepository->initiateOrContinueImport('file/test.txt');

        $this->assertEquals('file/test.txt', $importFile->getFilename());
        $this->assertEquals('initiated', $importFile->getStatus());
        $this->assertEquals(0, $importFile->getParsedItemsCount());
        $this->assertEquals(0, $importFile->getImportedItemsCount());
    }

    public function testCanContinueImportFile(): void
    {
        $importFileRepository = $this->entityManager->getRepository(ImportFile::class);
        /** @var ImportFileRepository $importFileRepository */
        $importFile = $importFileRepository->initiateOrContinueImport('file/test1.txt');
        $importFileContinue = $importFileRepository->initiateOrContinueImport('file/test1.txt');

        $this->assertEquals($importFile->getId(), $importFileContinue->getId());
    }

    public function testItCanUpdateStatus(): void
    {
        $importFileRepository = $this->entityManager->getRepository(ImportFile::class);
        /** @var ImportFileRepository $importFileRepository */
        $importFile = $importFileRepository->initiateOrContinueImport('file/test2.txt');
        $importFileResult = $importFileRepository->setImportStatus($importFile, 'successful');

        $this->assertEquals('successful', $importFileResult->getStatus());
    }

    public function testItCanUpdateCount(): void
    {
        $importFileRepository = $this->entityManager->getRepository(ImportFile::class);
        /** @var ImportFileRepository $importFileRepository */
        $importFile = $importFileRepository->initiateOrContinueImport('file/test2.txt');
        $importFileResult = $importFileRepository->updateCount($importFile, 100, 120);

        $this->assertEquals(100, $importFileResult->getParsedItemsCount());
        $this->assertEquals(120, $importFileResult->getImportedItemsCount());
    }
}
