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
        Schema::create('order_status', function (Blueprint $table) {
            $table->increments('status_id')->length(11);
            $table->string('title',150)->nullable()->default('NULL');
            $table->string('color',200)->nullable()->default('NULL');
            $table->tinyInteger('is_default')->length(4)->nullable()->default('0');
            $table->tinyInteger('is_payment')->length(4)->nullable()->default('0');
            $table->tinyInteger('is_complete')->length(4)->nullable()->default('0');
            $table->tinyInteger('is_cancel')->length(4)->nullable()->default('0');
            $table->tinyInteger('is_customer')->length(4)->nullable()->default('0');
            $table->integer('menu_order')->length(11)->nullable()->default('0');
            $table->tinyInteger('display')->length(4)->nullable()->default('1');
            $table->string('lang',50)->default('vi');
            $table->string('date_post')->length(255)->default('0');
            $table->string('date_update')->length(255)->default('0');
            $table->integer('adminid')->length(11)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status');
    }
};
