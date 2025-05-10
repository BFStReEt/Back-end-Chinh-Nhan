<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryDesc;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductDesc;
use App\Models\ProductFlashSale;
use App\Models\ProductGroup;
use App\Models\ProductPicture;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // public function __construct()
    // {
    //     Product::observe(ProductObserver::class);
    // }
    protected function getModel()
    {
        return new Product();
    }

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

    public function index(Request $request)
    {
        try {
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' => $stringTime,
                'ip' => $request->ip(),
                'action' => 'show all product',
                'cat' => 'product',
            ]);
            if (Gate::allows('QUẢN LÝ SẢN PHẨM.Tính năng sản phẩm.manage')) {
                $productPrice = $request->price;
                $productPriceOld = $request->price_old;

                $product = Product::with('productDesc');
                if (!empty($request->input('data')) && $request->input('data') !== 'null' && $request->input('data') !== 'undefined') {
                    $product->whereHas('productDesc', function ($query) use ($request) {
                        $query->where("title", 'like', '%' . $request->input('data') . '%');
                    })->orWhere("macn", 'like', '%' . $request->input('data') . '%');

                }
                if ($request->input('brand') !== null && $request->input('brand') !== '0') {
                    $product->where('brand_id', $request->input('brand'));
                }
                if ($request->input('category') !== null && $request->input('category') !== '0') {
                    $product->whereRaw('FIND_IN_SET(?, cat_list)', [$request->input('category')]);
                }
                if ($request->status != '') {
                    $product->where('status', $request->status);
                }
                if ($productPrice != '') {
                    $product->where('price', $productPrice);
                    // $product->whereBetween('price',[$productPrice,$productPriceOld]);
                }
                if ($request->stock == true) {
                    $product->where('stock', 1);
                }

                //$product->whereNotNull('priceSAP')->whereNotNull('priceSAP1');

                if ($request->startDate != '' && $request->endDate != '') {
                    $start = $request->startDate;
                    $end = $request->endDate;
                    $product->whereBetween('created_at', [$start, $end]);
                }

                if ($request->isNotPaginate == true) {
                    $products = $product->orderBy('product_id', 'desc')->get();
                } else {
                    $products = $product->orderBy('product_id', 'desc')->paginate(15);
                }

                foreach ($products as $product) {

                    $catIdParent = explode(",", $product->cat_list)[0];
                    $product['categoryParent'] = CategoryDesc::where('cat_id', $catIdParent)->first();
                }

                return response()->json([
                    'status' => true,
                    'product' => $products,
                ]);
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

    //Phần store sản phẩm mới bắt đầu
    public function store(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $now = now()->timestamp;

        if (!Gate::allows('QUẢN LÝ SẢN PHẨM.Tính năng sản phẩm.add')) {
            return response()->json(['status' => false, 'mess' => 'no permission'], 403);
        }

        try {
            if ($request->input('status') == 5 && $request->input('stock') == 2) {
                return response()->json(['status' => false, 'message' => 'stock'], 422);
            }
            if ($request->input('status') == 4 && $request->input('stock') == 2) {
                return response()->json(['status' => false, 'message' => 'stock'], 422);
            }

            if (!empty($request->input('macn')) && Product::where('MaHH', $request->input('macn'))->exists()) {
                return response()->json(['status' => false, 'message' => 'macn'], 409);
            }

            if (ProductDesc::where('title', $request->input('title'))->exists()) {
                return response()->json(['status' => false, 'message' => 'title'], 409);
            }

            DB::beginTransaction();

            DB::table('adminlogs')->insert([
                'admin_id' => $admin->id,
                'time' => $now,
                'ip' => $request->ip(),
                'action' => 'add a product',
                'cat' => 'product',
            ]);

            $filePath = null;
            if ($request->picture && is_array($request->picture)) {
                $filePath = $this->saveBase64Image($request->picture[0], 'uploads/product');
            }

            $product = Product::create([
                'cat_id' => $request->input('cat_id'),
                'cat_list' => implode(',', $request->input('cat_list')),
                'macn' => $request->input('macn'),
                'code_script' => $request->input('code_script'),
                'picture' => $filePath,
                'price' => $request->input('price'),
                'price_old' => $request->input('price_old'),
                'brand_id' => $request->input('brand_id'),
                'status' => $request->input('status'),
                'op_search' => implode(',', $request->input('op_search')),
                'focus' => 0,
                'deal_date_start' => 0,
                'deal_date_end' => 0,
                'stock' => $request->input('stock'),
                'votes' => $request->input('votes'),
                'views' => 0,
                'display' => $request->input('display'),
                'date_post' => $now,
                'date_update' => $now,
            ]);

            ProductDesc::create([
                'product_id' => $product->product_id,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'option' => 2,
                'short' => $request->input('short'),
                'friendly_url' => $request->input('friendly_url') ?? Str::slug($request->input('title')),
                'friendly_title' => $request->input('friendly_title'),
                'metakey' => $request->input('metakey'),
                'metadesc' => $request->input('metadesc'),
                'lang' => 'vi',
            ]);

            $price = Price::create([
                'product_id' => $product->product_id,
                'cat_id' => $request->input('cat_id'),
                'price' => $request->input('price'),
                'price_old' => $request->input('price_old'),
            ]);

            $technologies = $request->input('technology', []);
            if (!empty($technologies) && is_array($technologies)) {
                foreach ($technologies as $propertyId => $description) {
                    DB::table('product_properties')->insert([
                        'price_id' => $price->id,
                        'properties_id' => $propertyId,
                        'pv_id' => 1,
                        'description' => $description,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if (is_array($request->picture_detail)) {
                foreach ($request->picture_detail as $img) {
                    $path = $this->saveBase64Image($img, 'uploads/product');
                    ProductPicture::create([
                        'product_id' => $product->product_id,
                        'pic_name' => basename($path),
                        'picture' => $path,
                        'menu_order' => 0,
                        'display' => 1,
                        'date_post' => 0,
                        'date_update' => 0,
                    ]);
                }
            }

            if ($request->input('status') == 5 && $request->input('stock') != 2) {
                ProductFlashSale::create([
                    'product_id' => $product->product_id,
                    'price' => $request->input('price'),
                    'price_old' => $request->input('price_old'),
                    'discount_percent' => 0,
                    'discount_price' => $request->input('discount_price') ?? 0,
                    'start_time' => $request->input('flashStart'),
                    'end_time' => $request->input('flashEnd'),
                    'adminid' => $admin->id,
                ]);
            } else {
                ProductFlashSale::where('product_id', $product->product_id)->delete();
            }

            DB::commit();

            return response()->json(['status' => true, 'productId' => $product->product_id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error while storing product: " . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Đã xảy ra lỗi trong quá trình lưu sản phẩm: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function saveBase64Image($base64String, $folder)
    {
        $file_chunks = explode(';base64,', $base64String);
        $imageData = base64_decode($file_chunks[1]);
        $name = uniqid() . '.png';
        $path = $folder . '/' . $name;
        file_put_contents(public_path($path), $imageData);
        return str_replace('uploads/', '', $path);
    }

    //Phần store của sản phẩm kết thúc

    public function edit(Request $request, string $id)
    {
        try {
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' => $stringTime,
                'ip' => $request->ip(),
                'action' => 'edit a product',
                'cat' => 'product',
            ]);
            if (Gate::allows('QUẢN LÝ SẢN PHẨM.Tính năng sản phẩm.edit')) {
                $list = Product::with('productDesc', 'productPicture', 'productGroups')->find($id);
                $list_cate = explode(',', $list->cat_list);
                $i = count($list_cate);
                for ($j = 0; $j < $i; $j++) {
                    $save[] = (int) $list_cate[$j];
                }
                $list['list_cate'] = $save;
                $list['parentId'] = $save[0] ?? null;
                $list['cateId'] = $save[1] ?? null;
                $list['childId'] = $save[2] ?? null;

                //TSKT
                $technologies = [];

                $product = Product::find($id);

                $price = DB::table('price')->where('product_id', $product->product_id)->first();
                $priceId = $price->id ?? null;

                if ($priceId) {
                    $productPropertiesRaw = DB::table('product_properties')
                        ->where('price_id', $priceId)
                        ->get();

                    $productProperties = [];
                    foreach ($productPropertiesRaw as $pp) {
                        if (!empty($pp->description)) {
                            $productProperties[$pp->properties_id] = $pp->description;
                        }
                    }

                    $catList = explode(',', $product->cat_list);
                    $parentCatId = (int) ($catList[0] ?? 0);

                    $catIds = DB::table('properties_category')
                        ->where('cat_id', $parentCatId)
                        ->where('parentid', 0)
                        ->orderBy('properties_id')
                        ->pluck('properties_id')
                        ->toArray();

                    foreach ($catIds as $propertiesId) {
                        if (!empty($productProperties[$propertiesId])) {
                            $technologies[$propertiesId] = $productProperties[$propertiesId];
                        }
                    }
                }

                $list['technology'] = $technologies;

                //Phần value
                $list['op_search'] = [];

                if ($priceId) {
                    $pvIds = DB::table('product_properties')
                        ->where('price_id', $priceId)
                        ->pluck('pv_id')
                        ->toArray();

                    $list['op_search'] = array_map('intval', $pvIds);
                }
                //xong phần value

                $group = [];
                if (isset($list->productGroups) && count($list->productGroups)) {
                    foreach ($list->productGroups as $items) {
                        $groupProduct = ProductDesc::where('product_id', $items->product_child)->first();
                        $group[] = [
                            'product_id' => $groupProduct->product_id,
                            'nameProduct' => $groupProduct->title,
                            'price' => $groupProduct->product->price,
                            'price_old' => $groupProduct->product->price_old,
                            'picture' => $groupProduct->product->picture,
                            'macn' => $groupProduct->product->macn,
                            'friendly_url' => $groupProduct->friendly_url,
                            'title' => $items->titleGroup,
                            'date_start' => $items->date_start,
                            'date_end' => $items->date_end,
                            'discount' => $items->discount,
                        ];
                    }
                }
                $list['group'] = $group;

                //Nếu sản phẩm từ SAP về giá bị null do chưa đồng bộ
                $list->price = $list->price ?? 0;
                $list->price_old = $list->price_old ?? 0;
                return response()->json([
                    'status' => true,
                    'product' => $list,
                ]);
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

    //Phần update
    public function update(Request $request, string $id)
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' => $stringTime,
            'ip' => $request->ip(),
            'action' => 'update a product',
            'cat' => 'product',
        ]);

        $product = new Product();
        $productPicture = new ProductPicture();
        $productDesc = new ProductDesc();
        $listProduct = Product::Find($id);
        try {
            if (Gate::allows('QUẢN LÝ SẢN PHẨM.Tính năng sản phẩm.update')) {
                $checkMacn = Product::where('macn', $request->input('macn'))->where('product_id', '!=', $id)->first();
                if ($checkMacn != null && $request->input('macn') != '') {
                    return response()->json([
                        'message' => 'macn',
                        'status' => false,
                    ], 202);
                }

                if ($request->input('status') == 5 && $request->input('stock') == 2) {
                    return response()->json([
                        'message' => 'stock',
                        'status' => false,
                    ], 202);
                }

                if ($request->input('status') == 4 && $request->input('stock') == 2) {
                    return response()->json([
                        'message' => 'stock',
                        'status' => false,
                    ], 202);
                }

                $checkDesc = ProductDesc::where('product_id', '!=', $id)->where(function ($query) use ($request) {
                    $query->where('title', $request->input('title'))
                        ->orWhere('friendly_url', $request->input('friendly_url'));
                })->get();

                if (count($checkDesc) > 0) {
                    return response()->json([
                        'message' => 'title',
                        'status' => false,
                    ], 202);
                }

                if (isset($request->product_combo) && count($request->product_combo) > 0) {
                    foreach ($request->product_combo as $value) {
                        $findItemGroup = ProductGroup::where('product_child', $value['productId'])->where('product_main', $id)->first();

                        if (!$findItemGroup) {
                            $productGroup = new ProductGroup;
                            $productGroup->product_main = $id;
                            $productGroup->product_child = $value['productId'];
                            $productGroup->date_post = strtotime('now');
                            $productGroup->date_update = strtotime('now');
                            $productGroup->date_start = $value['discountApplied'] == true ? Carbon::createFromFormat('Y-m-d', $value['discountDetails']['startDate'])->timestamp : null;
                            $productGroup->date_end = $value['discountApplied'] == true ? Carbon::createFromFormat('Y-m-d', $value['discountDetails']['endDate'])->timestamp : null;
                            $productGroup->titleGroup = $value['discountDetails']['content'];
                            $productGroup->discount = $value['discountDetails']['discountPrice'];
                            $productGroup->save();
                        } else {
                            $findItemGroup->date_post = strtotime('now');
                            $findItemGroup->date_update = strtotime('now');
                            $findItemGroup->date_start = $value['discountApplied'] == true ? Carbon::createFromFormat('Y-m-d', $value['discountDetails']['startDate'])->timestamp : null;
                            $findItemGroup->date_end = $value['discountApplied'] == true ? Carbon::createFromFormat('Y-m-d', $value['discountDetails']['endDate'])->timestamp : null;
                            $findItemGroup->titleGroup = $value['discountDetails']['content'];
                            $findItemGroup->discount = $value['discountDetails']['discountPrice'];
                            $findItemGroup->save();
                        }

                    }
                }

                if (isset($request->delete_item_product_groups) && count($request->delete_item_product_groups) != 0) {

                    foreach ($request->delete_item_product_groups as $value) {
                        $idProductGroup = ProductGroup::where('product_main', $id)->where('product_child', $value)->first()->delete();
                    }
                }

                $listProduct->cat_id = $request->input('cat_id');
                $listProduct->cat_list = implode(',', $request->input('cat_list'));
                $listProduct->macn = $request->input('macn');
                if (is_string($request->picture) && (substr($request->picture, -4) === ".png" || substr($request->picture, -4) === ".jpg")) {
                    $filePath = $request->picture;
                } else {
                    $filePath = '';
                }

                if ($request->picture != null && $filePath == '') {
                    $DIR = 'uploads/product';
                    $httpPost = file_get_contents('php://input');

                    $file_chunks = explode(';base64,', $request->picture[0]);

                    $fileType = explode('image/', $file_chunks[0]);

                    $image_type = $fileType[0];
                    $base64Img = base64_decode($file_chunks[1]);
                    $data = iconv('latin5', 'utf-8', $base64Img);
                    $name = uniqid();
                    $file = public_path($DIR) . '/' . $name . '.png';
                    $filePath = 'product/' . $name . '.png';
                    file_put_contents($file, $base64Img);
                }

                $listProduct->picture = $filePath;

                $listProduct->price = $request->input('price');
                $listProduct->price_old = $request->input('price_old');
                $listProduct->brand_id = $request->input('brand_id');
                $listProduct->status = $request->input('status');
                $listProduct->op_search = implode(',', $request->input('op_search'));
                $listProduct->focus = 0;
                $listProduct->deal_date_start = '0';
                $listProduct->deal_date_end = '0';
                $listProduct->stock = $request->input('stock');
                $listProduct->votes = $request->input('votes');
                $listProduct->menu_order_cate_lv0 = 0;
                $listProduct->menu_order_cate_lv1 = 0;
                $listProduct->menu_order_cate_lv2 = 0;
                $listProduct->menu_order_cate_lv3 = 0;
                $listProduct->menu_order_cate_lv4 = 0;
                $listProduct->menu_order_cate_lv5 = 0;
                $listProduct->menu_order_cate_lv6 = 0;
                $listProduct->menu_order_cate_lv7 = 0;
                $listProduct->menu_order_cate_lv8 = 0;
                $listProduct->menu_order_cate_lv9 = 0;
                $listProduct->menu_order_cate_lv10 = 0;
                $listProduct->display = $request->input('display');
                $listProduct->date_post = '0';
                $listProduct->date_update = '0';
                $listProduct->save();

                $productDesc = ProductDesc::where('product_id', $id)->first();
                if ($productDesc) {
                    $productDesc->product_id = $listProduct->product_id;
                    $productDesc->title = $request->input('title');
                    $productDesc->description = $request->input('description');
                    $productDesc->gift_desc = $request->input('gift_desc');
                    $productDesc->video_desc = $request->input('video_desc');
                    $productDesc->tech_desc = $request->input('tech_desc');
                    $productDesc->option = 2;
                    $productDesc->short = $request->input('short');
                    $productDesc->start_date_promotion = 0;
                    $productDesc->end_date_promotion = 0;
                    $productDesc->status_promotion = 0;
                    $productDesc->shortcode = $request->input('shortcode');
                    $productDesc->key_search = $request->input('key_search');
                    $productDesc->friendly_url = $request->input('friendly_url');
                    $productDesc->friendly_title = $request->input('friendly_title');
                    $productDesc->metakey = $request->input('metakey');
                    $productDesc->metadesc = $request->input('metadesc');
                    $productDesc->lang = 'vi';
                    $productDesc->save();
                } else {
                    $productDesc = new ProductDesc();
                    $productDesc->product_id = $listProduct->product_id;
                    $productDesc->title = $request->input('title');
                    $productDesc->description = $request->input('description');
                    // $productDesc->gift_desc = $request->input( 'gift_desc' );
                    // $productDesc->video_desc = $request->input( 'video_desc' );
                    // $productDesc->tech_desc = $request->input( 'tech_desc' );
                    // $productDesc->option = 2;
                    $productDesc->short = $request->input('short');
                    $productDesc->start_date_promotion = 0;
                    $productDesc->end_date_promotion = 0;
                    $productDesc->status_promotion = 0;
                    // $productDesc->shortcode = $request->input( 'shortcode' );
                    // $productDesc->key_search = $request->input( 'key_search' );
                    $productDesc->friendly_url = $request->input('friendly_url') ?? Str::slug($request->input('title'));
                    $productDesc->friendly_title = $request->input('friendly_title');
                    $productDesc->metakey = $request->input('metakey');
                    $productDesc->metadesc = $request->input('metadesc');
                    $productDesc->lang = 'vi';
                    $productDesc->save();

                }

                if ($request->picture_detail != null) {
                    $deletePicture = ProductPicture::where('product_id', $id)->get();
                    foreach ($deletePicture as $value) {

                        $namepicture = $value->picture;

                        //unlink( $filePath );
                        $value->delete();
                    }
                    $listProductPicture = ProductPicture::where('product_id', $id)->delete();

                    foreach ($request->picture_detail as $value) {
                        $productPicture = new ProductPicture();

                        $DIR = 'uploads/product';
                        $httpPost = file_get_contents('php://input');
                        $file_chunks = explode(';base64,', $value);

                        $fileType = explode('image/', $file_chunks[0]);

                        $image_type = $fileType[0];

                        $base64Img = base64_decode($file_chunks[1]);
                        $name = uniqid();
                        $file = public_path($DIR) . '/' . $name . '.png';
                        $filePath = 'product/' . $name . '.png';

                        file_put_contents($file, $base64Img);

                        $productPicture->product_id = $listProduct->product_id;
                        $productPicture->pic_name = $name . '.png';
                        $productPicture->picture = $filePath;
                        $productPicture->menu_order = 0;
                        $productPicture->display = 1;
                        $productPicture->date_post = 0;
                        $productPicture->date_update = 0;
                        $productPicture->save();
                    }
                }

                if ($request->input('status') == 5 && $request->input('stock') != 2) {
                    $list = ProductFlashSale::where('product_id', $id)->first();
                    //return response()->json($list == "");
                    if ($list != "") {
                        ProductFlashSale::where('product_id', $id)->first()->delete();
                    }
                    $ProductFlashSale = new ProductFlashSale();
                    $ProductFlashSale->product_id = $listProduct->product_id;
                    $ProductFlashSale->price = $request->input('price');
                    $ProductFlashSale->price_old = $request->input('price_old');
                    $ProductFlashSale->discount_percent = 0;
                    $ProductFlashSale->discount_price = $request->input('discount_price') ?? 0;
                    $ProductFlashSale->start_time = $request->input('flashStart') ?? null;
                    $ProductFlashSale->end_time = $request->input('flashEnd') ?? null;
                    $ProductFlashSale->status = 5;
                    $ProductFlashSale->adminid = 1;
                    $ProductFlashSale->save();
                }

                if ($request->input('status') != 5) {
                    $list = ProductFlashSale::where('product_id', $id)->first();
                    //return response()->json($list == "");
                    if ($list != "") {
                        ProductFlashSale::where('product_id', $id)->first()->delete();
                    }
                }

                $price = Price::where('product_id', $id)->first();
                $priceId = $price->id;
                if ($request->has('op_search') && is_array($request->op_search)) {
                    $pvIdsFromFE = $request->op_search;
                    $productId = $listProduct->product_id;

                    $priceIds = DB::table('price')
                        ->where('product_id', $productId)
                        ->pluck('id');

                    $productValues = DB::table('properties_value')
                        ->whereIn('id', $pvIdsFromFE)
                        ->get();

                    $insertedKeys = [];

                    foreach ($productValues as $pv) {
                        foreach ($priceIds as $priceId) {
                            $existing = DB::table('product_properties')
                                ->where('price_id', $priceId)
                                ->where('pv_id', $pv->id)
                                ->first();

                            if ($existing) {
                                DB::table('product_properties')
                                    ->where('id', $existing->id)
                                    ->update([
                                        'updated_at' => now(),
                                    ]);
                            } else {
                                DB::table('product_properties')->insert([
                                    'pv_id' => $pv->id,
                                    'properties_id' => $pv->properties_id,
                                    'price_id' => $priceId,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }

                            $insertedKeys[] = [
                                'price_id' => $priceId,
                                'pv_id' => $pv->id,
                                'properties_id' => $pv->properties_id,
                            ];
                        }
                    }

                    foreach ($priceIds as $priceId) {
                        $existingRows = DB::table('product_properties')
                            ->where('price_id', $priceId)
                            ->get();

                        foreach ($existingRows as $row) {
                            $shouldKeep = collect($insertedKeys)->contains(function ($item) use ($row) {
                                return $item['price_id'] == $row->price_id &&
                                $item['pv_id'] == $row->pv_id &&
                                $item['properties_id'] == $row->properties_id;
                            });

                            if (!$shouldKeep) {
                                DB::table('product_properties')->where('id', $row->id)->delete();
                            }
                        }
                    }
                }
                $technologies = $request->input('technology', []);
                foreach ($technologies as $propertyId => $description) {
                    DB::table('product_properties')->updateOrInsert(
                        [
                            'price_id' => $priceId,
                            'properties_id' => $propertyId,
                            'pv_id' => 1,
                        ],
                        [
                            'description' => $description,
                        ]
                    );
                }

                return response()->json([
                    'status' => true,
                ]);
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

    public function destroy(Request $request, string $id)
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' => $stringTime,
            'ip' => $request->ip(),
            'action' => 'delete a product',
            'cat' => 'product',
        ]);

        try {
            if (Gate::allows('QUẢN LÝ SẢN PHẨM.Tính năng sản phẩm.del')) {
                $product = Product::find($id);

                if ($product) {
                    if (!empty($product->picture) && file_exists(public_path($product->picture))) {
                        @unlink(public_path($product->picture));
                    }

                    $productDesc = ProductDesc::where('product_id', $id)->first();
                    if ($productDesc) {
                        $productDesc->delete();
                    }

                    $product->delete();
                }

                return response()->json([
                    'status' => true,
                ]);
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

    public function deleteAll(Request $request)
    {
        try {
            $arr = $request->data;
            if (Gate::allows('QUẢN LÝ SẢN PHẨM.Tính năng sản phẩm.del')) {
                if ($arr) {
                    foreach ($arr as $item) {
                        $product = Product::find($item);

                        if ($product) {
                            $productDesc = ProductDesc::where('product_id', $item)->first();
                            if ($productDesc) {
                                if (!empty($product->picture) && file_exists(public_path($product->picture))) {
                                    @unlink(public_path($product->picture));
                                }
                                $productDesc->delete();
                            }

                            $product->delete();
                        }
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

    public function deleteAllDetailImage(Request $request)
    {
        try {
            if (Gate::denies('QUẢN LÝ SẢN PHẨM.Tính năng sản phẩm.del')) {
                abort(403, 'No permission');
            }

            $ids = $request->ids;
            $deleted = [];

            if (is_array($ids) && !empty($ids)) {
                foreach ($ids as $id) {
                    $picture = ProductPicture::find($id);
                    if ($picture) {
                        if ($picture->picture && file_exists(public_path($picture->picture))) {
                            unlink(public_path($picture->picture));
                        }
                        $picture->delete();
                        $deleted[] = $id;
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'success',
                'deleted' => $deleted,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //==============================================================================================================================
    //Store cũ
    // public function store(Request $request)
    // {
    //     $admin = Auth::guard('admin')->user();
    //     $now = now()->timestamp;

    //     DB::table('adminlogs')->insert([
    //         'admin_id' => $admin->id,
    //         'time' => $now,
    //         'ip' => $request->ip(),
    //         'action' => 'add a product',
    //         'cat' => 'product',
    //     ]);

    //     if (!Gate::allows('QUẢN LÝ SẢN PHẨM.Tính năng sản phẩm.add')) {
    //         return response()->json(['status' => false, 'mess' => 'no permission'], 403);
    //     }

    //     //$disPath = public_path();
    //     $product = new Product();
    //     $productDesc = new ProductDesc();
    //     $productPicture = new ProductPicture();

    //     try {
    //         $checkMacn = Product::where('MaHH',$request->input( 'macn' ))->first();
    //         if($checkMacn != null && $request->input( 'macn' )!=''){
    //             return response()->json([
    //                 'message'=>'macn',
    //                 'status'=>false,
    //              ],202);
    //         }

    //         $checkDesc = ProductDesc::where('title',$request->input( 'title' ))
    //         ->first();
    //         if($checkDesc != null){
    //             return response()->json([
    //                 'message'=>'title',
    //                 'status'=>false,
    //             ],202);
    //         }
    //         //Kiểm tra 2 trường hợp đặc biệt
    //         if ($request->input('status') == 5 && $request->input('stock') == 2) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'stock']
    //                 , 422);
    //         }

    //         if ($request->input('status') == 4 && $request->input('stock') == 2) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'stock']
    //                 , 422);
    //         }

    //         $filePath = '';
    //         if ( $request->picture != null )
    //         {
    //             $DIR = 'uploads/product';
    //             $httpPost = file_get_contents( 'php://input' );
    //             $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
    //             $fileType = explode( 'image/', $file_chunks[ 0 ] );
    //             $image_type = $fileType[ 0 ];
    //             $base64Img = base64_decode( $file_chunks[ 1 ] );
    //             $data = iconv( 'latin5', 'utf-8', $base64Img );
    //             $name = uniqid();
    //             $file = public_path($DIR) . '/' . $name . '.png';
    //             $filePath = 'product/'.$name . '.png';
    //             file_put_contents( $file,  $base64Img );
    //         }

    //         $product->cat_id = $request->input( 'cat_id' );
    //         $product->cat_list = implode(',', $request->input( 'cat_list' ));
    //         $product->macn = $request->input( 'macn' );
    //         $product->code_script = $request->input( 'code_script' );
    //         $product->picture =$filePath;
    //         $product->price = $request->input( 'price' );
    //         $product->price_old = $request->input( 'price_old' );
    //         $product->brand_id = $request->input( 'brand_id' );
    //         $product->status = $request->input( 'status' );
    //         $product->op_search =implode(',', $request->input( 'op_search' ));
    //         $product->focus = 0;

    //         $product->deal_date_start = '0';
    //         $product->deal_date_end = '0';
    //         $product->stock = $request->input( 'stock' );
    //         $product->votes = $request->input( 'votes' );

    //         $product->menu_order_cate_lv0 = 0;
    //         $product->menu_order_cate_lv1 = 0;
    //         $product->menu_order_cate_lv2 = 0;
    //         $product->menu_order_cate_lv3 = 0;
    //         $product->menu_order_cate_lv4 = 0;
    //         $product->menu_order_cate_lv5 = 0;
    //         $product->menu_order_cate_lv6 = 0;
    //         $product->menu_order_cate_lv7 = 0;
    //         $product->menu_order_cate_lv8 = 0;
    //         $product->menu_order_cate_lv9 = 0;
    //         $product->menu_order_cate_lv10 = 0;
    //         $product->views = 0;
    //         $product->display = $request->input( 'display' );
    //         $product->date_post =  strtotime( 'now' );
    //         $product->date_update =  strtotime( 'now' );
    //             // $product->adminid =$request->input('adminid');
    //             // $product->url = $request->input( 'url' );
    //         $product->save();

    //         $productDesc->product_id = $product->product_id;
    //         $productDesc->title = $request->input( 'title' );
    //         $productDesc->description = $request->input( 'description' );
    //             // $productDesc->gift_desc = $request->input( 'gift_desc' );
    //             // $productDesc->video_desc = $request->input( 'video_desc' );
    //             // $productDesc->tech_desc = $request->input( 'tech_desc' );
    //         $productDesc->option = 2;
    //         $productDesc->short = $request->input( 'short' );
    //         $productDesc->start_date_promotion = 0;
    //         $productDesc->end_date_promotion = 0;
    //         $productDesc->status_promotion = 0;
    //             // $productDesc->shortcode = $request->input( 'shortcode' );
    //             // $productDesc->key_search = $request->input( 'key_search' );
    //         $productDesc->friendly_url = $request->input( 'friendly_url' ) ? $request->input( 'friendly_url' ) : Str::slug($request->title);
    //         $productDesc->friendly_title = $request->input( 'friendly_title' );
    //         $productDesc->metakey = $request->input( 'metakey' );
    //         $productDesc->metadesc = $request->input( 'metadesc' );
    //         $productDesc->lang = 'vi';
    //         $productDesc->save();

    //             //Thông số kỹ thuật

    //             $price = new Price();
    //             $price->product_id = $product->product_id;
    //             $price->cat_id = $request->input('cat_id');
    //             $price->price = $request->input('price');
    //             $price->price_old = $request->input('price_old');
    //             $price->save();

    //             $descriptions = $request->input('technologies.tskt', []);
    //             $pvValues = $request->input('technologies.value', []);
    //             $catId = $request->input('cat_list')[0] ?? null;

    //             if ($catId && !empty($descriptions)) {
    //                 $propertyIds = DB::table('properties_category')
    //                     ->where('category_id', $catId)
    //                     ->pluck('property_id')
    //                     ->toArray();

    //                 foreach ($propertyIds as $index => $propertyId) {
    //                     $description = $descriptions[$index] ?? null;
    //                     $pvInput = $pvValues[$index] ?? '';

    //                     if (empty($pvInput)) {
    //                         $pv_id = 1;
    //                     } else {
    //                         $exists = DB::table('properties_value')->where('id', $pvInput)->exists();
    //                         $pv_id = $exists ? $pvInput : 1;
    //                     }

    //                     if ($description !== null) {
    //                         DB::table('product_properties')->insert([
    //                             'pv_id' => $pv_id,
    //                             'properties' => $propertyId,
    //                             'price_id' => $price->id,
    //                             'description' => $description,
    //                             'created_at' => now(),
    //                             'updated_at' => now(),
    //                         ]);
    //                     }
    //                 }
    //             }

    //             if ( $request->picture_detail != null ) {
    //                 foreach ( $request->picture_detail as $value ) {
    //                     $productPicture = new ProductPicture();
    //                     $DIR = 'uploads/product';
    //                     $httpPost = file_get_contents( 'php://input' );
    //                     $file_chunks = explode( ';base64,', $value );
    //                     $fileType = explode( 'image/', $file_chunks[ 0 ] );
    //                     $image_type = $fileType[ 0 ];
    //                     $base64Img = base64_decode( $file_chunks[ 1 ] );
    //                     $name = uniqid();
    //                     $file = public_path($DIR) . '/' . $name . '.png';
    //                     $filePath = 'product/' . $name . '.png';
    //                     file_put_contents( $file, $base64Img );
    //                     $productPicture->product_id = $product->product_id;
    //                     $productPicture->pic_name = $name . '.png';
    //                     $productPicture->picture = $filePath;
    //                     $productPicture->menu_order = 0;
    //                     $productPicture->display = 1;
    //                     $productPicture->date_post = 0;
    //                     $productPicture->date_update = 0;
    //                     $productPicture->save();
    //                 }
    //             }
    //         //      if(isset($request->product_groups) && count($request->product_groups) > 0 ) {
    //         //     foreach ($request->product_group as $value) {
    //         //         $productGroup = new ProductGroup();
    //         //         $productGroup->product_main =  $product->product_id;
    //         //         $productGroup->product_child = $value;
    //         //         $productGroup->save();
    //         //     }
    //         // }
    //             if($request->input( 'status' ) == 5 &&  $request->input( 'stock' )!=2)
    //             {

    //                 $ProductFlashSale = new ProductFlashSale();
    //                 $ProductFlashSale->product_id  = $product->product_id;
    //                 $ProductFlashSale->price  = $request->input( 'price' );
    //                 $ProductFlashSale->price_old  =$request->input( 'price_old' );
    //                 $ProductFlashSale->discount_percent  = 0;
    //                 $ProductFlashSale->discount_price  = $request->input( 'discount_price' )??0;
    //                 $ProductFlashSale->start_time  = $request->input( 'flashStart' )??null;
    //                 $ProductFlashSale->end_time  = $request->input( 'flashEnd' )??null;
    //                 // $ProductFlashSale->status  = 1;
    //                 $ProductFlashSale->adminid  = 1;
    //                 $ProductFlashSale->save();
    //             }
    //             if($request->input( 'status' ) != 5)
    //             {
    //                 $list = ProductFlashSale::where('product_id',$product->product_id)->first();

    //                 if($list != "")
    //                 {
    //                     ProductFlashSale::where('product_id',$id)->first()->delete();
    //                 }
    //             }

    //             return response()->json([
    //                 'status'=>true,
    //                 'productId'=>$product->product_id
    //             ]);
    //     } catch ( \Exception $e ) {
    //         $errorMessage = $e->getMessage();
    //         $response = [
    //             'status' => 'false',
    //             'error' => $errorMessage
    //         ];
    //         return response()->json( $response, 500 );
    //     }
    // }

    //Update cũ
    // public function update(Request $request, string $id)
    // {
    //     $now = date('d-m-Y H:i:s');
    //     $stringTime = strtotime($now);
    //     DB::table('adminlogs')->insert([
    //         'admin_id' => Auth::guard('admin')->user()->id,
    //         'time' =>  $stringTime,
    //         'ip'=> $request->ip(),
    //         'action'=>'update a product',
    //         'cat'=>'product',
    //     ]);

    //     //$disPath = public_path();
    //     $product = new Product();
    //     $productPicture = new ProductPicture();
    //     $productDesc = new ProductDesc();
    //     $listProduct = Product::Find( $id );
    //     try {
    //         if(Gate::allows('QUẢN LÝ SẢN PHẨM.Tính năng sản phẩm.update')){
    //             $checkMacn = Product::where('macn',$request->input( 'macn' ))->where('product_id','!=',$id)->first();
    //             if($checkMacn != null && $request->input( 'macn' )!='')
    //             {
    //                 return response()->json([
    //                     'message'=>'macn',
    //                     'status'=>false,
    //                 ],202);
    //             }
    //             if($request->input( 'status' )==5 &&$request->input( 'stock' )==2){
    //                 return response()->json([
    //                     'message'=>'stock',
    //                     'status'=>false,
    //                 ],202);
    //             }
    //             if($request->input( 'status' )==4 &&$request->input( 'stock' )==2){
    //                 return response()->json([
    //                     'message'=>'stock',
    //                     'status'=>false,
    //                 ],202);
    //             }

    //             $checkDesc = ProductDesc::where('product_id','!=',$id)->where(function($query) use($request) {
    //                 $query->where('title',$request->input( 'title' ))
    //                     ->orWhere('friendly_url',$request->input( 'friendly_url' ));
    //             })->get();

    //             if(count($checkDesc)>0)
    //             {
    //                 return response()->json([
    //                     'message'=>'title',
    //                     'status'=>false,
    //                 ],202);
    //             }

    //             if(isset($request->product_combo) && count($request->product_combo) > 0 ) {
    //                 foreach ($request->product_combo as $value) {
    //                     $findItemGroup = ProductGroup::where('product_child',$value['productId'])->where('product_main',$id)->first();

    //                         if(!$findItemGroup) {
    //                             $productGroup = new ProductGroup;
    //                             $productGroup->product_main = $id;
    //                             $productGroup->product_child = $value['productId'];
    //                             $productGroup->date_post=strtotime( 'now' );
    //                             $productGroup->date_update=strtotime( 'now' );
    //                             $productGroup->date_start=$value['discountApplied']==true?Carbon::createFromFormat('Y-m-d', $value['discountDetails']['startDate'] )->timestamp:null;
    //                             $productGroup->date_end= $value['discountApplied']==true?Carbon::createFromFormat('Y-m-d', $value['discountDetails']['endDate'])->timestamp:null;
    //                             $productGroup->titleGroup=$value['discountDetails']['content'];
    //                             $productGroup->discount=$value['discountDetails']['discountPrice'];
    //                             $productGroup->save();
    //                         }else{
    //                             $findItemGroup->date_post=strtotime( 'now' );
    //                             $findItemGroup->date_update=strtotime( 'now' );
    //                             $findItemGroup->date_start=$value['discountApplied']==true?Carbon::createFromFormat('Y-m-d', $value['discountDetails']['startDate'] )->timestamp:null;
    //                             $findItemGroup->date_end= $value['discountApplied']==true?Carbon::createFromFormat('Y-m-d', $value['discountDetails']['endDate'])->timestamp:null;
    //                             $findItemGroup->titleGroup=$value['discountDetails']['content'];
    //                             $findItemGroup->discount=$value['discountDetails']['discountPrice'];
    //                             $findItemGroup->save();
    //                         }

    //                 }
    //             }

    //             if(isset($request->delete_item_product_groups) && count($request->delete_item_product_groups) != 0) {

    //                 foreach ($request->delete_item_product_groups as  $value) {
    //                     $idProductGroup =ProductGroup::where('product_main',$id)->where('product_child',$value)->first()->delete();
    //                 }
    //             }

    //             $listProduct->cat_id = $request->input( 'cat_id' );
    //             $listProduct->cat_list = implode(',', $request->input( 'cat_list' ));
    //             //$listProduct->maso = $request->input( 'maso' );
    //             $listProduct->macn = $request->input( 'macn' );
    //             // $listProduct->code_script = $request->input( 'code_script' );
    //             if (is_string($request->picture) && (substr($request->picture, -4) === ".png" || substr($request->picture, -4) === ".jpg")) {
    //                 $filePath = $request->picture;
    //             } else {
    //                 $filePath = '';
    //             }

    //             if ( $request->picture!= null && $filePath =='' ) {
    //                 $DIR = 'uploads/product';
    //                 $httpPost = file_get_contents( 'php://input' );

    //                 $file_chunks = explode( ';base64,', $request->picture[0] );

    //                 $fileType = explode( 'image/', $file_chunks[ 0 ] );

    //                 $image_type = $fileType[ 0 ];
    //                 $base64Img = base64_decode( $file_chunks[ 1 ] );
    //                 $data = iconv( 'latin5', 'utf-8', $base64Img );
    //                 $name = uniqid();
    //                 $file = public_path($DIR) . '/' . $name . '.png';
    //                 $filePath = 'product/'.$name . '.png';
    //                 file_put_contents( $file,  $base64Img );
    //             }
    //             $listProduct->picture = $filePath;

    //             $listProduct->price =$request->input( 'price' );
    //             $listProduct->price_old = $request->input( 'price_old' );
    //             $listProduct->brand_id = $request->input( 'brand_id' );
    //             $listProduct->status = $request->input( 'status' );
    //             // $listProduct->options = $request->input( 'options' );
    //             $listProduct->op_search = implode(',', $request->input( 'op_search' ));
    //             // $listProduct->cat_search = $request->input( 'cat_search' );
    //             //$listProduct->technology = $technology;
    //             $listProduct->focus = 0;
    //             // $listProduct->focus_order = $request->input( 'focus_order' );
    //             // $listProduct->deal = $request->input( 'deal' ) ? $request->input( 'deal' ) : 0;
    //             // $listProduct->deal_order = $request->input( 'deal_order' ) ? $request->input( 'deal_order' ) : 0;
    //             $listProduct->deal_date_start = '0';
    //             $listProduct->deal_date_end = '0';
    //             $listProduct->stock = $request->input( 'stock' );
    //             $listProduct->votes = $request->input( 'votes' );
    //             // $listProduct->numvote = $request->input( 'numvote' );
    //             // $listProduct->menu_order = $request->input( 'menu_order' );
    //             $listProduct->menu_order_cate_lv0 = 0;
    //             $listProduct->menu_order_cate_lv1 = 0;
    //             $listProduct->menu_order_cate_lv2 = 0;
    //             $listProduct->menu_order_cate_lv3 = 0;
    //             $listProduct->menu_order_cate_lv4 = 0;
    //             $listProduct->menu_order_cate_lv5 = 0;
    //             $listProduct->menu_order_cate_lv6 = 0;
    //             $listProduct->menu_order_cate_lv7 = 0;
    //             $listProduct->menu_order_cate_lv8 = 0;
    //             $listProduct->menu_order_cate_lv9 = 0;
    //             $listProduct->menu_order_cate_lv10 = 0;
    //             // $listProduct->views = 0;
    //             $listProduct->display = $request->input( 'display' );
    //             $listProduct->date_post = '0';
    //             $listProduct->date_update = '0';
    //             // $listProduct->adminid = $request->input( 'adminid' );
    //             // $listProduct->url = $request->input( 'url' );
    //             $listProduct->save();

    //             //product_desc
    //             $productDesc = ProductDesc::where( 'product_id', $id )->first();
    //             if ( $productDesc ) {
    //                 $productDesc->product_id = $listProduct->product_id;
    //                 $productDesc->title = $request->input( 'title' );
    //                 $productDesc->description = $request->input( 'description' );
    //                 $productDesc->gift_desc = $request->input( 'gift_desc' );
    //                 $productDesc->video_desc = $request->input( 'video_desc' );
    //                 $productDesc->tech_desc = $request->input( 'tech_desc' );
    //                 $productDesc->option = 2;
    //                 $productDesc->short = $request->input( 'short' );
    //                 $productDesc->start_date_promotion = 0;
    //                 $productDesc->end_date_promotion = 0;
    //                 $productDesc->status_promotion = 0;
    //                 $productDesc->shortcode = $request->input( 'shortcode' );
    //                 $productDesc->key_search = $request->input( 'key_search' );
    //                 $productDesc->friendly_url = $request->input( 'friendly_url' );
    //                 $productDesc->friendly_title = $request->input( 'friendly_title' );
    //                 $productDesc->metakey = $request->input( 'metakey' );
    //                 $productDesc->metadesc = $request->input( 'metadesc' );
    //                 $productDesc->lang = 'vi';
    //                 $productDesc->save();
    //             }else{
    //                 $productDesc = new ProductDesc();
    //                 $productDesc->product_id = $listProduct->product_id;
    //                 $productDesc->title = $request->input( 'title' );
    //                 $productDesc->description = $request->input( 'description' );
    //                 // $productDesc->gift_desc = $request->input( 'gift_desc' );
    //                 // $productDesc->video_desc = $request->input( 'video_desc' );
    //                 // $productDesc->tech_desc = $request->input( 'tech_desc' );
    //                 // $productDesc->option = 2;
    //                 $productDesc->short = $request->input( 'short' );
    //                 $productDesc->start_date_promotion = 0;
    //                 $productDesc->end_date_promotion = 0;
    //                 $productDesc->status_promotion = 0;
    //                 // $productDesc->shortcode = $request->input( 'shortcode' );
    //                 // $productDesc->key_search = $request->input( 'key_search' );
    //                 $productDesc->friendly_url = $request->input( 'friendly_url' ) ??Str::slug($request->input( 'title' ));
    //                 $productDesc->friendly_title = $request->input( 'friendly_title' );
    //                 $productDesc->metakey = $request->input( 'metakey' );
    //                 $productDesc->metadesc = $request->input( 'metadesc' );
    //                 $productDesc->lang = 'vi';
    //                 $productDesc->save();

    //             }

    //             if ( $request->picture_detail != null ) {
    //                 $deletePicture = ProductPicture::where( 'product_id', $id )->get();
    //                 foreach ( $deletePicture as $value ) {

    //                     $namepicture = $value->picture;

    //                     //unlink( $filePath );
    //                     $value->delete();
    //                 }
    //                 $listProductPicture = ProductPicture::where( 'product_id', $id )->delete();

    //                 foreach ( $request->picture_detail as $value ) {
    //                     $productPicture = new ProductPicture();

    //                     $DIR = 'uploads/product';
    //                     $httpPost = file_get_contents( 'php://input' );
    //                     $file_chunks = explode( ';base64,', $value );

    //                     $fileType = explode( 'image/', $file_chunks[ 0 ] );

    //                     $image_type = $fileType[ 0 ];

    //                     $base64Img = base64_decode( $file_chunks[ 1 ] );
    //                     $name = uniqid();
    //                     $file = public_path($DIR) . '/' . $name . '.png';
    //                     $filePath = 'product/' . $name . '.png';

    //                     file_put_contents( $file, $base64Img );

    //                     $productPicture->product_id = $listProduct->product_id;
    //                     $productPicture->pic_name = $name . '.png';
    //                     $productPicture->picture = $filePath;
    //                     $productPicture->menu_order = 0;
    //                     $productPicture->display = 1;
    //                     $productPicture->date_post = 0;
    //                     $productPicture->date_update = 0;
    //                     $productPicture->save();
    //                 }
    //             }
    //             if($request->input( 'status' ) == 5 &&  $request->input( 'stock' )!=2)
    //             {
    //                 $list = ProductFlashSale::where('product_id',$id)->first();
    //                 //return response()->json($list == "");
    //                 if($list != "")
    //                 {
    //                     ProductFlashSale::where('product_id',$id)->first()->delete();
    //                 }
    //                 $ProductFlashSale = new ProductFlashSale();
    //                 $ProductFlashSale->product_id  = $listProduct->product_id;
    //                 $ProductFlashSale->price  = $request->input( 'price' );
    //                 $ProductFlashSale->price_old  =$request->input( 'price_old' );
    //                 $ProductFlashSale->discount_percent  = 0;
    //                 $ProductFlashSale->discount_price  = $request->input( 'discount_price' )??0;
    //                 $ProductFlashSale->start_time  = $request->input( 'flashStart' )??null;
    //                 $ProductFlashSale->end_time  = $request->input( 'flashEnd' )??null;
    //                 $ProductFlashSale->status  = 5;
    //                 $ProductFlashSale->adminid  = 1;
    //                 $ProductFlashSale->save();
    //             }
    //             if($request->input( 'status' ) != 5)
    //             {
    //                 $list = ProductFlashSale::where('product_id',$id)->first();
    //                 //return response()->json($list == "");
    //                 if($list != "")
    //                 {
    //                     ProductFlashSale::where('product_id',$id)->first()->delete();
    //                 }
    //             }

    //             return response()->json([
    //                 'status' => true,
    //             ]);
    //     } else {
    //         return response()->json([
    //             'status'=>false,
    //             'mess' => 'no permission',
    //         ]);
    //     }

    //     } catch ( \Exception $e ) {
    //         $errorMessage = $e->getMessage();
    //         $response = [
    //             'status' => 'false',
    //             'error' => $errorMessage
    //         ];
    //         return response()->json( $response, 500 );
    //     }
    // }
}
