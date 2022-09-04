<?php

declare(strict_types=1);

namespace Tests\Modules;

use App\Exceptions\ParserException;
use App\Modules\RequestLogParser;
use App\ValueObject\RequestLog;
use PHPUnit\Framework\TestCase;

class RequestLogParserTest extends TestCase
{
    public function testParseValidRow(): void
    {
        $requestLogParser = new RequestLogParser();
        $requestLog = $requestLogParser->parse(
            'USER-SERVICE - - [18/Aug/2021:10:32:56 +0000] "POST /users HTTP/1.1" 201'
        );

        $this->assertInstanceOf(RequestLog::class, $requestLog);
        $this->assertEquals('USER-SERVICE', $requestLog->getServiceName());
        $this->assertEquals('2021-08-18 10:32:56', $requestLog->getTime());
        $this->assertEquals('POST', $requestLog->getMethod());
        $this->assertEquals('/users', $requestLog->getSlug());
        $this->assertEquals('HTTP/1.1', $requestLog->getVersion());
        $this->assertEquals(201, $requestLog->getStatusCode());
    }

    public function testParseEmptyRow(): void
    {
        $this->expectException(ParserException::class);

        $requestLogParser = new RequestLogParser();
        $requestLogParser->parse('');
    }

    public function testParseInvalidRow(): void
    {
        $this->expectException(ParserException::class);

        $requestLogParser = new RequestLogParser();
        $requestLogParser->parse('USER-SERVICE - - "POST /users HTTP/1.1" 201');
    }
}
