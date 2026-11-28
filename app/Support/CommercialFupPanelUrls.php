<?php

namespace App\Support;

use App\Filament\Resources\CommercialFups\CommercialFupResource;
use App\Models\CommercialFup;
use App\Models\CommercialFupAuthorizationRequest;

final class CommercialFupPanelUrls
{
    public const EDIT_TAB_AUTORIZACIONES = 'autorizaciones';

    public static function editAuthorizationTab(CommercialFup $fup): string
    {
        return CommercialFupResource::getUrl('edit', [
            'record' => $fup,
        ]).'?tab='.self::EDIT_TAB_AUTORIZACIONES;
    }

    public static function reviewAuthorization(CommercialFup $fup, CommercialFupAuthorizationRequest $authorizationRequest): string
    {
        return CommercialFupResource::getUrl('review-authorization', [
            'record' => $fup,
            'authorizationRequest' => $authorizationRequest,
        ]);
    }
}