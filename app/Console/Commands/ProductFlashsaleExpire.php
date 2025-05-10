<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductFlashSale;
use App\Models\Product;
use Carbon\Carbon;

class ProductFlashsaleExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:product-flashsale-expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        $listFlashSaleProduct = ProductFlashSale::where('status', 1)->get();
        foreach ($listFlashSaleProduct as $value) {
                $timeStart=$value->StartDate;
                $timeEnd=$value->StartDate;
                if ( $timeStart> $nowFormatted || $timeEnd<$nowFormatted) {
                    $value->status=0;
                    $value->save();
                    $product=Product::where('product_id',$value->product_id)->first();
                    $product->status=0;
                    $product->save();

                }
        }
        $this->info('flash sale success.');

    }
}
