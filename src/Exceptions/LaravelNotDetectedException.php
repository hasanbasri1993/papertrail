<?php

namespace StephaneCoinon\Papertrail\Exceptions;

class LaravelNotDetectedException extends FrameworkNotDetectedException
{
    protected string $frameworkName = 'Laravel';
}
