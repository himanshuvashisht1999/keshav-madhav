<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Direct map for known inventory records with 0 total_boxes
        $directUpdates = [
            // Session 1133 (Stock Consume)
            6642 => 1,
            6643 => 1,
            6644 => 2,
            6645 => 2,

            // Session 1132 (Stock Consume)
            6630 => 1,
            6631 => 1,
            6632 => 1,
            6633 => 1,
            6634 => 1,
            6635 => 1,
            6636 => 1,
            6637 => 1,
            6638 => 1,
            6639 => 1,
            6640 => 1,
            6641 => 1,

            // Session 1131 (Stock Consume)
            6627 => 1,
            6628 => 1,
            6629 => 1,

            // Session 1129
            6626 => 1,

            // Session 1127
            6624 => 2,

            // Session 1105
            6597 => 2,

            // Session 1090
            6589 => 1,
            6592 => 1,

            // Session 1087
            6576 => 1,
            6577 => 1,

            // Session 1084
            6572 => 1,
            6573 => 1,
            6574 => 3,

            // Session 1077
            6562 => 1,
            6563 => 1,

            // Session 1076
            6560 => 1,
            6561 => 1,

            // Session 1054
            6506 => 1,

            // Session 1052
            6505 => 1,

            // Session 1051
            6499 => 1,
            6500 => 1,
            6501 => 1,
            6502 => 1,
            6503 => 1,
            6504 => 1,
        ];

        foreach ($directUpdates as $id => $targetBoxes) {
            DB::table('domestic_inventories')
                ->where('id', $id)
                ->where('total_boxes', '<', $targetBoxes)
                ->update(['total_boxes' => $targetBoxes]);
        }

        // 2. Semantic fallback for live environments where auto-increment IDs might differ:
        // Match by (packing_main_id, product_id, color_id, size_set_id)
        $semanticItems = [
            // Session 1133
            ['session' => 1133, 'prod' => 691, 'color' => 16, 'size' => 58, 'boxes' => 1],
            ['session' => 1133, 'prod' => 691, 'color' => 47, 'size' => 58, 'boxes' => 1],
            ['session' => 1133, 'prod' => 715, 'color' => 16, 'size' => 58, 'boxes' => 2],
            ['session' => 1133, 'prod' => 725, 'color' => 16, 'size' => 58, 'boxes' => 2],

            // Session 1132
            ['session' => 1132, 'prod' => 686, 'color' => 16, 'size' => 12, 'boxes' => 1],
            ['session' => 1132, 'prod' => 686, 'color' => 10, 'size' => 12, 'boxes' => 1],
            ['session' => 1132, 'prod' => 686, 'color' => 16, 'size' => 58, 'boxes' => 1],
            ['session' => 1132, 'prod' => 686, 'color' => 10, 'size' => 58, 'boxes' => 1],
            ['session' => 1132, 'prod' => 690, 'color' => 16, 'size' => 12, 'boxes' => 1],
            ['session' => 1132, 'prod' => 690, 'color' => 37, 'size' => 12, 'boxes' => 1],
            ['session' => 1132, 'prod' => 690, 'color' => 16, 'size' => 58, 'boxes' => 1],
            ['session' => 1132, 'prod' => 690, 'color' => 37, 'size' => 58, 'boxes' => 1],
            ['session' => 1132, 'prod' => 694, 'color' => 47, 'size' => 2,  'boxes' => 1],
            ['session' => 1132, 'prod' => 694, 'color' => 16, 'size' => 2,  'boxes' => 1],
            ['session' => 1132, 'prod' => 694, 'color' => 47, 'size' => 3,  'boxes' => 1],
            ['session' => 1132, 'prod' => 694, 'color' => 16, 'size' => 3,  'boxes' => 1],

            // Session 1131
            ['session' => 1131, 'prod' => 440, 'color' => 4,  'size' => 2,  'boxes' => 1],
            ['session' => 1131, 'prod' => 440, 'color' => 12, 'size' => 2,  'boxes' => 1],
            ['session' => 1131, 'prod' => 440, 'color' => 1,  'size' => 2,  'boxes' => 1],

            // Session 1129
            ['session' => 1129, 'prod' => 698, 'color' => 112,'size' => 5,  'boxes' => 1],

            // Session 1127
            ['session' => 1127, 'prod' => 291, 'color' => 5,  'size' => 3,  'boxes' => 2],

            // Session 1105
            ['session' => 1105, 'prod' => 701, 'color' => 4,  'size' => 3,  'boxes' => 2],

            // Session 1090
            ['session' => 1090, 'prod' => 26,  'color' => 16, 'size' => 62, 'boxes' => 1],
            ['session' => 1090, 'prod' => 26,  'color' => 4,  'size' => 62, 'boxes' => 1],

            // Session 1087
            ['session' => 1087, 'prod' => 19,  'color' => 42, 'size' => 2,  'boxes' => 1],
            ['session' => 1087, 'prod' => 19,  'color' => 40, 'size' => 2,  'boxes' => 1],

            // Session 1084
            ['session' => 1084, 'prod' => 432, 'color' => 69, 'size' => 193,'boxes' => 1],
            ['session' => 1084, 'prod' => 432, 'color' => 69, 'size' => 194,'boxes' => 1],
            ['session' => 1084, 'prod' => 432, 'color' => 69, 'size' => 195,'boxes' => 3],

            // Session 1077
            ['session' => 1077, 'prod' => 846, 'color' => 1,  'size' => 83, 'boxes' => 1],
            ['session' => 1077, 'prod' => 846, 'color' => 2,  'size' => 83, 'boxes' => 1],

            // Session 1076
            ['session' => 1076, 'prod' => 667, 'color' => 1,  'size' => 137,'boxes' => 1],
            ['session' => 1076, 'prod' => 667, 'color' => 12, 'size' => 137,'boxes' => 1],

            // Session 1054
            ['session' => 1054, 'prod' => 844, 'color' => 37, 'size' => 58, 'boxes' => 1],

            // Session 1052
            ['session' => 1052, 'prod' => 844, 'color' => 16, 'size' => 58, 'boxes' => 1],

            // Session 1051
            ['session' => 1051, 'prod' => 846, 'color' => 1,  'size' => 3,   'boxes' => 1],
            ['session' => 1051, 'prod' => 846, 'color' => 2,  'size' => 3,   'boxes' => 1],
            ['session' => 1051, 'prod' => 845, 'color' => 12, 'size' => 58,  'boxes' => 1],
            ['session' => 1051, 'prod' => 845, 'color' => 1,  'size' => 58,  'boxes' => 1],
            ['session' => 1051, 'prod' => 844, 'color' => 37, 'size' => 12,  'boxes' => 1],
            ['session' => 1051, 'prod' => 844, 'color' => 16, 'size' => 12,  'boxes' => 1],
        ];

        foreach ($semanticItems as $item) {
            DB::table('domestic_inventories')
                ->where('packing_main_id', $item['session'])
                ->where('product_id', $item['prod'])
                ->where('color_id', $item['color'])
                ->where('size_set_id', $item['size'])
                ->where('total_boxes', '<', $item['boxes'])
                ->update(['total_boxes' => $item['boxes']]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reversal not recommended as it restores valid stock
    }
};
