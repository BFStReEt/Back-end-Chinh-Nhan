<?php

namespace App\Observers;

use App\Models\Log;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LogManager;

class CouponObserver
{
    /**
     * Handle the Coupon "created" event.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return void
     */
    public function created(Coupon $coupon)
    {
        LogManager::info('Coupon '.$coupon->id.'  has been save.');
        $saveActionCoupon = new Log();
        $saveActionCoupon -> user_id = Auth::guard('admin')->user()->name;
        $saveActionCoupon -> coupon_id = $coupon->id.'-'.$coupon->TenCoupon;
        $saveActionCoupon -> description = 'Coupon '.$coupon->id.'  has been save.';
        $saveActionCoupon -> action = 'Create coupon new';
        $saveActionCoupon -> save();
    }

    /**
     * Handle the Coupon "updated" event.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return void
     */
    public function updated(Coupon $coupon)
    {
        
    }

    /**
     * Handle the Coupon "deleted" event.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return void
     */
    public function deleted(Coupon $coupon)
    {
        //
    }

    /**
     * Handle the Coupon "restored" event.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return void
     */
    public function restored(Coupon $coupon)
    {
        //
    }

    /**
     * Handle the Coupon "force deleted" event.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return void
     */
    public function forceDeleted(Coupon $coupon)
    {
        //
    }
}
