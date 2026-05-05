<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FairBatch extends Model
{
    use HasFactory;

    protected $fillable = ['batch_no'];

    public function products()
    {
        return $this->hasMany(FairProduct::class, 'fair_batch_id');
    }
}
