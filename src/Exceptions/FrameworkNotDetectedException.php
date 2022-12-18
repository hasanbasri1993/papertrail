<?php

namespace StephaneCoinon\Papertrail\Exceptions;

use Exception;

class FrameworkNotDetectedException extends Exception
{
    protected string $frameworkName = 'PHP';
    protected string $driverClass = '';

    public static function inDriver($driver): static
    {
        $exception = new static;
        $exception->driverClass = get_class($driver);
        $exception->formatMessage();

        return $exception;
    }

    protected function formatMessage()
    {
        $this->message = 'Framework not detected: '
            . $this->driverClass
            . ' driver must be used in '
            . $this->frameworkName
            . ' framework';
    }
}
