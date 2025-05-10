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
        Schema::create('statistics_pages', function (Blueprint $table) {
            $table->increments('id_static_page')->length(11);
            $table->string('url',250)->nullable()->default('NULL');
            $table->string('date',250)->length(11)->default('NULL');
            $table->tinyInteger('count')->length(4)->default('1');
            $table->integer('mem_id')->length(11)->default('0');
            $table->string('module',250)->nullable()->default('NULL');
            $table->string('action',250)->nullable()->default('NULL');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistics_pages');
    }
};
