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
        Schema::create('gift_promotion', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->string('title', 250)->nullable();
            $table->string('code', 250)->nullable();
            $table->string('list_cat', 256)->nullable();
            $table->text('content')->nullable();
            $table->integer('type')->length(11)->nullable();
            $table->tinyInteger('display')->length(4)->default('1');
            $table->string('priceMin', 150)->nullable()->default('NULL');
            $table->string('priceMax', 150)->nullable()->default('NULL');
            $table->string('StartDate', 150)->nullable()->default('NULL');
            $table->string('EndDate', 150)->nullable()->default('NULL');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_promotion');
    }
};
