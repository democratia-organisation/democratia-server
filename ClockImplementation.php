<?php

namespace Koyok\democratia\Extension\Clock;

use DateInterval;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final class ClockImplementation implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        $marge = 60;

        return new DateTimeImmutable('now')->add(new DateInterval('PT2S'));

    }
}

final class FroozenClockImplementation implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable(DateTimeImmutable::createFromFormat('j-M-Y', '15-01-2026'));

    }
}
