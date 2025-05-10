<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\CategoryDesc;
use App\Models\Properties;
use App\Models\PropertiesValue;
use App\Models\PropertiesCategory;
class PropertiesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $cat_id=$request->catId??1;
            if($request->data == 'undefined' || $request->data =="")
            {
                $listProperties = Properties::with('propertiesValue')
                ->whereHas('PropertiesCategory', function ($q) use($cat_id) {
                    $q->where('cat_id',$cat_id);
                })->get();
            }
            else{
                $listProperties = Properties::with('propertiesValue')
                ->whereHas('PropertiesCategory', function ($q) use($cat_id) {
                    $q->where('cat_id',$cat_id);
                })
                ->where("title", 'like', '%' . $request->data . '%')->get();
            }
            $response = [
                'status' => true,
                'list' => $listProperties, 
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json($response, 500);
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
        $properties = new Properties();
        try {
            if($request->input('title') == '')
            {
                if($request->value != '')
                {
                    foreach ($request->value as $item) {
                        $propertiesValue = new PropertiesValue();
                        $propertiesValue->properties_id = $request->update;
                        $propertiesValue->name = $item;
                        $propertiesValue->save();
                    }
                }
                $response = [
                    'status' => true,
                ];
                return response()->json($response, 200);
            }
            else
            {

                $properties->title = $request->input('title');
                $properties->save();
                if($request->cat_id !=[])
                {
                    foreach ($request->cat_id as $items) {
                        $cate=Category::where('cat_id',$items)->first();
                        $propertiesCategory = new PropertiesCategory();
                        $propertiesCategory->cat_id = $items;
                        $propertiesCategory->properties_id = $properties->id;
                        if($cate->parentid!=0)
                        {
                            $propertiesCategory->parentid =  $cate->parentid;
                        }
                        $propertiesCategory->save();

                    }
                }
                if($request->value != '')
                {
                    foreach ($request->value as $item) {
                        $propertiesValue = new PropertiesValue();
                        $propertiesValue->properties_id = $properties->id;
                        $propertiesValue->name = $item;
                        $propertiesValue->save();
                    }
                }
                $response = [
                    'status' => true,
                    'data' => $properties,
                ];
                return response()->json($response, 200);
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
            $listProperties = Properties::find($id);
            $listProperties['cat_id'] = PropertiesCategory::where('properties_id',$id)->pluck('cat_id');
            $listProperties['propertiesValue'] = PropertiesValue::where('properties_id', $id)->pluck('name');
            $response = [
                'status' => true,
                'list' => $listProperties
            ];
            return response()->json($response, 200);
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
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
            $properties = Properties::find($id);

                $properties->title = $request->input('title');
                $properties->save();

                $listPropertiesCategory = PropertiesCategory::where('properties_id',$id)->delete();
                if($request->cat_id !=[])
                {
                    foreach ($request->cat_id as $items) {
                        $propertiesCategory = new PropertiesCategory();
                        $propertiesCategory->cat_id = $items;
                        $propertiesCategory->properties_id = $properties->id;
                        $cate=Category::where('cat_id',$items)->first();
                        if($cate->parentid!=0)
                        {
                            $propertiesCategory->parentid =  $cate->parentid;
                        }
                        $propertiesCategory->save();
                    }
                }

                $listPropertiesValue = PropertiesValue::where('properties_id',$id)->delete();
                if($request->value != '')
                {
                    foreach ($request->value as $item) {
                        $propertiesValue = new PropertiesValue();
                        $propertiesValue->properties_id = $id;
                        $propertiesValue->name = $item;
                        $propertiesValue->save();
                    }
                }


                $response = [
                    'status' => true,
                    'data' => $properties,
                ];
                return response()->json($response, 200);
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
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
            $properties = Properties::Find($id)->delete();
            $propertiesValue = PropertiesValue::where('properties_id',$id)->delete();
            $propertiesCategory = PropertiesCategory::where('properties_id',$id)->delete();
            $response = [
                'status' => true

            ];
            return response()->json($response, 200);

        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }
}
