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

    /**
     * An opening balance is the party's starting balance - it does NOT belong to a financial year.
     * Every party has exactly one row; this fixed value is stored in the (legacy) financial_year
     * column so lookups, updateOrCreate and the unique key all point at that single row.
     * Years are only a view filter on the ledgers.
     */
    public const OPENING_KEY = 'OPENING';

    public static function openingKey()
    {
        return self::OPENING_KEY;
    }

    /** Real current financial year (April-March), for display / filters only - never for opening balances. */
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

    /** A party's opening balance as a signed number (Credit = +, Debit = -). 0 when none is saved. */
    public static function signedOpening(string $masterType, $partyId): float
    {
        $row = self::where('master_type', $masterType)
            ->where('master_id', $partyId)
            ->where('financial_year', self::OPENING_KEY)
            ->first();

        if (!$row) {
            return 0.0;
        }

        return strtolower(trim((string) $row->balance_type)) === 'debit' ? -(float) $row->amount : (float) $row->amount;
    }

    /**
     * Change a party's opening balance from an edit form WITHOUT losing its transactions.
     *
     * The opening row is saved and the party's running balance moves by exactly the change in
     * the opening (never replaced by it). $amount === null means the form did not send the field:
     * nothing changes. Sets $party->balance; the caller saves the party in the same transaction.
     */
    public static function applyOpeningChange(Model $party, string $masterType, $amount, $balanceType): void
    {
        if ($amount === null) {
            return;
        }

        $newAmount = abs((float) $amount);
        $newType = strtolower(trim((string) $balanceType)) === 'debit' ? 'Debit' : 'Credit';
        $newSigned = $newType === 'Debit' ? -$newAmount : $newAmount;
        $oldSigned = self::signedOpening($masterType, $party->id);

        self::updateOrCreate(
            ['master_type' => $masterType, 'master_id' => $party->id, 'financial_year' => self::OPENING_KEY],
            ['amount' => $newAmount, 'balance_type' => $newType]
        );

        $party->balance = round((float) $party->balance + ($newSigned - $oldSigned), 2);
    }

    public static function getTotalOpeningBalance($masterType, $financialYear = null)
    {
        $records = self::where('master_type', $masterType)
            ->where('financial_year', self::OPENING_KEY)
            ->get();

        return $records->sum(function ($item) {
            return $item->balance_type == 'Credit' ? $item->amount : -$item->amount;
        });
    }
}
