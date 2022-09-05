<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ServiceLog;
use App\Exceptions\ServiceLogRepositoryException;
use App\ValueObject\RequestLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<ServiceLog>
 *
 * @method ServiceLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServiceLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServiceLog[]    findAll()
 * @method ServiceLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceLog::class);
    }

    /**
     * Bulk insert using raw mysql query
     *
     * @param RequestLog[] $requestLogs
     *
     * @throws ServiceLogRepositoryException
     */
    public function bulkInsertFromRequestLogs(array $requestLogs): int
    {
        if (count($requestLogs) === 0) {
            return 0;
        }

        $connection = $this->getEntityManager()->getConnection();
        // turn off sql logger for bulk queries
        $connection->getConfiguration()->setSQLLogger();

        $values = [];

        foreach ($requestLogs as $requestLog) {
            $values[] = sprintf(
                '(NULL,"%s", "%s", "%s", %d, "%s", "%s")',
                $requestLog->getServiceName(),
                $requestLog->getTime(),
                $requestLog->getMethod(),
                $requestLog->getStatusCode(),
                $requestLog->getSlug(),
                $requestLog->getVersion(),
            );
        }

        try {
            return $connection->executeStatement(
                sprintf('INSERT INTO `service_log` VALUES %s', rtrim(implode(',', $values), ','))
            );
        } catch (Exception $exception) {
            throw ServiceLogRepositoryException::cannotInsertBatch($values, $exception);
        }
    }

    public function getCountFromRequest(Request $request): int
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select($queryBuilder->expr()->count('sl'))
            ->from(ServiceLog::class, 'sl');

        $query = $request->query;

        // added for each of the columns single
        // and composite index for faster search
        if ($serviceNames = $query->get('serviceNames')) {
            $serviceNames = explode(',', $serviceNames);
            $queryBuilder->andWhere('sl.name IN (:names)')
                ->setParameter('names', $serviceNames);
        }

        if ($statusCode = $request->query->get('statusCode')) {
            $queryBuilder->andWhere('sl.status_code = :status_code')
                ->setParameter('status_code', $statusCode);
        }

        if ($startDate = $request->query->get('startDate')) {
            $queryBuilder->andWhere('sl.requested_at >= :start_date')
                ->setParameter('start_date', $startDate);
        }

        if ($endDate = $request->query->get('endDate')) {
            $queryBuilder->andWhere('sl.requested_at <= :end_date')
                ->setParameter('end_date', $endDate);
        }

        return $queryBuilder
            ->getQuery()
            ->getSingleScalarResult();
    }
}
