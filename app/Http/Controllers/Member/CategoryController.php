<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryDesc;
use App\Models\GiftPromotion;
use App\Models\Menu;
use App\Models\Present;
use App\Models\Product;
use App\Models\ProductAdvertise;
use App\Models\ProductDesc;
use App\Models\StatisticsPages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function selectCategoryChild(Request $request, $slug)
    {
        try {
            $catCheck = CategoryDesc::with('category')->where('friendly_url', $slug)->first();
            if ($slug == "linh-kien" || $slug == "phu-kien" || (isset($catCheck->category) && $catCheck->category->parentid == 11) || (isset($catCheck->category) && $catCheck->category->parentid == 12)) {
                $cat = CategoryDesc::where('friendly_url', $slug)->first();
                if (isset($cat)) {
                    $catChild = category::with('categoryDesc', 'catProperties.properties.propertiesValue')->where('parentid', $cat->cat_id)->get();
                    return response()->json([
                        'status' => true,
                        'data' => isset($catChild) ? $catChild : 'null',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => false,
            ]);
        }

    }

    public function menu()
    {
        try {
            $menu = Menu::where('display', 1)
                ->select('menu_id', 'parentid', 'menu_icon')
                ->where('pos', 'primary')
                ->with('menuDesc')
                ->get();

            $menu->each(function ($item) {
                $link = $item->menuDesc->link;

                // Remove base URL if present
                if (strpos($link, "https://vitinhnguyenkim.vn/") === 0) {
                    $link = substr($link, strlen("https://vitinhnguyenkim.vn/"));
                }

                // Remove leading slash if present
                if (strpos($link, '/') === 0) {
                    $link = substr($link, 1);
                }

                $item->menuDesc->link = $link;
            });

            $menu = $menu->groupBy('parentid');
            $result = [];

            if (isset($menu[0])) {

                foreach ($menu[0] as $value) {
                    $data = $value;
                    $dataParent = [];

                    if (isset($menu[$value->menu_id])) {
                        foreach ($menu[$value->menu_id] as $value2) {
                            $dataParent2 = $value2;
                            $parent = $menu[$value2->menu_id] ?? [];
                            $dataParent2['parentx'] = $parent;
                            $dataParent[] = $dataParent2;
                        }
                    }
                    $data['parenty'] = $dataParent;
                    $result[] = $data;
                }
            }

            return response()->json([
                'message' => 'Fetched from database',
                'status' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => false,
            ]);
        }
    }

    public function checkCompareProduct($productId)
    {
        $list = [9, 10, 13, 14, 15, 16, 166, 169];
        $products = Product::where('product_id', $productId)->select('product_id', 'cat_list')->first();

        $cat_list = explode(',', $products->cat_list);
        $cat_id = $cat_list[0];

        if (in_array($cat_id, $list)) {
            return false;
        }
        return true;
    }

    public function checkPresent($id)
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);

        $listPresent = Present::orderBy('id', 'DESC')
            ->where('StartDate', '<=', $stringTime)
            ->where('EndDate', '>=', $stringTime)
            ->where('display', 1)
            ->get();

        $arrayPresent = [];
        $product = Product::where('product_id', $id)->first();

        foreach ($listPresent as $present) {
            $listCate = explode(",", $present->list_cat);
            $listProduct = explode(",", $present->list_product);

            if ((in_array($product->cat_id, $listCate) && ($present->priceMin <= $product->price && $product->price <= $present->priceMax))
                || in_array($product->macn, $listProduct)
            ) {
                $arrayPresent[] = $present;
            }

        }
        return $arrayPresent;
    }

    public function showCategoryHeader()
    {
        $listCat = Category::where('show_home', 1)->pluck('cat_id');
        $arrCate = [];
        foreach ($listCat as $catId) {
            $cate = CategoryDesc::where('cat_id', $catId)->first();
            $listCateChild = Category::where('parentid', $catId)->pluck('cat_id');
            $CateChild = null;
            if ($listCateChild) {
                $CateChild = CategoryDesc::whereIn('cat_id', $listCateChild)->select('cat_name', 'friendly_url')->take(6)->get();
            }
            $arrCate[] = [
                'CatId' => $cate->cat_id ?? null,
                'Category' => $cate->cat_name ?? null,
                'CatUrl' => $cate->friendly_url ?? null,
                // 'CateChild'=> $CateChild??null
            ];

        }
        return response()->json([
            'status' => true,

            'data' => $arrCate,

        ]);

    }

    public function urlExists($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_NOBODY, true); // Chỉ lấy header
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Thiết lập thời gian chờ
        curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        return $responseCode === 200;
    }

    public function showCategory()
    {
        try {
            $listCat = Category::where('show_home', 1)->pluck('cat_id');

            $arrCate = [];
            foreach ($listCat as $catId) {
                $cate = CategoryDesc::where('cat_id', $catId)->first();
                $listCateChild = Category::where('parentid', $catId)->pluck('cat_id');

                // $products = Product::with([
                //     'productDesc',
                //     'brandDesc',
                //     'categoryDes',
                //     'productProperties.property',
                //     'productProperties.propertyValue',
                // ])
                $products = Product::with(['productDesc', 'brandDesc', 'categoryDes'])
                    ->select('product_id', 'TenHH', 'cat_list', 'macn', 'cat_id', 'TonKho', 'stock', 'picture', 'price', 'price_old', 'brand_id', 'status', 'votes', 'views')
                    ->whereHas('productProperties')
                    ->where('HienThi', 'Y')
                    ->where('TonKho', '>', 0)
                //->where('display', 1)
                    ->where('price', '>', 0)
                    ->where('price_old', '>', 0)

                //->where('status', 1)
                    ->whereRaw('FIND_IN_SET(?, cat_list)', [$catId])
                    ->orderByDesc('TonKho')
                    ->orderByDesc('views')
                    ->limit(12)
                    ->get();

                $arrProduct = [];
                foreach ($products as $product) {
                    $urlExist = 'http://192.168.245.190:8000/uploads/' . $product->picture;

                    // $technology = [];
                    // foreach ($product->productProperties as $prop) {
                    //     $technology[] = [
                    //         'catOption' => $prop->property->title ?? '',
                    //         'nameCatOption' => $prop->propertyValue->title ?? $prop->description ?? '',
                    //     ];
                    // }

                    $arrProduct[] = [
                        'ProductId' => $product->product_id,
                        'ParentCatId' => explode(",", $product->cat_list)[0] ?? null,
                        'maso' => $product->macn,
                        'CatId' => $product->cat_id ?? null,
                        'type' => $product->status == 5 ? 'flash-sale' : null,
                        'PriceFlashSale' => $product->productFlashSale->discount_price ?? null,
                        'stock' => $product->stock ?? null,
                        'ProductName' => $product->TenHH ?? null,
                        'Image' => $product->picture ?? null,
                        'Price' => $product->price ?? null,
                        'PriceOld' => $product->price_old ?? null,
                        'UrlProduct' => $product->productDesc->friendly_url ?? null,
                        'brandName' => $product->brandDesc->title ?? null,
                        'Category' => $product->categoryDes->cat_name ?? null,
                        'DesShort' => $product->productDesc->short ?? null,
                        'votes' => $product->votes ?? 0,
                        'compareStatus' => $this->checkCompareProduct($product->product_id),
                        'checkPresent' => $this->checkPresent($product->product_id),
                        'checkGiftPromotion' => $this->checkGiftPromotion($product->product_id),
                        //'technology' => $technology,
                    ];

                }

                $banner = ProductAdvertise::where('pos', $cate->cat_name)->where('display', 1)->first();
                $arrCate[] = [
                    'CatId' => $cate->cat_id ?? null,
                    'Category' => $cate->cat_name ?? null,
                    'background' => $cate->category->background ?? null,
                    'CatUrl' => $cate->friendly_url ?? null,
                    'ProductChild' => $arrProduct,
                    'Banner' => $banner ?? null,
                ];
            }
            return response()->json([
                'status' => true,
                'data' => $arrCate,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function checkGiftPromotion($id)
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);

        $listGiftPromotion = GiftPromotion::orderBy('id', 'DESC')
            ->where('StartDate', '<=', $stringTime)
            ->where('EndDate', '>=', $stringTime)
            ->where('display', 1)
            ->get();

        $arrayGiftPromotion = [];
        $product = Product::where('product_id', $id)->first();
        //return $listGiftPromotion;

        foreach ($listGiftPromotion as $present) {
            $listCate = explode(",", $present->list_cat);
            $listProduct = explode(",", $present->list_product);
            if ((in_array($product->cat_id, $listCate) && ($present->priceMin <= $product->price && $product->price <= $present->priceMax))
                || in_array($product->macn, $listProduct)
            ) {
                $arrayGiftPromotion[] = $present;
            }

        }
        return $arrayGiftPromotion;
    }
    public function getTechnology($id)
    {
        $dataTechnology = [];
        $options = DB::table('product_cat_option_desc')->pluck('title', 'op_id')->all();
        $product = Product::where('product_id', $id)->first();
        $techs = is_string($product->technology) ? unserialize($product->technology) : null;

        if (is_array($techs)) {
            foreach ($techs as $key => $techValue) {
                if (isset($options[$key])) {
                    $dataTechnology[] = [
                        'catOption' => $options[$key],
                        'nameCatOption' => $techValue !== "" ? $techValue : null,
                    ];
                }
            }
        }
        return $dataTechnology;
    }

    public function categoryParentName()
    {
        try {
            $catListParentId = Category::where('parentid', 0)->pluck('cat_id')
                ->toArray();

            $arr = [];
            // $categories = Category::with(['categoryDesc' => function ($query) {
            //     $query->select('cat_id', 'cat_name');
            //         }])->select('cat_id', 'parentid')
            //         ->whereIn('cat_id', $catListParentId)
            //         ->get();
            $categories = CategoryDesc::with(['category' => function ($query) {
                $query->select('cat_id', 'picture');
            }])->select('cat_id', 'cat_name', 'friendly_url')
                ->whereIn('cat_id', $catListParentId)
                ->get();
            return response()->json([
                'status' => true,
                'categories' => $categories,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

    }

    public function index(Request $request)
    {
        try {
            //productDesc
            $category = CategoryDesc::where('friendly_url', $request->slug)->first();
            $products = Product::with('productDesc', 'brandDesc', 'categoryDes')
                ->where('stock', 1)
                ->where('display', 1)
                ->whereRaw('FIND_IN_SET(?, cat_list)', [$category->cat_id])
                ->orderBy('views', 'desc')
                ->limit(10)->get();
            $arrProduct = [];
            foreach ($products as $product) {
                $urlExist = 'http://192.168.245.190:8000/uploads/' . $product->picture;

                if (@file_get_contents($urlExist)) {

                    $arrProduct[] = [
                        'ProductId' => $product->product_id,
                        'ParentCatId' => explode(",", $product->cat_list)[0] ?? null,
                        'CatId' => $product->cat_id ?? null,
                        // 'CatId'=>explode(",",$product->cat_list)[0],
                        // 'CatId'=>$product->cat_id,
                        'Stock' => $product->stock,
                        'ProductName' => $product->productDesc->title,
                        'Image' => $product->picture,
                        'Price' => $product->price,
                        'PriceOld' => $product->price_old,
                        'UrlProduct' => $product->productDesc->friendly_url,
                        'brandName' => $product->brandDesc->title,
                        // 'Category'=>$category->cat_name,
                        'Category' => $product->categoryDes->cat_name ?? null,
                        'DesShort' => $product->productDesc->short,
                        'compareStatus' => $this->checkCompareProduct($product->product_id),
                        'checkPresent' => $this->checkPresent($product->product_id),
                        'checkGiftPromotion' => $this->checkGiftPromotion($product->product_id),
                    ];
                }
            }
            return response()->json([
                'status' => true,
                'listProduct' => $arrProduct,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        // $catListParentId=Category::where('parentid',0)->pluck('cat_id')
        // ->toArray();

        // $arr = [];
        // $categories = Category::with(['categoryDesc' => function ($query) {
        //     $query->select('cat_id', 'cat_name');
        //         }])->select('cat_id', 'parentid')
        //         ->whereIn('cat_id', $catListParentId)
        //         ->get();
        //         foreach ($categories as $category) {

        //             $products = Product::with(['productDesc' => function ($query) {
        //                     $query->select('product_id', 'title','friendly_url','short');
        //                 }])->select('product_id', 'price', 'price_old'
        //                 ,'picture')
        //                 ->where('stock', 1)
        //                 ->where('display', 1)
        //                 ->whereRaw('FIND_IN_SET(?, cat_list)', [$category->cat_id])
        //                 ->limit(10)->get();
        //             $category->product_child = $products;
        //             foreach ($products as $key => $value) {
        //                  $value->technology=$this->getTechnology($value->product_id);
        //                  $encry =  Crypt::encryptString($value->product_id);

        //                  $encryKey = substr($encry,2);
        //                  $value->product_id = 0;
        //                  $value->product_encry_key = $encryKey;

        //             }
        //             $arr[] = $category;
        //         }

        //     return response()->json([
        //         'data' => $arr,
        //         'status' => true,
        //     ]);

    }

    public function statisticsCategory(Request $request)
    {
        try {
            // $cate=StatisticsPages::where('module','category')->orderBy('count','desc')->pluck('url')->take(10);
            // $product=StatisticsPages::where('module','product')->orderBy('count','desc')->pluck('url')->take(10);

            // $cateDes=CategoryDesc::whereIn('friendly_url',$cate)->select('cat_name','friendly_url')->get();
            // $productDes=ProductDesc::whereIn('friendly_url',$product)->select('title','friendly_url')->get();
            $statistics = StatisticsPages::orderBy('count', 'desc')->take(15)->get();

            $arrStatistics = [];
            foreach ($statistics as $item) {
                if ($item->module == "category") {
                    $CategoryDesc = CategoryDesc::where('friendly_url', $item->url)->select('cat_name')->first();
                    if ($CategoryDesc) {
                        $cat_name = $CategoryDesc->cat_name;
                        $arrStatistics[] = [
                            'type' => $item->module,
                            'name' => $cat_name ?? null,
                            'url' => $item->url ?? null,
                        ];

                    }

                } else if ($item->module == "product") {
                    $ProductDesc = ProductDesc::where('friendly_url', $item->url)->select('title')->first();
                    if ($ProductDesc) {
                        $product_name = $ProductDesc->title;
                        $arrStatistics[] = [
                            'type' => $item->module,
                            'name' => $product_name ?? null,
                            'url' => $item->url ?? null,
                        ];

                    }
                    //ProductDesc::where('friendly_url',$item->url)->select('title')->first()->title;

                    // $arrStatistics[]=[
                    //     'type'=>$item->module,
                    //     'name'=> $product_name??null,
                    //     'url'=>$item->url??null
                    // ];
                }
            }

            return response()->json([
                'status' => true,
                'data' => $arrStatistics,
                // 'product'=>$productDes
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

    }

}
