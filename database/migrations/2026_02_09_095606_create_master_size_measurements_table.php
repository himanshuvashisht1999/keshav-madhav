<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_size_measurements', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->integer('corporate_company_id')->nullable();
            $table->string('design_number')->nullable();
            $table->string('sku')->nullable();
            $table->integer('size_type')->default('0');
            $table->string('name')->nullable();
            $table->string('set_size')->nullable();
            $table->integer('no_of_pcs')->nullable();
            $table->string('size_group')->nullable();
            $table->string('size_selection')->nullable();
            $table->integer('measurement')->nullable();
            $table->string('base_cloth_consumption')->nullable();
            $table->integer('status')->default('1');
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
        Schema::dropIfExists('master_size_measurements');
    }
};
