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
        Schema::create('coupon', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->string('TenCoupon', 250);
            $table->string('MaPhatHanh', 256);
            $table->string('StartCouponDate', 150);
            $table->string('EndCouponDate', 150)->nullable()->default('NULL');
            $table->integer('NoEndDateCoupon')->length(11)->default('0');
            $table->text('DesCoupon')->nullable();
            $table->integer('CouponType')->length(11)->default('0');
            $table->double('GiaTriCoupon')->default('0');
            $table->double('MaxValueCoupon')->default('0');
            $table->integer('SoLanSuDung')->length(11)->default('1');
            $table->integer('KHSuDungToiDa')->length(11)->default('1');
            $table->integer('SuDungDongThoi')->length(11)->default('0');
            $table->double('DonHangChapNhanTu')->default('0');
            $table->string('DanhMucSpChoPhep', 250)->default('0');
            $table->string('ThuongHieuSPApDung', 250)->nullable()->default('0');
            $table->integer('LoaiKHSuDUng')->length(11)->default('0');
            $table->integer('mem_id')->length(11)->nullable();
            $table->integer('IDAdmin')->length(11)->nullable();
            $table->string('DateCreateCoupon', 150);
            // $table->foreignId("status_id")->constrained()->cascadeOnDelete();
            $table->unsignedInteger('status_id')->length(11);
            $table->foreign('status_id')
            ->references('status_id')
            ->on('coupon_status')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->integer('TotalCouponItem')->length(11)->default('0');
            $table->string('DateUpdateCoupon', 150)->nullable()->default('NULL');
            $table->text('NoteCoupon')->nullable();
            $table->integer('CouponConlai')->length(11)->default('0');
            $table->string('MaKhoSPApdung', 150)->nullable()->default('NULL');
            $table->integer('TypeDiscount')->length(11)->nullable()->default('0');
            $table->integer('showcart')->length(11)->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon');
    }
};
