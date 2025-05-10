<?php

namespace App\Observers;

//use App\Models\Log;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LogManager;

class ProductObserver
{
    public function created(Product $product)
    {
        LogManager::info('Product '.$product->id.'  has been save.');
        //$saveActionProduct = new Log();
        $saveActionProduct -> user_id = Auth::guard('admin')->user()->name?? '';
        $saveActionProduct -> product_id = $product->id;
        $saveActionProduct -> description = 'Product '.$product->id.'  has been save.'.
        $product->price ?? ''.'.' . $product->price_old ?? ''. $product->status ?? ''.
        $product->picture ?? ''.$product->tecnology.Auth::guard('admin')->user()->name;
        $saveActionProduct -> action = 'Create product new';
        $saveActionProduct -> save();
    }

    public function updated(Product $product)
    {
        LogManager::info('product '.$product->id.'  has been updated.');
        $saveActionProduct = new Log();
        $saveActionProduct -> user_id = $product->IDAdmin;
        $saveActionProduct -> product_id = $product->id;
        $saveActionProduct -> description = 'product '.$product->id.'  has been updated.'.
        $product->price ?? ''.'.' . $product->price_old ?? ''. $product->status ?? ''.$product->picture ?? '' ;
        $saveActionProduct -> action = $product->id.' updated product by '.'admin';
        $saveActionProduct -> save();
    }

    public function deleted(Product $product)
    {
        LogManager::info('product '.$product->id.'  has been deleted.');
        $saveActionProduct = new Log();
        $saveActionProduct -> user_id = $product->IDAdmin;
        $saveActionProduct -> product_id = $product->id;
        $saveActionProduct -> description = 'product '.$product->id.'  has been deleted.';
        $saveActionProduct -> action = $product->id.' deleted product by '.Auth::guard('admin')->user()->name;
        $saveActionProduct -> save();
    }

    public function restored(Coupon $coupon)
    {
    }

    public function forceDeleted(Coupon $coupon)
    {
    }
}
