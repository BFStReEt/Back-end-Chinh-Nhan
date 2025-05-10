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
        Schema::create('promotion', function (Blueprint $table) {
            $table->bigIncrements('promotion_id')->length(20);
            $table->string('picture',250)->nullable()->default('NULL');
            $table->tinyInteger('focus')->length(4)->default('0');
            $table->integer('focus_order')->length(11)->default('0');
            $table->integer('views')->length(11)->nullable()->default('0');
            $table->tinyInteger('display')->length(4)->default('1');
            $table->integer('menu_order')->length(11)->nullable()->default('0');
            $table->integer('date_post')->length(11);
            $table->integer('date_update')->length(11);
            $table->tinyInteger('adminid')->length(4)->default('1');
            $table->integer('date_start_promotion')->length(11)->nullable()->default('0');
            $table->integer('date_end_promotion')->length(11)->nullable()->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion');
    }
};
