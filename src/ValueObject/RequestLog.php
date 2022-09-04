<?php

declare(strict_types=1);

namespace App\ValueObject;

use Exception;
use DateTime;

final class RequestLog
{
    private CONST SERVICE = 'service';
    private CONST TIME = 'time';
    private CONST REQUEST = 'request';
    private CONST STATUS = 'status';

    public CONST GROUP_MATCH = [
        self::SERVICE,
        self::TIME,
        self::REQUEST,
        self::STATUS,
    ];

    private string $method;

    private string $slug;

    private string $version;

    public function __construct(
        private string $serviceName,
        private string $time,
        private string $request,
        private int $statusCode
    ) {
        $requestArray = explode(" ", $this->request);
        $this->method = $requestArray[0] ?? null;
        $this->slug = $requestArray[1] ?? null;
        $this->version = $requestArray[2] ?? null;
    }

    public static function create(array $requestLog): self
    {
        return new self(
            $requestLog[self::SERVICE],
            $requestLog[self::TIME],
            $requestLog[self::REQUEST],
            (int) $requestLog[self::STATUS]
        );
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    /**
     * @throws Exception
     */
    public function getTime(): string
    {
        $time = new DateTime($this->time);

        return $time->format('Y-m-d H:i:s');
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }
}
