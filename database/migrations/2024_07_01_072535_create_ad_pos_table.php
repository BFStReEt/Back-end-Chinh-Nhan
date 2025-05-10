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
        Schema::create('ad_pos', function (Blueprint $table) {
            $table->bigIncrements('id_pos')->length(20);
            $table->unsignedInteger('cat_id')->length(11);
            $table->foreign('cat_id')
            ->references('cat_id')
            ->on('product_category')
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('name', 255)->default('');
            $table->string('title', 255)->default('');
            $table->integer('width')->length(11)->default('0');
            $table->integer('height')->length(11)->default('0');
            $table->tinyInteger('n_show')->length(4)->default('5');
            $table->text('description')->nullable();
            $table->tinyInteger('display')->length(4)->default('1');
            $table->integer('menu_order')->length(11)->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_pos');
    }
};
