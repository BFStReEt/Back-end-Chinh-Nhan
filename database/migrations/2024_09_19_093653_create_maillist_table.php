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
        Schema::create('maillist', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->string('g_name',150)->default('')->nullable();
            $table->string('name',150)->default('')->nullable();
            $table->string('email',150)->default('')->nullable();
            $table->tinyInteger('display')->length(4)->default('1')->nullable();
            $table->integer('menu_order')->length(11)->default('0')->nullable();
            $table->integer('date_send')->length(11)->nullable();
            $table->integer('date_update')->length(11)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maillist');
    }
};
