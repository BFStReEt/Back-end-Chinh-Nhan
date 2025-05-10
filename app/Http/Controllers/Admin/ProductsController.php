<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Products;
use App\Models\ProductDesc;
use App\Models\CategoryDesc;
use App\Models\ProductFlashSale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\ProductPicture;
use App\Models\ProductGroup;
use App\Models\Price;
use App\Models\PropertiesCategory;
use Illuminate\Support\Str;
use Gate;
use GuzzleHttp\Client;
use App\Models\ProductProperties;
class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $perPage = 500;
        // $page =  $request->page;
        // $offset = ($page - 1) * $perPage;
        // $endpoint = 'http://192.168.117.222:8094/NKC/Web/GetListItemSync?type=tatca';
        // $headers = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
        // $client = new Client(['timeout' => 60]);

        // $response = $client->request('GET', $endpoint, ['headers' => $headers]);
        // $responseData = json_decode($response->getBody(), true);
       
      
        // $existProducts = Products::with('priceList')->whereIn('MaHH', array_column($responseData, 'MaHH'))
        // ->offset($offset)->limit($perPage)->orderBy('product_id','desc')->get();
     
        // $modifiedMaHHList=array_column($responseData, 'MaHH');

        // $data=[];
        // foreach($existProducts as $product){
           
        //         $MaHH=$product['macn'];
        //         $MaHH=$product['MaHH'];
        //         $index = array_search($MaHH, $modifiedMaHHList);
        //         if($index !== false){
               
        //             $item = $responseData[$index];
        //             $product->PriceSAP=$item['Price'];
        //             $product->PriceSAP1=$item['Price1'];
        //             $product->price=$item['Price1'];
        //             $product->picture= isset($product->priceList[0])?$product->priceList[0]->picture:null;
        //             $product->price_old=$item['Price'];
        //             $product->maso=$item['MaHH'];
            
        //             $product->DVT=$item['DVT'];
        //             $product->TonKho=$item['TonKho'];
        //             if($item['TonKho']==0){
        //                 $product->stock=0;
        //             }else{
        //                 $product->stock=1;
        //             }
                
        //             if($item['Hienthi']=='Y'){
        //                 $product->display=1;
        //             }else if($item['Hienthi']=='N'){
        //                 $product->display=0;
        //             }
        //             $product->save();
        //     }
        // }



        // return 111;

        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'show all product',
                'cat'=>'product',
            ]);
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Tính năng sản phẩm.manage')){
                $productPrice = $request->price;
                $productPriceOld = $request->price_old;


                $product = Products::with('productDescs');
                if (!empty($request->input('data'))&& $request->input('data') !== 'null'&& $request->input('data') !== 'undefined') {

                        $product->whereHas('productDescs', function ($query) use ($request) {

                            $query->where("title", 'like', '%' . $request->input('data') . '%');
                        })->orWhere("macn", 'like', '%' . $request->input('data') . '%');

                }
                if ($request->input('brand') !== null && $request->input('brand') !== '0') {
                    $product->where('brand_id', $request->input('brand'));
                }
                if ($request->input('category') !== null && $request->input('category') !== '0') {
                    $product->whereRaw('FIND_IN_SET(?, cat_list)', [$request->input('category')]);
                }
                if($request->status != '')
                {
                    $product->where('status',$request->status);
                }
                if($productPrice!='')
                {
                    $product->where('price', $productPrice);
                    // $product->whereBetween('price',[$productPrice,$productPriceOld]);

                }
                if($request->stock==true)
                {
                    $product->where('stock','!=',2);
                }
                if($request->startDate!='' && $request->endDate!=''){
                    $start=$request->startDate;
                    $end=$request->endDate;
                    $product->whereBetween('date_post',[$start,$end]);
                }
                $products = $product->orderBy('product_id','desc')->paginate(15);

                foreach($products as $product){

                    $catIdParent=explode(",",$product->cat_list)[0];
                    $product['categoryParent']=CategoryDesc::where('cat_id', $catIdParent)->first();
                }

                return response()->json([
                    'status'=>true,
                    'product' => $products,
                ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        }catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json( $response, 500 );
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
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' =>  $stringTime,
            'ip'=> $request->ip(),
            'action'=>'add a product',
            'cat'=>'product',
        ]);

        $disPath = public_path();
        $product = new Products();
        $productDesc = new ProductDescs();
        $productPicture = new ProductPicture();

        try {

            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Tính năng sản phẩm.add')){
                $checkMaso = Products::where('maso',$request->input( 'maso' ))->first();
                if($checkMaso != null && $request->input( 'maso' )!='')
                {
                    return response()->json([
                        'message'=>'maso',
                        'status'=>false,
                    ],202);
                }

                $checkMacn = Products::where('macn',$request->input( 'macn' ))->first();
                if($checkMacn != null && $request->input( 'macn' )!='')
                {
                    return response()->json([
                        'message'=>'macn',
                        'status'=>false,
                    ],202);
                }

                $checkDesc = ProductDescs::where('title',$request->input( 'title' ))
                ->first();
                if($checkDesc != null)
                {
                    return response()->json([
                        'message'=>'title',
                        'status'=>false,
                    ],202);
                }

                $filePath = '';
                if ( $request->picture != null )
                {
                    $DIR = $disPath.'\uploads\product';
                    $httpPost = file_get_contents( 'php://input' );
                    $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                    $fileType = explode( 'image/', $file_chunks[ 0 ] );
                    $image_type = $fileType[ 0 ];
                    $base64Img = base64_decode( $file_chunks[ 1 ] );
                    $data = iconv( 'latin5', 'utf-8', $base64Img );
                    $name = uniqid();
                    $file = $DIR .'\\'. $name . '.png';
                    $filePath = 'product/'.$name . '.png';
                    file_put_contents( $file,  $base64Img );
                }

                $dataArray = $request->input( 'technology' );

                $serializedString = 'a:' . count($dataArray) . ':{';
                foreach ($dataArray as $key => $value) {
                    $serializedString .= 'i:' . $key . ';s:' . strlen($value) . ':"' . $value . '";';
                }

                $serializedString .= '}';
                $technology = $serializedString;
                $product->cat_id = $request->input( 'cat_id' );
                $product->cat_list = implode(',', $request->input( 'cat_list' ));
                $product->maso = $request->input( 'maso' );
                $product->macn = $request->input( 'macn' );
                $product->code_script = $request->input( 'code_script' );
                $product->picture =$filePath;
                $product->price = $request->input( 'price' );
                $product->price_old = $request->input( 'price_old' );
                $product->brand_id = $request->input( 'brand_id' );
                $product->status = $request->input( 'status' );
                $product->op_search =implode(',', $request->input( 'op_search' ));
                $product->technology = $technology;
                $product->focus = 0;

                $product->deal_date_start = '0';
                $product->deal_date_end = '0';
                $product->stock = $request->input( 'stock' );

                $product->menu_order_cate_lv0 = 0;
                $product->menu_order_cate_lv1 = 0;
                $product->menu_order_cate_lv2 = 0;
                $product->menu_order_cate_lv3 = 0;
                $product->menu_order_cate_lv4 = 0;
                $product->menu_order_cate_lv5 = 0;
                $product->menu_order_cate_lv6 = 0;
                $product->menu_order_cate_lv7 = 0;
                $product->menu_order_cate_lv8 = 0;
                $product->menu_order_cate_lv9 = 0;
                $product->menu_order_cate_lv10 = 0;
                $product->views = 0;
                $product->display = $request->input( 'display' );
                $product->date_post =  strtotime( 'now' );
                $product->date_update =  strtotime( 'now' );
                // $product->adminid =$request->input('adminid');
                // $product->url = $request->input( 'url' );
                $product->save();


                $productDesc->product_id = $product->product_id;
                $productDesc->title = $request->input( 'title' );
                $productDesc->description = $request->input( 'description' );
                // $productDesc->gift_desc = $request->input( 'gift_desc' );
                // $productDesc->video_desc = $request->input( 'video_desc' );
                // $productDesc->tech_desc = $request->input( 'tech_desc' );
                $productDesc->option = 2;
                $productDesc->short = $request->input( 'short' );
                $productDesc->start_date_promotion = 0;
                $productDesc->end_date_promotion = 0;
                $productDesc->status_promotion = 0;
                // $productDesc->shortcode = $request->input( 'shortcode' );
                // $productDesc->key_search = $request->input( 'key_search' );
                $productDesc->friendly_url = $request->input( 'friendly_url' ) ? $request->input( 'friendly_url' ) : Str::slug($request->title);
                $productDesc->friendly_title = $request->input( 'friendly_title' );
                $productDesc->metakey = $request->input( 'metakey' );
                $productDesc->metadesc = $request->input( 'metadesc' );
                $productDesc->lang = 'vi';
                $productDesc->save();

                $price = new Price();
                $price->cat_id =$request->input( 'cat_list' )[0];
                $price->product_id = $product->product_id;
                $price->price_old = $request->input( 'price_old' )??0;
                $price->price =$request->input( 'price' )??0;
                $price->picture = $filePath;
                $price->main=1;
                $price->save();

                $propertiesCategory = PropertiesCategory::with('properties')->where('cat_id',$request->input( 'cat_list' )[0])->get();
                if(isset($propertiesCategory)){
                    foreach ($propertiesCategory as $key => $value) {
                        $productProperties = new ProductProperties();
                        $productProperties->pv_id = $request->input('value')[$key]!= null ?$request->input('value')[$key]:0;
                        $productProperties->properties_id = $value->properties_id;
                        $productProperties->price_id  = $price->id;
                        $productProperties->description = $request->input('tskt')[$key] != null ? $request->input('tskt')[$key]:"";
                        $productProperties->save();
                    }
                }


                if ( $request->picture_detail != null ) {

                    foreach ( $request->picture_detail as $value ) {
                        $productPicture = new ProductPicture();
                        $DIR = $disPath.'\uploads\product';
                        $httpPost = file_get_contents( 'php://input' );
                        $file_chunks = explode( ';base64,', $value );
                        $fileType = explode( 'image/', $file_chunks[ 0 ] );
                        $image_type = $fileType[ 0 ];
                        $base64Img = base64_decode( $file_chunks[ 1 ] );
                        $name = uniqid();
                        $file = $DIR . '\\' . $name . '.png';
                        $filePath = 'product/' . $name . '.png';
                        file_put_contents( $file, $base64Img );
                        $productPicture->product_id = $product->product_id;
                        $productPicture->pic_name = $name . '.png';
                        $productPicture->picture = $filePath;
                        $productPicture->menu_order = 0;
                        $productPicture->display = 1;
                        $productPicture->date_post = 0;
                        $productPicture->date_update = 0;
                        $productPicture->save();
                    }
                }
                 if(isset($request->product_groups) && count($request->product_groups) > 0 ) {
                foreach ($request->product_groups as $value) {
                    $productGroup = new ProductGroup();
                    $productGroup->product_main =  $product->product_id;
                    $productGroup->product_child = $value;
                    $productGroup->save();
                }
            }
                if($request->input( 'status' ) == 5)
                {

                    $ProductFlashSale = new ProductFlashSale();
                    $ProductFlashSale->product_id  = $product->product_id;
                    $ProductFlashSale->price  = $request->input( 'price' );
                    $ProductFlashSale->price_old  =$request->input( 'price_old' );
                    $ProductFlashSale->discount_percent  = 0;
                    $ProductFlashSale->discount_price  = $request->input( 'discount_price' )??0;
                    $ProductFlashSale->start_time  = $request->input( 'flashStart' )??null;
                    $ProductFlashSale->end_time  = $request->input( 'flashEnd' )??null;
                    // $ProductFlashSale->status  = 1;
                    $ProductFlashSale->adminid  = 1;
                    $ProductFlashSale->save();
                }
                if($request->input( 'status' ) != 5)
                {
                    $list = ProductFlashSale::where('product_id',$product->product_id)->first();

                    if($list != "")
                    {
                        ProductFlashSale::where('product_id',$id)->first()->delete();
                    }
                }

                return response()->json([
                    'status'=>true,
                    'productId'=>$product->product_id
                ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }



        } catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json( $response, 500 );
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
    public function edit(Request $request,string $id)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'edit a product',
                'cat'=>'product',
            ]);
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Tính năng sản phẩm.edit')){
                $list = Products::with('productDescs','productPicture')->find($id);

                $list_cate = explode(',',$list->cat_list);
               
                $i = count($list_cate);
                for($j=0; $j<$i; $j++)
                {
                    $save[] = (int)$list_cate[$j];
                }
                $list['list_cate']=$save;
                $list['parentId'] = $save[0]??null;
                $list['cateId'] = $save[1]??null;
                $list['childId'] = $save[2]??null;

               
                // $list['op_search'] =  explode(',',$list->op_search);
                // $list['op_search'] = array_map('intval', $list['op_search']);

                //$list['tech'] = $data;

                
                $category = PropertiesCategory::where('cat_id',$list_cate[0])->get();
                
                $data = Price::with('propertiesProduct')->where('product_id',$id)->first();
               

                $a=[];
               
              
                    $tskt = [];
                    $valueTskt = [];


                    foreach ($category as $ky => $row) {
                        $saveTskt="";
                        $saveValueTskt ="";
                        foreach ( $data->propertiesProduct as $ke => $item) {

                            if($row->properties_id == $item->properties_id)
                            {
                                $saveTskt = $item->description;
                                $saveValueTskt = $item->pv_id;
                            }
                        }

                        $tskt[] = $saveTskt;
                        $valueTskt[] = $saveValueTskt;
                    }


                    $listData = [
                        'tskt' => $tskt,
                        'value' => $valueTskt,

                    ];
                    $a = $listData;
                
                //return response()->json($a);
                $list['tech'] = $a;
                


                return response()->json([
                    'status'=> true,
                    'product' => $list
                ] );
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }

        }catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json( $response, 500 );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' =>  $stringTime,
            'ip'=> $request->ip(),
            'action'=>'update a product',
            'cat'=>'product',
        ]);

        $disPath = public_path();
        $product = new Products();
        $productPicture = new ProductPicture();
        $productDesc = new ProductDescs();
        $listProduct = Products::Find( $id );
        try {
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Tính năng sản phẩm.update')){
                $checkMacn = Products::where('macn',$request->input( 'macn' ))->where('product_id','!=',$id)->first();
                if($checkMacn != null && $request->input( 'macn' )!='')
                {
                    return response()->json([
                        'message'=>'macn',
                        'status'=>false,
                    ],202);
                }

                $checkDesc = ProductDescs::where('product_id','!=',$id)->where(function($query) use($request) {
                    $query->where('title',$request->input( 'title' ))
                        ->orWhere('friendly_url',$request->input( 'friendly_url' ));
                })->get();

                if(count($checkDesc)>0)
                {
                    return response()->json([
                        'message'=>'title',
                        'status'=>false,
                    ],202);
                }
                if(isset($request->product_groups) && count($request->product_groups) > 0 ) {
                    foreach ($request->product_groups as $value) {
                        $findItemGroup = ProductGroup::where('product_child',$value)->where('product_main',$id)->first();
                        if(!$findItemGroup) {
                            $productGroup = new ProductGroup;
                            $productGroup->product_main = $id;
                            $productGroup->product_child = $value;
                            $productGroup->save();
                        }
                    }
                }



                if(count($request->delete_item_product_groups) != 0) {

                    foreach ($request->delete_item_product_groups as  $value) {
                        $idProductGroup =ProductGroup::where('product_main',$id)->where('product_child',$value)->delete();
                    }

                }

                $listProduct->cat_id = $request->input( 'cat_id' );
                $listProduct->cat_list = implode(',', $request->input( 'cat_list' ));
                $listProduct->maso = $request->input( 'maso' );
                $listProduct->macn = $request->input( 'macn' );
                // $listProduct->code_script = $request->input( 'code_script' );
                if (is_string($request->picture) && (substr($request->picture, -4) === ".png" || substr($request->picture, -4) === ".jpg")) {
                    $filePath = $request->picture;
                } else {
                    $filePath = '';
                }

                if ( $request->picture!= null && $filePath =='' ) {
                    $DIR = $disPath.'\uploads\product';
                    $httpPost = file_get_contents( 'php://input' );

                    $file_chunks = explode( ';base64,', $request->picture[0] );

                    $fileType = explode( 'image/', $file_chunks[ 0 ] );

                    $image_type = $fileType[ 0 ];
                    $base64Img = base64_decode( $file_chunks[ 1 ] );
                    $data = iconv( 'latin5', 'utf-8', $base64Img );
                    $name = uniqid();
                    $file = $DIR .'\\'. $name . '.png';
                    $filePath = 'product/'.$name . '.png';
                    file_put_contents( $file,  $base64Img );
                }
                $listProduct->picture = $filePath;

                $dataArray = $request->input( 'technology' );

                $serializedString = 'a:' . count($dataArray) . ':{';

                // Iterate through the array and construct the serialized string
                foreach ($dataArray as $key => $value) {
                    $serializedString .= 'i:' . $key . ';s:' . strlen($value) . ':"' . $value . '";';
                }

                $serializedString .= '}';

                $technology = $serializedString;
                $listProduct->price =$request->input( 'price' );
                $listProduct->price_old = $request->input( 'price_old' );
                $listProduct->brand_id = $request->input( 'brand_id' );
                $listProduct->status = $request->input( 'status' );
           
                $listProduct->op_search = implode(',', $request->input( 'op_search' ));
             
                $listProduct->technology = $technology;
                $listProduct->focus = 0;
               
                $listProduct->deal_date_start = '0';
                $listProduct->deal_date_end = '0';
                $listProduct->stock = $request->input( 'stock' );
                $listProduct->votes = $request->input( 'votes' );
     
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
                // $listProduct->views = 0;
                $listProduct->display = $request->input( 'display' );
                $listProduct->date_post = '0';
                $listProduct->date_update = '0';
             
                $listProduct->save();

                //product_desc
                $productDesc = ProductDescs::where( 'product_id', $id )->first();
                if ( $productDesc ) {

                    $productDesc->product_id = $listProduct->product_id;
                    $productDesc->title = $request->input( 'title' );
                    $productDesc->description = $request->input( 'description' );
                    $productDesc->gift_desc = $request->input( 'gift_desc' );
                    $productDesc->video_desc = $request->input( 'video_desc' );
                    $productDesc->tech_desc = $request->input( 'tech_desc' );
                    $productDesc->option = 2;
                    $productDesc->short = $request->input( 'short' );
                    $productDesc->start_date_promotion = 0;
                    $productDesc->end_date_promotion = 0;
                    $productDesc->status_promotion = 0;
                    $productDesc->shortcode = $request->input( 'shortcode' );
                    $productDesc->key_search = $request->input( 'key_search' );
                    $productDesc->friendly_url = $request->input( 'friendly_url' );
                    $productDesc->friendly_title = $request->input( 'friendly_title' );
                    $productDesc->metakey = $request->input( 'metakey' );
                    $productDesc->metadesc = $request->input( 'metadesc' );
                    $productDesc->lang = 'vi';
                    $productDesc->save();
                }else{

                    $productDesc = new ProductDescs();
                    $productDesc->product_id = $listProduct->product_id;
                    $productDesc->title = $request->input( 'title' );
                    $productDesc->description = $request->input( 'description' );
               
                    $productDesc->short = $request->input( 'short' );
                    $productDesc->start_date_promotion = 0;
                    $productDesc->end_date_promotion = 0;
                    $productDesc->status_promotion = 0;
             
                    $productDesc->friendly_url = $request->input( 'friendly_url' ) ??Str::slug($request->input( 'title' ));
                    $productDesc->friendly_title = $request->input( 'friendly_title' );
                    $productDesc->metakey = $request->input( 'metakey' );
                    $productDesc->metadesc = $request->input( 'metadesc' );
                    $productDesc->lang = 'vi';
                    $productDesc->save();

                }

                  //delete
            $deletePrice = Price::with('propertiesProduct')->where('product_id', $id)->get();

            foreach ($deletePrice as $list) {
                ProductProperties::where('price_id', $list->id)->delete();
            }
            Price::with('propertiesProduct')->where('product_id', $id)->delete();
            //product_option
            $propertiesCategory = PropertiesCategory::with('properties')->where('cat_id',$request->input( 'cat_list' )[0])->get();
            $price = new Price();

            $price->cat_id = $request->input( 'cat_list' )[0];
            $price->product_id = $id;
            $price->price_old = $request->input( 'price_old' )??0;
            $price->price = $request->input( 'price' )??0;
            $price->picture = $filePath;
            $price->main=1;
            $price->save();
            if(isset($propertiesChildCategory))
            {
                foreach ($propertiesCategory as $key => $value) {
                    $productProperties = new ProductProperties();
                    $productProperties->pv_id = $request->input('value') != null ?$request->input('value'):0;
                    $productProperties->properties_id = $value->properties_id;
                    $productProperties->price_id  = $price->id;
                    $productProperties->description = $request->input('tskt') != null ? $request->input('tskt'):"";
                    $productProperties->save ();
                }
            }

                if ( $request->picture_detail != null ) {
                    $deletePicture = ProductPicture::where( 'product_id', $id )->get();

                    foreach ( $deletePicture as $value ) {
                        $namepicture = $value->picture;
                        $filePath = $disPath . '/uploads/' . $namepicture;
                        unlink( $filePath );
                        $value->delete();
                    }
                    $listProductPicture = ProductPicture::where( 'product_id', $id )->delete();



                    foreach ( $request->picture_detail as $value ) {

                        $productPicture = new ProductPicture();

                        $DIR = $disPath.'\uploads\product';
                        $httpPost = file_get_contents( 'php://input' );
                        $file_chunks = explode( ';base64,', $value );

                        $fileType = explode( 'image/', $file_chunks[ 0 ] );

                        $image_type = $fileType[ 0 ];

                        $base64Img = base64_decode( $file_chunks[ 1 ] );
                        $name = uniqid();
                        $file = $DIR . '\\' . $name . '.png';
                        $filePath = 'product/' . $name . '.png';

                        file_put_contents( $file, $base64Img );

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
                if($request->input( 'status' ) == 5)
                {
                    $list = ProductFlashSale::where('product_id',$id)->first();
                    //return response()->json($list == "");
                    if($list != "")
                    {
                        ProductFlashSale::where('product_id',$id)->first()->delete();
                    }
                    $ProductFlashSale = new ProductFlashSale();
                    $ProductFlashSale->product_id  = $listProduct->product_id;
                    $ProductFlashSale->price  = $request->input( 'price' );
                    $ProductFlashSale->price_old  =$request->input( 'price_old' );
                    $ProductFlashSale->discount_percent  = 0;
                    $ProductFlashSale->discount_price  = $request->input( 'discount_price' )??0;
                    $ProductFlashSale->start_time  = $request->input( 'flashStart' )??null;
                    $ProductFlashSale->end_time  = $request->input( 'flashEnd' )??null;
                    $ProductFlashSale->status  = 5;
                    $ProductFlashSale->adminid  = 1;
                    $ProductFlashSale->save();
                }
                if($request->input( 'status' ) != 5)
                {
                    $list = ProductFlashSale::where('product_id',$id)->first();
                    //return response()->json($list == "");
                    if($list != "")
                    {
                        ProductFlashSale::where('product_id',$id)->first()->delete();
                    }
                }

                return response()->json([
                    'status' => true,
                ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }

        } catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json( $response, 500 );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' =>  $stringTime,
            'ip'=> $request->ip(),
            'action'=>'delete a product',
            'cat'=>'product',
        ]);

        try{
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Tính năng sản phẩm.del')){
                $list = Products::find($id);

                if($list) {
                    $list->delete();
                    $ProductDesc=ProductDescs::where('product_id',$id)->first();
                    $ProductDesc->delete();
                    $Price=Price::where('product_id',$id)->first();
                    $ProductProperties=ProductProperties::where('price_id',$Price->id)->first();
                    $ProductProperties->delete();
                    $Price->delete();
                }
                return response()->json([
                    'status'=>true
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json( $response, 500 );
        }
    }
}
