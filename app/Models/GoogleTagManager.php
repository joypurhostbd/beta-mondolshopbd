<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class GoogleTagManager extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'status',
        'is_server_side',
        'server_container_url',
        'measurement_id',
        'api_secret',
        'custom_loader_domain',
        'description',
    ];

    protected $casts = [
        'status' => 'integer',
        'is_server_side' => 'boolean',
        'custom_loader_domain' => 'boolean',
    ];

    public function eventConfigs(): HasMany
    {
        return $this->hasMany(TagManagerEventConfig::class, 'tag_manager_id');
    }

    public function setCodeAttribute($value): void
    {
        $trimmed = strtoupper(trim((string) $value));
        if (!empty($trimmed) && !str_starts_with($trimmed, 'GTM-')) {
            $trimmed = 'GTM-' . $trimmed;
        }
        $this->attributes['code'] = $trimmed;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    public function isServerSide(): bool
    {
        return (bool) $this->is_server_side;
    }

    public function hasServerSide(): bool
    {
        return (bool) $this->is_server_side && !empty($this->server_container_url);
    }

    public function getEffectiveScriptBaseUrl(): string
    {
        if ($this->hasServerSide() && (bool) $this->custom_loader_domain) {
            return rtrim($this->server_container_url, '/');
        }

        return 'https://www.googletagmanager.com';
    }

    public function isWebEventEnabled(string $eventKey): bool
    {
        if (!$this->relationLoaded('eventConfigs')) {
            $this->load('eventConfigs');
        }

        $config = $this->eventConfigs->firstWhere('event_key', $eventKey);

        return $config ? (bool) $config->is_web_enabled : true;
    }

    public function isServerEventEnabled(string $eventKey): bool
    {
        if (!$this->hasServerSide()) {
            return false;
        }

        if (!$this->relationLoaded('eventConfigs')) {
            $this->load('eventConfigs');
        }

        $config = $this->eventConfigs->firstWhere('event_key', $eventKey);

        return $config ? (bool) $config->is_server_enabled : true;
    }
}
