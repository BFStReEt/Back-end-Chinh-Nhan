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
        Schema::create('hire_category', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->string('name',500)->nullable()->default('NULL');
            $table->string('slug',250)->nullable()->default('NULL');
            $table->tinyInteger('status')->length(4)->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hire_category');
    }
};
