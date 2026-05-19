<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterOpeningBalance extends Model
{
    use HasFactory;

    protected $table = 'payment_opening_balances';

    protected $fillable = [
        'master_type',
        'master_id',
        'financial_year',
        'amount',
        'balance_type',
    ];

    public static function getCurrentFinancialYear()
    {
        $month = date('m');
        $year = date('Y');
        if ($month >= 4) {
            $fy = $year . '-' . ($year + 1);
        } else {
            $fy = ($year - 1) . '-' . $year;
        }
        return $fy;
    }

    public static function getFinancialYearForDate($date)
    {
        if (empty($date)) {
            return self::getCurrentFinancialYear();
        }
        $time = strtotime($date);
        $month = (int)date('m', $time);
        $year = (int)date('Y', $time);
        if ($month >= 4) {
            $fy = $year . '-' . ($year + 1);
        } else {
            $fy = ($year - 1) . '-' . $year;
        }
        return $fy;
    }

    public static function getTotalOpeningBalance($masterType, $financialYear = null)
    {
        if (!$financialYear) {
            $financialYear = self::getCurrentFinancialYear();
        }

        $records = self::where('master_type', $masterType)
            ->where('financial_year', $financialYear)
            ->get();

        return $records->sum(function ($item) {
            return $item->balance_type == 'Credit' ? $item->amount : -$item->amount;
        });
    }
}
