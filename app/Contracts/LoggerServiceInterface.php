<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Logger Service Interface
 *
 * Defines a contract for logging operations.
 * This allows for different logger implementations and makes testing easier.
 */
interface LoggerServiceInterface
{
    /**
     * Log an error message
     */
    public function error(string $message, array $context = []): void;

    /**
     * Log an info message
     */
    public function info(string $message, array $context = []): void;

    /**
     * Log a warning message
     */
    public function warning(string $message, array $context = []): void;
}
