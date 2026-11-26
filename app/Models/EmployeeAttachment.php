<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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

        static::saving(function (EmployeeAttachment $attachment): void {
            if (blank($attachment->title) && filled($attachment->original_name)) {
                $attachment->title = $attachment->original_name;
            }

            if (filled($attachment->file_path) && blank($attachment->mime_type)) {
                $disk = Storage::disk('public');

                if ($disk->exists($attachment->file_path)) {
                    try {
                        $attachment->mime_type = $disk->mimeType($attachment->file_path);
                        $attachment->file_size ??= $disk->size($attachment->file_path);
                    } catch (\Throwable) {
                        //
                    }
                }
            }
        });
    }

    public function displayName(): string
    {
        if (filled($this->title)) {
            return $this->title;
        }

        if (filled($this->original_name)) {
            return $this->original_name;
        }

        return $this->file_path ? basename($this->file_path) : 'Archivo';
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
