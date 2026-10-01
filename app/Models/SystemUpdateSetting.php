<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemUpdateSetting extends Model
{
    use HasFactory;

    protected $table = 'system_update_settings';

    protected $fillable = [
        'protocol',
        'repository_url',
        'branch',
        'ssh_private_key_path',
        'ssh_public_key',
        'https_token',
        'webhook_secret',
        'auto_run_composer',
        'auto_run_migrations',
        'auto_run_optimize',
        'auto_run_queue_restart',
        'auto_run_npm_build',
        'last_deployed_at',
        'last_deployed_commit',
        'last_deployed_duration',
        'last_deployment_status',
        'last_deployment_log',
    ];

    protected $casts = [
        'auto_run_composer' => 'boolean',
        'auto_run_migrations' => 'boolean',
        'auto_run_optimize' => 'boolean',
        'auto_run_queue_restart' => 'boolean',
        'auto_run_npm_build' => 'boolean',
        'last_deployed_at' => 'datetime',
        'last_deployed_duration' => 'integer',
    ];

    /**
     * Retrieve the singleton settings instance or instantiate with sensible defaults.
     */
    public static function getSettings(): self
    {
        $settings = static::first();

        if (!$settings) {
            $settings = static::create([
                'protocol' => 'ssh',
                'repository_url' => 'git@github.com:maccpro/mondolshopbd.git',
                'branch' => 'main',
                'webhook_secret' => \Illuminate\Support\Str::random(32),
                'auto_run_composer' => true,
                'auto_run_migrations' => true,
                'auto_run_optimize' => true,
                'auto_run_queue_restart' => true,
                'auto_run_npm_build' => false,
            ]);
        } elseif (empty($settings->webhook_secret)) {
            $settings->update([
                'webhook_secret' => \Illuminate\Support\Str::random(32),
            ]);
        }

        return $settings;
    }
}
