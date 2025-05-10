<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Present;
use App\Models\CategoryDesc;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class PresentController extends Controller
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
                'action'=>'show all present',
                'cat'=>'present',
            ]);


            if(Gate::allows('QUẢN LÝ QÙA TẶNG.Quản lý quà tặng.manage')){
                $present=Present::query();
                if($request->data == 'undefined' || $request->data =="")
                {
                    $present= $present;
                }
                else{
                    $present =$present->where("title", 'like', '%' . $request->data . '%')
                    ->orWhere('code','like', '%' . $request->data . '%');
                }
                if($request->StartDate && $request->EndDate){

                    //return strtotime($request->StartDate)."-".strtotime($request->EndDate);
                    $start=($request->StartDate);
                    $end=($request->EndDate);

                    $present =$present->whereBetween('DateCreate',[$start,$end]);
                }
                $present=$present->orderBy('id','desc')->get();
                return response()->json([
                    'status'=>true,
                    'data'=>$present
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
                'action'=>'add a present',
                'cat'=>'present',
            ]);
            if(Gate::allows('QUẢN LÝ QÙA TẶNG.Quản lý quà tặng.add')){

            $date = Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('DD-MM-YYYY hh:mm:ss');
            $present=new Present();
            $present->title=$request->title;
            $present->code=$request->code;
            $present->list_cat=$request->list_cat;
            $present->cat_parent_id=implode(',',$request->cat_parent_id);
            if(!is_null($request->list_cat) )
            {
                if(count($request->list_cat)>0){
                    $present->list_cat = implode(',',$request->list_cat);
                }else{
                    $present->list_cat = implode(',',$request->cat_parent_id);
                }
                // $arrCategory=array_merge($request->cat_parent_id,$request->list_cat);

                // $present->list_cat = implode(',',$arrCategory);
            }else{
                $present->list_cat = NULL;
            }
            if(!is_null($request->list_product) )
            {
                $present->list_product = implode(',',$request->list_product);
            }else{
                $present->list_product = NULL;
            }
            $present->content=$request->content;
            $present->type=$request->type;
            $present->display=$request->display;
            $present->priceMin=$request->priceMin;
            $present->priceMax=$request->priceMax;
            $present->StartDate=strtotime($request->StartDate);
            $present->EndDate=strtotime($request->EndDate);
            $present->DateCreate=strtotime($date);
            $present->save();
            return response()->json([
                'status'=>true,
                'data'=>$present
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
                'action'=>'edit a present',
                'cat'=>'present',
            ]);
            if(Gate::allows('QUẢN LÝ QÙA TẶNG.Quản lý quà tặng.edit')){

            $listPresent=Present::where('id',$id)->first();
            $category = explode(",",$listPresent->list_cat);
            $saveCategory =[];
            if($listPresent->list_cat!="")
            {
                foreach ($category as $value) {
                    // $saveCategory[] = CategoryDesc::select('cat_name')->whereRaw('FIND_IN_SET(?, cat_id)', [$value])->first();
                    $saveCategory[] = intval($value);
                }
            }
            //$cat_parent_id=explode(",",$listPresent->cat_parent_id);
            // $saveParentCategory=[];
            // if($listPresent->cat_parent_id!="")
            // {
            //     foreach ($cat_parent_id as $value) {

            //         $saveParentCategory[] = $value;
            //     }
            // }


            $listPresent['list_cat'] = $saveCategory;
            //$listPresent['parent_cat'] = $saveParentCategory;

        //    if($listPresent->list_product){
        //         $saveProduct = explode(",",$listPresent->list_product);
        //    }else{
        //         $saveProduct = [];
        //    }


        //     $listPresent['list_product'] = $saveProduct;
            return response()->json([
                'status'=>true,
                'data'=>$listPresent
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
                'action'=>'update a present',
                'cat'=>'present',
            ]);
            if(Gate::allows('QUẢN LÝ QÙA TẶNG.Quản lý quà tặng.update')){
            $present=Present::where('id',$id)->first();
            $present->title=$request->title;
            $present->code=$request->code;
            $present->list_cat=$request->list_cat;
            if(!is_null($request->list_cat) )
            {
                //$present->list_cat = implode(',',$request->list_cat);

                if(count($request->list_cat)>0){
                    $present->list_cat = implode(',',$request->list_cat);
                }else{
                    $present->list_cat = implode(',',$request->cat_parent_id);
                }
            }else{
                $present->list_cat = NULL;
            }
            if(!is_null($request->list_product) )
            {
                $present->list_product = implode(',',$request->list_product);
            }else{
                $present->list_product = NULL;
            }
            $present->content=$request->content;
            $present->type=$request->type;
            $present->display=$request->display;
            $present->priceMin=$request->priceMin;
            $present->priceMax=$request->priceMax;
            $present->StartDate=strtotime($request->StartDate);
            $present->EndDate=strtotime($request->EndDate);

            $present->save();
            return response()->json([
                'status'=>true,
                'data'=>$present
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
                'action'=>'delete a present',
                'cat'=>'present',
            ]);
            if(Gate::allows('QUẢN LÝ QÙA TẶNG.Quản lý quà tặng.del')){
            $present=Present::where('id',$id)->first();
            $present->delete();
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

            if(Gate::allows('QUẢN LÝ QÙA TẶNG.Quản lý quà tặng.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $present=Present::where('id',$item)->first();
                        $present->delete();
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
}
