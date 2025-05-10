<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\CategoryDesc;
use App\Models\Comment;
use App\Models\Coupon;
use App\Models\GiftPromotion;
use App\Models\Present;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductAdvertiseSpecial;
use App\Models\ProductDesc;
use App\Models\ProductFlashSale;
use App\Models\ProductGroup;
use App\Models\ProductProperties;
use App\Models\PropertiesCategory;
use App\Models\StatisticsPages;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function getTechnology($id)
    {
        $price = Price::where('product_id', $id)->where('main', 1)->first();
        $dataOp = [];
        if (isset($price)) {
            $propertiesProduct = ProductProperties::with('properties', 'propertiesValue')->where('price_id', $price->id)->get();

            foreach ($propertiesProduct as $value) {

                if ($value->description != null || isset($value->propertiesValue)) {
                    array_push($dataOp, [
                        'catOption' => isset($value->properties) ? $value->properties->title : '',
                        'nameCatOption' => $value->description != null ? $value->description : (isset($value->propertiesValue) ? $value->propertiesValue->name : ''),
                    ]);
                }
            }
        }
        return $dataOp;
    }

    public function getProductTechnology($slug)
    {
        try {
            $productDesc = ProductDesc::where('friendly_url', $slug)->first();
            if (!isset($productDesc)) {
                $response = [
                    'status' => 'false',
                    'error' => 'null Product',
                ];
                return response()->json($response, 500);
            }

            $productId = $productDesc->product_id;
            $list = Product::with('price', 'productDesc', 'category', 'categoryDes', 'brand', 'brandDesc', 'productPicture')
                ->where('product_id', $productId)
                ->firstOrFail();

            $catList = $list->cat_list;
            $catArray = explode(",", $catList);

            $firstCat = $catArray[0];

            $PropertiesCategory = PropertiesCategory::with('properties')->where('cat_id', $firstCat)
                ->whereNotNull('stt')
                ->orderBy('stt', 'asc')->get();

            $listProperties = [];

            foreach ($PropertiesCategory as $Properties) {
                if (!is_null($Properties->properties) && !is_null($Properties->properties->title)) {
                    $listProperties[] = [
                        'catOption' => $Properties->properties->title,
                        'nameCatOption' => '',
                    ];
                }
            }

            $valueProperties = [];

            // if(!in_array($value['catOption'], $listTech)){
            //     $listTech[]=$value['catOption'];
            // }
            $listTech[] = null;

            foreach ($listProperties as $item) {
                foreach ($this->getTechnology($productId) as $value) {
                    if ($item['catOption'] == $value['catOption'] && !in_array($item['catOption'], $listTech)) {
                        $listTech[] = $item['catOption'];
                        $valueProperties[] = [
                            'catOption' => $item['catOption'],
                            'nameCatOption' => $value['nameCatOption'],
                        ];
                    }
                }
            }
            return response()->json([
                'status' => true,
                'technology' => mb_convert_encoding($valueProperties, 'UTF-8', 'UTF-8') ?? null,
            ]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage,
            ];
            return response()->json($response, 500);
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

    public static function paginate($items, $perPage = 5, $page = null)
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $total = count($items);
        $currentpage = $page;
        $offset = ($currentpage * $perPage) - $perPage;
        $itemstoshow = array_slice($items, $offset, $perPage);

        return new LengthAwarePaginator($itemstoshow, $total, $perPage);
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

    public function checkCoupon($id)
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        $dataForYou = [];

        $listCouponForYou = Coupon::with('couponDesc')->orderBy('id', 'DESC')
            ->where('StartCouponDate', '<=', $stringTime)
            ->where('EndCouponDate', '>=', $stringTime)
            ->get();

        $arrayCoupon = [];

        $product = Product::where('product_id', $id)->first();

        foreach ($listCouponForYou as $coupon) {
            $listCate = explode(",", $coupon->DanhMucSpChoPhep);
            $listProduct = explode(",", $coupon->MaKhoSPApdung);

            if ((
                in_array("all", $listCate) ||
                in_array($product->cat_id, $listCate) ||
                in_array($product->macn, $listProduct)
            ) &&
                $coupon->DonHangChapNhanTu <= $product->price &&
                $product->price >= $coupon->GiaTriCoupon
            ) {
                $arrayCoupon[] = $coupon;
            }

        }
        return $arrayCoupon;
    }

    public function getComboProduct(Request $request)
    {
        try {
            $newComboId = DB::table('product_combo')->insertGetId([
                'product_main' => $request->product_main,
                'product_child' => $request->product_child,
                'discount' => $request->discount,
                'date_post' => strtotime('now'),
                'isCheck' => $request->isCheck,
            ]);
            return response()->json([
                'status' => true,
                'newComboId' => $newComboId,
            ]);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage,
            ];
            return response()->json($response, 500);
        }

    }

    public function setStatisticsPages($mem_id, $url, $date, $ip)
    {
        if ($mem_id) {
            // $mem_id=Auth::guard('member')->user()->mem_id;
            $statisticsPages = StatisticsPages::where('mem_id', $mem_id)->where('url', $url)->first();
            if (isset($statisticsPages)) {
                $statisticsPages->date = $date;
                $statisticsPages->count = $statisticsPages->count + 1;
                $statisticsPages->ip = $ip ?? null;
                $statisticsPages->save();
            } else {
                $statisticsPages = new StatisticsPages;
                $statisticsPages->mem_id = $mem_id ?? 0;
                $statisticsPages->url = $url;
                $statisticsPages->date = $date;
                $statisticsPages->count = 1;
                $statisticsPages->module = "product";
                $statisticsPages->action = "detail_product";
                $statisticsPages->url = $url;
                $statisticsPages->ip = $ip ?? null;
                $statisticsPages->save();
            }
        } else {
            $statisticsPages = StatisticsPages::where('mem_id', 0)->where('url', $url)->first();
            if (isset($statisticsPages)) {
                $statisticsPages->date = $date;
                $statisticsPages->count = $statisticsPages->count + 1;
                $statisticsPages->ip = $ip ?? null;
                $statisticsPages->save();
            } else {
                $statisticsPages = StatisticsPages::create([
                    'url' => $url,
                    'date' => $date,
                    'count' => 1,
                    'module' => "product",
                    'action' => "detail_product",
                    'url' => $url,
                    'ip' => $ip,
                ]);
            }
        }
    }

    public function getProductName(Request $request)
    {
        try {
            $slug = $request->productSlug;
            $productDesc = ProductDesc::with('product')->select('product_id', 'title', 'friendly_url', 'description', 'metakey', 'metadesc')
                ->where('friendly_url', $slug)
                ->first();
            // $productId = $productDesc->product_id;
            // $picture = Product::with('price')->select('picture')->where('product_id',$productId)->first();
            // if(!$productDesc){
            //     return response()->json([
            //         'status'=>false,
            //         'message'=>'notFoundProduct'
            //     ]);
            // }

            $title = $productDesc->title;
            $description = $productDesc->description;
            $metakey = $productDesc->metakey;
            $metadesc = $productDesc->metadesc;
            $image = $productDesc->product->picture;
            return response()->json([
                'status' => true,
                'productName' => $title,
                'description' => $description,
                'metakey' => $metakey,
                'metadesc' => $metadesc,
                'image' => $image,
            ]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage,
            ];
            return response()->json($response, 500);
        }
    }

    public function getDescription($slug)
    {
        $productDesc = ProductDesc::select('product_id', 'description')
            ->where('friendly_url', $slug)
            ->first();
        return response()->json([
            'status' => true,
            'description' => $productDesc->description,
        ]);

    }

    public function detail(Request $request, $slug, Client $client)
    {
        try {
            $productDesc = ProductDesc::with([
                'product' => function ($query) {
                    $query->select('product_id', 'cat_list', 'status', 'cat_id', 'stock', 'display', 'views',
                        'macn', 'price', 'price_old', 'picture', 'brand_id', 'votes');
                },
            ])
                ->select('product_id', 'title', 'friendly_url')
                ->where('friendly_url', $slug)
                ->first();

            if ($productDesc) {
                $productDesc->description = ProductDesc::where('product_id', $productDesc->product_id)
                    ->value('description');
            }

            if (!$productDesc) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found with slug.',
                ], 404);
            }

            $product = $productDesc->product;
            $ParentCatId = explode(",", $product->cat_list);

            $parentid = $product->category->parentid;

            $catParent = CategoryDesc::where('cat_id', $parentid)->first();

            $catNameParent = $catParent->cat_name ?? null;
            $catNameParentUrl = $catParent->friendly_url ?? null;

            $dataValue = $this->getTechnology($productDesc->product_id);
            $pictureProduct = $product->picture;
            $arrPic[] = [
                'picture' => $pictureProduct,
            ];
            $productStatus = $product->productStatus;

            $combined_array = array_merge($arrPic, $product->productPicture->toArray());

            //    $urlProduct='http://192.168.245.190:8000/uploads/'.$product->picture;
            //     $response = Http::head($urlProduct);

            //     if ($response->successful()) {
            //         $picture=$product->picture;
            //     } else {
            //         $picture='no-image.jpg';
            //     }
            $group = [];
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);

            $productGroup = ProductGroup::where('product_main', $productDesc->product_id)
                ->whereHas('productChild', function ($q) {
                    $q->where('stock', 1);
                })
                ->where('date_start', '<=', $stringTime)
                ->where('date_end', '>=', $stringTime)
                ->get();
            if (isset($productGroup) && count($productGroup) > 0) {
                foreach ($productGroup as $items) {

                    $groupProductDesc = ProductDesc::where('product_id', $items->product_child)->first();

                    $productInfo = $groupProductDesc->product;
                    $ParentCatIdGroup = explode(",", $productInfo->cat_list);

                    $parentidGroup = $productInfo->category->parentid;

                    $catParentGroup = CategoryDesc::where('cat_id', $parentidGroup)->first();

                    $catNameParentGroup = $catParentGroup->cat_name ?? null;
                    $catNameParentUrlGroup = $catParentGroup->friendly_url ?? null;

                    $group[] = [
                        'GroupId' => $items->id_group,
                        'CatId' => $productInfo->cat_id,
                        'ParentCatId' => $ParentCatIdGroup[0] ?? null,
                        'Category' => optional($productInfo->categoryDes)->cat_name ?? null,
                        'DesShort' => optional($productInfo->categoryDes)->short ?? null,
                        'ProductId' => $groupProductDesc->product_id,
                        'ProductName' => $groupProductDesc->title,
                        'Price' => $groupProductDesc->product->price,
                        'PriceOld' => $groupProductDesc->product->price_old,
                        'Image' => $groupProductDesc->product->picture,
                        'maso' => $groupProductDesc->product->macn,
                        'macn' => $groupProductDesc->product->macn,
                        'friendly_url' => $groupProductDesc->friendly_url,
                        'GroupName' => $items->titleGroup,

                        'BrandName' => optional($productInfo->brandDesc)->title ?? null,
                        'stock' => $productInfo->stock ?? null,
                        'display' => $productInfo->display ?? null,
                        'short' => optional($groupProductDesc)->short ?? null,
                        'status' => $productInfo->status ?? null,

                        'date_start' => $items->date_start,
                        'date_end' => $items->date_end,
                        'discount' => $items->discount,
                        'checkPresent' => $this->checkPresent($groupProductDesc->product_id),
                    ];
                }
            }
            $data = [
                'CatId' => $product->cat_id,
                'ParentCatId' => $ParentCatId[0] ?? null,
                'Category' => optional($product->categoryDes)->cat_name ?? null,
                'DesShort' => optional($product->categoryDes)->short ?? null,
                'Image' => $product->picture,
                'Price' => $product->price ?? null,
                'PriceOld' => $product->price_old ?? null,
                'PriceFlashSale' => $product->productFlashSale->discount_price ?? null,
                'ProductId' => $productDesc->product_id,
                'ProductName' => optional($productDesc)->title ?? null,
                'UrlProduct' => optional($productDesc)->friendly_url ?? null,
                // 'urlCatName' => optional($product->categoryDes)->friendly_url ?? null,
                // 'catNameParent' => $catNameParent ?? null,
                // 'catIdParent' => $product->category->parentid ?? null,
                // 'catNameParentUrl' => $catNameParentUrl ?? null,
                // 'brandName' => optional($product->brandDesc)->title ?? null,
                //'productDescription' => optional($productDesc)->description ?? null,
                // 'metakey' => optional($productDesc)->metakey ?? null,
                // 'metadesc' => optional($productDesc)->metadesc ?? null,
                'BrandName' => optional($product->brandDesc)->title ?? null,
                'stock' => $product->stock ?? null,
                'display' => $product->display ?? null,
                'short' => optional($productDesc)->short ?? null,
                'status' => $product->status ?? null,
                'ImageFlashSale' => $productStatus ? $productStatus->picture : null,
                'views' => $product->views ?? null,
                'votes' => $product->votes ?? 0,
                'maso' => $product->macn ?? null,
                'compareStatus' => $this->checkCompareProduct($productDesc->product_id),
                // 'friendlyTitle' => optional($productDesc)->friendly_title ?? null,
                // 'listPictures' => $product->productPicture,
                'listPictures' => $combined_array,
                //'parameter' => mb_convert_encoding($dataValue, 'UTF-8', 'UTF-8') ?? null,
                'noPicture' => DB::table('config')->first() ? DB::table('config')->first()->picture : null,
                'groupProduct' => $group,
                //đây
                'checkPresent' => $this->checkPresent($productDesc->product_id),
                'checkCoupon' => $this->checkCoupon($productDesc->product_id),
                'checkGiftPromotion' => $this->checkGiftPromotion($productDesc->product_id),
            ];
            $commentProductId = Comment::with('subcomments')
                ->orderByDesc('comment_id')
                ->where('parentid', 0)
                ->where('module', 'product')
                ->where('display', 1)
                ->where('post_id', $productDesc->product_id)->get();

            $list_cate = explode(',', $product->cat_list);
            $i = count($list_cate);
            for ($j = 0; $j < $i; $j++) {
                $save[] = (int) $list_cate[$j];
            }
            $listCate = $save;
            $parentId = $save[0] ?? null;
            $cateId = $save[1] ?? null;
            $childId = $save[2] ?? null;
            $subChildId = $save[3] ?? null;

            $parentId = CategoryDesc::where('cat_id', $parentId)->first();
            $cateId = CategoryDesc::where('cat_id', $cateId)->first();
            $childId = CategoryDesc::where('cat_id', $childId)->first();
            $subChildId = CategoryDesc::where('cat_id', $subChildId)->first();
            $breadcrumb = [
                'parentCateId' => [
                    'cat_name' => $parentId->cat_name ?? null,
                    'url' => $parentId->friendly_url ?? null,
                ],
                'CateId' => [
                    'cat_name' => $cateId->cat_name ?? null,
                    'url' => $cateId->friendly_url ?? null,
                ],
                'childCateId' => [
                    'cat_name' => $childId->cat_name ?? null,
                    'url' => $childId->friendly_url ?? null,
                ],
                'subChildId' => [
                    'cat_name' => $subChildId->cat_name ?? null,
                    'url' => $subChildId->friendly_url ?? null,
                ],
            ];
            $memberId = null;
            // return $request->userId;
            if ($request->userId !== "null") {
                $memberId = $request->userId;
            }
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            $ip = $request->ip();
            $this->setStatisticsPages($memberId, $slug, $stringTime, $ip);

            return response()->json([
                'status' => true,
                'productDetail' => $data,
                'breadcrumb' => $breadcrumb,
                'commentProductId' => $commentProductId ?? null,
                // 'checkPresent'=>$this->checkPresent($productDesc->product_id),
                // 'checkCoupon'=>$this->checkCoupon($productDesc->product_id),
                // 'checkGiftPromotion'=>$this->checkGiftPromotion($productDesc->product_id),
                'message' => 'Fetching product from database',

            ]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage,
            ];
            return response()->json($response, 500);
        }
    }

    public function relatedProductTechnology(Request $request)
    {
        try {
            $slug = $request->key;
            $client = new Client();
            $slug = $request->key;
            // $response = $client->get('http://192.168.245.176:8503/api/product-avatar/'.$slug);
            // $listItemImg = json_decode($response->getBody(), true);

            $productDes = ProductDesc::where('friendly_url', $slug)->select('product_id')->first();
            $productId = $productDes->product_id;
            $productCatId = $productDes->product->cat_id;
            $priceList = $productDes->product->price;
            $priceMin = $priceList * 0.9;
            $priceMax = $priceList * 1.1;

            $relatedProduct = Product::where('product_id', '!=', $productId)
            // ->orWhereNull('product_id')
                ->where('cat_id', $productCatId)
                ->whereBetWeen('price', [$priceMin, $priceMax])
                ->where('stock', 1)
                ->where('display', 1)
                ->select('product_id', 'price', 'price_old', 'picture', 'stock', 'cat_id', 'brand_id', 'cat_list', 'display')
                ->orderBy('product_id', 'DESC')->take(3)->get();

            $data = [];
            foreach ($relatedProduct as $product) {
                // if (isset($listItemImg[$product->product_id])) {
                //     $product->picture = $listItemImg[$product->product_id];
                // }
                $picture = $product->picture;
                $data_technology = $this->getTechnology($product->product_id);
                //$urlExist = 'http://192.168.245.190:8000/uploads/'.$product->picture;

                //if(@file_get_contents($urlExist)){
                $data[] = [
                    'ProductId' => $product->product_id,
                    'ParentCatId' => explode(",", $product->cat_list)[0] ?? null,
                    'CatId' => $product->cat_id ?? null,
                    'stock' => $product->stock ?? null,
                    'productName' => $product->productDesc->title ?? null,
                    'brandName' => $product->brandDesc->title ?? null,
                    'catName' => $product->categoryDes->cat_name ?? null,
                    'picture' => $picture ?? null,
                    'price' => $product->price ?? null,
                    'priceOld' => $product->price_old ?? null,
                    'friendlyUrl' => $product->productDesc->friendly_url ?? null,
                    'technology' => $data_technology ?? null,
                    'compareStatus' => $this->checkCompareProduct($product->product_id),
                    'checkPresent' => $this->checkPresent($product->product_id),
                    'checkGiftPromotion' => $this->checkGiftPromotion($product->product_id),

                ];
                //}
            }
            return response()->json([
                'Technology' => $data,
                'status' => true,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    // public function groupProduct(Request $request, $slug){
    //     $productDesc = ProductDesc::where('friendly_url', $slug)->first();
    //     $product=$productDesc->product;

    //     $productId=$productDesc->product_id;
    //     if ($product->productGroups) {
    //         $group = Product::with('productGroups')
    //             ->whereHas('productGroups', function ($qr) use ($productId) {
    //                 $qr->where('product_main', $productId);
    //             })
    //             ->get();

    //         foreach ($group as $product) {

    //             $data_groups = [];

    //             foreach ($product->productGroups as $productGroup) {
    //                 $data_group = Product::with('productDesc')
    //                     ->where('product_id', $productGroup->product_child)
    //                     ->select('product_id','picture', 'cat_id', 'maso', 'price', 'price_old', 'brand_id', 'status', 'stock', 'votes', 'numvote')
    //                     ->first();

    //                 if ($data_group) {
    //                     $encry =  Crypt::encryptString($data_group->product_id);

    //                     $encryKey = substr($encry, 2);

    //                    $data_group->encryId = $encryKey;
    //                    $data_group->product_id = 0;
    //                 $data_groups[] = $data_group;
    //                 }
    //             }
    //             $product->data_groups =$data_groups;
    //         }
    //     }
    //     return $group;
    // }

    public function topSaleProduct(Request $request)
    {
        try {
            $client = new Client();
            $slug = $request->key;
            $response = $client->get('http://192.168.245.176:8503/api/product-avatar/' . $slug);
            $listItemImg = json_decode($response->getBody(), true);
            $productDes = ProductDesc::where('friendly_url', $slug)->select('product_id')->first();
            $productId = $productDes->product_id;
            $productCatId = $productDes->product->cat_id;

            $topSaleProduct = Product::where('product_id', '!=', $productId)
                ->where('cat_id', $productCatId)
                ->where('stock', 1)
                ->where('status', 1)
                ->select('product_id', 'price', 'price_old', 'picture', 'brand_id', 'cat_id', 'stock', 'cat_list', 'views', 'display')
                ->orderBy('views', 'DESC')
                ->take(5)->get();

            $data = [];
            foreach ($topSaleProduct as $product) {
                $urlExist = 'http://192.168.245.190:8000/uploads/' . $product->picture;

                if (@file_get_contents($urlExist)) {

                    if (isset($listItemImg[$product->product_id])) {
                        $product->picture = $listItemImg[$product->product_id];
                    }
                    // $data_technology = $this->getTechnology($product->product_id);

                    $picture = $product->picture;
                    $price = $product->price;
                    $priceOld = $product->price_old;
                    $data[] = [
                        // 'CatId'=>explode(",",$product->cat_list)[0],
                        'ProductId' => $product->product_id,
                        'ParentCatId' => explode(",", $product->cat_list)[0] ?? null,
                        'CatId' => $product->cat_id ?? null,

                        'Stock' => $product->stock,
                        'ProductName' => $product->productDesc->title,
                        'Image' => $product->picture,
                        'Price' => $price ?? null,
                        'PriceOld' => $priceOld ?? null,
                        'UrlProduct' => $product->productDesc->friendly_url,
                        'brandName' => $product->brandDesc->title ?? null,
                        'Category' => $product->categoryDes->cat_name ?? null,
                        'DesShort' => $product->productDesc->short,

                        // 'catName' => $product->categoryDes->cat_name ?? null,
                        'compareStatus' => $this->checkCompareProduct($product->product_id),
                        'checkPresent' => $this->checkPresent($product->product_id),
                        'checkGiftPromotion' => $this->checkGiftPromotion($product->product_id),
                        // 'technology' => $data_technology ?? null
                    ];
                }
            }
            return response()->json([
                'topSaleProduct' => $data,
                'status' => true,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function relatedProduct(Request $request)
    {
        try {
            $client = new Client();
            $slug = $request->key;
            // $response = $client->get('http://192.168.245.176:8503/api/product-avatar/'.$slug);
            // $listItemImg = json_decode($response->getBody(), true);

            $productDes = ProductDesc::where('friendly_url', $slug)->select('product_id')->first();
            $productId = $productDes->product_id;
            $productCatId = $productDes->product->cat_id;
            $priceList = $productDes->product->price;
            $priceMin = $priceList * 0.9;
            $priceMax = $priceList * 1.1;
            $cat_list = $productDes->product->cat_list;
            $ParentCatId = explode(",", $cat_list)[0];

            $relatedProduct = Product::where('product_id', '!=', $productId)
            // ->where('cat_id',$ParentCatId)
                ->whereRaw('FIND_IN_SET(?, cat_list)', [$ParentCatId])
                ->whereBetWeen('price', [$priceMin, $priceMax])
                ->where('stock', 1)
                ->where('display', 1)
                ->select('product_id', 'price', 'price_old', 'picture', 'brand_id', 'cat_id', 'stock', 'cat_list', 'display')
                ->orderBy('product_id', 'DESC')->take(10)->get();
            //return $relatedProduct;

            $data = [];
            foreach ($relatedProduct as $product) {

                // if (isset($listItemImg[$product->product_id])) {
                //     $product->picture = $listItemImg[$product->product_id];
                // }
                $data_technology = $this->getTechnology($product->product_id);

                $picture = $product->picture;
                $price = $product->price;
                $priceOld = $product->price_old;
                $urlExist = 'http://192.168.245.190:8000/uploads/' . $product->picture;

                //if(@file_get_contents($urlExist)){
                $data[] = [
                    // 'CatId'=>explode(",",$product->cat_list)[0],

                    'ParentCatId' => explode(",", $product->cat_list)[0] ?? null,
                    'CatId' => $product->cat_id ?? null,
                    'ProductId' => $product->product_id,
                    'Stock' => $product->stock,
                    'ProductName' => $product->productDesc->title ?? null,
                    'Image' => $picture ?? null,
                    'Price' => $price ?? null,
                    'PriceOld' => $priceOld ?? null,
                    'UrlProduct' => $product->productDesc->friendly_url ?? null,
                    'BrandName' => $product->brandDesc->title ?? null,
                    'CatName' => $product->categoryDes->cat_name ?? null,
                    'compareStatus' => $this->checkCompareProduct($product->product_id),
                    'checkPresent' => $this->checkPresent($product->product_id),
                    'checkGiftPromotion' => $this->checkGiftPromotion($product->product_id),
                    // 'technology' => $data_technology ?? null
                ];
                //}
            }
            return response()->json([
                'relatedProduct' => $data,
                'status' => true,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }

    }

    public function searchProduct(Request $request)
    {
        try {
            $searchKeywords = $_GET['key'];
            $searchKeywords = explode(' ', $searchKeywords);
            $products = Product::with('productPicture')
                ->whereHas('productDesc', function ($q) use ($searchKeywords) {
                    foreach ($searchKeywords as $keyword) {
                        $q->where(function ($subQuery) use ($keyword) {
                            $subQuery->where('title', 'LIKE', '%' . $keyword . '%')
                                ->orWhere('macn', 'LIKE', '%' . $keyword . '%');
                            ;
                        });
                    }
                })->orderBy('product_id', 'desc')->take(30)->get();

            try {
                $client = new Client();
                // $response = $client->get('http://192.168.245.176:8503/api/product-avatar');
                // $listItemImg = json_decode($response->getBody(), true);
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage(),
                ]);
            }
            $data = [];
            if (count($products) > 0) {

                foreach ($products as $product) {
                    // if(!empty($listItemImg[$product->product_id])){
                    //     $product->picture = $listItemImg[$product->product_id];
                    // }
                    $price = $product->price;
                    $priceOld = $product->price_old;
                    $picture = $product->picture;
                    // $urlExist = 'http://192.168.245.190:8000/uploads/'.$product->picture;

                    // if(@file_get_contents($urlExist)){
                    $data[] = [
                        'ProductId' => $product->product_id,
                        'stock' => $product->stock,
                        'ParentCatId' => explode(",", $product->cat_list)[0] ?? null,
                        'CatId' => $product->cat_id ?? null,
                        // 'CatId'=>explode(",",$product->cat_list)[0],
                        'productName' => $product->productDesc->title,
                        'Category' => $product->categoryDes->cat_name ?? null,
                        'BrandName' => $product->brandDesc->title ?? null,
                        'Price' => $price,
                        'PriceOld' => $priceOld,
                        'picture' => $picture ?? null,
                        'Image' => $picture ?? null,
                        'friendLyUrl' => $product->productDesc->friendly_url,
                        'metakey' => isset($product->productDesc->metakey) ? $product->productDesc->metakey : 'null',
                        'metadesc' => isset($product->productDesc->metadesc) ? $product->productDesc->metadesc : 'null',
                        'PriceFlashSale' => $product->productFlashSale->discount_price ?? null,
                        // 'checkPresent'=>$this->checkPresent($product->product_id),
                        // 'checkGiftPromotion'=>$this->checkGiftPromotion($product->product_id),
                    ];
                    // }
                }

            } else {

                $data = [];
            }

            return response()->json([
                'product' => $data,
                'status' => true,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function checkProductHaveImage()
    {
        try {
            $query = Product::select('picture', 'maso', 'product_id')
                ->whereRaw('FIND_IN_SET(?, cat_list)', [1])
                ->orderBy('product_id', 'asc')->take(100)->get();
            //return count($query);
            $listProduct = [];
            foreach ($query as $item) {
                $urlExist = 'http://192.168.245.190:8000/uploads/' . $item->picture;
                if (!@file_get_contents($urlExist)) {
                    $listProduct[] = $item->maso;
                }
            }
            return count($listProduct);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function recommendProduct(Request $request)
    {
        try {
            $offset = $request->page ? $request->page : 1;
            $query = Product::with('productDesc', 'categoryDes')
                ->where('stock', 1)
                ->where('display', 1)
                ->where('views', '>', 4000)
                ->orderBy('product_id', 'desc');

            $totalProduct = count($query->get());
            $arrProductRecommend = $query->limit(10)
                ->offset(($offset - 1) * 10)->get();

            $listProductRecommend = [];
            foreach ($arrProductRecommend as $idProduct) {

                $id = $idProduct->product_id;
                $dataValue = $this->getTechnology($id);
                //return $this->checkPresent($idProduct->product_id);

                // $encry =  Crypt::encryptString($id);
                // $encryKey = substr($encry, 2);
                // if (isset($listItemImg[$id])) {
                //     $idProduct->picture = $listItemImg[$id];
                // }else{
                //     $idProduct->picture = $idProduct->picture;
                // }
                //return $idProduct;

                $urlExist = 'http://192.168.245.190:8000/uploads/' . $idProduct->picture;

                // if(@file_get_contents($urlExist)){
                $listProductRecommend[] = [
                    'ProductId' => $idProduct->product_id,
                    'maso' => $idProduct->macn ?? null,
                    'stock' => $idProduct->stock,
                    'ParentCatId' => explode(",", $idProduct->cat_list)[0] ?? null,
                    'CatId' => $idProduct->cat_id ?? null,
                    // 'CatId'=>explode(",",$idProduct->cat_list)[0],
                    'ProductName' => $idProduct->productDesc->title,
                    'Image' => $idProduct->picture,
                    'Price' => $idProduct->price,
                    'PriceOld' => $idProduct->price_old,
                    'UrlProduct' => $idProduct->productDesc->friendly_url,
                    'votes' => $idProduct->votes ?? 0,
                    // 'Category'=>$catNameParent->cat_name
                    'Category' => $idProduct->categoryDes->cat_name ?? null,
                    'BrandName' => $idProduct->brandDesc->title ?? null,
                    'DesShort' => $idProduct->productDesc->short,
                    'compareStatus' => $this->checkCompareProduct($idProduct->product_id),
                    'checkPresent' => $this->checkPresent($idProduct->product_id),
                    'checkGiftPromotion' => $this->checkGiftPromotion($idProduct->product_id),
                ];
            }

            // }
            // $listProductRecommend= $this->paginate($listProductRecommend,15);
            return response()->json([
                'totalProduct' => $totalProduct ?? 0,
                'listProductRecommend' => $listProductRecommend,
                'status' => true,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }

    }

    public function productHot(Request $request)
    {
        try {
            $arrProductHot = Product::with('productDesc', 'categoryDes')
                ->where('status', 4)
                ->where('display', 1)
                ->where('stock', 1)
                ->where('price', '>', 0)
                ->where('price_old', '>', 0)
                ->orderBy('product_id', 'desc')
                ->orderBy('views', 'desc')
                ->limit(8)
                ->get();

            $listProductHot = [];
            foreach ($arrProductHot as $idProduct) {
                // return $idProduct->productDesc->friendly_url;

                //return $this->checkPresent($idProduct->product_id);
                $itemProduct = explode(',', $idProduct->cat_list);
                $catNameParent = CategoryDesc::where('cat_id', $itemProduct[0])->first();

                $id = $idProduct->product_id;
                $dataValue = $this->getTechnology($id);

                $productStatus = $idProduct->productStatus;

                $listProductHot[] = [
                    'ProductId' => $idProduct->product_id,
                    'maso' => $idProduct->macn ?? null,
                    'stock' => $idProduct->stock,
                    'ParentCatId' => explode(",", $idProduct->cat_list)[0] ?? null,
                    'CatId' => $idProduct->cat_id ?? null,
                    // 'CatId'=>explode(",",$idProduct->cat_list)[0],
                    'ProductName' => $idProduct->productDesc->title,
                    'Image' => $idProduct->picture,
                    'Price' => $idProduct->price,
                    'PriceOld' => $idProduct->price_old,
                    'UrlProduct' => $idProduct->productDesc->friendly_url,
                    // 'Category'=>$catNameParent->cat_name??null,
                    'Category' => $idProduct->categoryDes->cat_name ?? null,
                    'BrandName' => $idProduct->brandDesc->title ?? null,
                    'DesShort' => $idProduct->productDesc->short,
                    'compareStatus' => $this->checkCompareProduct($idProduct->product_id),
                    'checkPresent' => $this->checkPresent($idProduct->product_id),
                    'checkGiftPromotion' => $this->checkGiftPromotion($idProduct->product_id),
                    'status' => $idProduct->status,
                    'votes' => $idProduct->votes ?? 0,
                    'nameStatus' => $productStatus->productStatusDesc ? $productStatus->productStatusDesc->title : null,
                    'ImageStatus' => $productStatus ? $productStatus->picture : null,
                ];

            }
            return response()->json([
                'productHot' => $listProductHot,
                'status' => true,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }

    }

    public function showProductHotForCategory(Request $request)
    {
        try {
            $arrProductHot = Product::with('productDesc', 'categoryDes')
                ->where('status', 4)->where('display', 1)->orderBy('product_id', 'desc')->get();

            $categoryProductHot = [];
            $listCatId = [];
            foreach ($arrProductHot as $product) {

                $itemProduct = explode(',', $product->cat_list);

                $catNameParent = CategoryDesc::where('cat_id', $itemProduct[0])->first();

                if (!in_array($catNameParent->cat_id, $listCatId)) {
                    array_push($listCatId, $catNameParent->cat_id);
                    $productList = Product::whereRaw('FIND_IN_SET(?, cat_list)', $catNameParent->cat_id)
                        ->where('display', 1)
                        ->get();
                    $arrayProductHot = [];
                    foreach ($productList as $item) {
                        $first = explode(',', $item->cat_list);
                        if ($item->status == 4 && $first[0] == $catNameParent->cat_id) {
                            $idProduct = $item;
                            $productStatus = $idProduct->productStatus;
                            $itemProduct = explode(',', $idProduct->cat_list);
                            $catNameParent = CategoryDesc::where('cat_id', $itemProduct[0])->first();
                            $id = $idProduct->product_id;
                            $dataValue = $this->getTechnology($id);

                            $arrayProductHot[] = [
                                'ProductId' => $idProduct->product_id,
                                'stock' => $idProduct->stock,
                                'ParentCatId' => explode(",", $idProduct->cat_list)[0] ?? null,
                                'CatId' => $idProduct->cat_id ?? null,
                                // 'CatId'=>explode(",",$idProduct->cat_list)[0],
                                'ProductName' => $idProduct->productDesc->title,
                                'Image' => $idProduct->picture,
                                'Price' => $idProduct->price,
                                'PriceOld' => $idProduct->price_old,
                                'UrlProduct' => $idProduct->productDesc->friendly_url,
                                'Category' => $idProduct->categoryDes->cat_name ?? null,
                                'BrandName' => $idProduct->brandDesc->title ?? null,
                                'DesShort' => $idProduct->productDesc->short,
                                'compareStatus' => $this->checkCompareProduct($idProduct->product_id),
                                'checkPresent' => $this->checkPresent($idProduct->product_id),
                                'checkGiftPromotion' => $this->checkGiftPromotion($idProduct->product_id),
                                'status' => $idProduct->status,
                                'nameStatus' => $productStatus->productStatusDesc ? $productStatus->productStatusDesc->title : null,
                                'ImageStatus' => $productStatus ? $productStatus->picture : null,
                                'widthStatus' => $productStatus ? $productStatus->width : null,
                                'heightStatus' => $productStatus ? $productStatus->height : null,

                            ];
                        }
                    }
                    $ProductAdvertiseSpecial = ProductAdvertiseSpecial::where('status', 1)->where('pos', $catNameParent->cat_name)->first();
                    $categoryProductHot[] = [
                        'cat_id' => $catNameParent->cat_id,
                        'cat_name' => $catNameParent->cat_name,
                        'home_title' => $catNameParent->home_title,
                        'friendly_url' => $catNameParent->friendly_url,
                        'friendly_title' => $catNameParent->friendly_url,
                        'metakey' => $catNameParent->metakey,
                        'metadesc' => $catNameParent->metadesc,
                        'banner' => $ProductAdvertiseSpecial->picture ?? null,
                        'background' => $ProductAdvertiseSpecial->background ?? null,
                        'link' => $ProductAdvertiseSpecial->link ?? null,
                        'product' => $arrayProductHot,
                    ];
                }

            }
            $listProductHot = [];

            foreach ($arrProductHot as $idProduct) {
                $itemProduct = explode(',', $idProduct->cat_list);
                $catNameParent = CategoryDesc::where('cat_id', $itemProduct[0])->first();

                $id = $idProduct->product_id;
                $dataValue = $this->getTechnology($id);

                $productStatus = $idProduct->productStatus;

                $listProductHot[] = [
                    'ProductId' => $idProduct->product_id,
                    'stock' => $idProduct->stock,
                    'ParentCatId' => explode(",", $idProduct->cat_list)[0] ?? null,
                    'CatId' => $idProduct->cat_id ?? null,
                    // 'CatId'=>explode(",",$idProduct->cat_list)[0],
                    'ProductName' => $idProduct->productDesc->title,
                    'Image' => $idProduct->picture,
                    'Price' => $idProduct->price,
                    'PriceOld' => $idProduct->price_old,
                    'UrlProduct' => $idProduct->productDesc->friendly_url,

                    'Category' => $idProduct->categoryDes->cat_name ?? null,
                    'BrandName' => $idProduct->brandDesc->title ?? null,
                    'DesShort' => $idProduct->productDesc->short,
                    'compareStatus' => $this->checkCompareProduct($idProduct->product_id),
                    'checkPresent' => $this->checkPresent($idProduct->product_id),
                    'checkGiftPromotion' => $this->checkGiftPromotion($idProduct->product_id),
                    'status' => $idProduct->status,
                    'nameStatus' => $productStatus->productStatusDesc ? $productStatus->productStatusDesc->title : null,
                    'ImageStatus' => $productStatus ? $productStatus->picture : null,
                ];

            }

            return response()->json([
                'ProductHot' => $categoryProductHot,
                'showAllHot' => $listProductHot,
                'status' => true,
            ]);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage,
            ];
            return response()->json($response, 500);
        }

    }

    public function showProductFlashSaleForCategory(Request $request)
    {
        try {
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);

            $arrProductFashSale = ProductFlashSale::with('product.productDesc')
                ->where('start_time', '<=', $stringTime)
                ->where('end_time', '>=', $stringTime)
                ->get();

            try {
                $client = new Client();
                $response = $client->get('http://192.168.245.176:8503/api/product-avatar');
                $listItemImg = json_decode($response->getBody(), true);

            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage(),
                ]
                );
            }
            $categoryProductFashSale = [];
            $listCatId = [];
            foreach ($arrProductFashSale as $value) {
                $product = $value->product;

                $itemProduct = explode(',', $product->cat_list);

                $catNameParent = CategoryDesc::where('cat_id', $itemProduct[0])->first();

                if (!$catNameParent) {
                    continue;
                }

                if (!in_array($catNameParent->cat_id, $listCatId)) {
                    array_push($listCatId, $catNameParent->cat_id);
                    $productList = Product::whereRaw('FIND_IN_SET(?, cat_list)', $catNameParent->cat_id)
                        ->where('display', 1)
                        ->get();
                    $arrProductFS = [];
                    foreach ($productList as $item) {
                        $first = explode(',', $item->cat_list);
                        if ($item->status == 5 && $first[0] == $catNameParent->cat_id) {
                            $idProduct = $item;
                            $productStatus = $idProduct->productStatus;
                            $itemProduct = explode(',', $idProduct->cat_list);
                            $catNameParent = CategoryDesc::where('cat_id', $itemProduct[0])->first();

                            if (!$catNameParent) {
                                continue;
                            }

                            $id = $idProduct->product_id;
                            $dataValue = $this->getTechnology($id);

                            // $encry =  Crypt::encryptString($id);
                            // $encryKey = substr($encry, 2);
                            // if (isset($listItemImg[$id])) {
                            //     $idProduct->picture = $listItemImg[$id];
                            // }else{
                            //     $idProduct->picture = $idProduct->picture;
                            // }

                            $arrProductFS[] = [
                                'ProductId' => $idProduct->product_id,
                                'stock' => $idProduct->stock,
                                'ParentCatId' => explode(",", $idProduct->cat_list)[0] ?? null,
                                'CatId' => $idProduct->cat_id ?? 0,
                                // 'CatId'=>explode(",",$idProduct->cat_list)[0],
                                'ProductName' => $idProduct->productDesc->title,
                                'Image' => $idProduct->picture,
                                'Price' => $idProduct->price,
                                'PriceOld' => $idProduct->price_old,
                                'UrlProduct' => $idProduct->productDesc->friendly_url,
                                'Category' => $idProduct->categoryDes->cat_name ?? null,
                                'BrandName' => $idProduct->brandDesc->title ?? null,
                                'DesShort' => $idProduct->productDesc->short,
                                'compareStatus' => $this->checkCompareProduct($idProduct->product_id),
                                'checkPresent' => $this->checkPresent($idProduct->product_id),
                                'checkGiftPromotion' => $this->checkGiftPromotion($idProduct->product_id),
                                'startTime' => $value->start_time,
                                'endTime' => $value->end_time,
                                'discountPrice' => $value->discount_price,
                                'status' => $idProduct->status,
                                'nameStatus' => $productStatus->productStatusDesc ? $productStatus->productStatusDesc->title : null,
                                'ImageStatus' => $productStatus ? $productStatus->picture : null,
                                'widthStatus' => $productStatus ? $productStatus->width : null,
                                'heightStatus' => $productStatus ? $productStatus->height : null,

                            ];

                        }
                    }
                    $ProductAdvertiseSpecial = ProductAdvertiseSpecial::where('status', 0)->where('pos', $catNameParent->cat_name)->first();
                    $categoryProductFashSale[] = [
                        'cat_id' => $catNameParent->cat_id,
                        'cat_name' => $catNameParent->cat_name,
                        'home_title' => $catNameParent->home_title,
                        'friendly_url' => $catNameParent->friendly_url,
                        'friendly_title' => $catNameParent->friendly_url,
                        'metakey' => $catNameParent->metakey,
                        'metadesc' => $catNameParent->metadesc,
                        'banner' => $ProductAdvertiseSpecial->picture ?? null,
                        'background' => $ProductAdvertiseSpecial->background ?? null,
                        'link' => $ProductAdvertiseSpecial->link ?? null,
                        'product' => $arrProductFS,
                    ];
                }

            }

            //----------------------------
            $listProductFashSale = [];

            foreach ($arrProductFashSale as $value) {

                $idProduct = $value->product;
                $productStatus = $idProduct->productStatus;
                $itemProduct = explode(',', $idProduct->cat_list);

                $catNameParent = CategoryDesc::where('cat_id', $itemProduct[0])->first();

                if (!$catNameParent) {
                    continue;
                }

                $id = $idProduct->product_id;
                $dataValue = $this->getTechnology($id);

                // $encry =  Crypt::encryptString($id);
                // $encryKey = substr($encry, 2);
                // if (isset($listItemImg[$id])) {
                //     $idProduct->picture = $listItemImg[$id];
                // }else{
                //     $idProduct->picture = $idProduct->picture;
                // }

                $listProductFashSale[] = [
                    'ProductId' => $idProduct->product_id,
                    'stock' => $idProduct->stock,
                    'ParentCatId' => explode(",", $idProduct->cat_list)[0] ?? null,
                    'CatId' => $idProduct->cat_id ?? 0,
                    'ProductName' => $idProduct->productDesc->title,
                    'Image' => $idProduct->picture,
                    'Price' => $idProduct->price,
                    'PriceOld' => $idProduct->price_old,
                    'UrlProduct' => $idProduct->productDesc->friendly_url,

                    'Category' => $idProduct->categoryDes->cat_name ?? null,
                    'BrandName' => $idProduct->brandDesc->title ?? null,
                    'DesShort' => $idProduct->productDesc->short,
                    'compareStatus' => $this->checkCompareProduct($idProduct->product_id),
                    'checkPresent' => $this->checkPresent($idProduct->product_id),
                    'checkGiftPromotion' => $this->checkGiftPromotion($idProduct->product_id),
                    'startTime' => $value->start_time,
                    'endTime' => $value->end_time,
                    'discountPrice' => $value->discount_price,
                    'status' => $idProduct->status,
                    'nameStatus' => $productStatus->productStatusDesc ? $productStatus->productStatusDesc->title : null,
                    'ImageStatus' => $productStatus ? $productStatus->picture : null,
                    'widthStatus' => $productStatus ? $productStatus->width : null,
                    'heightStatus' => $productStatus ? $productStatus->height : null,
                ];

            }
            return response()->json([
                'ProductFlashSale' => $categoryProductFashSale,
                'showAllFlashSale' => $listProductFashSale,
                'status' => true,
            ]);

            // $response = [
            //     'status' => true,
            //     'list' => $list
            // ];
            // return response()->json($response);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage,
            ];
            return response()->json($response, 500);
        }

    }

    public function showAllProductFlashSale(Request $request)
    {
        try {
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);

            $arrProductFashSale = ProductFlashSale::with('product.productDesc')
                ->where('start_time', '<=', $stringTime)
                ->where('end_time', '>=', $stringTime)
                ->get();

            try {
                $client = new Client();
                $response = $client->get('http://192.168.245.176:8503/api/product-avatar');
                $listItemImg = json_decode($response->getBody(), true);

            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage(),
                ]
                );
            }

            $listProductFashSale = [];

            foreach ($arrProductFashSale as $value) {
                // return $idProduct->productDesc->friendly_url;

                $idProduct = $value->product;
                $productStatus = $idProduct->productStatus;
                //return  $idProduct;
                $itemProduct = explode(',', $idProduct->cat_list);

                $catNameParent = CategoryDesc::where('cat_id', $itemProduct[0])->first();

                $id = $idProduct->product_id;
                $dataValue = $this->getTechnology($id);

                // $encry =  Crypt::encryptString($id);
                // $encryKey = substr($encry, 2);
                // if (isset($listItemImg[$id])) {
                //     $idProduct->picture = $listItemImg[$id];
                // }else{
                //     $idProduct->picture = $idProduct->picture;
                // }

                $listProductFashSale[] = [
                    'ProductId' => $idProduct->product_id,
                    'maso' => $idProduct->macn ?? null,
                    'stock' => $idProduct->stock,
                    'CatId' => $idProduct->cat_id ?? null,
                    'ParentCatId' => explode(",", $idProduct->cat_list)[0] ?? null,
                    'ProductName' => $idProduct->productDesc->title,
                    'Image' => $idProduct->picture,
                    'Price' => $idProduct->price,
                    'PriceOld' => $idProduct->price_old,
                    'UrlProduct' => $idProduct->productDesc->friendly_url,
                    // 'Category'=>$catNameParent->cat_name??null,
                    'Category' => $idProduct->categoryDes->cat_name ?? null,
                    'BrandName' => $idProduct->brandDesc->title ?? null,
                    'DesShort' => $idProduct->productDesc->short,
                    'compareStatus' => $this->checkCompareProduct($idProduct->product_id),
                    'checkPresent' => $this->checkPresent($idProduct->product_id),
                    'checkGiftPromotion' => $this->checkGiftPromotion($idProduct->product_id),
                    'startTime' => $value->start_time,
                    'endTime' => $value->end_time,
                    'discountPrice' => $value->discount_price,
                    'status' => $idProduct->status ?? null,
                    'nameStatus' => $productStatus->productStatusDesc ? $productStatus->productStatusDesc->title : null,
                    'ImageStatus' => $productStatus ? $productStatus->picture : null,
                    'widthStatus' => $productStatus ? $productStatus->width : null,
                    'heightStatus' => $productStatus ? $productStatus->height : null,
                ];

            }
            return response()->json([
                'ProductFlashSale' => $listProductFashSale,
                'status' => true,
            ]);

            $response = [
                'status' => true,
                'list' => $list,
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage,
            ];
            return response()->json($response, 500);
        }
    }

    public function exportProductSAP(Request $request)
    {
        try {
            $array = [
                11063,
                11064,
                11065,
                11066,
                11067,
                11068,
                11069,
                11070,
                21340,
                22920,
            ];

            $productsDesc = ProductDesc::whereIn('product_id', $array)->get();
            return $productsDesc;
            $fileName = 'productNotSAP_' . date('Y_m_d_H_i_s') . '.xlsx';
            $data = $products;

            $export = new ProductNotSapExport($data);
            $fileContents = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
            $headers = [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ];
            return response($fileContents, 200, $headers);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage,
            ];
            return response()->json($response, 500);
        }
    }

    //Lấy ra sản phẩm có thể bạn quan tâm
    public function getProductRelated(Request $request)
    {
        try {
            $member = $request->member_id;
            $results = [];

            $descCache = DB::table('product_descs')
                ->select('product_id', 'title', 'friendly_url', 'short')
                ->get()
                ->keyBy('product_id');

            if ($member) {
                $urls = DB::table('statistics_pages')
                    ->select('url')
                    ->where('mem_id', $member)
                    ->orderByDesc('id_static_page')
                    ->limit(50)
                    ->pluck('url');

                $watchedProductIds = DB::table('product_descs')
                    ->whereIn('friendly_url', $urls)
                    ->pluck('product_id')
                    ->toArray();

                $watchedProducts = DB::table('products')
                    ->whereIn('product_id', $watchedProductIds)
                    ->whereNotNull('picture')
                    ->where('picture', '!=', '')
                    ->orderByDesc('TonKho')
                    ->limit(12)
                    ->get(['product_id', 'MaHH', 'picture', 'price', 'price_old']);

                foreach ($watchedProducts as $product) {
                    $desc = $descCache[$product->product_id] ?? null;
                    $results[] = [
                        'product_id' => $product->product_id,
                        'MaHH' => $product->MaHH,
                        'picture' => $product->picture,
                        'price' => $product->price,
                        'price_old' => $product->price_old,
                        'title' => $desc->title ?? '',
                        'short_desc' => $desc->short ?? '',
                        'friendly_url' => $desc->friendly_url ?? '',
                    ];
                }

                if (count($results) < 12) {
                    $missing = 12 - count($results);

                    $catIds = [];
                    if (!empty($watchedProductIds)) {
                        $catIds = DB::table('products')
                            ->whereIn('product_id', $watchedProductIds)
                            ->pluck('cat_list')
                            ->map(fn($catList) => explode(',', $catList)[0])
                            ->filter()
                            ->unique()
                            ->toArray();
                    }

                    if (empty($catIds)) {
                        $catIds = DB::table('product_category')
                            ->where('parentid', 0)
                            ->limit(5)
                            ->pluck('cat_id')
                            ->toArray();
                    }

                    $avgPrice = 0;
                    if (collect($watchedProducts)->isNotEmpty()) {
                        $avgPrice = collect($watchedProducts)->pluck('price')->filter()->avg() ?: 500000;
                    } else {
                        $avgPrice = 500000;
                    }

                    $minPrice = $avgPrice * 0.7;
                    $maxPrice = $avgPrice * 1.3;

                    $whereNotIn = !empty($watchedProductIds) ? $watchedProductIds : [0];

                    $suggested = DB::table('products')
                        ->whereIn(DB::raw("SUBSTRING_INDEX(cat_list, ',', 1)"), $catIds)
                        ->whereNotIn('product_id', $whereNotIn)
                        ->where('price', '>', 0)
                        ->whereNotNull('picture')
                        ->where('picture', '!=', '')
                        ->whereBetween('price', [$minPrice, $maxPrice])
                        ->orderByDesc('TonKho')
                        ->limit($missing)
                        ->get(['product_id', 'MaHH', 'picture', 'price', 'price_old']);

                    foreach ($suggested as $product) {
                        $desc = $descCache[$product->product_id] ?? null;
                        $results[] = [
                            'product_id' => $product->product_id,
                            'MaHH' => $product->MaHH,
                            'picture' => $product->picture,
                            'price' => $product->price,
                            'price_old' => $product->price_old,
                            'title' => $desc->title ?? '',
                            'short_desc' => $desc->short ?? '',
                            'friendly_url' => $desc->friendly_url ?? '',
                        ];
                        if (count($results) >= 12) {
                            break;
                        }
                    }

                    if (count($results) < 12) {
                        $stillMissing = 12 - count($results);
                        $existingIds = collect($results)->pluck('product_id')->toArray();

                        $popularProducts = DB::table('products')
                            ->whereNotIn('product_id', $existingIds)
                            ->where('price', '>', 0)
                            ->whereNotNull('picture')
                            ->where('picture', '!=', '')
                            ->orderByDesc('TonKho')
                            ->limit($stillMissing)
                            ->get(['product_id', 'MaHH', 'picture', 'price', 'price_old']);

                        foreach ($popularProducts as $product) {
                            $desc = $descCache[$product->product_id] ?? null;
                            $results[] = [
                                'product_id' => $product->product_id,
                                'MaHH' => $product->MaHH,
                                'picture' => $product->picture,
                                'price' => $product->price,
                                'price_old' => $product->price_old,
                                'title' => $desc->title ?? '',
                                'short_desc' => $desc->short ?? '',
                                'friendly_url' => $desc->friendly_url ?? '',
                            ];
                        }
                    }
                }
            } else {
                $excludedProductIds = [];

                $categories = DB::table('product_category')
                    ->where('parentid', 0)
                    ->limit(10)
                    ->pluck('cat_id')
                    ->toArray();

                if (empty($categories)) {
                    $popularProducts = DB::table('products')
                        ->where('price', '>', 0)
                        ->whereNotNull('picture')
                        ->where('picture', '!=', '')
                        ->orderByDesc('TonKho')
                        ->limit(12)
                        ->get(['product_id', 'MaHH', 'picture', 'price', 'price_old']);

                    foreach ($popularProducts as $product) {
                        $desc = $descCache[$product->product_id] ?? null;
                        $results[] = [
                            'product_id' => $product->product_id,
                            'MaHH' => $product->MaHH,
                            'picture' => $product->picture,
                            'price' => $product->price,
                            'price_old' => $product->price_old,
                            'title' => $desc->title ?? '',
                            'short_desc' => $desc->short ?? '',
                            'friendly_url' => $desc->friendly_url ?? '',
                        ];
                    }

                    return response()->json([
                        'status' => true,
                        'data' => $results,
                    ]);
                }

                shuffle($categories);

                $categoryNames = DB::table('product_category_desc')
                    ->whereIn('cat_id', $categories)
                    ->pluck('cat_name', 'cat_id');

                foreach ($categories as $catId) {
                    if (count($results) >= 12) {
                        break;
                    }

                    $products = DB::table('products')
                        ->whereRaw("FIND_IN_SET(?, cat_list)", [$catId])
                        ->whereNotIn('product_id', $excludedProductIds)
                        ->where('price', '>', 0)
                        ->whereNotNull('picture')
                        ->where('picture', '!=', '')
                        ->orderByDesc('TonKho')
                        ->limit(4)
                        ->get(['product_id', 'MaHH', 'picture', 'price', 'price_old']);

                    foreach ($products as $product) {
                        if (count($results) >= 12) {
                            break;
                        }

                        $desc = $descCache[$product->product_id] ?? null;
                        $results[] = [
                            'cat_id' => $catId,
                            'cat_name' => $categoryNames[$catId] ?? '',
                            'product_id' => $product->product_id,
                            'MaHH' => $product->MaHH,
                            'picture' => $product->picture,
                            'price' => $product->price,
                            'price_old' => $product->price_old,
                            'title' => $desc->title ?? '',
                            'short_desc' => $desc->short ?? '',
                            'friendly_url' => $desc->friendly_url ?? '',
                        ];
                        $excludedProductIds[] = $product->product_id;
                    }
                }

                if (count($results) < 12) {
                    $stillMissing = 12 - count($results);

                    $popularProducts = DB::table('products')
                        ->whereNotIn('product_id', $excludedProductIds)
                        ->where('price', '>', 0)
                        ->whereNotNull('picture')
                        ->where('picture', '!=', '')
                        ->orderByDesc('TonKho')
                        ->limit($stillMissing)
                        ->get(['product_id', 'MaHH', 'picture', 'price', 'price_old']);

                    foreach ($popularProducts as $product) {
                        $desc = $descCache[$product->product_id] ?? null;
                        $results[] = [
                            'product_id' => $product->product_id,
                            'MaHH' => $product->MaHH,
                            'picture' => $product->picture,
                            'price' => $product->price,
                            'price_old' => $product->price_old,
                            'title' => $desc->title ?? '',
                            'short_desc' => $desc->short ?? '',
                            'friendly_url' => $desc->friendly_url ?? '',
                        ];
                    }
                }
            }

            return response()->json([
                'status' => true,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getProductRelated: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
