<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderSum;
use App\Models\OrderAddress;
use App\Models\Member;
use App\Models\OrderDetail;
use App\Models\OrderStatus;
use App\Models\ProductDesc;
use App\Models\PresentDesUsing;
use App\Models\Present;
use App\Models\CouponDesUsing;
use App\Models\CouponDes;
use App\Models\Brand;
use App\Models\ProductGroup;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
class OrderController extends Controller
{
    public function showOrder(Request $request){
        try{
            $memberId= $request->userId ?? NULL;
            $key= $request->key;
            $orderStatus=OrderStatus::where('keyStatus',$key)->first();

            $listOrder=null;
            if ( $memberId && !is_null($memberId)  ) {
                $member=Member::where('id', $memberId)->first();
                $orderSum=OrderSum::with(['orderAddress'=> function ($query) {
                    $query->select('order_id', 'district', 'ward','province','address','from_day','time');
                },'invoiceOrder'=>function ($query){
                    $query->select('order_id','taxCodeCompany','nameCompany','emailCompany','addressCompany');
                },'orderStatus'=>function($query){
                    $query->select('status_id','title','keyStatus');
                },'shippingMethod'=>function($query){
                    $query->select('name','title');
                },
                'paymentMethod'=>function($query){
                    $query->select('name','title');
                },
                'member'=>function($query){
                    $query->select('id','username','mem_code','email','full_name','phone');
                },
                'orderDetail'=>function($query){
                $query->select('order_id','item_title','item_price');
            }
            ])->select('order_id','order_code','MaKH','gender','d_phone','d_email',
                'total_cart','shipping_method','payment_method','status','date_order','mem_id')
                ->where('mem_id',$memberId);


            if($orderStatus){



                $orderSum->where('status',$orderStatus->status_id);
            }

            $listOrder=$orderSum->orderBy('order_id','desc')->get();

           }
           return response()->json([
            'status'=>true,
            'data'=>$listOrder
           ]);



        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage,
            ];
            return response()->json($response, 500);
        }
    }
    public function detailOrder($orderId,$userId){
        try{
            $idOrder=$orderId;
            $idMember=$userId;


            $orderDetail=OrderDetail::where('order_id',$orderId)->get()->groupBy('group_id');

            $orderSum = OrderSum::with('orderStatus','shippingMethod','paymentMethod','member','orderAddress','invoiceOrder')
            ->where('order_id', $idOrder)->where('mem_id', $idMember)->first();
            //return $orderSum;
            $CouponDesUsing=CouponDesUsing::where('IDOrderCode',$orderSum->order_code)->first();
            $coupon=null;
            if($CouponDesUsing){
                $coupon= CouponDes::with('coupon')->where('idCouponDes',$CouponDesUsing->idCouponDes)->first();
            }

            // $PresentDesUsing=PresentDesUsing::where('IDOrderCode',$orderSum->order_code)->first();
            // $coupon=null;
            // if($CouponDesUsing){
            //     $coupon= CouponDes::with('coupon')->where('idCouponDes',$CouponDesUsing->idCouponDes)->first();
            // }


           // $orderDetail=$orderSum ->orderDetail??[];
            $orderStatus=$orderSum ->orderStatus??null;
            $listOrderDetail=[];
            $dataOrder=[];
           
            if($orderSum){
               
               
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
                $listCart=[];
                foreach( $listOrderDetail as $item){
                   $listCart[]=$item;
                }
                
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
                    'orderPoints'=>$orderSum->accumulatedPoints,
                    'accumulatedPoints'=>$orderSum->accumulatedPoints_1,
                    'totalValueOfPoint'=>$orderSum->totalValueOfPoint,
                    'keyStatus'=>$orderSum->orderStatus->keyStatus??null,
                    'date_order'=>$orderSum->date_order,
                    'comment'=>$orderSum->comment,
                    'coupon'=>$coupon,
                    'shipping_method'=>$orderSum->shippingMethod??null,
                    'payment_method'=>$orderSum->paymentMethod??null,
                    'order_status'=>$orderSum->orderStatus->title??null,
                    'CouponDiscout'=>$orderSum->CouponDiscout??0,
                    'member'=>[
                        'id'=>$orderSum->member->id??null,
                        'username'=>$orderSum->member->username??null,
                        'email'=>$orderSum->member->email??null,
                        'full_name'=>$orderSum->member->full_name??null,

                    ],
                    'address'=>$orderSum->orderAddress,
                    'invoiceOrder'=>$orderSum->invoiceOrder,

                    'orderDetail'=>$listCart,
                ];
            }


            return response()->json([
                'status'=>true,
                'dataOrder'=>$dataOrder,

            ]);
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage,
            ];
            return response()->json($response, 500);
        }

    }
}
