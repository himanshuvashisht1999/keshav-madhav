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
        Schema::create('production_goods', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('name_of_garment')->nullable();
            $table->string('type_of_garment')->nullable();
            $table->integer('master_size_id')->nullable();
            $table->string('garment_pattern')->nullable();
            $table->string('fabric_sku')->nullable();
            $table->integer('master_color_id')->nullable();
            $table->integer('is_printing')->default('0');
            $table->integer('is_embroidery')->default('0');
            $table->integer('printing_stage_after')->default('1');
            $table->integer('embroidery_stage_after')->default('1');
            $table->string('design_number')->nullable();
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
        Schema::dropIfExists('production_goods');
    }
};
