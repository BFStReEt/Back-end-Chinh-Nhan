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
        Schema::create('advertise', function (Blueprint $table) {
            $table->bigIncrements('id')->length(32);
            $table->string('title', 150)->nullable()->default('NULL');
            $table->string('picture', 250)->nullable()->default('NULL');
            $table->string('pos', 255)->default('NULL');
            $table->unsignedBigInteger('id_pos')->length(20);
            $table->foreign('id_pos')
            ->references('id_pos')
            ->on('ad_pos')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('width', 10)->nullable()->default('0');
            $table->string('height', 10)->default('0');
            $table->string('link', 150)->nullable()->default('NULL');
            $table->string('target', 50)->default('_blank');
            $table->string('module_show', 150)->default('');
            $table->text('description')->nullable();
            $table->integer('menu_order')->length(11)->default('0');
            $table->tinyInteger('display')->length(4)->default('1');
            $table->string('lang', 50)->default('vi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertise');
    }
};
