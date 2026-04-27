<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineryMaster extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'balance', 'status'];

    public function currentOpeningBalance()
    {
        return $this->hasOne(MasterOpeningBalance::class, 'master_id')
            ->where('master_type', 'machinery')
            ->where('financial_year', MasterOpeningBalance::getCurrentFinancialYear());
    }
}
