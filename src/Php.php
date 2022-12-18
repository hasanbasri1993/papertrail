<?php

namespace StephaneCoinon\Papertrail;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Php
{
    public static string $defaultLoggerName = 'PHP';
    public static mixed $formatLogger = '';
    public static array $papertrailProcessor = [];

    /**
     * Papertrail log handler.
     *
     * @var HandlerInterface|SyslogUdpHandler
     */
    protected SyslogUdpHandler|HandlerInterface $handler;

    /**
     * Make a new PHP driver to send logs to Papertrail.
     *
     * @param string $host Papertrail log server, ie log.papertrailapp.com
     * @param int $port Papertrail port number for log server
     * @param string $prefix Prefix to use for each log message
     */
    protected function __construct(string $host, int $port, string $prefix)
    {
        $this->handler = $this->getHandler($host, $port, $prefix);
    }

    /**
     * Boot connector with given host, port and log message prefix.
     *
     * If host or port are omitted, we'll try to get them from the environment
     * variables PAPERTRAIL_HOST and PAPERTRAIL_PORT.
     *
     * @param string|null $host Papertrail log server, ie log.papertrailapp.com
     * @param int|null $port Papertrail port number for log server
     * @param string $prefix Prefix to use for each log message
     * @return LoggerInterface
     */
    public static function boot(string $host = null, int $port = null, string $prefix = ''): LoggerInterface
    {
        $host or $host = getenv('PAPERTRAIL_HOST');
        $port or $port = getenv('PAPERTRAIL_PORT');
        $prefix and $prefix = "[$prefix] ";

        return (new static($host, $port, $prefix))
            ->detectFrameworkOrFail()
            ->registerPapertrailHandler();
    }

    /**
     * Boot connector using credentials set in environment variables and the
     * given log message prefix.
     *
     * @param string $prefix Prefix to use for each log message
     */
    public static function bootWithPrefix(string $prefix): LoggerInterface
    {
        return static::boot(null, null, $prefix);
    }

    /**
     * Get Papertrail SysLog handler.
     *
     * @param string $host
     * @param int $port
     * @param string $prefix
     * @return HandlerInterface|SyslogUdpHandler
     */
    public function getHandler(string $host, int $port, string $prefix): HandlerInterface|SyslogUdpHandler
    {
        $syslog = new SyslogUdpHandler($host, $port);
        $formatter = new LineFormatter("$prefix%channel%.%level_name%: %message% %extra%");
        $syslog->setFormatter(static::$formatLogger ?? $formatter);
        return $syslog;
    }

    /**
     * Get the logger instance.
     *
     * @return Logger|LoggerInterface
     */
    public function getLogger(): Logger|LoggerInterface
    {
        return new Logger(static::$defaultLoggerName);
    }

    /**
     * Throw an exception if the framework for this driver is not detected
     *
     * @return $this
     */
    protected function detectFrameworkOrFail(): static
    {
        // no framework to detect in a plain PHP context
        return $this;
    }

    /**
     * Register papertrail log handler with the current logger.
     *
     * @return LoggerInterface
     */
    protected function registerPapertrailHandler(): LoggerInterface
    {
        $defaultPapertrailHandler = $this->getLogger()->pushHandler($this->handler);
        if (static::$papertrailProcessor)
            foreach (static::$papertrailProcessor as $processor)
                $defaultPapertrailHandler->pushProcessor($processor);
        return $defaultPapertrailHandler;
    }
}
