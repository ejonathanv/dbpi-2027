<?php

namespace App\Support;

final class PanelBrandColors
{
    public const NAVY = '#20234a';

    /**
     * Paleta primaria del panel: tonos de interacción (400–700) = NAVY, sin aclarados automáticos.
     *
     * @return array<int, string>
     */
    public static function primary(): array
    {
        $navy = self::NAVY;

        return [
            50 => '#f2f3f7',
            100 => '#e5e6ee',
            200 => '#cbcedd',
            300 => '#9fa3bc',
            400 => $navy,
            500 => $navy,
            600 => $navy,
            700 => $navy,
            800 => '#1a1c3c',
            900 => '#15172f',
            950 => '#0e0f1f',
        ];
    }
}
