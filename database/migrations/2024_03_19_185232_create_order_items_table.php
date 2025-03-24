<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');// fk orders.id
            $table->unsignedBigInteger('product_id');// fk products.id
            $table->string('product_name', 255); //products.name
            $table->integer('product_quantity')->default(0);
            $table->enum('status', ['approved', 'refused'])->default('approved');
//            $table->unique(['order_id', 'product_id']);//valores únicos juntos
            $table->timestamps();

            //crete foreign
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
