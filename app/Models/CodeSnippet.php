<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeSnippet extends Model
{
    use HasFactory;

    protected $table = 'code_snippets';

    protected $fillable = [
        'title',
        'type',
        'location',
        'code',
        'status',
        'priority',
        'device_target',
        'target_pages',
        'custom_page_urls',
        'auth_condition',
        'description',
    ];

    protected $casts = [
        'status' => 'boolean',
        'priority' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'asc')->orderBy('id', 'asc');
    }

    public function scopeLocation($query, string $location)
    {
        return $query->where('location', $location);
    }
}
