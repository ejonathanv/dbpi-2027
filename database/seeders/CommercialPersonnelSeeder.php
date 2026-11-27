<?php

namespace Database\Seeders;

use App\Models\CommercialPersonnel;
use Illuminate\Database\Seeder;

class CommercialPersonnelSeeder extends Seeder
{
    public function run(): void
    {
        $resources = [
            ['name' => 'Técnico instalador junior', 'daily_cost_mxn' => 1800.00, 'daily_cost_usd' => 105.00],
            ['name' => 'Técnico instalador senior', 'daily_cost_mxn' => 2800.00, 'daily_cost_usd' => 165.00],
            ['name' => 'Ingeniero de campo', 'daily_cost_mxn' => 4500.00, 'daily_cost_usd' => 265.00],
            ['name' => 'Ingeniero de proyectos', 'daily_cost_mxn' => 5500.00, 'daily_cost_usd' => 320.00],
            ['name' => 'Project manager', 'daily_cost_mxn' => 7500.00, 'daily_cost_usd' => 440.00],
            ['name' => 'Supervisor de obra', 'daily_cost_mxn' => 4000.00, 'daily_cost_usd' => 235.00],
            ['name' => 'Certificador fibra óptica', 'daily_cost_mxn' => 5000.00, 'daily_cost_usd' => 295.00],
            ['name' => 'Especialista CCTV / videovigilancia', 'daily_cost_mxn' => 3500.00, 'daily_cost_usd' => 205.00],
            ['name' => 'Especialista control de acceso', 'daily_cost_mxn' => 3800.00, 'daily_cost_usd' => 225.00],
            ['name' => 'Especialista VoIP / telefonía IP', 'daily_cost_mxn' => 4200.00, 'daily_cost_usd' => 245.00],
            ['name' => 'Electricista certificado', 'daily_cost_mxn' => 3200.00, 'daily_cost_usd' => 188.00],
            ['name' => 'Ingeniero de preventa', 'daily_cost_mxn' => 6500.00, 'daily_cost_usd' => 380.00],
        ];

        foreach ($resources as $resource) {
            CommercialPersonnel::query()->updateOrCreate(
                ['name' => $resource['name']],
                [
                    'daily_cost_mxn' => $resource['daily_cost_mxn'],
                    'daily_cost_usd' => $resource['daily_cost_usd'],
                    'is_active' => true,
                ],
            );
        }
    }
}