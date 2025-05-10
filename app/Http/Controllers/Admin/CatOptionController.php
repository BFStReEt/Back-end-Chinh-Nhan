<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCatOption;
use App\Models\ProductCatOptionDesc;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatOptionController extends Controller
{
    // public function index(Request $request)
    // {
    //     //b$options = DB::table('product_cat_option_desc')->pluck('title', 'op_id')->all();
    //     $now = date('d-m-Y H:i:s');
    //         $stringTime = strtotime($now);
    //         DB::table('adminlogs')->insert([
    //             'admin_id' => Auth::guard('admin')->user()->id,
    //             'time' =>  $stringTime,
    //             'ip'=> $request->ip(),
    //             'action'=>'show all properties',
    //             'cat'=>'properties',
    //         ]);

    //     if(Gate::allows('QUẢN LÝ SẢN PHẨM.Thuộc tính sản phẩm.manage')){
    //         $cat_id=$request->catId??1;
    //         $query=ProductCatOptionDesc::whereHas('catOption', function ($q) use($cat_id) {
    //             $q->where('cat_id',$cat_id)->where('parentid',0);
    //         })->select('op_id','title','slug');
    //         if($request->input('data')==""){
    //             $productCatOpDesc = $query->get();
    //         }
    //         else{
    //             $productCatOpDesc = $query->where('title',$request->input('data'))->get();
    //         }
    //         $listOption=[];
    //         foreach($productCatOpDesc as $option){
    //             $id=$option->op_id;
    //             $productCatOp=ProductCatOptionDesc::whereHas('catOption', function ($q) use($id) {
    //                 $q->where('parentid',$id);
    //             })->select('op_id','title','slug')->get();
    //             $optionChild=[];
    //             foreach($productCatOp as $catOp){
    //                 $optionChild[]=[
    //                     'op_id'=>$catOp->op_id,
    //                     'title'=>$catOp->title,
    //                     'slug'=>$catOp->slug
    //                 ];
    //             }
    //             $listOption[]=[
    //                 'op_id'=>$id,
    //                 'title'=>$option->title,
    //                 'slug'=>$option->slug,
    //                 'optionChild'=>$optionChild
    //             ];
    //         }
    //         return response()->json([
    //             'status'=>true,
    //             'listOption'=>$listOption
    //         ]);
    //     } else {
    //         return response()->json([
    //             'status'=>false,
    //             'mess' => 'no permission',
    //         ]);
    //     }
    // }

    public function index(Request $request)
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' => $stringTime,
            'ip' => $request->ip(),
            'action' => 'show all properties',
            'cat' => 'properties',
        ]);

        if (Gate::allows('QUẢN LÝ SẢN PHẨM.Thuộc tính sản phẩm.manage')) {
            $catId = ($request->catId ?? 1) ?: 1;

            $categoryDesc = \DB::table('product_category_desc')
                ->where('cat_id', $catId)
                ->select('cat_id', 'cat_name')
                ->first();

            if (!$categoryDesc) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Category not found',
                ], 404);
            }

            $propertiesCategory = \DB::table('properties_category')
                ->where('cat_id', $catId)
                ->where('parentid', 0)
                ->orderBy('stt', 'asc')
                ->pluck('properties_id');

            $properties = \DB::table('properties')
                ->whereIn('id', $propertiesCategory)
                ->select('id', 'title')
                ->get();

            $propertiesValues = \DB::table('properties_value')
                ->whereIn('properties_id', $propertiesCategory)
                ->select('id', 'name', 'properties_id')
                ->get();

            $groupedPropertiesValues = $propertiesValues->groupBy('properties_id');

            $properties = $properties->map(function ($property) use ($groupedPropertiesValues) {
                $property->op_id = $property->id;
                unset($property->id);
                $property->slug = Str::slug($property->title);
                $property->optionChild = $groupedPropertiesValues->get($property->op_id)
                ? $groupedPropertiesValues->get($property->op_id)->map(function ($value) {
                    $value->slug = Str::slug($value->name);
                    $value->op_id = $value->id;
                    $value->title = $value->name;
                    unset($value->id);
                    unset($value->name);
                    unset($value->properties_id);
                    return $value;
                })
                : [];
                return $property;
            });

            $response = [
                'status' => true,
                'listOption' => $properties,
            ];

            return response()->json($response);
        } else {
            return response()->json([
                'status' => false,
                'mess' => 'no permission',
            ]);
        }
    }

    public function catOpchild(Request $request)
    {
        $cat_id = $request->catId ?? 1;

        $query = ProductCatOptionDesc::whereHas('catOption', function ($q) use ($cat_id) {
            $q->where('cat_id', $cat_id)->where('parentid', 0);
        })->select('op_id', 'title', 'slug');
        $productCatOpDesc = $query->get();
        //return $productCatOpDesc ;
        // $listOption=[];
        // foreach($productCatOpDesc as $option){
        //     $id=$option->op_id;
        //     $productCatOp=ProductCatOptionDesc::whereHas('catOption', function ($q) use($id) {
        //         $q->where('parentid',$id);
        //     })->select('op_id','title','slug')->get();
        //     $optionChild=[];
        //     foreach($productCatOp as $catOp){
        //         $optionChild[]=[
        //             'op_id'=>$catOp->op_id,
        //             'title'=>$catOp->title,
        //             'slug'=>$catOp->slug
        //         ];
        //     }
        //     $listOption[]=[
        //         'op_id'=>$id,
        //         'title'=>$option->title,
        //         'slug'=>$option->slug,
        //         'optionChild'=>$optionChild

        //     ];
        // }
        return response()->json([
            'status' => true,
            'listOption' => $productCatOpDesc,
        ]);

    }

    public function create()
    {
        //
    }

    // public function store(Request $request)
    // {
    //     try{
    //         $now = date('d-m-Y H:i:s');
    //         $stringTime = strtotime($now);
    //         DB::table('adminlogs')->insert([
    //             'admin_id' => Auth::guard('admin')->user()->id,
    //             'time' =>  $stringTime,
    //             'ip'=> $request->ip(),
    //             'action'=>'add a properties',
    //             'cat'=>'properties',
    //         ]);
    //     if(Gate::allows('QUẢN LÝ SẢN PHẨM.Thuộc tính sản phẩm.add')){

    //         $productCatOption=new ProductCatOption();
    //         $productCatOptionDesc=new ProductCatOptionDesc();
    //         $productCatOption->fill( [
    //             'cat_id' => $request->cat_id,
    //             'parentid'=>$request->parentid,
    //             'is_search' => 1,
    //             'is_detail' => 1,
    //             'is_focus' => 1,
    //             'is_warranty'=>0,
    //             'menu_order'=>110,
    //             'display' => $request->input( 'display' ),
    //             'date_post' => strtotime( 'now' ),
    //             'date_update' => strtotime( 'now' ),
    //             'adminid' => 1,
    //         ] )->save();

    //         $productCatOptionDesc->op_id= $productCatOption->op_id;
    //         $productCatOptionDesc->title= $request->title;
    //         $productCatOptionDesc->slug= $request->slug;
    //         $productCatOptionDesc->description= $request->description;
    //         $productCatOptionDesc->lang="vi";
    //         $productCatOptionDesc->save();
    //         return response()->json([
    //             'status'=>true,
    //             'productCatOption'=>$productCatOption,
    //             'productCatOptionDesc'=>$productCatOptionDesc
    //         ]);
    //     } else {
    //         return response()->json([
    //             'status'=>false,
    //             'mess' => 'no permission',
    //         ]);
    //     }
    //     }catch (\Exception $e) {
    //             $errorMessage = $e->getMessage();
    //             $response = [
    //                 'status' => false,
    //                 'error' => $errorMessage,
    //             ];
    //             return response()->json($response, 500);
    //     }
    // }

    public function store(Request $request)
    {
        try {
            $now = now();
            $stringTime = strtotime($now);

            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' => $stringTime,
                'ip' => $request->ip(),
                'action' => 'add a properties',
                'cat' => 'properties',
            ]);

            if (!Gate::allows('QUẢN LÝ SẢN PHẨM.Thuộc tính sản phẩm.add')) {
                return response()->json([
                    'status' => false,
                    'mess' => 'no permission',
                ]);
            }

            $cat_id = $request->cat_id;
            $parent_id = $request->parentid ?? 0;
            $title = $request->title;

            DB::beginTransaction();

            $properties_id = DB::table('properties')->insertGetId([
                'title' => $title,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($parent_id != 0) {
                $parent_cat = DB::table('properties_category')->where('properties_id', $parent_id)->first();
                if ($parent_cat) {
                    $cat_id = $parent_cat->cat_id;
                }
            }

            DB::table('properties_category')->insert([
                'cat_id' => $cat_id,
                'properties_id' => $properties_id,
                'parentid' => $parent_id,
                'stt' => 100,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Property created successfully',
                'properties_id' => $properties_id,
                'cat_id' => $cat_id,
                'parent_id' => $parent_id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            if (!Gate::allows('QUẢN LÝ SẢN PHẨM.Thuộc tính sản phẩm.edit')) {
                return response()->json([
                    'status' => false,
                    'mess' => 'no permission',
                ]);
            }

            // Lấy thông tin từ bảng properties
            $property = DB::table('properties')
                ->where('id', $id)
                ->select('id', 'title')
                ->first();

            if (!$property) {
                return response()->json([
                    'status' => false,
                    'message' => 'Property not found',
                ], 404);
            }

            // Lấy thông tin từ bảng properties_category liên quan đến property
            $propertyCategory = DB::table('properties_category')
                ->where('properties_id', $id)
                ->select('cat_id', 'parentid', 'stt')
                ->first();

            $response = [
                'status' => true,
                'data' => [
                    'id' => $property->id,
                    'title' => $property->title,
                    'cat_id' => $propertyCategory->cat_id ?? null,
                    'parentid' => $propertyCategory->parentid ?? 0,
                    'stt' => $propertyCategory->stt ?? 100,
                ],
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $now = now();
            $stringTime = strtotime($now);

            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' => $stringTime,
                'ip' => $request->ip(),
                'action' => 'update a properties',
                'cat' => 'properties',
            ]);

            if (!Gate::allows('QUẢN LÝ SẢN PHẨM.Thuộc tính sản phẩm.update')) {
                return response()->json([
                    'status' => false,
                    'mess' => 'no permission',
                ]);
            }

            $title = $request->title;
            $cat_id = $request->cat_id;
            $parent_id = $request->parentid ?? 0;
            //$stt = $request->stt ?? 100;

            DB::beginTransaction();

            DB::table('properties')->where('id', $id)->update([
                'title' => $title,
                'updated_at' => now(),
            ]);

            $exists = DB::table('properties_category')->where('properties_id', $id)->exists();

            if ($parent_id != 0) {
                $parent_cat = DB::table('properties_category')->where('properties_id', $parent_id)->first();
                if ($parent_cat) {
                    $cat_id = $parent_cat->cat_id;
                }
            }

            if ($exists) {
                DB::table('properties_category')->where('properties_id', $id)->update([
                    'cat_id' => $cat_id,
                    'parentid' => $parent_id,
                    //'stt' => $stt,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('properties_category')->insert([
                    'cat_id' => $cat_id,
                    'properties_id' => $id,
                    'parentid' => $parent_id,
                    //'stt' => $stt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'success',
                'properties_id' => $id,
                'cat_id' => $cat_id,
                'parent_id' => $parent_id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id, Request $request)
    {
        try {
            if (!Gate::allows('QUẢN LÝ SẢN PHẨM.Thuộc tính sản phẩm.del')) {
                return response()->json([
                    'status' => false,
                    'mess' => 'no permission',
                ]);
            }

            $property = DB::table('properties')->where('id', $id)->first();
            if (!$property) {
                return response()->json([
                    'status' => false,
                    'message' => 'Property not found',
                ], 404);
            }

            DB::beginTransaction();

            $now = now();
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' => $stringTime,
                'ip' => $request->ip(),
                'action' => 'delete a property',
                'cat' => 'properties',
            ]);

            DB::table('properties_category')->where('properties_id', $id)->delete();

            DB::table('properties')->where('id', $id)->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'success',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteAllProductCatOption(string $id)
    {
        try {
            if (Gate::allows('QUẢN LÝ SẢN PHẨM.Thuộc tính sản phẩm.del')) {
                $arr = explode(",", $id);
                if ($id) {
                    foreach ($arr as $item) {
                        $list = ProductCatOption::where('op_id', $item)->delete();
                        ProductCatOptionDesc::where('op_id', $item)->delete();
                    }
                } else {
                    return response()->json([
                        'status' => false,
                    ], 422);
                }
                return response()->json([
                    'status' => true,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'mess' => 'no permission',
                ]);
            }

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage,
            ];
            return response()->json($response, 500);
        }
    }

}
