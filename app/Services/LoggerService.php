<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\LoggerServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Logger Service
 *
 * Implementation of LoggerServiceInterface that wraps Laravel's Log facade.
 */
class LoggerService implements LoggerServiceInterface
{
    /**
     * Log an error message
     */
    public function error(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }

    /**
     * Log an info message
     */
    public function info(string $message, array $context = []): void
    {
        Log::info($message, $context);
    }

    /**
     * Log a warning message
     */
    public function warning(string $message, array $context = []): void
    {
        Log::warning($message, $context);
    }
}
