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
        Schema::create('contact_staff', function (Blueprint $table) {
            $table->increments('staff_id')->length(11);
            $table->string('title', 150);
            $table->string('email', 150);
            $table->string('phone', 150)->nullable()->default('0');
            $table->text('description')->nullable();
            $table->integer('menu_order')->length(10)->nullable()->default('0');
            $table->tinyInteger('display')->length(3)->default('1');
            $table->integer('date_post')->length(11);
            $table->integer('date_update')->length(11);
            $table->integer('adminid')->length(11)->default('1');
            $table->string('lang', 50)->default('vi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_staff');
    }
};
