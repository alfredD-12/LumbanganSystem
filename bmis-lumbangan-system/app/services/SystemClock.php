<?php

require_once __DIR__ . '/ClockInterface.php';

class SystemClock implements ClockInterface
{
    public function now()
    {
        return new DateTimeImmutable('now');
    }
}
