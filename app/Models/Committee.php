<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Committee extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'amount', 'balance', 'period', 'status'];

    public function currentOpeningBalance()
    {
        return $this->hasOne(MasterOpeningBalance::class, 'master_id')
            ->where('master_type', 'committee')
            ->where('financial_year', MasterOpeningBalance::getCurrentFinancialYear());
    }
}
