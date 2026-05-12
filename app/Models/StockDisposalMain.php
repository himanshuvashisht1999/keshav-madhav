<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockDisposalMain extends Model
{
    use HasFactory;

    protected $fillable = [
        'disposal_no',
        'user_id',
        'item_type',
        'reason',
        'remarks'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(StockDisposalItem::class, 'stock_disposal_main_id');
    }
}
