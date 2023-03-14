<?php

namespace StephaneCoinon\Papertrail;

use Psr\Log\LoggerInterface;
use StephaneCoinon\Papertrail\Exceptions\LaravelNotDetectedException;

class Laravel extends Php
{
    public static string $defaultLoggerName = 'Laravel';

    /**
     * Is Laravel installed?
     *
     * @return boolean
     */
    public function isLaravelInstalled(): bool
    {
        return class_exists('Illuminate\Foundation\Application');
    }

    /**
     * {@inheritDoc}
     * @throws LaravelNotDetectedException
     */
    protected function detectFrameworkOrFail(): Laravel
    {
        if (! $this->isLaravelInstalled()) {
            throw LaravelNotDetectedException::inDriver($this);
        }

        return $this;
    }

    /**
     * Get the logger instance.
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        $logger = app('log');

        // Laravel < 5.6
        if ($logger instanceof \Illuminate\Log\Writer) {
            return $logger->getMonolog();
        }

        // Laravel >= 5.6
        return $logger->getLogger();
    }
}
