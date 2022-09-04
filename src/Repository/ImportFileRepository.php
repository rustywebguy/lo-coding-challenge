<?php

declare(strict_types=1);

namespace App\Repository;

use DateTime;
use App\Entity\ImportFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ImportFile>
 *
 * @method ImportFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImportFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImportFile[]    findAll()
 * @method ImportFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImportFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImportFile::class);
    }

    public function initiateOrContinueImport(string $filename): ImportFile
    {
        $importFile = $this->findOneBy(['filename' => $filename]);

        if ($importFile === null) {
            $importFile = new ImportFile();
            $importFile->setFilename($filename);
            $importFile->setStatus(ImportFile::INITIATED);
            $importFile->setParsedItemsCount(0);
            $importFile->setImportedItemsCount(0);

            $datetime = (new DateTime());
            $importFile->setCreatedAt($datetime);
            $importFile->setUpdatedAt($datetime);

            $this->getEntityManager()->persist($importFile);
            $this->getEntityManager()->flush();
        }

        return $importFile;
    }

    public function setImportStatus(ImportFile $importFile, string $status): ImportFile
    {
        $importFile->setStatus($status);
        $importFile->setUpdatedAt(new DateTime());
        $this->getEntityManager()->flush();

        return $importFile;
    }

    public function updateCount(ImportFile $importFile, int $parsedItemsCount, int $importedItemsCount): ImportFile
    {
        $importFile->setParsedItemsCount($importFile->getParsedItemsCount() + $parsedItemsCount);
        $importFile->setImportedItemsCount($importFile->getImportedItemsCount() + $importedItemsCount);
        $importFile->setUpdatedAt(new DateTime());
        $this->getEntityManager()->flush();

        return $importFile;
    }
}
