<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasTable('sales_men')) {
    Schema::create('sales_men', function(Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('phone');
        $table->string('email')->nullable();
        $table->text('address')->nullable();
        $table->tinyInteger('status')->default(1);
        $table->timestamps();
        $table->softDeletes();
    });
    echo "Table created successfully.\n";
} else {
    echo "Table already exists.\n";
}
