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
        Schema::create('product', function (Blueprint $table) {
            $table->bigIncrements('product_id')->length(20);
            $table->unsignedInteger('cat_id')->length(10);
            $table->foreign('cat_id')
            ->references('cat_id')
            ->on('product_category')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('cat_list',250)->default('0');
            $table->string('maso',150)->default('0');
            $table->string('macn',150);
            $table->string('code_script')->nullable()->default('NULL');
            $table->string('picture',250)->nullable()->default('NULL');
            $table->double('price')->default('0');
            $table->double('price_old')->default('0');
            $table->integer('brand_id')->length(11)->nullable()->default('0');
            $table->integer('status')->length(11)->nullable();
            $table->text('options')->nullable();
            $table->string('op_search',250)->nullable()->default('0');
            $table->string('cat_search',250)->nullable()->default('0');
            $table->text('technology')->nullable();
            $table->tinyInteger('focus')->length(4)->default('0');
            $table->integer('focus_order')->length(11)->default('0');
            $table->tinyInteger('deal')->length(4)->default('0');
            $table->integer('deal_order')->length(11)->default('0');
            $table->string('deal_date_start',50);
            $table->string('deal_date_end',50);
            $table->tinyInteger('stock')->length(4)->default('1');
            $table->float('votes')->default('0');
            $table->integer('numvote')->length(11)->default('0');
            $table->integer('menu_order')->length(11)->default('0');
            $table->integer('menu_order_cate_lv0')->length(11)->default('0');
            $table->integer('menu_order_cate_lv1')->length(11)->default('0');
            $table->integer('menu_order_cate_lv2')->length(11)->default('0');
            $table->integer('menu_order_cate_lv3')->length(11)->default('0');
            $table->integer('menu_order_cate_lv4')->length(11)->default('0');
            $table->integer('menu_order_cate_lv5')->length(11)->default('0');
            $table->integer('menu_order_cate_lv6')->length(11)->default('0');
            $table->integer('menu_order_cate_lv7')->length(11)->default('0');
            $table->integer('menu_order_cate_lv8')->length(11)->default('0');
            $table->integer('menu_order_cate_lv9')->length(11)->default('0');
            $table->integer('menu_order_cate_lv10')->length(11)->default('0');
            $table->integer('views')->length(11)->default('0');
            $table->tinyInteger('display')->length(4)->default('1');
            $table->integer('date_post')->length(11);
            $table->integer('date_update')->length(11);
            $table->tinyInteger('adminid')->length(4)->default('1');
            $table->string('url',250)->nullable()->default('NULL');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
