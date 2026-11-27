<?php

namespace App\Support;

final class WeekdayOptions
{
    /** @return array<int, string> */
    public static function labels(): array
    {
        return [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];
    }
}
