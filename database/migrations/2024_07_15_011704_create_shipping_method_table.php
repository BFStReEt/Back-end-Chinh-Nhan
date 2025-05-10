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
        Schema::create('shipping_method', function (Blueprint $table) {
            $table->increments('shipping_id')->length(11);
            $table->string('title',250)->nullable()->default('');
            $table->string('name',150)->nullable()->default('');
            $table->text('description')->nullable();
            $table->string('price',150)->nullable()->default('');
            $table->string('discount',250)->nullable()->default('');
            $table->tinyInteger('status')->length(4)->default('0');
            $table->tinyInteger('s_type')->length(4)->default('0');
            $table->integer('s_time')->length(11)->nullable()->default('0');
            $table->tinyInteger('menu_order')->length(4)->default('0');
            $table->tinyInteger('display')->length(4)->default('1');
            $table->integer('date_post')->length(11)->default('0');
            $table->integer('date_update')->length(11)->default('0');
            $table->string('lang',50)->default('vi');
            $table->integer('adminid')->length(11);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_method');
    }
};
