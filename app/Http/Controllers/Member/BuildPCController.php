<?php

namespace App\Http\Controllers\Member;

use App\Exports\BuildPCExport;
use App\Http\Controllers\Controller;
use App\Models\BrandDesc;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCatOptionDesc;
use App\Models\productDesc;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BuildPCController extends Controller
{
    public function filterBuildPc(Request $request)
    {
        try {
            $catId = $request->key;
            $opSearch = Product::where('cat_id', $catId)->pluck('op_search')->filter()->toArray();
            $brand = Product::with('brandDesc')->where('cat_id', $catId)->get();

            $out = [];
            foreach ($opSearch as $item) {
                $sub = explode(',', $item);
                $out = array_merge($out, $sub);
            }

            $listOpsearch = ProductCatOptionDesc::whereIn('op_id', $out)->get();
            $itemData = [];
            $brandId = [];

            foreach ($brand as $item) {
                if (!in_array($item->brand_id, $brandId) && isset($item->brandDesc)) {
                    $brandId[] = $item->brand_id;
                    $dataBrand[] = [
                        'brandName' => $item->brandDesc->title ?? null,
                        'brandId' => $item->brand_id ?? null,
                        'label' => $item->brandDesc->title ?? null,
                        'value' => $item->brand_id ?? null,

                    ];
                }
            }

            $dataOpsearch = $listOpsearch->map(function ($val) {
                return [
                    'OpSearchName' => $val->title,
                    'Op_id' => $val->op_id,
                    'label' => $val->title,
                    'value' => $val->op_id,

                ];
            });

            return response()->json([
                'brand' => $dataBrand,
                'opSearch' => $dataOpsearch,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function index(Request $request)
    {
        try {
            $offset = $request->page ? $request->page : 1;
            if ($request->has('key')) {
                $catId = $request->key;
                $name = $request->NameProduct;
                $brandId = $request->BrandId;
                $opSearch = $request->OpSearch;
                $price = $request->Sort ? $request->Sort : 'DESC';
                $view = $request->View ? $request->View : 'DESC';
                $products = Product::whereRaw('FIND_IN_SET(?, cat_list)', [$catId])
                    ->with('productDesc', 'category.categoryDesc');
                // ->where('cat_id', $catId)
                // ->where('stock',1);
                if (!empty($price)) {
                    $products->orderBy('price', $price);
                }
                if (!empty($view)) {
                    $products->orderBy('views', $view);
                }
                if (!empty($opSearch)) {
                    $products->whereRaw('FIND_IN_SET(?,op_search)', [$opSearch]);
                }
                if (!empty($brandId)) {
                    $products->where('brand_id', $brandId);
                }
                if (!empty($name)) {
                    $products->whereHas('productDesc', function ($q) use ($name) {
                        $q->where('title', 'LIKE', '%' . $name . '%');
                    });
                }

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

                $total = count($products->get());
                $listProduct = $products->limit(10)
                    ->offset(($offset - 1) * 10)->get();

                foreach ($listProduct as $product) {
                    $brandName = null;
                    $BrandDesc = BrandDesc::where('brand_id', $product->brand_id)->first();
                    if ($BrandDesc) {
                        $brandName = $BrandDesc->title;
                    }

                    $catNameParent = Category::with('categoryDesc')->where('cat_id', $product->category->parentid)->first();
                    // if(!empty($listItemImg[$product->product_id])){
                    //     $product->picture = $listItemImg[$product->product_id];
                    // }
                    $price = $product->price;
                    $price_old = $product->price_old;

                    $data[] = [
                        'ProductName' => $product->productDesc->title ?? null,
                        'Price' => $price ?? null,
                        'CatId' => $product->cat_id ?? null,
                        'PriceOld' => $price_old ?? null,
                        // 'Picture' => $product->picture ?? null,
                        'Image' => $product->picture ?? null,

                        // 'ProductName' => $product->productDesc->friendly_url ?? null,
                        'ProductId' => $product->product_id,
                        'Category' => $product->categoryDes->cat_name,
                        // 'cat_name_parrent' =>$catNameParent,
                        'Macn' => $product->macn,
                        'stock' => $product->stock,
                        'brandName' => $brandName,

                        // 'metakey'=> $product->productDesc->metakey ?? null,
                        // 'metadesc'=> $product->productDesc->metadesc ?? null
                    ];

                }

                return response()->json([
                    'productResult' => $data ?? null,
                    'total' => $total,
                    'status' => true,
                ]);

            } else {
                $catCodes = Category::where('cat_code', 'like', '170%')
                    ->pluck('cat_code')
                    ->toArray();

                $catIds = [];

                foreach ($catCodes as $code) {
                    $parts = explode('_', $code);
                    if (isset($parts[1])) {
                        $catIds[] = (int) $parts[1];
                    }
                }

                $categories = Category::whereIn('cat_id', $catIds)->pluck('cat_id');

                $category_monitor = Category::where('parentid', 170)->pluck('cat_id');
                $listProduct_monitor = Product::with('productDesc', 'category', 'category.categoryDesc', 'category.subCategories')
                    ->whereIn('cat_id', $category_monitor)
                    ->orderBy('product_id', 'desc')
                    ->limit(1)
                    ->get();

                foreach ($listProduct_monitor as $key => $catProduct1) {

                    $data1[] = [
                        'catId' => $catProduct1->cat_id,
                        'catList' => $catProduct1->cat_list,
                        'catName' => 'Màn hình',
                        'picture' => $catProduct1->picture,
                        'price' => $catProduct1->price ?? null,
                        'priceOld' => $catProduct1->price_old ?? null,
                        'priceSAP' => $catProduct1->PriceSAP ?? null,
                        'productId' => $catProduct1->product_id,
                        'productName' => $catProduct1->productDesc->title ?? null,
                        'friendlyName' => $catProduct1->productDesc->friendly_url ?? null,
                        'friendlyTitle' => $catProduct1->productDesc->friendly_title ?? null,
                        'picture' => $catProduct1->picture ?? null,
                        'parentId' => $catProduct1->category->parentid,
                        'metakey' => $catProduct1->productDesc->metakey ?? null,
                        'metadesc' => $catProduct1->productDesc->metadesc ?? null,
                    ];

                }
                $groupData1 = [];
                foreach ($data1 as $item1) {
                    $parentId1 = $item1['catId'];
                    if (!isset($groupData1[$parentId1])) {
                        $groupData1[$parentId1] = [
                            'catId' => $parentId1,
                            'catList' => $item1['catList'],
                            'catName' => $item1['catName'],
                            'picture' => $item1['picture'],
                            'productId' => $item1['productId'],
                            'productName' => $item1['productName'],
                            'friendlyName' => $item1['friendlyName'],
                            'friendlyTitle' => $item1['friendlyTitle'],
                            'price' => $item1['price'],
                            'priceOld' => $item1['priceOld'],
                            'priceSAP' => $item1['priceSAP'],
                            'picture' => $item1['picture'] ?? null,
                            'parentId' => '1',
                            'metakey' => $item1['metakey'] ?? null,
                            'metadesc' => $item1['metadesc'] ?? null,
                            'productParent' => [],
                        ];
                    }

                }

                $rearrangedData1 = array_values($groupData1);
                $category = Category::where('parentid', 11)->pluck('cat_id');
                $listProduct = Product::with('productDesc', 'category', 'category.categoryDesc', 'category.subCategories')
                    ->whereIn('cat_id', $category)->get();

                foreach ($listProduct as $key => $catProduct) {
                    // $urlExist = 'http://192.168.245.190:8000/uploads/'.$catProduct->picture;
                    // if(@file_get_contents($urlExist)){

                    $data[] = [
                        'catId' => $catProduct->cat_id,
                        'catList' => $catProduct->cat_list,
                        'catName' => $catProduct->category->categoryDesc->cat_name,
                        'picture' => $catProduct->picture,
                        'price' => $catProduct->price ?? null,
                        'priceOld' => $catProduct->price_old ?? null,
                        'priceSAP' => $catProduct->PriceSAP ?? null,
                        'productId' => $catProduct->product_id,
                        'productName' => $catProduct->productDesc->title ?? null,
                        'friendlyName' => $catProduct->productDesc->friendly_url ?? null,
                        'friendlyTitle' => $catProduct->productDesc->friendly_title ?? null,
                        'picture' => $catProduct->picture ?? null,
                        'parentId' => $catProduct->category->parentid,
                        'metakey' => $catProduct->productDesc->metakey ?? null,
                        'metadesc' => $catProduct->productDesc->metadesc ?? null,
                    ];
                    // }
                }
                $groupData = [];
                foreach ($data as $item) {
                    $parentId = $item['catId'];
                    if (!isset($groupData[$parentId])) {
                        $groupData[$parentId] = [
                            'catId' => $parentId,
                            'catList' => $item['catList'],
                            'catName' => $item['catName'],
                            'picture' => $item['picture'],
                            'productId' => $item['productId'],
                            'productName' => $item['productName'],
                            'friendlyName' => $item['friendlyName'],
                            'friendlyTitle' => $item['friendlyTitle'],
                            'price' => $item['price'],
                            'priceOld' => $item['priceOld'],
                            'priceSAP' => $item['priceSAP'],
                            'picture' => $item['picture'] ?? null,
                            'parentId' => '1',
                            'metakey' => $item['metakey'] ?? null,
                            'metadesc' => $item['metadesc'] ?? null,
                            'productParent' => [],
                        ];
                    }

                }
                $rearrangedData = array_values($groupData);
                $mergedArray = array_merge($rearrangedData, $rearrangedData1);

                return response()->json([
                    'data' => $mergedArray,
                    'status' => 'true']);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    // public function index(Request $request)
    // {
    //     try {
    //         $offset = $request->page ? $request->page : 1;
    //         if ($request->has('key')) {
    //             $catId = $request->key;
    //             $name = $request->NameProduct;
    //             $brandId = $request->BrandId;
    //             $opSearch = $request->OpSearch;
    //             $price = $request->Sort ? $request->Sort : 'DESC';
    //             $view = $request->View ? $request->View : 'DESC';
    //             $products = Product::whereRaw('FIND_IN_SET(?, cat_list)', [$catId])
    //                 ->with('productDesc', 'category.categoryDesc');
    //             // ->where('cat_id', $catId)
    //             // ->where('stock',1);
    //             if (!empty($price)) {
    //                 $products->orderBy('price', $price);
    //             }
    //             if (!empty($view)) {
    //                 $products->orderBy('views', $view);
    //             }
    //             if (!empty($opSearch)) {
    //                 $products->whereRaw('FIND_IN_SET(?,op_search)', [$opSearch]);
    //             }
    //             if (!empty($brandId)) {
    //                 $products->where('brand_id', $brandId);
    //             }
    //             if (!empty($name)) {
    //                 $products->whereHas('productDesc', function ($q) use ($name) {
    //                     $q->where('title', 'LIKE', '%' . $name . '%');
    //                 });
    //             }

    //             try {
    //                 $client = new Client();
    //                 $response = $client->get('http://192.168.245.176:8503/api/product-avatar');
    //                 $listItemImg = json_decode($response->getBody(), true);
    //             } catch (\Throwable $th) {
    //                 return response()->json([
    //                     'status' => false,
    //                     'message' => $th->getMessage(),
    //                 ]);
    //             }

    //             $total = count($products->get());
    //             $listProduct = $products->limit(10)
    //                 ->offset(($offset - 1) * 10)->get();

    //             foreach ($listProduct as $product) {
    //                 $brandName = null;
    //                 $BrandDesc = BrandDesc::where('brand_id', $product->brand_id)->first();
    //                 if ($BrandDesc) {
    //                     $brandName = $BrandDesc->title;
    //                 }

    //                 $catNameParent = Category::with('categoryDesc')->where('cat_id', $product->category->parentid)->first();
    //                 // if(!empty($listItemImg[$product->product_id])){
    //                 //     $product->picture = $listItemImg[$product->product_id];
    //                 // }
    //                 $price = $product->price;
    //                 $price_old = $product->price_old;

    //                 $data[] = [
    //                     'ProductName' => $product->productDesc->title ?? null,
    //                     'Price' => $price ?? null,
    //                     'CatId' => $product->cat_id ?? null,
    //                     'PriceOld' => $price_old ?? null,
    //                     // 'Picture' => $product->picture ?? null,
    //                     'Image' => $product->picture ?? null,

    //                     // 'ProductName' => $product->productDesc->friendly_url ?? null,
    //                     'ProductId' => $product->product_id,
    //                     'Category' => $product->categoryDes->cat_name,
    //                     // 'cat_name_parrent' =>$catNameParent,
    //                     'Macn' => $product->macn,
    //                     'stock' => $product->stock,
    //                     'brandName' => $brandName,

    //                     // 'metakey'=> $product->productDesc->metakey ?? null,
    //                     // 'metadesc'=> $product->productDesc->metadesc ?? null
    //                 ];

    //             }

    //             return response()->json([
    //                 'productResult' => $data ?? null,
    //                 'total' => $total,
    //                 'status' => true,
    //             ]);

    //         } else {
    //             $linhkien = Category::where('cat_code', 'like', '11%')
    //                 ->pluck('cat_code');

    //             $manhinh = Category::where('cat_code', 'like', '170%')
    //                 ->pluck('cat_code');

    //             $listProduct_monitor = Product::with('productDesc', 'category', 'category.categoryDesc', 'category.subCategories')
    //                 ->whereIn('cat_id', $category_monitor)->get();

    //             foreach ($listProduct_monitor as $key => $catProduct1) {

    //                 $data1[] = [
    //                     'catId' => $catProduct1->cat_id,
    //                     'catList' => $catProduct1->cat_list,
    //                     'catName' => $catProduct1->category->categoryDesc->cat_name,
    //                     'picture' => $catProduct1->picture,
    //                     'price' => $catProduct1->price ?? null,
    //                     'priceOld' => $catProduct1->price_old ?? null,
    //                     'priceSAP' => $catProduct1->PriceSAP ?? null,
    //                     'productId' => $catProduct1->product_id,
    //                     'productName' => $catProduct1->productDesc->title ?? null,
    //                     'friendlyName' => $catProduct1->productDesc->friendly_url ?? null,
    //                     'friendlyTitle' => $catProduct1->productDesc->friendly_title ?? null,
    //                     'picture' => $catProduct1->picture ?? null,
    //                     'parentId' => $catProduct1->category->parentid,
    //                     'metakey' => $catProduct1->productDesc->metakey ?? null,
    //                     'metadesc' => $catProduct1->productDesc->metadesc ?? null,
    //                 ];

    //             }
    //             $groupData1 = [];
    //             foreach ($data1 as $item1) {
    //                 $parentId1 = $item1['catId'];
    //                 if (!isset($groupData1[$parentId1])) {
    //                     $groupData1[$parentId1] = [
    //                         'catId' => $parentId1,
    //                         'catList' => $item1['catList'],
    //                         'catName' => $item1['catName'],
    //                         'picture' => $item1['picture'],
    //                         'productId' => $item1['productId'],
    //                         'productName' => $item1['productName'],
    //                         'friendlyName' => $item1['friendlyName'],
    //                         'friendlyTitle' => $item1['friendlyTitle'],
    //                         'price' => $item1['price'],
    //                         'priceOld' => $item1['priceOld'],
    //                         'priceSAP' => $item1['priceSAP'],
    //                         'picture' => $item1['picture'] ?? null,
    //                         'parentId' => '1',
    //                         'metakey' => $item1['metakey'] ?? null,
    //                         'metadesc' => $item1['metadesc'] ?? null,
    //                         'productParent' => [],
    //                     ];
    //                 }

    //             }

    //             $rearrangedData1 = array_values($groupData1);

    //             //return $listProduct_monitor;
    //             // 11 in database category_table : linh kien
    //             $category = Category::where('parentid', 11)->pluck('cat_id');
    //             $listProduct = Product::with('productDesc', 'category', 'category.categoryDesc', 'category.subCategories')
    //                 ->whereIn('cat_id', $category)->get();

    //             // $listProduct = Product::with('productDesc','category','category.categoryDesc','category.subCategories')
    //             //                 ->where(function ($query) use ($category) {
    //             //     foreach ($category as $cat) {
    //             //         $query->orWhereRaw('FIND_IN_SET(?, cat_list)', [$cat]);
    //             //     }
    //             // })->get();

    //             // return $listProduct;
    //             foreach ($listProduct as $key => $catProduct) {
    //                 // $urlExist = 'http://192.168.245.190:8000/uploads/'.$catProduct->picture;

    //                 // if(@file_get_contents($urlExist)){

    //                 $data[] = [
    //                     'catId' => $catProduct->cat_id,
    //                     'catList' => $catProduct->cat_list,
    //                     'catName' => $catProduct->category->categoryDesc->cat_name,
    //                     'picture' => $catProduct->picture,
    //                     'price' => $catProduct->price ?? null,
    //                     'priceOld' => $catProduct->price_old ?? null,
    //                     'priceSAP' => $catProduct->PriceSAP ?? null,
    //                     'productId' => $catProduct->product_id,
    //                     'productName' => $catProduct->productDesc->title ?? null,
    //                     'friendlyName' => $catProduct->productDesc->friendly_url ?? null,
    //                     'friendlyTitle' => $catProduct->productDesc->friendly_title ?? null,
    //                     'picture' => $catProduct->picture ?? null,
    //                     'parentId' => $catProduct->category->parentid,
    //                     'metakey' => $catProduct->productDesc->metakey ?? null,
    //                     'metadesc' => $catProduct->productDesc->metadesc ?? null,
    //                 ];
    //                 // }
    //             }
    //             $groupData = [];
    //             foreach ($data as $item) {
    //                 $parentId = $item['catId'];
    //                 if (!isset($groupData[$parentId])) {
    //                     $groupData[$parentId] = [
    //                         'catId' => $parentId,
    //                         'catList' => $item['catList'],
    //                         'catName' => $item['catName'],
    //                         'picture' => $item['picture'],
    //                         'productId' => $item['productId'],
    //                         'productName' => $item['productName'],
    //                         'friendlyName' => $item['friendlyName'],
    //                         'friendlyTitle' => $item['friendlyTitle'],
    //                         'price' => $item['price'],
    //                         'priceOld' => $item['priceOld'],
    //                         'priceSAP' => $item['priceSAP'],
    //                         'picture' => $item['picture'] ?? null,
    //                         'parentId' => '1',
    //                         'metakey' => $item['metakey'] ?? null,
    //                         'metadesc' => $item['metadesc'] ?? null,
    //                         'productParent' => [],
    //                     ];
    //                 }

    //             }
    //             $rearrangedData = array_values($groupData);
    //             $mergedArray = array_merge($rearrangedData, $rearrangedData1);

    //             return response()->json(['data' => $mergedArray, 'status' => 'true']);
    //         }
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage(),
    //         ]);
    //     }
    // }
    public function exportExcelPC(Request $request)
    {

        try {
            $dataKey = json_decode($request['key']);
            //return  $dataKey ;

            // return $request;
            $data = [];
            foreach ($dataKey as $item) {
                //$urlExist = 'http://192.168.245.190:8000/uploads/'.$item->Image;

                //if(@file_get_contents($urlExist)){

                $data[] = [
                    'productName' => $item->ProductName,
                    'quantity' => $item->quantity,
                    'price' => $item->Price,
                    'picture' => $item->Image,
                    'time' => Carbon::now('Asia/Ho_Chi_Minh'),
                ];
                //}
            }
            //return  $data;
            $fileName = 'build-pc.xlsx';
            $export = new BuildPCExport($data);
            $fileContents = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

            $headers = [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ];

            return response($fileContents, 200, $headers);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

}
