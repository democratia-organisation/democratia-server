<?php

namespace Tests;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final class FroozenClockImplementation implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable(DateTimeImmutable::createFromFormat('j-M-Y', '15-01-2026'));

    }
}
