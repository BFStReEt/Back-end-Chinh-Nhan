<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\BrandDesc;
use App\Models\Category;
use App\Models\CategoryDesc;
use App\Models\Present;
use App\Models\Product;
use App\Models\Properties;
use App\Models\PropertiesValue;
use App\Models\StatisticsPages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterCategoryController extends Controller
{
    public function setStatisticsPages($mem_id, $url, $date, $ip)
    {
        if ($mem_id) {
            // return response()->json([
            //     'data'=>$mem_id
            // ]);
            // return 111;
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
                $statisticsPages->module = "category";
                $statisticsPages->action = "category";
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
                    'module' => "category",
                    'action' => "category",
                    'url' => $url,
                    'ip' => $ip,
                ]);
            }
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

    public function getTechnology($id)
    {
        $dataTechnology = [];
        $options = DB::table('product_cat_option_desc')->pluck('title', 'op_id')->all();
        $product = Product::where('product_id', $id)->first();

        //-------------------------------------------------
        $data = $product->technology;
        if (!empty(strlen($data) < 10)) {
            $data = [];
        } else {
            $data = preg_replace_callback(
                '/(?<=^|\{|;)s:(\d+):\"(.*?)\";(?=[asbdiO]\:\d|N;|\}|$)/s',
                function ($m) {
                    return 's:' . strlen($m[2]) . ':"' . $m[2] . '";';
                },
                $data);
        }
        $data = unserialize($data);
        //------------------------------------------------

        $techs = $data;

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

    public function buildCatOptionlog($catOption)
    {
        $category = [];
        $catalog = [];
        foreach ($catOption as $category) {
            $subCateOption = $category->subCateOption;
            if ($category->parentid == 0 && $subCateOption->isEmpty()) {
                continue;
            }
            $subCatalog = [];
            if (!$subCateOption->isEmpty()) {
                $subCatalog = $this->buildCatOptionlog($subCateOption);
            }
            $encry = $category->op_id;
            $catalogItem = [
                'parentid' => $category->parentid,
                'catName' => isset($category->catOptionDesc->title)
                ? $category->catOptionDesc->title : 'No Name Category',
                'slug' => $category->catOptionDesc->slug,
                'subCateOption' => $subCatalog,
                'op_search' => $encry,
            ];
            $catalog[] = $catalogItem;

        }
        return $catalog;

    }
    // public function index(Request $request)
    // {
    //     $categoryUrl = $request->categoryUrl;
    //     $responseData = [
    //         'nameCategory' => '',
    //         'rangePrice' => '',
    //         'listBrand' => '',
    //         'option' => '',
    //         'breadcrumb' => '',
    //     ];
    //     //if ($this->checkFilterOp($categoryUrl) !== 0) {

    //     $category = CategoryDesc::where('friendly_url', $categoryUrl)->first();
    //     if (!$category) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'notFoundCategory',
    //         ]);
    //     }

    //     $categoryId = $category->cat_id;

    //     //Giá
    //     $minProduct = Product::whereRaw('FIND_IN_SET(?, cat_list)', [$categoryId])
    //         ->min('price');
    //     $maxProduct = Product::whereRaw('FIND_IN_SET(?, cat_list)', [$categoryId])
    //         ->max('price');
    //     $rangePrice = [
    //         'minPrice' => $minProduct == 0 ? 1 : $minProduct,
    //         'maxPrice' => $maxProduct,
    //     ];

    //     $catOption = ProductCatOption::with('category.categoryDesc', 'subCateOption.catOptionDesc')->where('cat_id', $categoryId)
    //         ->where('display', 1)->get();

    //     $brandCategory = Category::where('cat_id', $categoryId)->first();
    //     if ($brandCategory->parentid > 0) {
    //         $catOption = ProductCatOption::with('category.categoryDesc', 'subCateOption.catOptionDesc')
    //             ->where('cat_id', $brandCategory->parentid)
    //             ->where('display', 1)->get();
    //         $brandCat = Category::where('cat_id', $brandCategory->parentid)->first();
    //     } else {
    //         $brandCat = $brandCategory->list_brand;
    //     }
    //     $idBrand = explode(',', $brandCat);

    //     $listBrand = DB::table('product_brand_desc')
    //         ->whereIn('product_brand_desc.brand_id', $idBrand)

    //         ->join('product_brand', 'product_brand.brand_id', '=', 'product_brand_desc.brand_id')

    //         ->select('product_brand_desc.brand_id', 'title as catName', 'friendly_url as slug', 'product_brand.picture as picture')
    //         ->orderBy('brand_id', 'asc')

    //         ->get();

    //     // $listBrand=BrandDesc::select('brand_id', 'title as catName', 'friendly_url as slug')
    //     // ->whereIn('brand_id',$idBrand)->get();
    //     $list = [
    //         'catName' => 'Thương hiệu',
    //         'slug' => 'thuong-hieu',
    //         'subCateOption' => $listBrand,
    //     ];

    //     $catOptionlog = $this->buildCatOptionlog($catOption);
    //     $listCatOptionlog = [];
    //     foreach ($catOptionlog as $item) {

    //         if ($item['parentid'] == 0) {
    //             $listCatOptionlog[] = $item;
    //         }
    //     }
    //     array_unshift($listCatOptionlog, $list);
    //     $breadcrumb = [
    //         'cat_name' => $category->cat_name ?? null,
    //         'url' => $category->friendly_url ?? null,
    //     ];
    //     $responseData = [
    //         'nameCategory' => $category->cat_name,
    //         'rangePrice' => $rangePrice,
    //         'listBrand' => $listBrand,
    //         'option' => $listCatOptionlog,
    //         'breadcrumb' => $breadcrumb,
    //     ];
    //     //}
    //     return response()->json($responseData);

    // }

    //Phần hiển thị công cụ lọc
    public function index(Request $request)
    {
        try {
            $categoryId = $request->categoryUrl;
            $category = DB::table('product_category_desc')->where('friendly_url', 'LIKE', $categoryId)->first();
            if (!is_numeric($categoryId)) {
                if ($category) {
                    $categoryId = $category->cat_id;
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Category id not found',
                    ]);
                }
            }

            if ($categoryId != "") {
                $needs = DB::table('customerNeeds')
                    ->whereRaw('FIND_IN_SET(?, cat_id)', [$categoryId])
                    ->where('display', 1)
                    ->select('id', 'title', 'description', 'friendly_url', 'picture')
                    ->get();

                $category = Category::with('categoryDesc', 'catProperties.properties.propertiesValue')
                    ->where('cat_id', $categoryId)
                    ->first();

                $currentCategoryId = $category->cat_id;

                $categoryRow = DB::table('product_category')->where('cat_id', $currentCategoryId)->first();

                $parentCategoryId = $currentCategoryId;

                if ($categoryRow && !empty($categoryRow->cat_code)) {
                    $catCodeParts = explode('_', $categoryRow->cat_code);
                    if (count($catCodeParts) > 0) {
                        $parentCategoryId = $catCodeParts[0];
                    }
                }
                $catParent = Category::with('categoryDesc', 'catProperties.properties.propertiesValue')
                    ->where('cat_id', $parentCategoryId)
                    ->first();

                $data = [];

                foreach ($catParent->catProperties as $key => $value) {
                    if (
                        $value->properties &&
                        $value->properties->id != 19 &&
                        $value->properties->id != 27 &&
                        count($value->properties->propertiesValue) > 0
                    ) {
                        $propertiesValue = [];

                        foreach ($value->properties->propertiesValue as $item) {
                            $saveItem = [
                                'id' => $item->id,
                                'properties_id' => $item->properties_id,
                                'catName' => $item->name,
                                'slug' => $item->slug ?? null,
                            ];
                            $propertiesValue[] = $saveItem;
                        }

                        $save = [
                            'id' => $value->properties->id,
                            'catName' => $value->properties->title,
                            'slug' => $value->properties->slug ?? null,
                            'subCateOption' => $propertiesValue,
                        ];
                        $data[] = $save;
                    }
                }

                $idBrand = explode(',', $category->list_brand);
                $categorySlug = $category->categoryDesc->friendly_url ?? null;

                $listBrand = Brand::with(['BrandDesc' => function ($q) {
                    $q->select('brand_id', 'title', 'friendly_url');
                }])
                    ->select('brand_id', 'picture')
                    ->whereIn('brand_id', $idBrand)
                    ->get();

                $listBrand = $listBrand->map(function ($brand) use ($categorySlug) {
                    return [
                        'brand_id' => $brand->brand_id,
                        'title' => $brand->BrandDesc->title ?? '',
                        //'friendly_url' => $brand->BrandDesc->friendly_url ?? '',
                        'slug' => $brand->BrandDesc->friendly_url ?? '',
                        'picture' => $brand->picture,
                    ];
                });

                //Giá
                $minProduct = Product::whereRaw('FIND_IN_SET(?, cat_list)', [$categoryId])
                    ->min('price');
                $maxProduct = Product::whereRaw('FIND_IN_SET(?, cat_list)', [$categoryId])
                    ->max('price');
                $rangePrice = [
                    'minPrice' => $minProduct == 0 ? 1 : $minProduct,
                    'maxPrice' => $maxProduct,
                ];

                $breadcrumb = [
                    'cat_name' => $category->categoryDesc->cat_name ?? null,
                    'url' => $category->categoryDesc->friendly_url ?? null,
                ];

                return response()->json([
                    'nameCategory' => $category->categoryDesc->cat_name ?? null,
                    'listBrand' => $listBrand,
                    'rangePrice' => $rangePrice,
                    'option' => $data,
                    'breadcrumb' => $breadcrumb,
                    'customerNeeds' => $needs,
                ]);
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    //Phần bộ lọc sản phẩm
    public function filter(Request $request)
    {
        try {
            $categoryDesc = CategoryDesc::where('friendly_url', $request->get('catUrl'))->firstOrFail();
            $catId = $categoryDesc->cat_id;

            $query = Product::with(['productDesc', 'brand', 'category'])
                ->where('HienThi', 'Y')
                ->whereRaw('FIND_IN_SET(?, cat_list)', [$catId]);

            if ($brandSlug = $request->input('thuong-hieu')) {
                $brand = DB::table('product_brand_desc')->where('friendly_url', $brandSlug)->first();
                if ($brand) {
                    $query->where('brand_id', $brand->brand_id);
                }
            }

            if ($request->filled('nhu-cau')) {
                $needSlugs = explode(',', $request->input('nhu-cau'));
                $needIds = DB::table('customerNeeds')
                    ->whereIn('friendly_url', $needSlugs)
                    ->pluck('id')
                    ->toArray();

                if (!empty($needIds)) {
                    $query->where(function ($q) use ($needIds) {
                        foreach ($needIds as $needId) {
                            $q->orWhereRaw('FIND_IN_SET(?, needs_list)', [$needId]);
                        }
                    });
                }
            }

            $excludeFields = ['catUrl', 'per_page', 'sort', 'minPrice', 'maxPrice', 'thuong-hieu', 'nhu-cau'];
            $filters = collect($request->except($excludeFields));
            $productIdSets = [];

            foreach ($filters as $propertySlug => $valueSlugString) {
                $property = DB::table('properties')->where('slug', $propertySlug)->first();
                if (!$property) {
                    continue;
                }

                $valueSlugs = explode(',', $valueSlugString);
                $valueIds = DB::table('properties_value')
                    ->where('properties_id', $property->id)
                    ->whereIn('slug', $valueSlugs)
                    ->pluck('id')
                    ->toArray();

                if (empty($valueIds)) {
                    continue;
                }

                $priceIds = DB::table('product_properties')
                    ->where('properties_id', $property->id)
                    ->whereIn('pv_id', $valueIds)
                    ->pluck('price_id')
                    ->toArray();

                if (empty($priceIds)) {
                    continue;
                }

                $productIds = DB::table('price')
                    ->whereIn('id', $priceIds)
                    ->pluck('product_id')
                    ->toArray();

                $productIdSets[] = $productIds;
            }

            if (!empty($productIdSets)) {
                $intersectedIds = array_shift($productIdSets);
                foreach ($productIdSets as $ids) {
                    $intersectedIds = array_intersect($intersectedIds, $ids);
                }
                $query->whereIn('product_id', $intersectedIds);
            }

            $minPrice = $request->input('minPrice');
            $maxPrice = $request->input('maxPrice');

            if (is_null($minPrice) || is_null($maxPrice)) {
                $priceBoundsQuery = clone $query;
                $priceBounds = $priceBoundsQuery->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
                $minPrice = $minPrice ?? $priceBounds->min_price;
                $maxPrice = $maxPrice ?? $priceBounds->max_price;
            }

            $query->whereBetween('price', [(float) $minPrice, (float) $maxPrice]);

            // Sắp xếp theo stock trước tiên (stock > 0 sẽ hiển thị trước)
            $query->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END');

            // Sau đó mới áp dụng sắp xếp theo giá
            switch ($request->get('sort')) {
                case 'new_products':
                    $query->orderBy('product_id', 'DESC');
                    break;
                case 'ASC':
                    $query->orderBy('price', 'ASC');
                    break;
                case 'DESC':
                default:
                    $query->orderBy('price', 'DESC');
                    break;
            }

            $perPage = (int) $request->input('per_page', 12);
            $products = $query->paginate($perPage);

            $formatted = $products->getCollection()->map(function ($product) {
                return [
                    'ProductName' => $product->productDesc->title ?? '',
                    'ParentCatId' => explode(',', $product->cat_list ?? '')[0] ?? null,
                    'Price' => (float) ($product->price ?? 0),
                    'PriceOld' => (float) ($product->price_old ?? 0),
                    'Image' => $product->picture ?? '',
                    'ProductId' => $product->product_id,
                    'Category' => $product->category->name ?? null,
                    'Macn' => $product->macn ?? null,
                    'type' => $product->type ?? null,
                    'stock' => isset($product->stock) ? (int) $product->stock : 1,
                    'PriceFlashSale' => $product->price_flash_sale,
                    'brandName' => $product->brand->brand_name ?? $product->brand_name ?? null,
                    'UrlProduct' => $product->productDesc->friendly_url ?? '',
                    'compareStatus' => $this->checkCompareProduct($product->product_id),
                    'checkPresent' => $this->checkPresent($product->product_id),
                ];
            });

            return response()->json([
                'status' => true,
                'total' => $products->total(),
                'listProduct' => $formatted,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    // public function filter(Request $request)
    // {
    //     try {
    //         $dataSlug = $request->all();

    //         $arrRequest = ['catUrl', 'thuong-hieu', 'page', 'sort', 'sortView', 'minPrice', 'maxPrice'];
    //         $arrOp = [];
    //         $catUrl = $request->catUrl;
    //         //return $dataSlug;
    //         // return $this->checkFilterOp($catUrl);

    //         $category = CategoryDesc::where('friendly_url', $catUrl)->first();

    //         if (!$category) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'notFoundCategory',
    //             ]);
    //         }

    //         $catId = $category->cat_id;

    //         $productCatOP = DB::table('product_cat_option')
    //             ->join('product_cat_option_desc', 'product_cat_option_desc.op_id', '=', 'product_cat_option.op_id')
    //             ->select('product_cat_option.op_id', 'product_cat_option_desc.title', 'product_cat_option_desc.slug')
    //             ->where('cat_id', $catId)->get();

    //         $memberId = null;
    //         // return $request->userId;
    //         if ($request->userId !== "null") {
    //             $memberId = $request->userId;
    //         }

    //         $now = date('d-m-Y H:i:s');
    //         $stringTime = strtotime($now);

    //         $ip = $request->ip();
    //         $this->setStatisticsPages($memberId, $catUrl, $stringTime, $ip);
    //         // return $this->setStatisticsPages($memberId,$catUrl,$stringTime,$ip);

    //         foreach ($dataSlug as $key => $slug) {

    //             if (!in_array($key, $arrRequest)) {
    //                 $arrOp[] = $slug;
    //             }
    //         }
    //         $arrOpId = [];
    //         foreach ($productCatOP as $option) {
    //             foreach ($arrOp as $op) {
    //                 if ($op == $option->slug) {
    //                     $arrOpId[] = $option->op_id;
    //                 }
    //             }
    //         }

    //         $keysToRemove = array("page", "catUrl");

    //         $parts = explode("/", $request->keySlugCate);

    //         // $catUrl=$request->catUrl;
    //         $minPrice = $request->minPrice;
    //         $maxPrice = $request->maxPrice;

    //         $sort = $request->sort != "" ? $request->sort : 'DESC';

    //         $sortStatus = $request->sortStatus;
    //         $itemPage = $request->item ? $request->item : 20;
    //         $offset = $request->page ? $request->page : 1;
    //         // $catId = CategoryDesc::where('friendly_url', $catUrl)->first()->cat_id;
    //         // $catId = CategoryDesc::where('cat_id', $catId)->first()->cat_id;

    //         if (!isset($catId)) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'category null',
    //             ]);
    //         }
    //         $query = Product::whereRaw('FIND_IN_SET(?, cat_list)', [$catId])
    //             ->select('product_id', 'cat_id', 'cat_list', 'macn', 'price', 'price_old',
    //                 'brand_id', 'op_search', 'picture', 'views', 'status', 'stock')
    //             ->orderByRaw("
    //                                 CASE
    //                                     WHEN stock = 1 THEN 1
    //             WHEN stock = 2 THEN 2
    //                                     ELSE 3
    //                                 END
    //                             ");

    //         $brandUrl = $request['thuong-hieu'];

    //         $brandId = BrandDesc::where('friendly_url', $brandUrl)->first();
    //         if ($brandUrl && $brandId) {
    //             //$brandId = BrandDesc::where('friendly_url', $brandUrl)->first()->brand_id;

    //             $query->where('brand_id', $brandId->brand_id);
    //         }
    //         // if(isset($listBrand)){
    //         //     $query->where('brand_id', $listBrand);
    //         // }

    //         if (!empty($minPrice) && !empty($maxPrice)) {

    //             $query->whereBetween("price", [$minPrice, $maxPrice]);
    //         }

    //         if ($sort == "DESC") {
    //             $query->orderBy('price', 'desc')->where('price', '!=', '0');
    //         } else if ($sort == "ASC") {
    //             $query->orderBy('price', 'asc')->where('price', '!=', '0');
    //         } else if ($sort == "new_products") {
    //             $query->orderBy('product_id', 'desc');
    //         } else if ($sort == "best_seller") {
    //             $query->orderBy('views', 'desc');
    //         }

    //         // if(!empty($sort)) {
    //         //     $query->orderBy('price',$sort)->where('price','!=','0');
    //         // }

    //         // if(!empty($sortView)) {
    //         //     $query->orderBy('views',$sortView);
    //         // }

    //         //return $productCatOP;
    //         $op_search = $arrOpId;

    //         $listProductId = [];

    //         //290,38,181,61
    //         //return $op_search;

    //         if ($op_search) {

    //             foreach ($query->get() as $product) {
    //                 $productOp = explode(",", $product->op_search);
    //                 //return  $productOp;

    //                 $diff = array_diff($op_search, $productOp);

    //                 if (empty($diff)) {

    //                     array_push($listProductId, $product->product_id);

    //                 }
    //             }

    //             $query->whereIn('product_id', $listProductId);

    //         }
    //         $totalProductForFilter = count($query->get());
    //         $listDataProduct = $query->limit(12)
    //             ->offset(($offset - 1) * 12)->get();

    //         $listProduct = [];
    //         foreach ($listDataProduct as $item) {
    //             // $item['technology'] = $this->getTechnology($item->product_id);
    //             // $urlExist = 'http://192.168.245.190:8000/uploads/'.$item->picture;

    //             // if(@file_get_contents($urlExist)){
    //             $listProduct[] = [
    //                 'ProductName' => $item->productDesc->title ?? null,
    //                 'ParentCatId' => explode(",", $item->cat_list)[0] ?? null,
    //                 'Price' => $item->price ?? null,
    //                 'PriceOld' => $item->price_old ?? null,
    //                 'Image' => $item->picture ?? null,
    //                 'ProductId' => $item->product_id ?? null,
    //                 'Category' => $item->categoryDes->cat_name ?? null,
    //                 'Macn' => $item->macn ?? null,
    //                 'type' => $item->status == 5 ? 'flash-sale' : null,
    //                 'stock' => $item->stock ?? null,
    //                 'PriceFlashSale' => $item->productFlashSale->discount_price ?? null,
    //                 'brandName' => $item->brandDesc->title ?? null,
    //                 'UrlProduct' => $item->productDesc->friendly_url ?? null,
    //                 'compareStatus' => $this->checkCompareProduct($item->product_id),
    //                 'checkPresent' => $this->checkPresent($item->product_id),
    //             ];
    //             // }
    //         }
    //         return response()->json([

    //             'status' => true,
    //             'total' => $totalProductForFilter,
    //             'listProduct' => $listProduct,
    //             'categoryDes' => $category->description ?? null,
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage(),
    //         ]);
    //     }
    // }

    public function getNameCategory(Request $request)
    {
        try {

            $categoryUrl = $request->categoryUrl;

            $category = CategoryDesc::where('friendly_url', $categoryUrl)->first();

            if (!$category) {
                return response()->json([
                    'status' => false,
                    'message' => 'notFoundCategory',
                ]);
            }
            $catName = $category->cat_name;
            $metakey = $category->metakey;
            $metadesc = $category->metadesc;
            $description = $category->description;
            return response()->json([
                'status' => true,
                'catName' => $catName ?? null,
                'metakey' => $metakey ?? null,
                'metadesc' => $metadesc ?? null,
                'description' => $description,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }

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
    public function checkFilterOp($catUrl)
    {
        try {

            $category = CategoryDesc::where('friendly_url', $catUrl)->first();

            if (!$category) {
                return response()->json([
                    'status' => false,
                    'message' => 'notFoundCategory',
                ]);
            }

            $catId = $category->cat_id;

            $productCatOP = DB::table('product_cat_option')
                ->join('product_cat_option_desc', 'product_cat_option_desc.op_id', '=', 'product_cat_option.op_id')
                ->select('product_cat_option.op_id', 'product_cat_option_desc.title', 'product_cat_option_desc.slug')
                ->where('cat_id', $catId)->get();
            // return $productCatOP;

            return count($productCatOP);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}

// public function filter(Request $request)
//     {
//         $vtnkdt=$request['vtnkdt'];
//         if( $vtnkdt==99999){

//         try{
//         $listTech = [];
//         $search = $request->search;
//         if(empty($search)){

//             if(empty($request->keySlugCate))
//             {

//                 $arrProductHot= Product::with('productDesc','category','categoryDes', 'brand', 'brandDesc')
//                 ->where('status', 4)->where('stock',1)->where('display',1)->orderBy('product_id', 'desc')->paginate(20);
//                 foreach($arrProductHot as $key => $value){
//                     $unserialzie = $value->technology;
//                     $unserialzie = preg_replace_callback(
//                         '/(?<=^|\{|;)s:(\d+):\"(.*?)\";(?=[asbdiO]\:\d|N;|\}|$)/s',
//                         function($m){
//                             return 's:' . strlen($m[2]) . ':"' . $m[2] . '";';
//                         },
//                         $unserialzie
//                     );
//                     $unserialzie = unserialize($unserialzie);
//                     $options = DB::table('product_cat_option_desc')->get();
//                     $dataValue = [];
//                     foreach( $unserialzie as $key1 =>$value1){
//                         if(is_numeric($key1)){
//                         foreach($options as $option){
//                             if($option->op_id == $key1 && $value1!=null){
//                             array_push($dataValue,[
//                                 'catOption'=> isset($option->title) ? $option->title : '',
//                                 'nameCatOption' => $value1]);
//                                 }
//                             }
//                         }else{
//                             if($value1!=null)
//                             {
//                                 array_push($dataValue,[
//                                     'catOption'=> $key1,
//                                     'nameCatOption' => $value1]);
//                             }
//                         }
//                     }
//                     $arrProductHot[$key]['technology'] = $dataValue;
//                     $catArray = explode(',', $value->cat_list);
//                     $catNameParent = CategoryDesc::where('cat_id',$catArray)->get();
//                     $encry =  Crypt::encryptString($value['product_id']);
//                     $encryKey = substr($encry, 2);

//                     $arrProductHot[$key]['id_product']= $encryKey;
//                     $arrProductHot[$key]['catNameParent'] = $catNameParent;
//                 }
//                 return response()->json([
//                     'productHot' => $arrProductHot,
//                     'status'=>true,

//                 ]);
//             }
//             else{

//                 $status=true;
//                 //$parts = explode("/", $request->keySlugCate);

//                 $minPrice = $request->from;
//                 $maxPrice = $request->to;
// //$brand =$request['thuong-hieu'];

//                 $sort = $request->sort != "" ? $request->sort : 'DESC';
//                 $sortView = $request->sortView != "" ? $request->sortView : 'DESC';
//                 $sortStatus = $request->sortStatus;
//                 $itemPage = $request->item ?  $request->item : 20 ;
//                 $cat = CategoryDesc::where('friendly_url', $request->keySlugCate)->first();
//                 if(!isset($cat)){
//                     return response()->json([
//                         'status'=>false,
//                         'message'=>'category null'
//                     ]);
//                 }

//                 $catId =$cat->cat_id;

//                 $catNameParent = CategoryDesc::where('cat_id',$catId)->first();

//                 $catNameParent['picture'] = Category::where('cat_id',$catId)->first()->picture;

//                 $query = Product::with('productDesc', 'category.subCategories', 'categoryDes', 'brandDesc')->whereHas('productDesc')
//                 ->whereRaw('FIND_IN_SET(?, cat_list)', [$catId])
//                 ->orderByRaw("
//                     CASE
//                         WHEN stock = 1 THEN 1
// WHEN stock = 2 THEN 2
//                         ELSE 3
//                     END
//                 ");

//                 if(isset($request['brand_id'])){

//                     $query->where('brand_id',$request['brand_id']);
//                 }

//                 if (!empty($sortStatus)) {
//                     $query->where('status',$sortStatus)->where('stock',1);
//                 }

//                 $ProductCatOptionDesc=[];

//                 foreach($request->all() as $value){

//                     if(ProductCatOptionDesc::where('slug',$value)->first()!=null  && $value!=$request->keySlugCate)
//                     {
//                         $ProductCatOptionDesc[]= ProductCatOptionDesc::where('slug',$value)->get();
//                     }

//                 }

//                 if(isset($request['op_id'])){
//                     $ProductCatOptionDesc[]=ProductCatOptionDesc::where('op_id',$request['op_id'])->get();
//                 }

//                 foreach($ProductCatOptionDesc as $items)
//                 {
//                     foreach( $items as $item){
//                         $slugs=$item->slug;
//                     }
//                 }

//                 if(count($ProductCatOptionDesc)>0 && $slugs!=$request->keySlugCate){
//                     //return 1;

//                     $countOp=count($ProductCatOptionDesc);

//                     $data=[];
//                     foreach($query->get() as $item)
//                     {
//                             $arr = explode(",", $item->op_search);
//                             $data[]=$arr;
//                     }

//                     $listOp1=[];
//                     $listOp=[];
//                     foreach($ProductCatOptionDesc as $item){
//                         if(count($item)>=2){
//                             $listOp[]=$item;
//                         }else if(count($item)==1)
//                         {
//                             $listOp1[]=$item;
//                         }
//                     }

//                     $listNumber1=[];
//                     foreach($listOp1 as $items){
//                         foreach($items as $item){
//                             $listNumber1[]=$item->op_id;
//                         }
//                     }

//                     $listNumber=[];
//                     foreach($listOp as $items){
//                         foreach($items as $item){
//                             $listNumber[]=$item->op_id;
//                         }
//                     }

//                     $listData = [];
//                     foreach($data as $item){
// foreach($item as $items){
//                             foreach($listNumber as $row){
//                                 if($items==$row && !in_array($row,$listData)){
//                                         array_push($listData,$row);
//                                     }
//                             }
//                         }
//                     }

//                     $listData=array_merge($listData,$listNumber1);
//                     $countListData=count($listData);
//                     if($countOp > $countListData)
//                     {

//                         $query->whereRaw('FIND_IN_SET(?, op_search)', "");

//                     }else{
//                         if(!empty($listData)){
//                             foreach ($listData as $value) {
//                                 $query->whereRaw('FIND_IN_SET(?, op_search)', [$value]);
//                             }
//                         }else{
//                             $query->whereRaw('FIND_IN_SET(?, op_search)', "");
//                         }

//                     }

//                 }

//                 if (!empty($minPrice) && !empty($maxPrice)) {
//                     $comparePrice = auth('member')->user() ? "price_old" : "price";
//                     $query->whereBetween($comparePrice, [$minPrice, $maxPrice]);
//                 }
//                 if(!empty($sort)) {
//                     $query->orderBy('price',$sort)->where('price','!=','0');
//                 }
//                 if (!empty($catId)) {
//                     $query->whereRaw('FIND_IN_SET(?,cat_list)', [$catId]);

//                 }

//                 $newProductCount = 10;

//                 $listProduct= $query->paginate(20);
//                 $i = "";

//                 foreach ($listProduct as $key => $value) {

//                     $unserialzie = $value->technology;

//                     $unserialzie = preg_replace_callback(
//                         '/(?<=^|\{|;)s:(\d+):\"(.*?)\";(?=[asbdiO]\:\d|N;|\}|$)/s',
//                         function($m){
//                             return 's:' . strlen($m[2]) . ':"' . $m[2] . '";';
//                         },
//                         $unserialzie
//                     );
//                     //return $unserialzie
//                     $unserialzie = unserialize($unserialzie);

//                     $options = DB::table('product_cat_option_desc')->get();
//                     $dataValue = [];

//                     if($unserialzie!=null)
//                     {

//                         foreach($unserialzie as $key1 =>$value1){
//                             if(is_numeric($key1)){
//                             foreach($options as $option){
//                                 if($option->op_id == $key1 && $value1!=null){
//                                 array_push($dataValue,[
// 'catOption'=> isset($option->title) ? $option->title : '',
//                                     'nameCatOption' => $value1]);
//                                     }
//                                 }
//                             }else{
//                                 if($value1!=null)
//                                 {
//                                     array_push($dataValue,[
//                                         'catOption'=> $key1,
//                                         'nameCatOption' => $value1]);
//                                 }
//                             }
//                         }
//                         $listProduct[$key]['technology'] = $dataValue ;
//                     }
//                     else{
//                         $listProduct[$key]['technology']=$dataValue;
//                     }

//                     if($listProduct->currentPage()==1)
//                     {
//                         if ($key < $newProductCount) {
//                             $value->is_hot = true;

//                         } else {
//                             $value->is_hot = false;
//                         }
//                     }

//                     $encry =  Crypt::encryptString($value['product_id']);
//                     $encryKey = substr($encry, 2);
//                     $listProduct[$key]['id_product']= $encryKey;

//                 }
//                 $catProduct = CategoryDesc::where('friendly_url',$request->keySlugCate)->first();

//                 if(!isset($catProduct)){

//                     $part = explode("-", $request->keySlugCate);
//                     $brandIsset=BrandDesc::where("friendly_url",$part[1])->first();
//                     if(!isset($brandIsset)){
//                         $status=false;
//                     }
//                 }else{

//                     $part = $request->keySlugCate;

//                 }
//                 $catProduct = CategoryDesc::where('friendly_url',$part)->first()->cat_id;

//                 $categoryList=Category::where('cat_id', $catProduct)->first()->cat_code;

//                 $listParent=explode("_", $categoryList);

//                 $dataListParent=[];

//                 $count=count($listParent);

//                 foreach($listParent as $index=> $item){

//                     $dataListParent[]=CategoryDesc::where('cat_id',$item)->first();

//                 }
//                 $categoryListChild=Category::where('parentid', $catProduct)->get()->pluck('cat_id');
//                 $dataListChild=[];
//                 foreach($categoryListChild as $item){
//                     $dataListChild[]=CategoryDesc::where('cat_id',$item)->first();

//                 }

//                 return response()->json([
//                     'products' => $listProduct,
// 'catname' => $catNameParent,
//                     'dataListParent'=> $dataListParent,
//                     'dataListChild'=>$dataListChild,
//                     'status'=>$status,
//                     'message'=>$status==false? 'brand null': '',
//                      'pageTitle' => $cat->metadesc
//                 ]);
//             }
//         }else{
//             $sort = $request->sort != "" ? $request->sort : 'DESC';
//            // return $sort;
//             $query = Product::with('productDesc', 'category', 'categoryDes', 'brandDesc')
//             ->where('macn', 'like', '%' . $search . '%')
//             ->orWhereHas('productDesc', function ($qr) use ($search) {
//                 $qr->where('title', 'like', '%' . $search . '%');
//             })
//             ->orderByRaw("
//                 CASE
//                     WHEN stock = 1 THEN 1
//                     WHEN stock = 2 THEN 2
//                     ELSE 3
//                 END
//             ");
//            $catList=$query->get();

//            $data=[];
//            foreach($catList as $item)
//            {
//                 if(!in_array($item->cat_id,$data)){
//                     array_push($data,$item->cat_id);
//                 }

//            }
//             $categories=Category::with('categoryDesc', 'subCategories',
//                 'catOption.subCateOption.catOptionDesc')
//                 ->orderBy('cat_id', 'ASC')->where('parentid',0)->where('display', 1)
//                 ->get();
//             $menuCategory=$this->buildCategoryMenu( $categories);

//             $query->orderBy('price',$sort);

//             $listProduct=$query->where('display',1)->paginate(20);

//             foreach ($listProduct as $key => $value) {
//                 $unserialzie = $value->technology;

//                 $unserialzie = preg_replace_callback(
//                         '/(?<=^|\{|;)s:(\d+):\"(.*?)\";(?=[asbdiO]\:\d|N;|\}|$)/s',
//                         function($m){
//                             return 's:' . strlen($m[2]) . ':"' . $m[2] . '";';
//                         },
//                         $unserialzie
//                 );
//                 $unserialzie = unserialize($unserialzie);
//                 $options = DB::table('product_cat_option_desc')->get();
//                 $dataValue = [];
//                     foreach( $unserialzie as $key1 =>$value1){
//                         foreach($options as $option){
//                             if($option->op_id == $key1 && $value1!=null){
//                             array_push($dataValue,[
//                                 'catOption'=> isset($option->title) ? $option->title : '',
//                                 'nameCatOption' => $value1]);
//                             }
//                         }
//                 }

//             $listProduct[$key]['technology'] = $dataValue ;
//             $catArray = explode(',', $value->cat_list);
//             $catNameParent = CategoryDesc::where('cat_id',$catArray)->get();
// $encry =  Crypt::encryptString($value->product_id);
//             $encryKey = substr($encry, 2);
//             $listProduct[$key]['id_product'] = $encryKey;
//             $listProduct[$key]['product_id'] = 0;
//             $listProduct[$key]['catNameParent'] = $catNameParent;
//          }
//          $catProduct = CategoryDesc::where('friendly_url',$request->keySlugCate)->first();
//          return response()->json([
//             'products' => $listProduct,
//             'categoryMenu'=>$menuCategory,
//             'catNameParent' => null
//         ]);
//         }
//     }
//     catch(Exception $e){
//         return response()->json([
//             'status' => false,
//             'message' => $e->getMessage()
//         ]);
//     }
//     }
//     else{
//         return response()->json([
//             'status'=>'true',
//             'data'=>['id'=>123456,'productName'=>'laptop hp','price'=>0]
//         ]);
//     }
// }
