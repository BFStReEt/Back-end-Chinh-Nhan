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
        Schema::create('product_cat_option', function (Blueprint $table) {
            $table->increments('op_id',11)->length(11);
            $table->unsignedInteger('cat_id')->lenght(11);
            $table->foreign('cat_id')
            ->references('cat_id')
            ->on('product_category')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->integer('parentid')->length(11)->default('0');
            $table->tinyInteger('is_search')->length(4)->default('0');
            $table->tinyInteger('is_detail')->length(4)->default('0');
            $table->tinyInteger('is_hover')->length(4)->default('0');
            $table->tinyInteger('is_focus')->length(4)->default('0');
            $table->tinyInteger('is_warranty')->length(4)->default('0');
            $table->integer('menu_order')->length(11)->default('0');
            $table->tinyInteger('display')->length(4)->default('1');
            $table->integer('date_post')->length(11);
            $table->integer('date_update')->length(11);
            $table->integer('adminid')->length(11)->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_cat_option');
    }
};
