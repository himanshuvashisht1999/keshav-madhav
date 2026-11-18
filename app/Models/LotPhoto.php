<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A photo sent by a Stitching unit for one of its lots (worker app > Lot Photo).
 * status 1 = shown, 0 = deleted (the row and the file are kept; removed_by / removed_at say who and when).
 */
class LotPhoto extends Model
{
    protected $table = 'lot_photos';

    protected $fillable = [
        'lot_no',
        'stage_master_unit_id',
        'image',
        'status',
        'removed_by',
        'removed_at',
    ];

    protected $casts = [
        'removed_at' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(StageMasterUnit::class, 'stage_master_unit_id');
    }

    public function getUrlAttribute()
    {
        return asset(\App\Services\LotPhotoService::FOLDER . '/' . $this->image);
    }
}
