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
        Schema::create('admin', function (Blueprint $table) {
            $table->increments('id')->length(10);
            $table->string('username', 50);
            $table->string('password', 250);
            $table->string('email', 100);
            $table->string('display_name', 250);
            $table->string('avatar', 250)->nullable()->default('NULL');
            $table->string('skin',250)->default('blue');
            $table->unsignedInteger('depart_id');
            $table->foreign('depart_id')->references('id')->on('department')
                ->onDelete('cascade');
            $table->tinyInteger('is_default')->length(4)->default('0');
            $table->string('lastlogin', 150)->default('0');
            $table->string('code_reset', 150)->nullable()->default('NULL');
            $table->integer('menu_order')->length(11)->default('0');
            $table->integer('status')->length(11)->default('2');
            $table->string('phone',50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};
