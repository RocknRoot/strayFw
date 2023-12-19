<?php

namespace RocknRoot\StrayFw;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException as LoggerInvalidArgumentException;
use Psr\Log\LogLevel;

/**
 * General class for logging. PSR-3 compliant.
 *
 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Logger extends AbstractLogger
{
    /**
     * General logger instance.
     *
     * @static
     */
    private static ?\RocknRoot\StrayFw\Logger $log = null;

    /**
     * Get general logger instance.
     *
     * @return Logger
     */
    public static function get(): Logger
    {
        if (self::$log == null) {
            self::$log = new Logger();
        }
        return self::$log;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param  mixed                          $level
     * @param  string                         $message
     * @param  array<string, string>          $context
     * @throws LoggerInvalidArgumentException if level is unknown
     */
    public function log($level, $message, array $context = array()): void
    {
        if (!\is_string($message)) {
            throw new LoggerInvalidArgumentException(\sprintf(
                'Argument 2 passed to %s must be a string!',
                __METHOD__
            ));
        }

        static $levels = array(
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG
        );
        if (\in_array($level, $levels) === false) {
            throw new LoggerInvalidArgumentException('unknown level "' . $level . '"');
        }
        $message = $message;
        foreach ($context as $key => $value) {
            $message = \str_replace('{' . $key . '}', $value, $message);
        }
        \error_log('[' . $level . '] ' . $message);
        if (\defined('STRAY_IS_CLI') === true && STRAY_IS_CLI === true) {
            echo '[' . $level . '] ' . $message . PHP_EOL;
        }
    }
}
