<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\CategoryDesc;
use App\Models\BrandDesc;
use App\Models\Product;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
class BrandController extends Controller
{

    public function buildCatalog($categories)
    {
        try{
            $catalog = null;
            foreach ($categories as $category) {
                $listBrand=[];
                $array = explode(',', $category->list_brand);
                $data=[];
                if($array!=null){
                    foreach($array as $item){
                        if($item!="" && BrandDesc::where('id',$item)->first()){
                            $listBrand[]=[
                                'id'=>$item,
                                'title'=>BrandDesc::where('id',$item)->first()->title??null,
                                'friendly_url'=>BrandDesc::where('id',$item)->first()->friendly_url??null,
                                'metakey'=> BrandDesc::where('id',$item)->first()->metakey ?? null,
                                'metadesc'=> BrandDesc::where('id',$item)->first()->metadesc ?? null
                            ];
                        }
                    }
                }

                $catalog = $listBrand;
            }
            return $catalog;
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function listBrand($idCategory){
        try{
            // $category = Category::with('categoryDesc')->where('cat_id',$idCategory)
            // ->first();
            $categories = Category::with('categoryDesc')->where('cat_id',$idCategory)->get();
            $catalog = $this->buildCatalog($categories);

            return response()->json($catalog);


            // $listBrand=[];
            // $array = explode(',', $category->list_brand);
            // $data=[];
            // if($array!=null){
            //         foreach($array as $item){
            //             if($item!=""){
            //                 $brandDesc=BrandDesc::where('id',$item)->first();
            //                 $listBrand[]=[
            //                     'id'=>$item,
            //                     'value'=> $brandDesc->title??null,
            //                     'label'=> $brandDesc->title??null,
            //                     'friendly_url'=> $brandDesc->friendly_url??null,
            //                     'picture'=> $brandDesc->brand->picture??null
            //                 ];
            //             }
            //         }
            // }
            // $id = $category->cat_id;
            // $catalogItem = [
            //     'id'=>$category->cat_id,
            //     'listBrand'=>$listBrand,
            // ];

            return $catalogItem;


        }catch(Exception $e){
           return response()->json([
             'status' => false,
             'message' => $e->getMessage()
           ], 422);
       }

    }
    public function searchCategoryProduct(Request $request){
        try{
            $idBrand = $request->brand;
            $idCate = $request->key;

            $productPrice = $request->priceMin;
            $productPriceOld = $request->priceMax;
            $searchKeywords= $request->search;

            $query = Product::whereRaw('FIND_IN_SET(?, cat_list)', [$idCate]);

            //return $productPriceOld;
            if(isset($productPrice) && isset($productPriceOld))
            {
                if(!empty($idBrand)){

                    $query=$query->where('brand_id', $idBrand)
                    ->whereBetween('price_old',[$productPrice,$productPriceOld]);
                }
                else{
                    $query=$query->whereBetween('price_old',[$productPrice,$productPriceOld]);

                }
            }
            // return 2222;
            try {
                $client = new Client();
                $response = $client->get('http://192.168.245.176:8503/api/product-avatar');
                $listItemImg = json_decode($response->getBody(), true);
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage(),
                ]);
            }
            $query=$query->whereHas('productDesc', function ($q) use ($searchKeywords) {
                    $q->where('macn', 'LIKE', '%' . $searchKeywords . '%')
                    ->orWhere('title', 'LIKE', '%' . $searchKeywords . '%');
            })->orderBy('product_id', 'desc')->take(30)->get();


            foreach($query as $value)
            {
                // if (isset($listItemImg[$value->product_id])) {
                //     $value->picture = $listItemImg[$value->product_id];
                // }
                    $data[] = [
                        'value' => $value->productDesc->title ?? null,
                        'productId' => $value->product_id,
                        'productName' => $value->productDesc->title ?? null,
                        'picture' => $value->picture ?? null,
                        'price' => $value->price ?? null,
                        'price_old' => $value->price_old?? null,
                        'friendlyUrl' => $value->productDesc->friendly_url ?? null,
                    ];

            }
            return response()->json($data ?? null);
        }catch(Exception $e){
            return response()->json([
              'status' => false,
              'message' => $e->getMessage()
            ], 422);
        }
    }
    public function getTechnology($id){

        $dataTechnology = [];
        $options = DB::table('product_cat_option_desc')->pluck('title', 'op_id')->all();

        $product = Product::where('product_id', $id)->first();
        $techs = is_string($product->technology) ? unserialize($product->technology) : null;

        if (is_array($techs)) {
            foreach ($techs as $key => $techValue) {
                if (isset($options[$key])) {
                    $dataTechnology[] = [
                        'catOption' => $options[$key],
                        'nameCatOption' => $techValue !== "" ? $techValue : null
                    ];
                }
            }
        }
        return $dataTechnology;
    }
    public function compareProducts(Request $request)
    {

        try{
            $key1 = $request->key1;
            $key2 = $request->key2;

            $ids = [
                'key1' => $key1,
                'key2' => $key2
            ];
            $data = [];
            $client = new Client();
            $response = $client->get('http://192.168.245.176:8503/api/product-avatar');
            $listItemImg = json_decode($response->getBody(), true);
            foreach ($ids as $index => $id) {
                if (!$id) {
                    continue;
                }

                //return Product::with('productDesc')->where('product_id', $id)->get();

                $info = Product::with('productDesc')->where('product_id', $id)
                    ->leftJoin('product_category_desc', 'product_category_desc.cat_id', '=', 'products.cat_id')
                    ->leftJoin('product_brand_desc', 'product_brand_desc.brand_id', '=', 'products.brand_id')
                    ->select(
                        '*',
                        'product_category_desc.cat_name as product_category',
                        'product_brand_desc.title as product_brand'
                    )->first();




                $dataValue=$this->getTechnology($id);

                // if (isset($listItemImg[$info->product_id])) {
                //     $info->picture = $listItemImg[$info->product_id];
                // }

                $data[] = [
                    'ProductId' => $info->product_id,
                    'Image' => $info->picture,
                    'listPictures' => $info->productPicture,
                    'Price' => $info->price,
                    'PriceOld' => $info->price_old,
                    'UrlProduct' => $info->productDesc->friendly_url,
                    'Category' => $info->cat_name,
                    'ProductName' => $info->productDesc->title,
                    'brandName' => $info->product_brand,
                    'dataTechnology'  => mb_convert_encoding($dataValue, 'UTF-8', 'UTF-8') ?? null,
                ];

            }

            return response()->json([
                'data' => $data,
            ]);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

    }

}
