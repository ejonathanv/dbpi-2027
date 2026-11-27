<?php

namespace App\Models;

use App\Enums\CommercialQuoteAttachmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CommercialQuoteAttachment extends Model
{
    protected $fillable = [
        'commercial_quote_id',
        'type',
        'title',
        'file_path',
        'uploaded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => CommercialQuoteAttachmentType::class,
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(CommercialQuote::class, 'commercial_quote_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function downloadUrl(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}