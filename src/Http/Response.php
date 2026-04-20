<?php

declare(strict_types=1);

namespace App\Http;

use JsonSerializable;

final class Response implements JsonSerializable
{
    private int $statusCode;
    private string $status;
    private array $data;
    private ?string $message;
    private ?int $count;

    private function __construct(
        int $statusCode,
        string $status,
        array $data = [],
        ?string $message = null,
        ?int $count = null
    ) {
        $this->statusCode = $statusCode;
        $this->status = $status;
        $this->data = $data;
        $this->message = $message;
        $this->count = $count;
    }

    public static function success(
        array $data,
        int $statusCode = 200,
        ?string $message = null,
        ?int $count = null
    ): self {
        return new self($statusCode, 'success', $data, $message, $count);
    }

    public static function error(
        string $message,
        int $statusCode = 400
    ): self {
        return new self($statusCode, 'error', [], $message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function jsonSerialize(): array
    {
        $response = [
            'status' => $this->status,
        ];

        if ($this->message !== null) {
            $response['message'] = $this->message;
        }

        if ($this->count !== null) {
            $response['count'] = $this->count;
        }

        $response['data'] = $this->data;

        // For errors, reorder to put message after status
        if ($this->status === 'error') {
            $response = [
                'status' => $this->status,
                'message' => $this->message,
            ];
        }

        return $response;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        header('Content-Type: application/json');
        echo json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
