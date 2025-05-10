<?php

namespace App\Observers;

use App\Models\Admin;
use App\Models\Log;
use Illuminate\Support\Facades\Log as LogManager;

class AdminObserver
{
    /**
     * Handle the Admin "created" event.
     *
     * @param  \App\Models\Admin  $admin
     * @return void
     */
    public function created(Admin $admin)
    {
        LogManager::info('admin '.$admin->id.'  login Vitinhnguyenkim.');
        $saveActionadmin = new Log();
        $saveActionadmin -> user_id = $admin->IDAdmin;
        $saveActionadmin -> adminid = $admin->id;
        $saveActionadmin -> description = 'admin '.$admin->id.'  has been save.';
        $saveActionadmin -> action = 'Create admin new';
        $saveActionadmin -> save();
    }

    /**
     * Handle the Admin "updated" event.
     *
     * @param  \App\Models\Admin  $admin
     * @return void
     */
    public function updated(Admin $admin)
    {
        //
    }

    /**
     * Handle the Admin "deleted" event.
     *
     * @param  \App\Models\Admin  $admin
     * @return void
     */
    public function deleted(Admin $admin)
    {
        //
    }

    /**
     * Handle the Admin "restored" event.
     *
     * @param  \App\Models\Admin  $admin
     * @return void
     */
    public function restored(Admin $admin)
    {
        //
    }

    /**
     * Handle the Admin "force deleted" event.
     *
     * @param  \App\Models\Admin  $admin
     * @return void
     */
    public function forceDeleted(Admin $admin)
    {
        //
    }
}
