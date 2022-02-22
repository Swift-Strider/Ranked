<?php

declare(strict_types=1);

namespace DiamondStrider1\Ranked\database;

class DateUtils
{
    public static function currentTime(): string
    {
        return self::time2string(time());
    }

    public static function time2string(int $timestamp): string
    {
        return date('Y-m-d H:i:s', $timestamp);
    }
}
