<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const GENDER_MAP = [
        'masculino' => 'masculino',
        'Masculino' => 'masculino',
        'MASCULINO' => 'masculino',
        'femenino' => 'femenino',
        'Femenino' => 'femenino',
        'FEMENINO' => 'femenino',
        'otro' => 'otro',
        'Otro' => 'otro',
        'OTRO' => 'otro',
    ];

    private const MARITAL_MAP = [
        'soltero' => 'soltero',
        'Soltero' => 'soltero',
        'casado' => 'casado',
        'Casado' => 'casado',
        'divorciado' => 'divorciado',
        'Divorciado' => 'divorciado',
        'viudo' => 'viudo',
        'Viudo' => 'viudo',
        'union libre' => 'union_libre',
        'Union libre' => 'union_libre',
        'Unión libre' => 'union_libre',
        'union_libre' => 'union_libre',
        'separado' => 'separado',
        'Separado' => 'separado',
    ];

    public function up(): void
    {
        foreach (DB::table('employees')->select('id', 'gender', 'marital_status')->cursor() as $row) {
            $gender = $row->gender;
            $marital = $row->marital_status;

            if (filled($gender) && isset(self::GENDER_MAP[$gender])) {
                $gender = self::GENDER_MAP[$gender];
            } elseif (filled($gender)) {
                $gender = strtolower($gender);
            }

            if (filled($marital) && isset(self::MARITAL_MAP[$marital])) {
                $marital = self::MARITAL_MAP[$marital];
            } elseif (filled($marital)) {
                $normalized = strtolower(str_replace(' ', '_', $marital));
                $marital = self::MARITAL_MAP[$marital] ?? $normalized;
            }

            DB::table('employees')->where('id', $row->id)->update([
                'gender' => $gender,
                'marital_status' => $marital,
            ]);
        }
    }

    public function down(): void
    {
        //
    }
};