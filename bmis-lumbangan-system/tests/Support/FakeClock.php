<?php

class FakeClock implements ClockInterface
{
    private $now;

    public function __construct($start = '2026-04-12 09:00:00')
    {
        $this->now = new DateTimeImmutable($start);
    }

    public function now()
    {
        return $this->now;
    }

    public function travelMinutes($minutes)
    {
        $this->now = $this->now->modify(($minutes >= 0 ? '+' : '') . (int) $minutes . ' minutes');
    }

    public function travelSeconds($seconds)
    {
        $this->now = $this->now->modify(($seconds >= 0 ? '+' : '') . (int) $seconds . ' seconds');
    }
}
