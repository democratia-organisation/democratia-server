<?php

namespace Koyok\democratia\Extension;

use DateInterval;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final class ClockImplementation implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now')->add(new DateInterval('PT60S'));

    }
}
