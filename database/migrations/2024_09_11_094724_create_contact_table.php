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
        Schema::create('contact', function (Blueprint $table) {
            $table->increments('id')->length(10);
            $table->string('subject', 250)->nullable()->default('NULL');
            $table->integer('staff_id')->length(10)->default('0');
            $table->text('content')->nullable();
            $table->string('name', 250)->nullable()->default('NULL');
            $table->string('email', 150)->nullable()->default('NULL');
            $table->string('phone', 150)->nullable()->default('NULL');
            $table->string('address', 250)->nullable()->default('NULL');
            $table->tinyInteger('status')->length(3)->default('0');
            $table->integer('menu_order')->length(10)->default('0');
            $table->string('date_post', 150);
            $table->integer('date_update')->length(10);
            $table->string('lang', 50)->default('vi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact');
    }
};
