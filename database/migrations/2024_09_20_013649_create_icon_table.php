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
        Schema::create('icon', function (Blueprint $table) {
            $table->increments('icon_id')->length(11);
            $table->string('type',50)->default('icon');
            $table->string('picture',250)->nullable()->default('NULL');
            $table->string('color',150)->nullable()->default('#000000');
            $table->string('link',150)->nullable()->default('NULL');
            $table->string('title',150)->nullable()->default('NULL');
            $table->string('font_icon',150)->nullable()->default('NULL');
            $table->string('target',50)->default('_blank');
            $table->text('description')->nullable();
            $table->integer('menu_order')->length(11)->unsigned()->default('0');
            $table->integer('date_post')->length(11)->nullable()->default('0');
            $table->integer('date_update')->length(11);
            $table->tinyInteger('display')->length(4)->default('1');
            $table->string('lang',50)->default('vi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('icon');
    }
};
