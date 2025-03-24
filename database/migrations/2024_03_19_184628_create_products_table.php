<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->integer('stock')->nullable()->default(0);
            $table->integer('reserved')->nullable()->default(0);
            $table->integer('processing')->nullable()->default(0);
            $table->integer('minimum');
            $table->enum('category', array('toner', 'paper', 'form', 'cartridge', 'ribbon', 'desk', 'others'));
            $table->enum('status', array('active', 'inactive'))->nullable()->default('active');
            $table->string('description', 400)->nullable()->default(null);
            $table->string('picture_path', 200)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
