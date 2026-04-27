<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'account_name',
        'account_number',
        'ifsc_code',
        'branch_name',
        'balance',
        'status',
    ];

    public function currentOpeningBalance()
    {
        return $this->hasOne(MasterOpeningBalance::class, 'master_id')
            ->where('master_type', 'bank_account')
            ->where('financial_year', MasterOpeningBalance::getCurrentFinancialYear());
    }
}
