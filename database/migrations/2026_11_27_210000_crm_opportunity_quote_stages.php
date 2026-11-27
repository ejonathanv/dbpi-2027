<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('crm_opportunities')->where('stage', 'prospecto')->update(['stage' => 'prospeccion']);
        DB::table('crm_opportunities')->where('stage', 'calificacion')->update(['stage' => 'prospeccion']);
        DB::table('crm_opportunities')->where('stage', 'propuesta')->update(['stage' => 'cotizacion_en_proceso']);
        DB::table('crm_opportunities')->where('stage', 'negociacion')->update(['stage' => 'negociacion_revision']);
        DB::table('crm_opportunities')->where('stage', 'ganada')->update(['stage' => 'cerrado_ganado']);
        DB::table('crm_opportunities')->where('stage', 'perdida')->update(['stage' => 'cerrado_perdido']);

        if (Schema::hasColumn('crm_opportunities', 'stage')) {
            DB::statement("UPDATE crm_opportunities SET stage = 'prospeccion' WHERE stage NOT IN (
                'prospeccion',
                'cotizacion_en_proceso',
                'autorizacion_interna',
                'cotizacion_enviada',
                'negociacion_revision',
                'cerrado_ganado',
                'cerrado_perdido'
            )");
        }
    }

    public function down(): void
    {
        DB::table('crm_opportunities')->where('stage', 'prospeccion')->update(['stage' => 'prospecto']);
        DB::table('crm_opportunities')->where('stage', 'cotizacion_en_proceso')->update(['stage' => 'propuesta']);
        DB::table('crm_opportunities')->where('stage', 'negociacion_revision')->update(['stage' => 'negociacion']);
        DB::table('crm_opportunities')->where('stage', 'cerrado_ganado')->update(['stage' => 'ganada']);
        DB::table('crm_opportunities')->where('stage', 'cerrado_perdido')->update(['stage' => 'perdida']);
    }
};