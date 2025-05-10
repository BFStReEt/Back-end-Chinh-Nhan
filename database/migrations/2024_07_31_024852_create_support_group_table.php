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
        Schema::create('support_group', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->string('title',150)->default('');
            $table->string('name',150)->nullable()->default('NULL');
            $table->tinyInteger('is_default')->length(4)->default('0');
            $table->integer('menu_order')->length(11)->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_group');
    }
};
