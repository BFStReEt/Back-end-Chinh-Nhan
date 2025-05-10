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
        Schema::create('contact_qoute', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->string('name', 250)->nullable()->default('NULL');
            $table->string('phone', 250)->nullable()->default('NULL');
            $table->string('email', 250)->nullable()->default('NULL');
            $table->string('company', 250)->nullable()->default('NULL');
            $table->string('address', 250)->nullable()->default('NULL');
            $table->longText('content')->nullable();
            $table->string('attach_file', 250)->nullable()->default('NULL');
            $table->tinyInteger('status')->length(4)->default('0');
            $table->integer('menu_order')->length(11)->nullable()->default('0');
            $table->integer('date_post')->length(11);
            $table->integer('date_update')->length(11);
            $table->string('lang', 50)->nullable()->default('NULL');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_qoute');
    }
};
