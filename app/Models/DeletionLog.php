<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletionLog extends Model
{
    use HasFactory;

    protected $table = 'deletion_logs';

    protected $fillable = [
        'module',
        'record_id',
        'payload',
        'deleted_by',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
