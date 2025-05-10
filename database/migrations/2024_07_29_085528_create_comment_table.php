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
        Schema::create('comment', function (Blueprint $table) {
            $table->increments('comment_id')->length(11);
            $table->string('module',150)->default('');
            $table->integer('post_id')->length(11);
            $table->integer('product_id')->length(11)->default('0');
            $table->integer('phone')->length(12)->default('0');
            $table->integer('parentid')->length(11)->default('0');
            $table->integer('mem_id')->length(11)->default('0');
            $table->string('name',150)->default('0');
            $table->string('email',150);
            $table->tinyInteger('hidden_email')->length(4)->default('1');
            $table->text('content');
            $table->string('avatar',50)->nullable()->default('NULL');
            $table->tinyInteger('mark')->length(4)->default('0');
            $table->integer('menu_order')->length(11)->default('0');
            $table->string('address_IP',50);
            $table->tinyInteger('display')->length(4)->default('0');
            $table->integer('date_post')->length(11);
            $table->integer('date_update')->length(11);
            $table->integer('adminid')->length(11)->default('0');
            $table->string('lang',50)->default('vi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment');
    }
};
