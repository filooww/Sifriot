<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class ColoredLineFormatter extends LineFormatter
{
    private const COLORS = [
        'DEBUG' => "\033[0;36m",    // Cyan
        'INFO' => "\033[0;32m",     // Green
        'NOTICE' => "\033[0;34m",   // Blue
        'WARNING' => "\033[1;33m",  // Yellow
        'ERROR' => "\033[0;31m",    // Red
        'CRITICAL' => "\033[1;31m", // Bold Red
        'ALERT' => "\033[1;35m",    // Bold Magenta
        'EMERGENCY' => "\033[1;41m", // Bold Red Background
    ];

    private const RESET = "\033[0m";

    public function format(LogRecord $record): string
    {
        $output = parent::format($record);

        $levelName = $record->level->getName();
        $color = self::COLORS[$levelName] ?? '';

        if ($color && $this->shouldColorize()) {
            return $color.$output.self::RESET;
        }

        return $output;
    }

    /**
     * Determine if output should be colorized.
     * Only colorize if writing to a terminal (not to file).
     */
    private function shouldColorize(): bool
    {
        // Check if running in CLI and STDOUT is a terminal
        return PHP_SAPI === 'cli' && function_exists('posix_isatty') && @posix_isatty(STDOUT);
    }

    /**
     * Laravel tap method to customize the logger.
     */
    public function __invoke($logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new self());
        }
    }
}
