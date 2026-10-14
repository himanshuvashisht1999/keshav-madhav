<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Audit trail of every balance / opening correction made by the balance repair tool. */
return new class extends Migration
{
    public function up()
    {
        Schema::create('balance_corrections', function (Blueprint $table) {
            $table->id();
            $table->string('ledger_type');
            $table->unsignedBigInteger('party_id');
            $table->string('party_name')->nullable();
            $table->string('field'); // balance | opening
            $table->decimal('old_value', 15, 2);
            $table->decimal('new_value', 15, 2);
            $table->string('group', 2);
            $table->text('reason')->nullable();
            $table->string('applied_by')->nullable();
            $table->timestamps();
            $table->index(['ledger_type', 'party_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('balance_corrections');
    }
};
