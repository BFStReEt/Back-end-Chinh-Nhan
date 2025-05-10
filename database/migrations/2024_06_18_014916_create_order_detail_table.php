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
        Schema::create('order_detail', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->unsignedInteger('order_id')->length(11);
            $table->foreign('order_id')
            ->references('order_id')
            ->on('order_sum')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('item_type',150)->default('product');
            $table->integer('item_id')->length(11)->default('0');
            $table->integer('quantity')->length(11)->default('0');
            $table->string('item_title',250)->nullable()->default('NULL');
            $table->string('item_price',150)->default('0');
            $table->integer('subtotal')->length(11)->default('0');
            $table->string('add_from',50)->nullable()->default('web');
            $table->timestamp('create_at');
            $table->timestamp('update_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_detail');
    }
};
