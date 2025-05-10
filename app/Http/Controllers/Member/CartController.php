<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Member\AbstractController;
use App\Models\BrandDesc;
use App\Models\ListCart;
use App\Models\Present;
use App\Models\Product;
use App\Models\ProductDesc;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends AbstractController
{
    protected function getModel()
    {
        return new ListCart();
    }

    public function showCart(Request $request)
    {
        $memberId = $request->id;

        if (!$memberId) {
            return response()->json([
                'status' => false,
                'error' => 'Member id not found',
            ], 400);
        }

        try {
            $lists = ListCart::where("mem_id", $memberId)->get()->groupBy('id_group');
            $dataTotal = [];

            foreach ($lists as $groupId => $carts) {
                foreach ($carts as $cart) {
                    if ($groupId != 0 && $cart->group_product_id) {
                        $groupProductIds = explode(',', $cart->group_product_id);

                        $validProductIds = array_filter($groupProductIds, function ($id) {
                            return Product::where('product_id', $id)->exists();
                        });

                        if (empty($validProductIds)) {
                            continue;
                        }

                        $updatedGroup = implode(',', $validProductIds);
                        if ($updatedGroup !== $cart->group_product_id) {
                            DB::table('list_cart')->where('id', $cart->id)
                                ->update(['group_product_id' => $updatedGroup]);
                            $cart->group_product_id = $updatedGroup;
                        }

                        $products = Product::whereIn('product_id', $validProductIds)->get();
                        $priceCombo = $products->sum('price');
                        $discount = $cart->groupProduct->discount ?? 0;

                        $comboItem = [
                            'typeCombo' => true,
                            'Id' => $cart->id,
                            'quantity' => $cart->quality,
                            'title' => $cart->groupProduct->titleGroup ?? null,
                            'Price' => ($priceCombo - $discount),
                            'discountCombo' => $discount,
                            'GroupId' => $cart->groupProduct->id_group ?? null,
                            'ProductId' => $cart->group_product_id,
                            'products' => [],
                        ];

                        foreach ($products as $product) {
                            $comboItem['products'][] = [
                                'ProductName' => $product->productDesc->title ?? null,
                                'UrlProduct' => $product->productDesc->friendly_url ?? null,
                                'Price' => $product->price ?? null,
                                'PriceFlashSale' => $cart->priceFlashSale ?? null,
                                'quantity' => $cart->quality ?? null,
                                'ProductId' => $product->product_id ?? null,
                                'Macn' => $product->macn ?? null,
                                'Image' => $product->picture ?? null,
                                'Category' => $product->categoryDes->cat_name ?? null,
                                'Brand' => $product->brandDesc->title ?? null,
                                'Status' => $product->status ?? null,
                                'Stock' => $product->stock ?? null,
                                'checkPresent' => $this->checkPresent($product->product_id),
                            ];
                        }

                        $dataTotal[] = $comboItem;
                    } elseif ($groupId == 0 && Product::where('product_id', $cart->product_id)->exists()) {
                        $product = $cart->product;

                        $dataTotal[] = [
                            'typeCombo' => false,
                            'Id' => $cart->id,
                            'ProductName' => $cart->title ?? null,
                            'UrlProduct' => $cart->productDesc->friendly_url ?? null,
                            'Price' => $product->price ?? null,
                            'PriceFlashSale' => $cart->priceFlashSale ?? null,
                            'quantity' => $cart->quality ?? null,
                            'ProductId' => $product->product_id ?? null,
                            'Macn' => $product->macn ?? null,
                            'Image' => $cart->picture ?? null,
                            'Category' => $cart->cat_name ?? null,
                            'Brand' => $product->brandDesc->title ?? null,
                            'Status' => $cart->status ?? null,
                            'Stock' => $product->stock ?? null,
                            'checkPresent' => $this->checkPresent($product->product_id),
                        ];
                    }
                }
            }

            return response()->json([
                'status' => true,
                'data' => $dataTotal,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // public function showCart(Request $request)
    // {
    //     $data = [];
    //     $memberId = $request->id;

    //     if (!$memberId) {
    //         return response()->json([
    //             'status' => false,
    //             'error' => 'Missing member ID',
    //         ], 400);
    //     }

    //     if ($memberId) {
    //         try {
    //             $date = Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('DD-MM-YYYY-HH:mm:ss');
    //             $lists = ListCart::where("mem_id", $memberId)->get()->groupBy('id_group');

    //             $list = $lists->map(function ($carts) {
    //                 return $carts->map(function ($cart) {
    //                     $groupProductIds = explode(',', $cart->group_product_id);

    //                     $validProductIds = array_filter($groupProductIds, function ($id) {
    //                         return DB::table('products')->where('product_id', $id)->exists();
    //                     });

    //                     $updatedGroup = implode(',', $validProductIds);

    //                     if ($updatedGroup !== $cart->group_product_id) {
    //                         DB::table('list_cart')
    //                             ->where('id', $cart->id)
    //                             ->update(['group_product_id' => $updatedGroup]);

    //                         $cart->group_product_id = $updatedGroup;
    //                     }

    //                     return $cart;
    //                 });
    //             });

    //             $dataTotal = [];
    //             foreach ($list as $key => $carts) {
    //                 if ($key == 0) {
    //                     foreach ($carts as $cart) {
    //                         if ($cart) {
    //                             $id = $cart['product_id'];
    //                             $dataTotal[] = [
    //                                 'typeCombo' => false,
    //                                 'Id' => $cart->id,
    //                                 'ProductName' => $cart->title ?? null,
    //                                 'UrlProduct' => $cart->productDesc->friendly_url ?? null,
    //                                 'Price' => $cart->product->price ?? null,

    //                                 'PriceFlashSale' => $cart['priceFlashSale'] ?? null,
    //                                 'quantity' => $cart['quality'] ?? null,
    //                                 'ProductId' => $id ?? null,
    //                                 'Macn' => $cart->product->macn ?? null,
    //                                 'Image' => $cart['picture'] ?? null,
    //                                 'Category' => $cart['cat_name'] ?? null,
    //                                 'ProductName' => $cart['title'] ?? null,
    //                                 'Brand' => $cart->product->brandDesc->title ?? null,
    //                                 'Status' => $cart['status'] ?? null,
    //                                 'Stock' => $cart['stock'] ?? null,
    //                                 'checkPresent' => $this->checkPresent($id),
    //                             ];
    //                         }
    //                     }
    //                 } else {
    //                     $discountCombo = 0;
    //                     $priceCombo = 0;
    //                     foreach ($carts as $key1 => $cart) {
    //                         if ($cart) {
    //                             $group_product_id = explode(",", $cart->group_product_id);
    //                             $products = Product::whereIn('product_id', $group_product_id)->get();

    //                             foreach ($products as $product) {

    //                                 $priceCombo += $product->price;
    //                                 $dataTotal[$key]["products"][] = [

    //                                     'ProductName' => $product->productDesc->title ?? null,
    //                                     'UrlProduct' => $product->productDesc->friendly_url ?? null,

    //                                     'Price' => $product->price ?? null,

    //                                     'PriceFlashSale' => $cart['priceFlashSale'] ?? null,
    //                                     'quantity' => $cart['quality'] ?? null,
    //                                     'ProductId' => $product->product_id ?? null,
    //                                     'Macn' => $product->macn ?? null,
    //                                     'Image' => $product->picture ?? null,
    //                                     'Category' => $product->categoryDes->cat_name ?? null,

    //                                     'Brand' => $product->brandDesc->title ?? null,
    //                                     'Status' => $product->status ?? null,
    //                                     'Stock' => $product->stock ?? null,
    //                                     'checkPresent' => $this->checkPresent($product->product_id),
    //                                 ];
    //                             }
    //                         }
    //                     }
    //                     $discount = $cart->groupProduct->discount ?? 0;
    //                     $dataTotal[$key]['typeCombo'] = true;
    //                     $dataTotal[$key]['Id'] = $cart->id;
    //                     $dataTotal[$key]['quantity'] = $cart['quality'];
    //                     $dataTotal[$key]['title'] = $cart->groupProduct->titleGroup ?? null;
    //                     $dataTotal[$key]['Price'] = ($priceCombo - $discount);
    //                     $dataTotal[$key]['discountCombo'] = $discount;
    //                     $dataTotal[$key]['GroupId'] = $cart->groupProduct->id_group;
    //                     $dataTotal[$key]['ProductId'] = $cart->group_product_id;
    //                 }
    //             }
    //             $listCart = [];
    //             foreach ($dataTotal as $item) {
    //                 $listCart[] = $item;
    //             }

    //             $response = [
    //                 'status' => true,
    //                 'data' => $listCart,
    //             ];
    //             return response()->json($response, 200);
    //         } catch (\Exception $e) {
    //             $errorMessage = $e->getMessage();
    //             $response = [
    //                 'status' => 'false',
    //                 'error' => $errorMessage,
    //             ];

    //             return response()->json($response, 500);
    //         }
    //     }

    // }

    public function repurchase(Request $request, $id)
    {

        try {
            $listProduct = $request->value;
            $memberId = $id;
            $dataCart = [];
            foreach ($listProduct as $key => $item) {
                if ($item["typeCombo"] == false) {

                    $cartMember = ListCart::where('mem_id', $memberId)->where('product_id', $item['ProductId'])->first();
                    if ($cartMember) {

                        $cartMember->quality += $item['quantity'];
                        $cartMember->save();
                    } else {

                        $cartMember = new ListCart();
                        $cartMember->mem_id = $memberId;
                        $cartMember->product_id = $item['ProductId'] ?? null;
                        $cartMember->stock = $item['stock'] ?? null;
                        $cartMember->brand_name = $item['brandName'] ?? null;
                        $cartMember->picture = $item['Image'] ?? null;
                        $cartMember->cat_name = $item['Category'] ?? null;
                        $cartMember->title = $item['ProductName'] ?? null;
                        $cartMember->quality = $item['quantity'];
                        $cartMember->price = $item['Price'] ?? null;

                        $cartMember->status = 1;
                        $cartMember->id_group = 0;
                        $cartMember->save();
                    }

                    $dataCart[] = [
                        'typeCombo' => false,
                        'CartId' => $cartMember->id,
                        'ProductId' => $cartMember->product_id,
                        'ProductName' => $cartMember->title,
                        'UrlProduct' => $cartMember->productDesc->friendly_url ?? null,
                        'Stock' => $cartMember->stock,
                        'Image' => $cartMember->picture,
                        'Price' => $cartMember->price,
                        'Brand' => $cartMember->brand_name,
                        'Category' => $cartMember->cat_name,
                        'quantity' => $cartMember->quality,
                        'Macn' => $cartMember->product->macn ?? null,
                        'Status' => $cartMember->product->status ?? null,
                        'checkPresent' => $this->checkPresent($cartMember->product_id),

                    ];

                } else if ($item["typeCombo"] == true) {

                    $idGroup = $item["GroupId"];
                    $listProduct = $item["products"];
                    $priceCombo = 0;
                    $group_product_id = [];
                    $price = 0;
                    $cartMember = ListCart::where('mem_id', $memberId)
                        ->where('id_group', $idGroup)->first();
                    if ($cartMember) {
                        $cartMember->quality += $item['quantity'];
                        $cartMember->save();
                        $group_product_id = explode(",", $cartMember->group_product_id);

                    } else {

                        foreach ($listProduct as $item) {
                            $group_product_id[] = $item['ProductId'];
                            $price += $item['Price'];

                        }
                        if (count($group_product_id) > 0 && $price > 0) {

                            $cartMember = new ListCart();
                            $cartMember->mem_id = $memberId;
                            $cartMember->group_product_id = implode(',', $group_product_id);
                            $cartMember->price = $price;
                            $cartMember->quality = $item['quantity'];

                            $cartMember->status = 1;
                            $cartMember->id_group = $idGroup ?? null;
                            $cartMember->save();
                        }
                    }

                    //$discount=$cartMember->groupProduct->discount??0;
                    $dataCart[$key]['typeCombo'] = true;
                    $dataCart[$key]['quantity'] = $cartMember->quality;
                    $dataCart[$key]['title'] = $cartMember->groupProduct->titleGroup;
                    $dataCart[$key]['Price'] = $item['Price'];
                    $dataCart[$key]['discountCombo'] = $cartMember->groupProduct->discount;
                    $dataCart[$key]['ProductId'] = implode(',', $group_product_id);
                    $dataCart[$key]['GroupId'] = $idGroup;
                    $dataCart[$key]['CartId'] = $cartMember->id;

                    $products = Product::whereIn('product_id', $group_product_id)->get();
                    foreach ($products as $product) {
                        $dataCart[$key]['products'][] = [

                            'ProductName' => $product->productDesc->title ?? null,
                            'UrlProduct' => $product->productDesc->friendly_url ?? null,
                            // 'PriceOld' => $cart['price'] ?? null,
                            'Price' => $product->price ?? null,
                            // 'PriceOld'=>$cart->product->price_old??null,

                            'quantity' => $cartMember->quality ?? null,
                            'ProductId' => $product->product_id ?? null,
                            'Macn' => $product->macn ?? null,
                            'Image' => $product->picture ?? null,
                            'Category' => $product->categoryDes->cat_name ?? null,

                            'Brand' => $product->brandDesc->brand_name ?? null,
                            'Status' => $product->status ?? null,
                            'Stock' => $product->stock ?? null,
                            'checkPresent' => $this->checkPresent($product->product_id),

                        ];
                    }

                }
            }
            return response()->json([
                'product' => $dataCart,
                'status' => true,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function addGroupProductCart(Request $request, $id)
    {
        try {
            $dataCart = [];
            $idGroup = $request->idGroup;

            $listProduct = $request->value;
            $memberId = $id;
            $priceCombo = 0;
            $group_product_id = [];
            $price = 0;
            $cartMember = ListCart::where('mem_id', $memberId)
                ->where('id_group', $idGroup)->first();
            if ($cartMember) {
                $cartMember->quality = $cartMember->quality + 1;
                $cartMember->save();
                $group_product_id = explode(",", $cartMember->group_product_id);

            } else {
                foreach ($listProduct as $item) {
                    $group_product_id[] = $item['ProductId'];
                    $price += $item['Price'];

                }

                if (count($group_product_id) > 0 && $price > 0) {
                    $cartMember = new ListCart();
                    $cartMember->mem_id = $memberId;
                    $cartMember->group_product_id = implode(',', $group_product_id);
                    $cartMember->price = $price;
                    $cartMember->quality = 1;

                    $cartMember->status = 1;
                    $cartMember->id_group = $idGroup ?? null;
                    $cartMember->save();
                }

            }
            $discount = $cartMember->groupProduct->discount ?? 0;
            $dataCart['typeCombo'] = true;
            $dataCart['quantity'] = $cartMember->quality;
            $dataCart['title'] = $cartMember->groupProduct->titleGroup ?? null;
            $dataCart['Price'] = $cartMember->price - $discount;
            $dataCart['discountCombo'] = $discount;
            $dataCart['GroupId'] = $cartMember->groupProduct->id_group;
            $dataCar['Id'] = $cartMember->id;

            $products = Product::whereIn('product_id', $group_product_id)->get();
            foreach ($products as $product) {
                $dataCart['products'][] = [

                    'Id' => $cartMember->id,
                    'ProductName' => $product->productDesc->title ?? null,
                    'UrlProduct' => $product->productDesc->friendly_url ?? null,
                    // 'PriceOld' => $cart['price'] ?? null,
                    'Price' => $product->price ?? null,
                    // 'PriceOld'=>$cart->product->price_old??null,

                    'quantity' => $cartMember->quality ?? null,
                    'ProductId' => $product->product_id ?? null,
                    'Macn' => $product->macn ?? null,
                    'Image' => $product->picture ?? null,
                    'Category' => $product->categoryDes->cat_name ?? null,

                    'Brand' => $product->brandDesc->brand_name ?? null,
                    'Status' => $product->status ?? null,
                    'Stock' => $product->stock ?? null,

                ];

            }
            return response()->json([
                'product' => $dataCart,
                'status' => true,
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
        if (!$product) {
            return [];
        }

        foreach ($listPresent as $present) {
            $listCate = explode(",", $present->list_cat);
            $listProduct = explode(",", $present->list_product);
            if (
                (in_array($product->cat_id, $listCate) &&
                    ($present->priceMin <= $product->price && $product->price <= $present->priceMax))
                || in_array($product->macn, $listProduct)
            ) {
                $arrayPresent[] = $present;
            }
        }

        return $arrayPresent;
    }

    public function index()
    {
        $data = [];

        if (auth('member')->user() != null) {
            try {
                $date = Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('DD-MM-YYYY-HH:mm:ss');
                $list = ListCart::where("mem_id", auth('member')->user()->mem_id)
                    ->get();
                foreach ($list as $cart) {
                    $id = $cart['product_id'];

                    $data[] = [
                        'Id' => $cart->id,
                        'ProductName' => $cart->mem_name ?? null,
                        'FriendlyUrlProduct' => $cart->productDesc->friendly_url ?? null,
                        'Price' => $cart['price'] ?? null,
                        'quantity' => $cart['quality'] ?? null,
                        'Product_id' => $id ?? null,
                        'Macn' => $cart['macn'] ?? null,
                        'Picture' => $cart['picture'] ?? null,
                        'Cat_name' => $cart['cat_name'] ?? null,
                        'Cat_name_parent' => $cart['cat_name_parent'] ?? null,
                        'Title' => $cart['title'] ?? null,
                        'Brand_name' => $cart['brand_name'] ?? null,
                        'Status' => $cart['status'] ?? null,
                        'stock' => $cart['stock'] ?? null,
                        'PriceFlashSale' => $cart['priceFlashSale'] ?? null,
                        'checkPresent' => $this->checkPresent($id),
                    ];
                }
                $response = [
                    'status' => true,
                    'data' => $data,
                ];
                return response()->json($response, 200);
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

    public function create()
    {
        try {
            $list = parent::create();
            return response()->json([
                'data' => $list,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function addArrayCart(Request $request, $id)
    {

        $listProduct = $request->value;
        // return $listProduct;
        $memberId = $id;

        try {
            $dataCart = [];
            foreach ($listProduct as $item) {
                $cartMember = ListCart::where('mem_id', $memberId)->where('product_id', $item['ProductId'])->first();
                if ($cartMember) {

                    $cartMember->quality = $item['quantity'];
                    $cartMember->save();
                } else {

                    $cartMember = new ListCart();
                    $cartMember->mem_id = $memberId;
                    $cartMember->product_id = $item['ProductId'] ?? null;
                    $cartMember->stock = $item['stock'] ?? null;
                    $cartMember->brand_name = $item['brandName'] ?? null;
                    $cartMember->picture = $item['Image'] ?? null;
                    $cartMember->cat_name = $item['Category'] ?? null;
                    $cartMember->title = $item['ProductName'] ?? null;
                    $cartMember->quality = $item['quantity'];
                    $cartMember->price = $item['value']['Price'] ?? null;
                    $cartMember->priceFlashSale = $item['value']['PriceFlashSale'] ?? null;
                    $cartMember->status = 1;
                    $cartMember->id_group = 0;
                    $cartMember->save();

                }
                $dataCart[] = [
                    'CartId' => $cartMember->id,
                    'ProductId' => $cartMember->product_id,
                    'ProductName' => $cartMember->title,
                    'Stock' => $cartMember->stock,
                    'Image' => $cartMember->picture,
                    'Price' => $cartMember->price,
                    'Brand' => $cartMember->brand_name,
                    'Category' => $cartMember->cat_name,
                    'quantity' => $cartMember->quality,
                    'PriceFlashSale' => $cartMember->priceFlashSale,
                ];
            }
            return response()->json([
                'product' => $dataCart,
                'status' => true,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function addOrUpdateCart(Request $request, $id)
    {
        try {
            //return $request->all();
            $memberId = $id;
            $data = $request->all();

            if ($memberId) {

                $cartMember = ListCart::where('product_id', $data['value']['ProductId'])
                    ->where('mem_id', $memberId)->first();
                if ($cartMember) {

                    $cartMember->quality = $cartMember['quality'] + 1;
                    $cartMember->price = $data['value']['Price'] ?? null;
                    $cartMember->priceFlashSale = $data['value']['PriceFlashSale'] ?? null;
                    $cartMember->save();
                } else {

                    // return  $data['value']['CatId'];
                    //return $data['value']['brandName'];
                    $cartMember = new ListCart();
                    $cartMember->mem_id = $memberId;
                    $cartMember->product_id = $data['value']['ProductId'] ?? null;
                    $cartMember->stock = $data['value']['stock'] ?? null;
                    $cartMember->brand_name = $data['value']['brandName'] ?? null;
                    $cartMember->picture = $data['value']['Image'] ?? null;
                    $cartMember->cat_name = $data['value']['Category'] ?? null;
                    $cartMember->title = $data['value']['ProductName'] ?? null;
                    $cartMember->quality = 1;
                    $cartMember->price = $data['value']['Price'] ?? null;
                    $cartMember->priceFlashSale = $data['value']['PriceFlashSale'] ?? null;
                    $cartMember->status = 1;
                    $cartMember->id_group = 0;
                    $cartMember->save();

                }
                $dataCart = [
                    'CartId' => $cartMember->id,
                    'ProductId' => $cartMember->product_id,
                    'ProductName' => $cartMember->title,
                    'Stock' => $cartMember->stock,
                    'Image' => $cartMember->picture,
                    'Price' => $cartMember->price,
                    'Brand' => $cartMember->brand_name,
                    'Category' => $cartMember->cat_name,
                    'PriceFlashSale' => $cartMember->priceFlashSale,

                ];
                return response()->json([
                    'product' => $dataCart,
                    'status' => true,
                ]);
            } else {

                $list = parent::store($request);
                return response()->json([
                    'product' => $list,
                    'status' => true,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function store(Request $request)
    {

        try {
            $data = $request->all();
            if (!empty(Auth::guard('member')->user()->mem_id)) {
                $cartMember = ListCart::where('product_id', $request->product_id)
                    ->where('mem_id', Auth::guard('member')->user()->mem_id)->first();
                if ($cartMember) {
                    $cartMember->quality = $cartMember['quality'] + 1;
                    $cartMember->price = $data['price'];
                    $cartMember->save();
                } else {
                    $cartMember = new ListCart();
                    $cartMember->mem_id = Auth::guard('member')->user()->mem_id;
                    $cartMember->mem_name = Auth::guard('member')->user()->username;
                    $cartMember->product_id = $request->product_id;
                    $cartMember->stock = $request->stock;
                    $cartMember->macn = $request->MaKhoSPApdung;
                    $cartMember->brand_name = $request->brandName;
                    $cartMember->picture = $request->picture;
                    $cartMember->cat_name = $request->catName;
                    $cartMember->title = $request->title;
                    $cartMember->cat_name_parent = $request->catNameParent;
                    $cartMember->quality = $request->quality;
                    $cartMember->price = $request->price;
                    $cartMember->status = 1;
                    $cartMember->save();
                }
                return response()->json([
                    'data' => $cartMember,
                    'status' => true,
                ]);
            } else {

                $list = parent::store($request);
                return response()->json([
                    'datas' => $list,
                    'status' => true,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $list = parent::show($id);
            return response()->json([
                'data' => $data[] = [
                    'name' => $list['name'],
                    'friendlyUrlProduct' => $list->productDesc->friendly_url ?? null,
                    'price' => $list['price'],
                    'quality' => $list['quality'],
                    'status' => $list['status'],
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $list = parent::edit($id);
            return response()->json([
                'data' => $data[] = [
                    'id' => $list['id'],
                    'name' => $list['name'],
                    'friendlyUrlProduct' => $list->productDesc->friendly_url ?? null,
                    'price' => $list['price'],
                    'quality' => $list['quality'],
                    'status' => $list['status'],
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateCart(Request $request, $CartId)
    {
        try {

            $MemerId = $request->userId;

            if ($MemerId) {
                $data = $request->all();
                $cartMember = ListCart::findOrFail($CartId);

                $cartMember->quality = $data['value']['quantity'];
                $cartMember->price = $data['value']['Price'] ?? null;
                $cartMember->priceFlashSale = $data['value']['PriceFlashSale'] ?? null;
                $cartMember->save();
                return response()->json([
                    // 'data' => $cartMember,
                    'status' => true,
                ]);
            } else {

                $data = $request->all();
                $cartMember = ListCart::findOrFail($CartId);
                $cartMember->quality = $data['value']['quantity'];
                $cartMember->price = $data['value']['Price'] ?? null;
                $cartMember->priceFlashSale = $data['value']['PriceFlashSale'] ?? null;
                $cartMember->save();
                return response()->json([
                    // 'data' => $cartMember,
                    'status' => true,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }

    }
    public function update(Request $request, $id)
    {
        try {
            if (!empty(Auth::guard('member')->user()->mem_id)) {
                $data = $request->all();
                $cartMember = ListCart::findOrFail($id);
                $cartMember->mem_id = Auth::guard('member')->user()->mem_id;
                $cartMember->mem_name = Auth::guard('member')->user()->username;
                $cartMember->product_id = $data['product_id'];
                $cartMember->quality = $data['quality'];
                $cartMember->price = $data['price'];
                $cartMember->title = $data['title'];
                $cartMember->status = 1;
                $cartMember->save();
                return response()->json([
                    'data' => $cartMember,
                    'status' => true,
                ]);
            } else {

                $data = $request->all();
                $cartMember = ListCart::findOrFail($id);
                $cartMember->product_id = $data['product_id'];
                $cartMember->quality = $data['quality'];
                $cartMember->price = $data['price'];
                $cartMember->title = $data['title'];
                $cartMember->status = 1;
                $cartMember->save();
                return response()->json([
                    'data' => $cartMember,
                    'status' => true,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function deleteGroupProduct(Request $request, $id)
    {
        try {

            $memberId = $id;
            $arrGroupId = $request->arrId;
            foreach ($arrGroupId as $Id) {
                $listId = ListCart::where('mem_id', $memberId)
                    ->where('id_group', $Id)
                    ->delete();
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteCart(Request $request)
    {
        try {
            $arrId = $request->arrId;
            foreach ($arrId as $id) {
                $listId = ListCart::where('id', $id)->delete();
            }

            return response()->json([
                'status' => true,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }

    }
    public function destroy($id)
    {
        try {
            $listId = parent::destroy($id);
            return response()->json([
                'status' => true,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
