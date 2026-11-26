<?php

namespace App\Models;

use App\Enums\AppModule;
use App\Enums\ModulePermissionLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleMembership extends Model
{
    protected $fillable = [
        'user_id',
        'module',
        'permission_level',
    ];

    protected function casts(): array
    {
        return [
            'module' => AppModule::class,
            'permission_level' => ModulePermissionLevel::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}