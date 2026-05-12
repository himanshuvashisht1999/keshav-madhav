<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop legacy table if exists
        Schema::dropIfExists('stock_disposals');

        Schema::create('stock_disposal_mains', function (Blueprint $table) {
            $table->id();
            $table->string('disposal_no')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('item_type', ['fabric', 'domestic']);
            $table->string('reason');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_disposal_mains');
    }
};
