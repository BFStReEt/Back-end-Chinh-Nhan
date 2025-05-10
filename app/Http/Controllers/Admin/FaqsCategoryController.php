<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaqsCategoryDesc;
use App\Models\FaqsCategory;
use Illuminate\Support\Facades\DB;
use Gate;
class FaqsCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showAllFaqsCate(){
        try{
            //$faqsCategory = FaqsCategory::where('parentid',0)->with('faqsCategoryDesc')->get();

            $listData=DB::table('faqs_category')
            ->where('parentid',0)
            ->join('faqs_category_desc', 'faqs_category_desc.cat_id', '=', 'faqs_category.cat_id')
            ->select('faqs_category_desc.cat_id','faqs_category_desc.cat_name','faqs_category_desc.friendly_url')
            ->orderBy('faqs_category.cat_id','asc')
            ->get();
            //return  $listData;


            return response()->json([
                'status'=>true,
                'data'=>$listData
            ]);
        }catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json( $response, 500 );
        }
    }
    public function index(Request $request)
    {
        try {
            if(Gate::allows('QUẢN LÝ TƯ VẤN.Quản lý danh mục.manage')){
                if($request->data == 'undefined' || $request->data =="")
                {
                    $faqsCategory = FaqsCategory::where('parentid',0)->with('faqsCategoryDesc')->get();
                }
                else{
                    $faqsCategory = FaqsCategory::where('parentid',0)->with('faqsCategoryDesc')->whereHas('faqsCategoryDesc', function ($query) use ($request) {
                        $query->where("cat_name", 'like', '%' . $request->data . '%');
                    })->get();
                }
                $response = [
                    'status' => true,
                    'list' => $faqsCategory,
                ];
                return response()->json( $response, 200 );
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        } catch ( \Exception $e ) {
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
        $faqsCategory = new FaqsCategory();
        $faqsCategoryDesc = new FaqsCategoryDesc();
        try {
            if(Gate::allows('QUẢN LÝ TƯ VẤN.Quản lý danh mục.add')){
                $cat = FaqsCategory::max('cat_id')+1;
            // if($request->input('parentid')==0)
            // {
            //  $cat_code = $cat;
            // }
            // else{
            //  $cat_code = $request->input('parentid').'_'.$cat;
            // }
                $faqsCategory->fill([
                    'cat_code' => 0,
                    'parentid'=> 0,
                    'picture' =>"",
                    'is_default' => 0,
                    'show_home' => 0,
                    'focus_order' => 0,
                    'menu_order' => 0,
                    'views' => 0,
                    'display' => $request->input('display'),
                    'adminid' =>0
                ])->save();
                $faqsCategoryDesc->cat_id = $faqsCategory->cat_id;
                $faqsCategoryDesc->cat_name = $request->input('cat_name');
                $faqsCategoryDesc->description = $request->input('description');
                $faqsCategoryDesc->friendly_url = $request->input('friendly_url');
                $faqsCategoryDesc->friendly_title = $request->input('friendly_title');
                $faqsCategoryDesc->metakey = $request->input('metakey');
                $faqsCategoryDesc->metadesc = $request->input('metadesc');
                $faqsCategoryDesc->lang = "vi";
                $faqsCategoryDesc->save();

                $response = [
                    'status' => true,
                    'faqsCategory' => $faqsCategory,
                    'faqsCategoryDesc' => $faqsCategoryDesc,
                ];
                return response()->json($response, 200);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
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
    public function edit(string $id)
    {
        try{
            if(Gate::allows('QUẢN LÝ TƯ VẤN.Quản lý danh mục.edit')){
                $faqsCategory = FaqsCategory::with('faqsCategoryDesc')->find($id);
                return response()->json([
                'status'=> true,
                'faqsCategory' => $faqsCategory
            ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            if(Gate::allows('QUẢN LÝ TƯ VẤN.Quản lý danh mục.update')){
                $faqsCategory = new FaqsCategory();
                $faqsCategoryDesc = new FaqsCategoryDesc();
                $listFaqsCategory = FaqsCategory::Find($id);

                // if($request->input('parentid')==0)
                // {
                //  $cat_code = $listFaqsCategory->cat_id;
                // }
                // else{
                //  $cat_code = $request->input('parentid').'_'.$listFaqsCategory->cat_id;
                // }
                $listFaqsCategory->fill([
                    'cat_code' =>0,
                    'parentid'=> 0,
                    'display' => $request->input('display'),
                    'adminid' => 0
                ])->save();

                $faqsCategoryDesc = FaqsCategoryDesc::where('cat_id', $id)->first();
                if ($faqsCategoryDesc) {
                    $faqsCategoryDesc->cat_name = $request->input('cat_name');
                    $faqsCategoryDesc->description = $request->input('description');
                    $faqsCategoryDesc->friendly_url = $request->input('friendly_url');
                    $faqsCategoryDesc->friendly_title = $request->input('friendly_title');
                    $faqsCategoryDesc->metakey = $request->input('metakey');
                    $faqsCategoryDesc->metadesc = $request->input('metadesc');
                    $faqsCategoryDesc->lang = "vi";
                    $faqsCategoryDesc->save();
                }
                $response = [
                    'status' => true,
                ];
                return response()->json($response, 200);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            if(Gate::allows('QUẢN LÝ TƯ VẤN.Quản lý danh mục.del')){
                $list = FaqsCategory::Find($id)->delete();
                FaqsCategoryDesc::where('cat_id',$id)->delete();
                return response()->json([
                    'status'=>true
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }

    }
    public function deleteAll(Request $request)
    {
        $arr =$request->data;
        try {
            if(Gate::allows('QUẢN LÝ TƯ VẤN.Quản lý danh mục.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $list = FaqsCategory::Find($item)->delete();
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
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }
}
