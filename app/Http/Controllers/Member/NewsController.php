<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\NewsDesc;
use App\Models\NewsCategory;
use App\Models\PromotionDesc;
use Illuminate\Support\Facades\DB;

class NewsController extends Controller
{
    public function showNewsbyViews(){
        try{
            //news_category_desc
            $listData=DB::table('news')
            ->where('display', 1)
            ->join('news_desc', 'news_desc.news_id', '=', 'news.news_id')
            ->join('news_category_desc', 'news_category_desc.cat_id', '=', 'news.cat_id')
            ->select('news.*','news_desc.title','news_category_desc.cat_name','news_category_desc.friendly_url as category_url','news_desc.short','news_desc.friendly_url',
            'news_desc.metakey','news_desc.metadesc')
            ->orderBy('news.views','desc')
            ->limit(5)->get();
            return response()->json([
                'status'=>true,
                'listNews'=>$listData
            ]);
        }catch(Exception $e){
            return response()->json([
              'status' => false,
              'message' => $e->getMessage()
            ]);
        }
    }

    public function take5news(Request $request)
    {
        try {
            $news = \DB::table('news')
                ->where('cat_id', 12)
                ->orderBy('news_id', 'desc')
                ->take(5)
                ->get();

            if ($news->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No news found for the given category.',
                ], 404);
            }

            $newsIds = $news->pluck('news_id');

            $newsDescriptions = \DB::table('news_desc')
                ->whereIn('news_id', $newsIds)
                ->get();

            $categoryDescriptions = \DB::table('news_category_desc')
                ->where('cat_id', 12)
                ->first(); 

            $result = $news->map(function ($item) use ($newsDescriptions, $categoryDescriptions) {
                $desc = $newsDescriptions->firstWhere('news_id', $item->news_id);
                $item->title = $desc->title ?? null;
                $item->short = $desc->short ?? null;
                $item->friendly_url = $desc->friendly_url ?? null; 
                $item->cat_name = $categoryDescriptions->cat_name ?? null;
                $item->cat_friendly_url = $categoryDescriptions->friendly_url ?? null;

                return $item;
            });

            return response()->json([
                'status' => true,
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching news.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function CategoryNewProdut(){
        $listNew=DB::table('news')
        ->where('news.cat_id',13)
        ->where('news.display', 1)
        ->join('news_desc', 'news_desc.news_id', '=', 'news.news_id')
        ->join('news_category_desc', 'news_category_desc.cat_id', '=', 'news.cat_id')
        ->select('news.*','news_desc.title','news_category_desc.cat_name','news_category_desc.friendly_url as url_cat','news_desc.short','news_desc.friendly_url','news_desc.metakey','news_desc.metadesc')
        ->orderBy('news.news_id','desc')
        ->limit(5)
        ->get();
        return response()->json([
            'status'=>true,
            'data'=>$listNew
        ]);
    }

    public function index($slug)
    {

        try{

            $category = NewsCategory::with('newsCategoryDesc')
            ->whereHas('newsCategoryDesc', function ($query) use ($slug) {$query->where('friendly_url',$slug);})
            ->where('display', 1)
            ->first()->cat_id;


            $listNew=DB::table('news')
            ->where('news.cat_id',$category)
            ->where('news.display', 1)
            ->join('news_desc', 'news_desc.news_id', '=', 'news.news_id')
            ->join('news_category_desc', 'news_category_desc.cat_id', '=', 'news.cat_id')
            ->select('news.*','news_desc.title','news_category_desc.cat_name','news_category_desc.friendly_url as url_cat','news_desc.short','news_desc.friendly_url','news_desc.metakey','news_desc.metadesc')
            ->orderBy('news.news_id','desc')
            ->limit(5)
            ->get();

            $listView=DB::table('news')
            ->where('news.cat_id',$category)
            ->where('display', 1)
            ->join('news_desc', 'news_desc.news_id', '=', 'news.news_id')
            ->join('news_category_desc', 'news_category_desc.cat_id', '=', 'news.cat_id')
            ->select('news.*','news_desc.title','news_category_desc.cat_name','news_category_desc.friendly_url as url_cat','news_desc.short','news_desc.friendly_url','news_desc.metakey','news_desc.metadesc')
            ->orderBy('news.views','desc')->paginate(15);
            return response()->json([
                'status' => true,
                'listNew' => $listNew,
                'listView' => $listView,
            ]);
        }catch(Exception $e){
            return response()->json([
              'status' => false,
              'message' => $e->getMessage()
            ]);
        }

    }

    public function search(Request $request)
    {
       try{
            if(isset($_GET['search'])){
                $search=$_GET['search'];
                $listNews = NewsDesc::with('news')->where('title', 'LIKE', '%'.$search.'%')->get();
                return response()->json($listNews);
            }else{
                return response()->json([
                    'message' => 'Invalid search parameters  provided for this search term.',
                    'status' => true
                ]);
            }
        }
        catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

    }

    public function showDetail($slug){
        try{

        }catch(Exception $e){
            return response()->json([
             'status' => false,
             'message' => $e->getMessage()
            ]);
        }
    }

    public function detail(Request $request,$catUrl,$slug)
    {
        try{

            if($catUrl=="tin-khuyen-mai"){
                $promotionDesc = PromotionDesc::where('friendly_url',$slug)->first();

                return response()->json([
                    'status' => true,
                    'data'=> $promotionDesc,

                ]);

            }else{
                $urlCat=$catUrl;
                $cat_id = NewsCategory::with('newsCategoryDesc')
                ->whereHas('newsCategoryDesc', function ($query) use ($urlCat) {$query->where('friendly_url',$urlCat);})
                ->where('display', 1)
                ->first()->cat_id;

                $newsDesc = News::with('newsDesc')->where('cat_id',$cat_id)
                        ->whereHas('newsDesc', function ($q) use ($slug) {
                            $q->where('friendly_url', 'LIKE', '%' . $slug . '%');
                        })->first();
                return response()->json([
                    'status' => true,
                    'data'=> $newsDesc,
                ]);

            }

        }catch(Exception $e){
            return response()->json([
             'status' => false,
             'message' => $e->getMessage()
            ]);
        }
    }

    public function getMetaCategoryNews($slug)
    {

        try{

            $category = NewsCategory::with('newsCategoryDesc')
            ->whereHas('newsCategoryDesc', function ($query) use ($slug) {$query->where('friendly_url',$slug);})
            ->where('display', 1)
            ->first()->cat_id;


            $listNew=DB::table('news')
            ->where('news.cat_id',$category)
            ->where('news.display', 1)
            ->join('news_desc', 'news_desc.news_id', '=', 'news.news_id')
            ->join('news_category_desc', 'news_category_desc.cat_id', '=', 'news.cat_id')
            ->select('news.*','news_desc.title','news_category_desc.cat_name','news_category_desc.friendly_url as url_cat','news_desc.short','news_desc.friendly_url','news_desc.metakey','news_desc.metadesc')
            ->orderBy('news.news_id','desc')
            ->limit(5)
            ->get();

            $listView=DB::table('news')
            ->where('news.cat_id',$category)
            ->where('display', 1)
            ->join('news_desc', 'news_desc.news_id', '=', 'news.news_id')
            ->join('news_category_desc', 'news_category_desc.cat_id', '=', 'news.cat_id')
            ->select('news.*','news_desc.title','news_category_desc.cat_name','news_category_desc.friendly_url as url_cat','news_desc.short','news_desc.friendly_url','news_desc.metakey','news_desc.metadesc')
            ->orderBy('news.views','desc')->paginate(15);
            return response()->json([
                'status' => true,
                'listNew' => $listNew,
                'listView' => $listView,
            ]);
        }catch(Exception $e){
            return response()->json([
              'status' => false,
              'message' => $e->getMessage()
            ]);
        }

    }

    public function getMetaDetail(Request $request,$catUrl,$slug)
    {
        try{

            if($catUrl=="tin-khuyen-mai"){
                $promotionDesc = PromotionDesc::with('promotion')->where('friendly_url',$slug)
                //->select('metakey','metadesc')
                ->first();
                $meta=[
                    'metakey'=>$promotionDesc->metakey??null,
                    'metadesc'=>$promotionDesc->metadesc??null,
                    'title'=>$promotionDesc->title??null,
                    'description'=>$promotionDesc->description??null,
                    'image'=>$promotionDesc->promotion->picture??null
                ];

                return response()->json([
                    'status' => true,
                    'data'=> $meta,

                ]);

            }else{
                $urlCat=$catUrl;
                $cat_id = NewsCategory::with('newsCategoryDesc')
                ->whereHas('newsCategoryDesc', function ($query) use ($urlCat) {$query->where('friendly_url',$urlCat);})
                ->where('display', 1)
                ->first()->cat_id;

                $newsDesc = News::with('newsDesc')->where('cat_id',$cat_id)
                        ->whereHas('newsDesc', function ($q) use ($slug) {
                            $q->where('friendly_url', 'LIKE', '%' . $slug . '%');
                        })->first();
                //$meta=$newsDesc?$newsDesc->newsDesc->select('metakey','metadesc')->first():null;
                $meta=[
                    'metakey'=>$newsDesc->newsDesc->metakey??null,
                    'metadesc'=>$newsDesc->newsDesc->metadesc??null,
                    'title'=>$newsDesc->newsDesc->title??null,
                    'description'=>$newsDesc->newsDesc->description??null,
                    'image'=>$newsDesc->picture??null
                ];
                return response()->json([
                    'status' => true,
                    'data'=> $meta,
                ]);

            }

        }catch(Exception $e){
            return response()->json([
             'status' => false,
             'message' => $e->getMessage()
            ]);
        }
    }



    public function relatedNew(Request $request,$catUrl,$slug){
        try{

            $slugCategoty=$catUrl;
            $slugProduct=$slug;

            $queryCategory = NewsCategory::with('newsCategoryDesc')
            ->whereHas('newsCategoryDesc', function ($query) use ($slugCategoty) {$query->where('friendly_url',$slugCategoty);})
            ->where('display', 1)
            ->first();

            $queryNew=NewsDesc::where('friendly_url',$slugProduct)->first();

            $relatedNew=null;
            if($queryCategory &&  $queryNew){
                $category=$queryCategory->cat_id;
                $new= $queryNew->news_id;
                $relatedNew=DB::table('news')
                ->where('news.cat_id',$category)
                ->where('news.news_id','!=', $new)
                ->where('news.display', 1)
                ->join('news_desc', 'news_desc.news_id', '=', 'news.news_id')
                ->join('news_category_desc', 'news_category_desc.cat_id', '=', 'news.cat_id')
                ->select('news.*','news_desc.title','news_category_desc.cat_name','news_category_desc.friendly_url as url_cat','news_desc.short','news_desc.friendly_url','news_desc.metakey','news_desc.metadesc')
                ->orderBy('news.news_id','desc')
                ->limit(8)
                ->get();

            }

            return response()->json([
                'status'=>true,
                'relatedNew'=>$relatedNew
            ]);
        }catch(Exception $e){
            return response()->json([
             'status' => false,
             'message' => $e->getMessage()
            ]);
        }
    }
}
