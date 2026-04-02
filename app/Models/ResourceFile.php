<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'resource_id',
    'uploader_id',
    'entry_key',
    'name',
    'status',
    'platform',
    'language',
    'size',
    'code',
    'extract_code',
    'uploaded_at',
    'download_detail',
    'download_url',
    'uploader_name',
    'uploader_avatar',
    'action_label',
])]
class ResourceFile extends Model
{
    /**
     * Get the parent resource for the current downloadable file.
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    /**
     * Get the user who uploaded or last maintained this downloadable file.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
