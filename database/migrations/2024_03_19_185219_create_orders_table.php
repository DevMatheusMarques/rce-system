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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->text('internal_information')->nullable();
            $table->unsignedBigInteger('user_id');// usuário que realizou um pedido
            $table->unsignedBigInteger('requester_user_id'); // usuário que solicitou um pedido
            $table->enum('status', ['in_progress', 'completed', 'canceled'])->default('in_progress');
            $table->string('order_proof_path', 400)->nullable();
            $table->timestamps();

            //create foreign
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('requester_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
