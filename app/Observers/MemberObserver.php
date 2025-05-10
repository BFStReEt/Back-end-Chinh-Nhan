<?php

namespace App\Observers;

use App\Models\Log;
use App\Models\Coupon;
use App\Models\Member;
use Illuminate\Support\Facades\Log as LogManager;

class MemberObserver
{
    /**
     * Handle the Coupon "created" event.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return void
     */
    public function created(Member $member)
    {
        LogManager::info('Member '.$member->id.'  has been save.');
        $saveActionCoupon = new Log();
        $saveActionCoupon -> member_id = $member->mem_id.''.$member->username;
        $saveActionCoupon -> coupon_id = '';
        $saveActionCoupon -> description = 'Member '.$member->id.' create.';
        $saveActionCoupon -> action = 'Member register account';
        $saveActionCoupon -> save();
    }

    /**
     * Handle the Coupon "updated" event.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return void
     */
    public function updated(Member $member)
    {
        LogManager::info('Member '.$member->id.'  has been updated.');
        $saveActionCoupon = new Log();
        $saveActionCoupon -> member_id = $member->mem_id;
        $saveActionCoupon -> description = 'Member '.$member->username.'  has been updated.';
        $saveActionCoupon -> action = $member->username.'updated member ';
        $saveActionCoupon -> save();
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
