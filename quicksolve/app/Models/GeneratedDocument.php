<?php

namespace App\Models;

use App\Enums\DocumentType;
use Database\Factories\GeneratedDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedDocument extends Model
{
    /** @use HasFactory<GeneratedDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'title',
        'private_file_path',
        'structured_data',
    ];

    protected $hidden = [
        'private_file_path',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'structured_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
