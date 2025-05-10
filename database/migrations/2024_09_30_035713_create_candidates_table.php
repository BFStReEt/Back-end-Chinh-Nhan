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
        Schema::create('candidates', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->string('name',500)->nullable()->default('NULL');
            $table->string('gmail',500)->nullable()->default('NULL');
            $table->string('phone',20)->nullable()->default('NULL');
            $table->string('cv',500)->nullable()->default('NULL');
            $table->unsignedInteger('hire_post_id')->length(10);
            $table->foreign('hire_post_id')
            ->references('id')
            ->on('hire_post')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->text('message')->nullable();
            $table->string('fileInfo',500)->nullable()->default('NULL');
            $table->tinyInteger('status')->length(4)->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
