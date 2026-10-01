<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemDeploymentLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'system_deployment_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'status',
        'commit_hash',
        'commit_message',
        'author',
        'trigger_type',
        'duration_seconds',
        'log_output',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'duration_seconds' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope a query to sort by newest first.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('id', 'desc');
    }

    /**
     * Get short commit hash.
     */
    public function getShortCommitAttribute(): string
    {
        return $this->commit_hash ? substr($this->commit_hash, 0, 8) : 'N/A';
    }
}
