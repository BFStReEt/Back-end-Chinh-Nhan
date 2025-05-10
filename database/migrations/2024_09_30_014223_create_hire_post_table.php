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
        Schema::create('hire_post', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->text('name')->nullable();
            $table->string('salary',250)->nullable()->default('NULL');
            $table->text('address')->nullable();
            $table->text('experience')->nullable();
            $table->text('deadline')->nullable();
            $table->text('information')->nullable();
            $table->text('rank')->nullable();
            $table->string('number',250)->nullable()->default('NULL');
            $table->string('form',250)->nullable()->default('NULL');
            $table->text('degree')->nullable();
            $table->string('department',500)->nullable()->default('NULL');
            $table->string('slug',500)->nullable()->default('NULL');
            $table->string('meta_keywords',250)->nullable()->default('NULL');
            $table->text('meta_description')->nullable();
            $table->unsignedInteger('hire_cate_id')->length(10);
            $table->foreign('hire_cate_id')
            ->references('id')
            ->on('hire_category')
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->string('image',500)->nullable()->default('NULL');
            $table->tinyInteger('status')->length(4)->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hire_post');
    }
};
