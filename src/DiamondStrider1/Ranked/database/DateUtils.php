<?php

declare(strict_types=1);

namespace DiamondStrider1\Ranked\database;

use InvalidArgumentException;

class DateUtils
{
    public static function currentTimeString(): string
    {
        return self::time2string(self::currentTime());
    }

    public static function currentTime(): int
    {
        return time();
    }

    public static function time2string(int $timestamp): string
    {
        return date('Y-m-d H:i:s', $timestamp);
    }

    public static function string2time(string $datetime): int
    {
        $time = strtotime($datetime);
        if (false === $time) {
            throw new InvalidArgumentException('Invalid $datetime given!');
        }

        return $time;
    }
}
