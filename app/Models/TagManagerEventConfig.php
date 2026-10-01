<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagManagerEventConfig extends Model
{
    use HasFactory;

    protected $table = 'tag_manager_event_configs';

    protected $fillable = [
        'tag_manager_id',
        'event_key',
        'is_web_enabled',
        'is_server_enabled',
        'custom_event_name',
        'parameters',
    ];

    protected $casts = [
        'is_web_enabled' => 'boolean',
        'is_server_enabled' => 'boolean',
        'parameters' => 'array',
    ];

    public function tagManager(): BelongsTo
    {
        return $this->belongsTo(GoogleTagManager::class, 'tag_manager_id');
    }
}
