<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_category'); // fabric_shipment, agent_order, etc.

            // The entity involved in the payment (Vendor, SalesAgent, Customer, etc.)
            $table->string('party_type')->nullable();
            $table->unsignedBigInteger('party_id')->nullable();

            // The specific item being paid for (FabricReceipt, AgentOrder, etc.) - Optional
            $table->string('paymentable_type')->nullable();
            $table->unsignedBigInteger('paymentable_id')->nullable();

            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_mode'); // Cash, Cheque, Online, etc.
            $table->string('reference_id')->nullable(); // Cheque No, Transaction ID
            $table->text('remarks')->nullable();
            $table->string('image')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
