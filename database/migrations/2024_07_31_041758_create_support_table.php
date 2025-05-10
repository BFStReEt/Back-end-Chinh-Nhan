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
        Schema::create('support', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->string('title',150);
            $table->string('group',50);
            $table->string('email',150);
            $table->string('phone',150)->nullable()->default('0');
            $table->string('name',150);
            $table->string('type',50)->nullable()->default('chat');
            $table->integer('menu_order')->length(11)->nullable()->default('0');
            $table->tinyInteger('display')->length(4)->default('1');
            $table->integer('adminid')->length(11)->default('1');
            $table->string('lang',50)->default('vi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support');
    }
};
