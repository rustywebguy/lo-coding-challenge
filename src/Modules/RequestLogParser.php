<?php

declare(strict_types=1);

namespace App\Modules;

use App\Exceptions\ParserException;
use App\ValueObject\RequestLog;

final class RequestLogParser
{
    private CONST LOG_PATTERN = '/(?P<service>\S+) - - \[(?P<time>.+)\] \"(?P<request>.+)\" (?P<status>[0-9]+)/';

    /**
     * @throws ParserException
     */
    public function parse(string $row): RequestLog
    {
        preg_match(self::LOG_PATTERN, $row, $matches);

        if ($this->isValid($matches) === false) {
            throw ParserException::cannotBeParsed($row);
        }

        return RequestLog::create($matches);
    }

    private function isValid(array $matches): bool
    {
        foreach (RequestLog::GROUP_MATCH as $groupMatchKey) {
            if (!array_key_exists($groupMatchKey, $matches)) {
                return false;
            }
        }

        return true;
    }
}
