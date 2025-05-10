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
        Schema::create('adminlogs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('admin_id')->length(11);
            $table->foreign('admin_id')
            ->references('id')
            ->on('admin')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('time',250);
            $table->string('ip',250);
            $table->string('cat',250);
            $table->string('action',250);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adminlogs');
    }
};
