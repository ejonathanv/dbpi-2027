<?php

namespace App\Support;

class HelpdeskTimeOptions
{
    /** @return array<int, int|string> */
    public static function workHours(): array
    {
        return collect(range(1, 10))
            ->mapWithKeys(fn (int $hour): array => [$hour => $hour])
            ->all();
    }

    /** @return array<int, string> */
    public static function minutes(): array
    {
        return collect(range(0, 55, 5))
            ->mapWithKeys(fn (int $minute): array => [$minute => sprintf('%02d', $minute)])
            ->all();
    }

    /** @return array<int, int> */
    public static function travelHours(): array
    {
        return collect(range(0, 24))
            ->mapWithKeys(fn (int $hour): array => [$hour => $hour])
            ->all();
    }
}
