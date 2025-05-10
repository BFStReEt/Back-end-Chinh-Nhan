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
        Schema::create('setting_logo', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->string('logo',250)->nullable()->default('NULL');
            $table->string('hotline',250)->nullable()->default('NULL');
            $table->string('email',250)->nullable()->default('NULL');
            $table->string('email_search',250)->nullable()->default('NULL');
            $table->text('address')->nullable()->default('NULL');
            $table->integer('tool_search')->length(11)->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_logo');
    }
};
