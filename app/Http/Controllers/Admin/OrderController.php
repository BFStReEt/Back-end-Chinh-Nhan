<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderSum;
use App\Models\OrderAddress;
use App\Models\Member;
use App\Models\OrderDetail;
use App\Models\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductDesc;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\GiftPromotion;
use App\Models\Product;
use App\Models\Present;
use App\Models\Coupon;
use App\Models\CouponDes;
use App\Models\CouponDesUsing;
use App\Models\PresentDesUsing;
use App\Models\InvoiceOrder;
use App\Models\ProductGroup;
use Gate;
//  $usingCoupon = new CouponDesUsing();
class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function verify(Request $request)
    {
        $captchaToken = $request->input('captchaToken');


        if (!$captchaToken) {
            return response()->json(['error' => 'Captcha token is missing.'], 400);
        }

        $secretKey = env('RECAPTCHA_SECRET_KEY');
        $verifyUrl = "https://www.google.com/recaptcha/api/siteverify";

        $response = Http::asForm()->post($verifyUrl, [
            'secret' => $secretKey,
            'response' => $captchaToken,
        ]);

        $responseBody = $response->json();

        if ($responseBody['success'] && $responseBody['score'] > 0.5) {
            return response()->json(['message' => 'Captcha verified successfully.'], 200);
        }

        return response()->json(['error' => 'Invalid captcha.'], 400);
    }
    public function checkGiftPromotion($id){
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);

        $listGiftPromotion = GiftPromotion::orderBy('id','DESC')
        ->where('StartDate','<=',$stringTime)
        ->where('EndDate','>=',$stringTime)
        ->where('display',1)
        ->get();


        $arrayGiftPromotion=[];
        $product = Product::where('product_id', $id)->first();
      //return $listGiftPromotion;

        foreach($listGiftPromotion as $present){
            $listCate= explode(",",$present->list_cat);
            $listProduct=explode(",",$present->list_product);
            if((in_array($product->cat_id, $listCate) &&($present->priceMin<=$product->price && $product->price<=$present->priceMax))
            || in_array($product->macn, $listProduct)
            ){
                $arrayGiftPromotion[]=$present;
            }

        }
        return $arrayGiftPromotion;
    }

    public function checkPresent($id){
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);

        $listPresent = Present::orderBy('id','DESC')
        ->where('StartDate','<=',$stringTime)
        ->where('EndDate','>=',$stringTime)
        ->where('display',1)
        ->get();


        $arrayPresent=[];
        $product = Product::where('product_id', $id)->first();

        foreach($listPresent as $present){
            $listCate= explode(",",$present->list_cat);
            $listProduct=explode(",",$present->list_product);
            if((in_array($product->cat_id, $listCate) &&($present->priceMin<=$product->price && $product->price<=$present->priceMax))
            || in_array($product->macn, $listProduct)
            ){
                $arrayPresent[]=$present;
            }

        }
        return $arrayPresent;
    }



    public function index(Request $request)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'add a order',
                'cat'=>'order',
            ]);
            // $query = Product::with([
            //     'productDesc' => function ($query) {
            //         $query->select('product_id', 'title', 'friendly_url');
            //     },

            //     'brandDesc'=> function ($query) {
            //         $query->select('brand_id', 'title');
            //     },
            //     'priceList.propertiesProduct.properties'
            // ])
            // ->select('product.product_id', 'maso', 'macn', 'stock', 'status','brand_id',
            // 'PriceSAP','MaHH','TenHH','MaKhoMacDinh','TenKhoMacDinh','DVT','TonKho',
            // 'SLDatHang','SLGiuHang','SLCoTheXuat','CodeBars','FirmName','ItmsGrpNam','Hienthi')
            //date_order

            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Quản lý đơn hàng.manage')){
            $search= $request->all();
            $orderSum=OrderSum::with(['orderAddress'=> function ($query) {
                $query->select('order_id', 'district', 'ward','province','address','from_day','time');
            },'invoiceOrder'=>function ($query){
                $query->select('order_id','taxCodeCompany','nameCompany','emailCompany','addressCompany');
            },'orderStatus'=>function($query){
                $query->select('status_id','title','color');
            },'shippingMethod'=>function($query){
                $query->select('name','title');
            },
            'paymentMethod'=>function($query){
                $query->select('name','title');
            },
            'member'=>function($query){
                $query->select('id','username','mem_code','email','full_name','phone');
            }
            ])->select('order_id','order_code','MaKH','gender','d_name','d_phone','d_email',
            'total_cart','shipping_method','payment_method','status','date_order','mem_id');
            // $name =  isset($search['name']) ? $search['name'] : '';
            // $status =   isset($search['status']) ?  $search['status'] : '';
            // $fromDate =  isset($search['fromDate']) ?$search['fromDate'] : null;
            // $toDate =  isset($search['toDate']) ? $search['toDate'] :null;
            // if($fromDate=="Invalid date"){
            //     $fromDate=null;
            // }
            // if($toDate=="Invalid date"){
            //     $toDate=null;
            // }

            if(isset($search['name'])){
                $name=$search['name'];
                $orderSum->where(function ($query) use ($name){
                    $query->where('order_code', 'LIKE', '%' . $name . '%')
                       ->orWhere('MaKH', 'LIKE', '%' . $name . '%')
                        ->orWhere('d_name', 'LIKE', '%' . $name . '%')
                        ->orWhere('d_phone', 'LIKE', '%' . $name . '%')
                        ->orWhere('d_email', 'LIKE', '%' . $name . '%');
                });
            }

            if(isset($search['status'])){
                $status=$search['status'];
                $orderSum->where('status',$status);
            }

            if(isset($search['fromDate']) && isset($search['toDate'])){
                $fromDate=$search['fromDate'];
                $toDate=$search['toDate'];
                $orderSum->whereBetween('date_order', [$fromDate, $toDate]);
            }

            if(isset($search['typeMember']) && $search['typeMember']==1){


                $orderSum->where('mem_id','!=',0);
            }else if(isset($search['typeMember']) && $search['typeMember']==0){
                $orderSum->where('mem_id',0);
            }

            $orderSum=$orderSum->orderBy('order_id','desc')->paginate(10);

            return response()->json([
                'status'=>true,
                'data'=>$orderSum
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
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
        //
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
    public function edit(Request $request,int $id)
    {
        try{

            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'edit a order',
                'cat'=>'order',
            ]);
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Quản lý đơn hàng.edit')){
        $orderDetail= OrderDetail::where('order_id',$id)->get()->groupBy('group_id');

        

        $InvoiceOrder=InvoiceOrder::where('order_id',$id)->first();
        $orderSum = OrderSum::with('orderStatus','shippingMethod','paymentMethod','member')->find($id);
        $OrderAddress=OrderAddress::where('order_id',$id)->first();
        $usingCoupon =CouponDesUsing::where('IDOrderCode',$orderSum->order_code)->first()??null;
        $PresentDesUsing=PresentDesUsing::where('IDOrderCode',$orderSum->order_code)->first()??null;
        $CouponDes=null;
        if($usingCoupon){
            $CouponDes=CouponDes::where('idCouponDes',$usingCoupon->idCouponDes)->first();
        }
        $PresentDes=null;

        if($PresentDesUsing){
            $PresentDes=Present::where('id',$PresentDesUsing->idPresent)->first();
        }



       // $orderDetail=$orderSum ->orderDetail??[];
        $orderStatus=$orderSum ->orderStatus??null;


        $listCart=[];
        if($orderDetail)
        {

            foreach($orderDetail as $key=>$groupOrder){
                    
                    
                if($key==0){
                    
                
                    foreach($groupOrder as $item){
                        $PresentDesUsing=PresentDesUsing::where('IDOrderCode',$orderSum->order_code)
                        ->where('IdProduct',$item->item_id)->first();

                        $present=null;
                        if($PresentDesUsing){

                            $present=$PresentDesUsing->present;

                        }
                        $brand=null;
                        if($item->product){
                            $brand=$item->product->brandDesc;
                        }
                        $Category=null;
                        if($item->product){
                            $Category=$item->product->categoryDes;
                        }

                        $listOrderDetail[]=[
                            'typeCombo'=>false,
                            'orderId'=>$item->order_id,
                            'item_type'=>$item->item_type,
                            'brandName'=>$brand->title??null,
                            'ProductId'=>$item->item_id,
                            'quantity'=>$item->quantity,
                            'stock'=>$item->product->stock,
                            'ProductName'=>$item->item_title,
                            'Price'=>$item->item_price,
                            'PriceOld'=>$item->item_price,
                            'subtotal'=>$item->subtotal,
                            'Category'=> $Category->cat_name??null,
                            'Image'=>$item->product->picture??null,
                            'Picture'=>$item->product->picture??null,
                            'present'=>$present??null

                        ];
                       

                    }
                    
                }else{
                
                    $priceCombo=0;
                    foreach($groupOrder as $item){
                        $priceCombo+=$item->item_price;
                            $PresentDesUsing=PresentDesUsing::where('IDOrderCode',$orderSum->order_code)
                            ->where('IdProduct',$item->item_id)
                            ->where('group_id',$key)
                            ->first();
                        
                            $present=null;
                            if($PresentDesUsing){
        
                                $present=$PresentDesUsing->present;
        
                            }
                            
                            $brand=null;
                            if($item->product){
                                $brand=$item->product->brandDesc;
                            }
                            $Category=null;
                            if($item->product){
                                $Category=$item->product->categoryDes;
                            }
                        
        
                        
                            $listOrderDetail[$key]["products"][]=[
                            
                                'orderId'=>$item->order_id,
                                'item_type'=>$item->item_type,
                                'brandName'=>$brand->title??null,
                                'ProductId'=>$item->item_id,
                                'quantity'=>$item->quantity,
                                'stock'=>$item->product->stock,
                                'ProductName'=>$item->item_title,
                                'Price'=>$item->item_price,
                                'PriceOld'=>$item->item_price,
                                'subtotal'=>$item->subtotal,
                                'Category'=> $Category->cat_name??null,
                                'Image'=>$item->product->picture??null,
                                'Picture'=>$item->product->picture??null,
                                'present'=>$present??null
        
                            ];
                        
                    }
                    $ProductGroup=ProductGroup::where('id_group',$key)->first();
                    
                    if($ProductGroup){

                        $listOrderDetail[$key]['typeCombo']=true;
                        $listOrderDetail[$key]["GroupId"]=$key;
                        
                        $listOrderDetail[$key]['quantity']= $groupOrder[0]['quantity'];
                        $listOrderDetail[$key]['title']= $ProductGroup->titleGroup;
                        $listOrderDetail[$key]['Price']=$priceCombo-$ProductGroup->discount;
                        $listOrderDetail[$key]['discountCombo']= $ProductGroup->discount;
                    
                    }
                    
                
                }
            
            }
            
            foreach( $listOrderDetail as $item){
            $listCart[]=$item;
            }
        }
       




        // $listOrderDetail=null;
        // foreach($orderDetail as $item){
        //     $listOrderDetail[]=[
        //         'order_id'=>$item->order_id,
        //         'item_type'=>$item->item_type,
        //         'item_id'=>$item->item_id,
        //         'quantity'=>$item->quantity,
        //         'item_title'=>$item->item_title,
        //         'item_price'=>$item->item_price,
        //         'subtotal'=>$item->subtotal,
        //         'picture'=>$item->product->picture??null,
        //         'checkPresent'=>$this->checkPresent($item->item_id),
        //         'checkGiftPromotion'=>$this->checkGiftPromotion($item->item_id),

        //     ];
        // }

        $dataOrder=[
            'order_id'=> $orderSum->order_id,
            'MaKH'=> $orderSum->MaKH,
            'order_code'=>$orderSum->order_code,
            'gender'=>$orderSum->gender,
            'd_name'=>$orderSum->d_name,
            'd_phone'=>$orderSum->d_phone,
            'd_email'=>$orderSum->d_email,
            'total_price'=>$orderSum->total_price,
            'total_cart'=>$orderSum->total_cart,
            'mem_id'=>$orderSum->mem_id,
            'accumulatedPoints'=>$orderSum->accumulatedPoints,
            'status'=>$orderSum->status,
            'date_order'=>$orderSum->date_order,
            'comment'=>$orderSum->comment,
            'shipping_method'=>$orderSum->shippingMethod->title??null,
            'payment_method'=>$orderSum->paymentMethod->title??null,
            'order_status'=>$orderSum->orderStatus->title??null,
            'CouponDiscout'=>$orderSum->CouponDiscout??0,
            'valueCoupon'=>$CouponDes->coupon??null,
            'totalValueOfPoint'=>$orderSum->totalValueOfPoint,
            'PresentDes'=>$PresentDes,
            'totalValueOfPoint'=>$orderSum->totalValueOfPoint??0,
            'member'=>[
                'id'=>$orderSum->member->id??null,
                'username'=>$orderSum->member->username??null,
                'email'=>$orderSum->member->email??null,
                'full_name'=>$orderSum->member->full_name??null,
            ],
            'orderDetail'=>$listCart,
            'invoiceOrder'=>$InvoiceOrder,
            'orderAddress'=>$OrderAddress
        ];


        // $orderStatus = OrderStatus::find($orderSum->status);
        //  foreach ($orderDetail as $value) {
        //     $orderProduct[] = Product::where('product_id',$value->item_id)->first();
        //  }
        // $orderProductDesc=[];
        // foreach ($orderDetail as $value) {
        //     $orderProductDesc[] = ProductDesc::with('product')->where('product_id',$value->item_id)->get();
        // }
        // $orderCard = CardPromotion::where('order_id',$orderSum->order_id)->get();
        return response()->json([
            //'orderSumId' => $orderSum,
            //'orderStatus' => $orderStatus,
            //'orderDetail' => $orderDetail,
            'dataOrder'=>$dataOrder,


        ]);
    } else {
        return response()->json([
            'status'=>false,
            'mess' => 'no permission',
        ]);
    }

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'update a order',
                'cat'=>'order',
            ]);
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Quản lý đơn hàng.update')){
            $data=$request->all();
            $orderSumId = OrderSum::find($id);
            $status= $data['status'];
            $comment=$data['comment'];
            $orderSumId->status=$status;
            $orderSumId->comment=$comment;

            switch ($status) {
                case 1:
                    $orderSumId->date_order_status1=  Carbon::now('Asia/Ho_Chi_Minh');
                    break;
                case 2:
                    $orderSumId->date_order_status2= Carbon::now('Asia/Ho_Chi_Minh');
                    break;
                case 3:
                    $orderSumId->date_order_status3= Carbon::now('Asia/Ho_Chi_Minh');
                    break;
                case 4:
                    $orderSumId->date_order_status4= Carbon::now('Asia/Ho_Chi_Minh');
                    break;
                case 5:
                    $orderSumId->date_order_status5= Carbon::now('Asia/Ho_Chi_Minh');
                    break;
                case 6:
                    $orderSumId->date_order_status6= Carbon::now('Asia/Ho_Chi_Minh');
                    break;
                case 7:
                    $orderSumId->date_order_status7= Carbon::now('Asia/Ho_Chi_Minh');
                    break;
            }

            $orderSumId->save();
            $member=$orderSumId->member;


           if( $member && $status==5){

                $Points= $member->accumulatedPoints;
                $member->accumulatedPoints= $Points+$orderSumId->accumulatedPoints;
                $member->save();
           }
           if( $member && ($status==6 || $status==7)){

            $accumulatedPoints=  $orderSumId->accumulatedPoints_1;
            $member->accumulatedPoints= $member->accumulatedPoints+$accumulatedPoints;
            $member->save();
       }

       $date = Carbon::now('Asia/Ho_Chi_Minh');
       $dataSocket =[
        'type'=>'orderStatus',
        'socketId'=>rand(9,9999)
        .Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('DDMMYYYY'),
        'idOrder'=>$orderSumId->order_id??null,
        'codeOrder'=>$orderSumId->order_code??null,
        'statusOrder'=> $orderSumId ->orderStatus->title??null,
        'memberId'=>$member->id??null,
        'date'=>$date,
        'seen'=>false

    ];
        try {
            $message=json_encode($dataSocket);

            $endpoint = 'https://socket.chinhnhan.net/api/notifies';
            $endpoint .= '?message='. urlencode($message);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->withoutVerifying()
                ->get($endpoint);

            if ($response->successful()) {
                    $responseData = $response->json(); // Assuming response is JSON
                    // Process $responseData if needed
                } else {
                    $error = $response->toPsrResponse()->getReasonPhrase();
                    echo "cURL Error: " . $error;
                }

        } catch(Exception $e) {
            return ['error' => $e->getMessage()];
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


        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

    }
}
