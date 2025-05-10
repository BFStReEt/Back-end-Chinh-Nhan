<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\NewsDesc;
use App\Models\NewsCategory;
use App\Models\PromotionDesc;

class NewsCategoryController extends Controller
{
    public function index(){
        try{
            $newCategory = NewsCategory::with('newsCategoryDesc')
            ->has('newsCategoryDesc')
                    ->where('display', 1)
                    ->get();

            return response()->json([
                'status' => true,
                'data' => $newCategory,
            ]);
        }catch(Exception $e){
            return response()->json([
              'status' => false,
              'message' => $e->getMessage()
            ]);
        }

    }
    public function getMetaCategoryNews($slug){
        try{
            if($slug=="tin-khuyen-mai"){
                $meta=[
                    'metakey'=>"Tin khuyến mãi",
                    'metadesc'=>"Tin khuyến mãi",
                ];
                //$PromotionDesc=PromotionDesc::where('friendly_url',$slug)->select('metakey','metadesc')->first();

            return response()->json([
                'status'=>true,
                'data'=>$meta
            ]);

            }

            $category = NewsCategory::with('newsCategoryDesc')
            ->whereHas('newsCategoryDesc', function ($query) use ($slug) {$query->where('friendly_url',$slug);})
            ->where('display', 1)
            ->first();
            //return $category->newsCategoryDesc;
            $meta=[
                'metakey'=>$category->newsCategoryDesc->metakey??null,
                'metadesc'=>$category->newsCategoryDesc->metadesc??null,
            ];
            return response()->json([
                'status'=>true,
                'data'=>$meta
            ]);
        }
        catch(Exception $e){
            return response()->json([
              'status' => false,
              'message' => $e->getMessage()
            ]);
        }
    }
}
