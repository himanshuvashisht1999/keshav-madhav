<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricReceiptOtherImage extends Model
{
    use HasFactory;
    protected $fillable = ['fabric_receipt_id', 'image'];
}
