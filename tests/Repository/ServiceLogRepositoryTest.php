<?php

declare(strict_types=1);

namespace Tests\Repository;

use App\Entity\ServiceLog;
use App\Modules\RequestLogParser;
use App\Repository\ServiceLogRepository;
use Tests\BaseKernelTestCase;

class ServiceLogRepositoryTest extends BaseKernelTestCase
{
    public function testItCanImportFromRequestLogs(): void
    {
        $requestLogParser = new RequestLogParser();
        $requestLog1 = $requestLogParser->parse(
            'USER-SERVICE - - [18/Aug/2021:10:32:56 +0000] "POST /users HTTP/1.1" 201'
        );
        $requestLog2 = $requestLogParser->parse(
            'USER-SERVICE - - [18/Aug/2021:10:32:57 +0000] "POST /users HTTP/1.1" 500'
        );

        $serviceLogRepository = $this->entityManager->getRepository(ServiceLog::class);
        /** @var ServiceLogRepository $serviceLogRepository */
        $serviceLogRepository->bulkInsertFromRequestLogs([
            $requestLog1,
            $requestLog2
        ]);

        $serviceLogs = $serviceLogRepository->findAll();
        $this->assertCount(2, $serviceLogs);
    }
}
