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
        Schema::create('infor_address', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->unsignedInteger('mem_id')->length(11);
            $table->foreign('mem_id')
            ->references('id')
            ->on('members')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('gender',255)->nullable();
            $table->string('fullName',500)->nullable();
            $table->string('Phone',255)->nullable();
            $table->string('address',500)->nullable();
            $table->string('province',500)->nullable();
            $table->string('district',500)->nullable();
            $table->string('ward',500)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infor_address');
    }
};
