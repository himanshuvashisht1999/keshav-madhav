<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Storeroom extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'description', 'status', 'order_taken', 'order_priority', 'order_dispatch', 'order_more_than_stock'];

    private static ?bool $hasOverStockColumn = null;

    public function racks()
    {
        return $this->hasMany(Rack::class, 'storeroom_id');
    }

    /**
     * SQL condition "this storeroom lets orders take more boxes than are in stock" (setting Order more than stock = Yes).
     * Before the database update has added that setting, the old rule (storeroom named ADVANCE SAMPLE) is used.
     */
    public static function hasOverStockSetting(): bool
    {
        return self::$hasOverStockColumn ??= \Illuminate\Support\Facades\Schema::hasColumn('storerooms', 'order_more_than_stock');
    }

    public static function overStockSql(string $alias = 'storerooms'): string
    {
        return self::hasOverStockSetting()
            ? "{$alias}.order_more_than_stock = 'Yes'"
            : "UPPER(TRIM({$alias}.name)) = 'ADVANCE SAMPLE'";
    }
}
