<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampleBatch extends Model
{
    use HasFactory;

    protected $fillable = ['batch_no'];

    public function products()
    {
        return $this->hasMany(SampleProduct::class, 'sample_batch_id');
    }
}
