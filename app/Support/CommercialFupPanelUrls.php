<?php

namespace App\Support;

use App\Filament\Resources\CommercialFups\CommercialFupResource;
use App\Models\CommercialFup;

final class CommercialFupPanelUrls
{
    public const EDIT_TAB_AUTORIZACIONES = 'autorizaciones';

    public static function editAuthorizationTab(CommercialFup $fup): string
    {
        return CommercialFupResource::getUrl('edit', [
            'record' => $fup,
        ]).'?tab='.self::EDIT_TAB_AUTORIZACIONES;
    }
}