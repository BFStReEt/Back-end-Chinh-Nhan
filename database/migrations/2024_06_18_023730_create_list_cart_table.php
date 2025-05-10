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
        Schema::create('list_cart', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->unsignedInteger('mem_id')->length(11);
            $table->foreign('mem_id')
            ->references('id')
            ->on('members')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('mem_name',255)->nullable();
            $table->string('md5_id', 255);
            $table->bigInteger('product_id')->length(11)->unsigned();
            $table->foreign('product_id')
            ->references('product_id')
            ->on('product')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->unsignedInteger('stock')->length(11);
            $table->integer('quality')->length(11);
            $table->integer('price')->length(11);
            $table->string('title', 255);
            $table->integer('status')->length(11);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_cart');
    }
};
