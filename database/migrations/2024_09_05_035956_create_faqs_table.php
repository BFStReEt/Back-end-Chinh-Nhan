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
        Schema::create('faqs', function (Blueprint $table) {
            $table->bigIncrements('faqs_id')->length(11);
            $table->integer('cat_id')->length(11)->default('0');
            $table->string('cat_list',250)->default('0');
            $table->string('poster',250)->nullable()->default('NULL');
            $table->string('email_poster',150)->nullable()->default('NULL');
            $table->integer('phone_poster')->length(11);
            $table->string('answer_by',150)->nullable()->default('NULL');
            $table->integer('views')->length(11)->nullable()->default('0');
            $table->tinyInteger('display')->length(4)->default('1');
            $table->integer('menu_order',)->length(11)->nullable()->default('0');
            $table->integer('date_post')->length(11);
            $table->integer('date_update')->length(11);
            $table->tinyInteger('adminid')->length(4)->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
