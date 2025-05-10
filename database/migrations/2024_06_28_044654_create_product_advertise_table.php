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
        Schema::create('product_advertise', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->integer('itemID')->length(11)->default('0');
            $table->string('type',50)->nullable()->default('');
            $table->string('pos',50)->nullable()->default('left');
            $table->string('picture',250)->nullable()->default('NULL');
            $table->string('link',150)->nullable()->default('NULL');
            $table->string('title',150)->nullable()->default('NULL');
            $table->text('description')->nullable();
            $table->string('target',50)->default('_blank');
            $table->string('height',10)->nullable()->default('0');
            $table->string('width',10)->nullable()->default('0');
            $table->tinyInteger('display')->length(4)->default('1');
            $table->integer('menu_order')->length(11)->default('0');
            $table->integer('date_post')->length(11)->nullable()->default('0');
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
        Schema::dropIfExists('product_advertise');
    }
};
