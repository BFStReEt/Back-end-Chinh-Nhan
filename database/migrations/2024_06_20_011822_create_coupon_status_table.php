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
        Schema::create('coupon_status', function (Blueprint $table) {
            $table->increments('status_id')->length(11);
            $table->string('title',150)->nullable()->default('NULL');
            $table->string('color',200)->nullable()->default('NULL');
            $table->tinyInteger('is_default')->length(4)->nullable();
            $table->tinyInteger('is_payment')->length(4)->nullable();
            $table->tinyInteger('is_complete')->length(4)->nullable();
            $table->tinyInteger('is_cancel')->length(4)->nullable();
            $table->tinyInteger('is_customer')->length(4)->nullable();
            $table->integer('menu_order')->length(11)->nullable();
            $table->tinyInteger('display')->length(4)->nullable()->default('1');
            $table->string('lang',50)->default('vi');
            $table->integer('date_post')->length(11)->default('0');
            $table->integer('date_update')->length(11)->default('0');
            $table->integer('adminid')->length(11);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_status');
    }
};
