<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterOrderRemark extends Model
{
    use HasFactory;

    protected $table = 'master_order_remarks';
    protected $fillable = ['name', 'status'];
}
