<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAttachment extends Model
{
    protected $fillable = [
        'employee_id',
        'uploaded_by',
        'title',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (EmployeeAttachment $attachment): void {
            $attachment->uploaded_by ??= auth()->id();
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}