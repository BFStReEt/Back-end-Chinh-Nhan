<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PromotionDesc;
use App\Models\Promotion;

class PromotionController extends Controller
{
    public function showPromotion(){
        try {
            $promotion = Promotion::with(['promotionDesc'=>function ($query) {
                 $query->select('promotion_id','title','friendly_url');
            }])->select('promotion.promotion_id','promotion.picture',
            'promotion.date_start_promotion','promotion.date_end_promotion','promotion.status')->paginate(8);
            $response = [
                'status' => 'success',
                'list' => $promotion,

            ];

            return response()->json( $response, 200 );
        } catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json( $response, 500 );
        }
    }
    public function index()
    {
        try {
            $promotion = Promotion::with(['promotionDesc'=>function ($query) {
                 $query->select('promotion_id','title','friendly_url');
            }])->select('promotion.promotion_id','promotion.picture',
            'promotion.date_start_promotion','promotion.date_end_promotion','promotion.status')->limit(5)->get();
            $response = [
                'status' => 'success',
                'list' => $promotion,

            ];

            return response()->json( $response, 200 );
        } catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json( $response, 500 );
        }
    }
    public function detail(Request $request,$slug)
    {
        try{

            $promotionDesc = PromotionDesc::where('friendly_url',$slug)->first();

            return response()->json([
                'status' => true,
                'data'=> $promotionDesc,

            ]);
        }catch(Exception $e){
            return response()->json([
             'status' => false,
             'message' => $e->getMessage()
            ]);
        }

    }
}
