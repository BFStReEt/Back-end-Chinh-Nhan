<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GiftPromotion;
use Carbon\Carbon;
use App\Models\CategoryDesc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class GiftPromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'edit all giftPromotion',
                'cat'=>'giftPromotion',
            ]);
            if(Gate::allows('QUẢN LÝ KHUYẾN MÃI.Quản lý khuyến mãi.manage')){
            $giftPromotion=GiftPromotion::query();
            if($request->data == 'undefined' || $request->data =="")
            {
                $giftPromotion= $giftPromotion;
            }
            else{
                $giftPromotion =$giftPromotion->where("title", 'like', '%' . $request->data . '%')
                ->orWhere('code','like', '%' . $request->data . '%');
            }
            if($request->StartDate && $request->EndDate){

                $start=($request->StartDate);
                $end=($request->EndDate);

                $giftPromotion =$giftPromotion->whereBetween('DateCreate',[$start,$end]);
            }
            $giftPromotion=$giftPromotion->orderBy('id','desc')->get();
            return response()->json([
                'status'=>true,
                'data'=>$giftPromotion
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        }catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json( $response, 500 );
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{

            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'add a giftPromotion',
                'cat'=>'giftPromotion',
            ]);
            if(Gate::allows('QUẢN LÝ KHUYẾN MÃI.Quản lý khuyến mãi.add')){
            $date = Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('DD-MM-YYYY hh:mm:ss');
            $giftPromotion=new GiftPromotion();
            $giftPromotion->title=$request->title;
            $giftPromotion->code=$request->code;
            $giftPromotion->list_cat=$request->list_cat;
            $giftPromotion->cat_parent_id=implode(',',$request->cat_parent_id);
            //cat_parent_id
            if(!is_null($request->list_cat) )
            {
                // $arrCategory=array_merge($request->cat_parent_id,$request->list_cat);
                // $giftPromotion->list_cat = implode(',',$arrCategory);
                if(count($request->list_cat)>0){
                    $giftPromotion->list_cat = implode(',',$request->list_cat);
                }else{
                    $giftPromotion->list_cat = implode(',',$request->cat_parent_id);
                }
            }else{
                $giftPromotion->list_cat = NULL;
            }
            if(!is_null($request->list_product) )
            {
                $giftPromotion->list_product = implode(',',$request->list_product);
            }else{
                $giftPromotion->list_product = NULL;
            }
            $giftPromotion->content=$request->content;
            $giftPromotion->type=$request->type;
            $giftPromotion->display=$request->display;
            $giftPromotion->priceMin=$request->priceMin;
            $giftPromotion->priceMax=$request->priceMax;
            $giftPromotion->StartDate=strtotime($request->StartDate);
            $giftPromotion->EndDate=strtotime($request->EndDate);
            $giftPromotion->DateCreate=strtotime($date);
            $giftPromotion->save();
            return response()->json([
                'status'=>true,
                'data'=>$giftPromotion
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request,string $id)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'edit a giftPromotion',
                'cat'=>'giftPromotion',
            ]);
            if(Gate::allows('QUẢN LÝ KHUYẾN MÃI.Quản lý khuyến mãi.edit')){
                $listGiftPromotion=GiftPromotion::where('id',$id)->first();
                $category = explode(",",$listGiftPromotion->list_cat);
                $saveCategory =[];
                if($listGiftPromotion->list_cat!="")
                {
                    foreach ($category as $value) {
                        // $saveCategory[] = CategoryDesc::select('cat_name')->whereRaw('FIND_IN_SET(?, cat_id)', [$value])->first();
                        $saveCategory[] = intval($value);
                    }
                }
                $listGiftPromotion['list_cat'] = $saveCategory;

                $saveProduct = explode(",",$listGiftPromotion->list_product);

                $listGiftPromotion['list_product'] = $saveProduct;
                return response()->json([
                    'status'=>true,
                    'data'=>$listGiftPromotion
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }

        }catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json( $response, 500 );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'update a giftPromotion',
                'cat'=>'giftPromotion',
            ]);
            if(Gate::allows('QUẢN LÝ KHUYẾN MÃI.Quản lý khuyến mãi.update')){
                $giftPromotion=GiftPromotion::where('id',$id)->first();
                $giftPromotion->title=$request->title;
                $giftPromotion->code=$request->code;
                if(!is_null($request->list_cat) )
                {
                    if(count($request->list_cat)>0){
                        $giftPromotion->list_cat = implode(',',$request->list_cat);
                    }else{
                        $giftPromotion->list_cat=implode(',',$request->cat_parent_id);
                    }

                }else{
                    $giftPromotion->list_cat = NULL;
                }
                if(!is_null($request->list_product) )
                {
                    $giftPromotion->list_product = implode(',',$request->list_product);
                }else{
                    $giftPromotion->list_product = NULL;
                }
                $giftPromotion->content=$request->content;
                $giftPromotion->type=$request->type;
                $giftPromotion->display=$request->display??1;
                $giftPromotion->priceMin=$request->priceMin;
                $giftPromotion->priceMax=$request->priceMax;
                $giftPromotion->StartDate=strtotime($request->StartDate);
                $giftPromotion->EndDate=strtotime($request->EndDate);

                $giftPromotion->save();
                return response()->json([
                    'status'=>true,
                    'data'=>$giftPromotion
                ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }

        }catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json( $response, 500 );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'delete a giftPromotion',
                'cat'=>'giftPromotion',
            ]);
            if(Gate::allows('QUẢN LÝ KHUYẾN MÃI.Quản lý khuyến mãi.del')){
                $giftPromotion=GiftPromotion::where('id',$id)->first();
                $giftPromotion->delete();
                return response()->json([
                    'status'=>true
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json( $response, 500 );
        }
    }
    public function deleteAll(Request $request){
        try{
           $arr =$request->data;

            if($arr)
            {
                foreach ($arr as $item) {
                    $list =GiftPromotion::where('id',$item)->delete();
                }
            }
            else
            {
                return response()->json([
                    'status'=>false,
                ],422);
            }
            return response()->json([
                'status'=>true,
            ],200);

        }catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json( $response, 500 );
        }
    }
}
