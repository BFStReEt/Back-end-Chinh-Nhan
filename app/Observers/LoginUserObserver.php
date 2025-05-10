<?php

namespace App\Observers;

use App\Models\Log;
use App\Models\member;
use Illuminate\Support\Facades\Log as LogManager;

class LoginObserver
{
    /**
     * Handle the member "created" event.
     *
     * @param  \App\Models\member  $member
     * @return void
     */
    public function created(Member $member)
    {
        LogManager::info('member '.$member->id.'  login Vitinhnguyenkim.');
        $saveActionmember = new Log();
        $saveActionmember -> user_id = $member->IDAdmin;
        $saveActionmember -> member_id = $member->id;
        $saveActionmember -> description = 'member '.$member->id.'  has been save.';
        $saveActionmember -> action = 'Create member new';
        $saveActionmember -> save();
    }

    /**
     * Handle the member "updated" event.
     *
     * @param  \App\Models\member  $member
     * @return void
     */
    public function updated(Member $member)
    {
        
    }

    /**
     * Handle the member "deleted" event.
     *
     * @param  \App\Models\member  $member
     * @return void
     */
    public function deleted(Member $member)
    {
        //
    }

    /**
     * Handle the member "restored" event.
     *
     * @param  \App\Models\member  $member
     * @return void
     */
    public function restored(member $member)
    {
        //
    }

    /**
     * Handle the member "force deleted" event.
     *
     * @param  \App\Models\member  $member
     * @return void
     */
    public function forceDeleted(member $member)
    {
        //
    }
}
