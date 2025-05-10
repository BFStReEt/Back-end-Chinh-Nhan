<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Mail\OrderEmail;
use App\Models\Config;
use App\Models\CouponDes;
use App\Models\CouponDesUsing;
use App\Models\InvoiceOrder;
use App\Models\ListCart;
use App\Models\Member;
use App\Models\OrderAddress;
use App\Models\OrderDetail;
use App\Models\OrderStatus;
use App\Models\OrderSum;
use App\Models\PaymentMethod;
use App\Models\Present;
use App\Models\PresentDesUsing;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ShippingMethod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    public function showOrderStatus()
    {
        try {
            $orderStatus = OrderStatus::orderBy('status_id', 'asc')->get();
            return response()->json([
                'status' => true,
                'data' => $orderStatus,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function showShippingMethod()
    {
        try {
            $shippingMethod = ShippingMethod::orderBy('shipping_id', 'asc')->get();
            return response()->json([
                'status' => true,
                'data' => $shippingMethod,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function showPaymentMethod()
    {
        try {
            $paymentMethod = PaymentMethod::orderBy('payment_id', 'asc')->get();
            return response()->json([
                'status' => true,
                'data' => $paymentMethod,
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

    public function checkout(Request $request)
    {
        try {
            $config = Config::orderBy('id', 'desc')->first();
            $priceOfPoint = $config->priceOfPoint;
            $valueOfPoint = $config->valueOfPoint;

            $data = $request->all();
            $memberId = $request->userId ?? null;
            $dataOder = $data['value'];
            // return $dataOder['dataOrder'];

            // $userId = $request->json('userId');

            $accumulatedPoints = 0;
            if ($memberId && !is_null($memberId)) {
                // Xử lý khi userId là null

                $member = Member::where('id', $memberId)->first();
                if (isset($dataOder['accumulatedPoints'])) {
                    $accumulatedPoints = $dataOder['accumulatedPoints'];
                } else {
                    $accumulatedPoints = 0;
                }

                if ($member && isset($dataOder['accumulatedPoints'])) {
                    $Points = $member->accumulatedPoints ?? 0;
                    if ($Points < $accumulatedPoints) {
                        return response()->json([
                            'status' => false,
                            'message' => 'accumulatedPoints',
                        ]);
                    }
                    $member->accumulatedPoints = $Points - $accumulatedPoints;
                    $member->save();
                }
            }
            $orderNew = new OrderSum();
            // return   $orderNew ;
            $orderNew->order_code = rand(9, 9999)
            . Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('DDMMYYYY');

            $orderNew->d_name = $dataOder['fullName'] ?? null;
            // $orderNew -> d_address = Auth::guard('member')->user() ? Auth::guard('member')->user()->address : $data['d_address'];
            $orderNew->d_phone = $dataOder['numberPhone'] ?? null;
            $orderNew->d_email = $dataOder['email'] ?? null;
            $orderNew->accumulatedPoints = $data['orderPoints'] ?? null;
            if ($memberId && !is_null($memberId)) {
                $orderNew->accumulatedPoints_1 = $accumulatedPoints ?? 0;
                $orderNew->CouponDiscout = $data['valueVoucher']['valueVoucher'] ?? 0;
                $orderNew->valueOfPoint = $data['valueOfPoint'] ?? 0;
                $orderNew->totalValueOfPoint = $accumulatedPoints * $data['valueOfPoint'];
            }

            // if ( $memberId && !is_null($memberId)  ) {
            //     $orderNew ->accumulatedPoints = $dataOder['accumulatedPoints'] ?? null;
            // }
            //$orderNew -> c_name = $data['c_name'] ?? '';
            // $orderNew -> c_address = $data['c_address'] ?? '';
            // $orderNew -> c_phone = $data['c_phone'];
            // $orderNew -> c_email = $data['c_email'] ?? '';
            $orderNew->total_cart = $data['total'] ?? null;
            $total_price = 0;
            if ($orderNew->CouponDiscout == 0) {
                // $orderNew ->total_price= $orderNew -> total_cart;
                $total_price = $orderNew->total_cart;
            } else {
                // $orderNew ->total_price= $orderNew -> total_cart+$orderNew ->CouponDiscout;
                $total_price = $orderNew->total_cart + $orderNew->CouponDiscout;
            }

            if ($orderNew->valueOfPoint == 0) {
                $orderNew->total_price = $total_price;
            } else {
                $orderNew->total_price = $total_price + $orderNew->totalValueOfPoint;
            }

            $orderNew->shipping_method = $dataOder['shippingMethod'] ?? null;
            // $orderNew -> payment_method = $data['payment_method'];
            $orderNew->status = 1;
            // $orderNew -> date_order = Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('DD-MM-YYYY HH:mm:ss');
            $orderNew->date_order = strtotime('now');
            // $orderNew -> comment = $data['comment']??'';
            // $orderNew -> note = $data['note'] ?? '';
            $orderNew->mem_id = is_null($memberId) ? null : $memberId;
            $orderNew->gender = $dataOder['sex'] ?? null;
            $orderNew->userManual = $dataOder['userManual'] ?? null;
            $orderNew->comment = $dataOder['otherRequirement'] ?? null;
            $orderNew->date_order_status1 = Carbon::now('Asia/Ho_Chi_Minh');
            //$orderNew -> CouponDiscout = $data['CouponDiscout'] ?? 0;
            //$orderNew -> diem_use = $data['diem_use'] ?? 0;
            //$orderNew -> status_diem = 0;
            //$orderNew -> update_at = Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('DD-MM-YYYY HH:mm:ss');
            // return  $orderNew;

            $orderNew->save();

            //return $data;

            $orderAddress = new OrderAddress();

            $orderAddress->order_id = $orderNew->order_id;
            $orderAddress->district = $dataOder['districtAddress'] ?? null;
            $orderAddress->ward = $dataOder['wardAddress'] ?? null;
            $orderAddress->province = $dataOder['cityAddress'] ?? null;
            $orderAddress->address = $dataOder['streetAddress'] ?? null;
            $orderAddress->from_day = $dataOder['day_receiving'] ?? null;
            $orderAddress->time = $dataOder['time_address'] ?? null;
            $orderAddress->save();

            if (isset($dataOder['companyInvoice']) && $dataOder['companyInvoice'] == true) {
                $invoiceOrder = new InvoiceOrder();
                $invoiceOrder->order_id = $orderNew->order_id;
                $invoiceOrder->taxCodeCompany = $dataOder['taxCodeCompany'];
                $invoiceOrder->nameCompany = $dataOder['nameCompany'];
                $invoiceOrder->emailCompany = $dataOder['emailCompany'];
                $invoiceOrder->addressCompany = $dataOder['addressCompany'];
                $invoiceOrder->save();
            }

            $temp = count($dataOder['dataOrder']);

            $dataProduct = [];
            $listGroup = [];
            for ($i = 0; $i < $temp; $i++) {

                if (isset($dataOder) &&
                    isset($dataOder['dataOrder']) &&
                    isset($dataOder['dataOrder'][$i]) &&
                    isset($dataOder['dataOrder'][$i]['typeCombo']) &&
                    $dataOder['dataOrder'][$i]['typeCombo'] == true) {
                    $listProduct = $dataOder['dataOrder'][$i]['products'];
                    $quantityGroup = $dataOder['dataOrder'][$i]['quantity'] ?? 0;
                    $GroupId = $dataOder['dataOrder'][$i]['GroupId'];
                    $listGroup[] = $GroupId;
                    if (count($listProduct) > 0) {

                        foreach ($listProduct as $group) {

                            $price = $group['Price'];
                            $productId = $group['ProductId'];
                            $orderDetail = new OrderDetail();
                            $orderDetail->order_id = $orderNew->order_id;

                            $orderDetail->item_id = $productId;
                            $orderDetail->group_id = $GroupId;

                            $orderDetail->quantity = $quantityGroup;
                            $orderDetail->item_title = $group['ProductName'] ?? null;
                            $orderDetail->item_price = $price;
                            $orderDetail->subtotal = $quantityGroup * ($price);
                            $orderDetail->save();

                            $dataProduct[] = [
                                'productId' => $productId,
                                'order_id' => $orderDetail->order_id ?? null,
                                'item_price' => $orderDetail->item_price ?? null,
                                'item_id' => $orderDetail->item_id ?? null,
                                'quantity' => $orderDetail->quantity ?? null,
                                'item_title' => $orderDetail->item_title ?? null,
                                'subtotal' => $orderDetail->subtotal ?? null,
                                'picture' => $dataOder['dataOrder'][$i]['Image'] ?? null,
                            ];
                            if (isset($group['presentOrder'])) {
                                $presentData = $group['presentOrder'];

                                //$Present=Present::where('code', $presentData->code)->first();
                                $presentDesUsing = new PresentDesUsing();
                                $presentDesUsing->IDuser = $memberId ?? 0;
                                $presentDesUsing->idPresent = $presentData['id'];
                                $presentDesUsing->DateUsingCode = Carbon::now('Asia/Ho_Chi_Minh');
                                $presentDesUsing->IDOrderCode = $orderNew->order_code;
                                $presentDesUsing->MaPresentUSer = $presentData['code'];
                                $presentDesUsing->IdProduct = $productId;
                                $presentDesUsing->group_id = $GroupId;
                                $presentDesUsing->save();

                            }

                        }
                        if ($memberId) {
                            ListCart::where('mem_id', $memberId)->where('id_group', $GroupId)->delete();
                        }
                    }

                } else {

                    $price = $dataOder['dataOrder'][$i]['Price'];
                    $productId = $dataOder['dataOrder'][$i]['ProductId'];
                    $orderDetail = new OrderDetail();
                    $orderDetail->order_id = $orderNew->order_id;

                    $orderDetail->item_id = $productId;
                    $orderDetail->quantity = $dataOder['dataOrder'][$i]['quantity'];
                    $orderDetail->item_title = $dataOder['dataOrder'][$i]['ProductName'];
                    $orderDetail->item_price = $price;
                    $orderDetail->subtotal = $dataOder['dataOrder'][$i]['quantity'] * ($price);
                    $orderDetail->save();

                    $dataProduct[] = [
                        'productId' => $productId,
                        'order_id' => $orderDetail->order_id ?? null,
                        'item_price' => $orderDetail->item_price ?? null,
                        'item_id' => $orderDetail->item_id ?? null,
                        'quantity' => $orderDetail->quantity ?? null,
                        'item_title' => $orderDetail->item_title ?? null,
                        'subtotal' => $orderDetail->subtotal ?? null,
                        'picture' => $dataOder['dataOrder'][$i]['Image'] ?? null,
                    ];

                    if (isset($dataOder['dataOrder'][$i]['presentOrder'])) {
                        $presentData = $dataOder['dataOrder'][$i]['presentOrder'];

                        //$Present=Present::where('code', $presentData->code)->first();
                        $presentDesUsing = new PresentDesUsing();
                        $presentDesUsing->IDuser = $memberId ?? 0;
                        $presentDesUsing->idPresent = $presentData['id'];
                        $presentDesUsing->DateUsingCode = Carbon::now('Asia/Ho_Chi_Minh');
                        $presentDesUsing->IDOrderCode = $orderNew->order_code;
                        $presentDesUsing->MaPresentUSer = $presentData['code'];
                        $presentDesUsing->IdProduct = $productId;

                        $presentDesUsing->save();

                    }
                    if ($memberId) {
                        ListCart::where('mem_id', $memberId)->where('product_id', $productId)->delete();
                    }
                }

                //return 111;
            }
            if (count($listGroup) > 0) {
                $orderUpdate = OrderSum::where('order_id', $orderNew->order_id)->first();
                $orderUpdate->list_group_product = implode(',', $listGroup);
                $orderUpdate->save();
            }

            try {

                $dataCoupon = null;

                $valueVoucher = 0;
                if ($request->valueVoucher && $memberId && !is_null($memberId)) {

                    $dataVoucher = $request->valueVoucher;

                    $couponDes = CouponDes::where('MaCouponDes', $dataVoucher['MaCouponDes'])->where('SoLanConLaiDes', '>', 0)->first();
                    if ($couponDes) {
                        $couponDesItem = $couponDes->idCouponDes;
                        $couponDescription = CouponDes::find($couponDesItem);
                        $couponDescription->SoLanSuDungDes += 1;
                        if ($couponDescription->SoLanConLaiDes >= 0) {
                            $couponDescription->SoLanConLaiDes -= 1;
                        }
                        $couponDescription->save();
                        $usingCoupon = new CouponDesUsing();
                        $usingCoupon->IDuser = $memberId;
                        $usingCoupon->idCouponDes = $couponDesItem;
                        $usingCoupon->DateUsingCode = Carbon::now('Asia/Ho_Chi_Minh');
                        $usingCoupon->IDOrderCode = $orderNew->order_code;
                        $usingCoupon->MaCouponUSer = $dataVoucher['MaCouponDes'];
                        $usingCoupon->save();
                        $valueVoucher = $dataVoucher['valueVoucher'] ?? 0;
                        // $dataCoupon=[
                        //     'valueVoucher'=>$dataVoucher['valueVoucher']??0,
                        //     'idCouponDes'=>$couponDesItem,
                        //     'SoLanSuDungDes'=> $couponDescription->SoLanSuDungDes,
                        //     'SoLanConLaiDes'=>$couponDescription->SoLanConLaiDes,
                        //     'DateUsingCode'=> $usingCoupon -> DateUsingCode ,
                        //     'MaCouponUSer'=> $usingCoupon -> MaCouponUSer,
                        // ];
                    }
                }

            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ]);
            }

            $addressOrder = null;

            if (isset($orderAddress->province) && isset($orderAddress->ward) && isset($orderAddress->district)) {
                $addressOrder = $orderAddress->address . ',' . $orderAddress->province . ',' . $orderAddress->ward . ',' . $orderAddress->district;

            } else {
                $addressOrder = '245B Trần Quang Khải, phường Tân Định, quận 1';

            }

            $dataEmail = [
                'd_code' => $orderNew->order_code,
                'd_name' => $orderNew->d_name,
                'd_adress' => $orderNew->d_name,
                'd_phone' => $orderNew->d_phone,
                'd_gmail' => $orderNew->d_email,
                'total_cart' => $orderNew->total_cart,
                'total_price' => $orderNew->total_price,
                'listProduct' => $dataProduct,
                'coupon' => $valueVoucher,
                'totalValueOfPoint' => $orderNew->totalValueOfPoint,
                'address' => $addressOrder,
                'timeOrder' => Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('DD/MM/YYYY'),
                'taxCodeCompany' => isset($invoiceOrder) ? $invoiceOrder->taxCodeCompany : null,
                'nameCompany' => isset($invoiceOrder) ? $invoiceOrder->nameCompany : null,
                'emailCompany' => isset($invoiceOrder) ? $invoiceOrder->emailCompany : null,
                'addressCompany' => isset($invoiceOrder) ? $invoiceOrder->addressCompany : null,
            ];

            //---------------------------------------------------

            // $MailTemplate=MailTemplate::where('name','OrdertoGuest')->first();

            //     $test='<div class="table_member">
            //     <table style="border-collapse: collapse; width: 100%;" class="table-order table table-bordered table-hover">
            //     <thead>
            //     <tr>
            //     <th style="border: 1px solid black;" scope="col">Id đơn hàng</th>
            //     <th style="border: 1px solid black; font-weight: bold;" scope="col">Hình ảnh sản phẩm</th>
            //         <th style="border: 1px solid black; font-weight: bold;"  scope="col">Tên sản phẩm</th>
            //         <th style="border: 1px solid black; font-weight: bold;" scope="col">Giá sản phẩm</th>
            //         <th style="border: 1px solid black; font-weight: bold;" scope="col">Số lượng</th>
            //         <th style="border: 1px solid black; font-weight: bold;" scope="col">Tổng cộng</th>
            //     </tr>
            //     </thead>
            //     <tbody>';
            //     foreach($dataProduct as $item){
            //         $test.='<tr style="border: 1px solid black;">
            //             <td style="border: 1px solid black; text-align: center;" class="center">'.$item['order_id'].'</td>
            //             <td style="border: 1px solid black; text-align: center;" class="center"><img width="100px" height="100px" src="http://api.chinhnhan.com/uploads/'.$item['picture'].'"></td>
            //             <td style="border: 1px solid black; text-align: center;" class="center">'.$item['item_title'].'</td>
            //             <td style="border: 1px solid black; text-align: center;" class="center">'.number_format($item['item_price'], 0, '', ',').'VNĐ</td>
            //             <td style="border: 1px solid black; text-align: center;" class="center">'.$item['quantity'].'</td>
            //             <td style="border: 1px solid black; text-align: center;" class="center">'.number_format( $item['subtotal'], 0, '', ',').'VNĐ</td>

            //     </tr>';
            // }
            //     $test.='</tbody>
            //     </table>
            // </div>';
            // if(isset($dataOder['cityAddress']) && isset($dataOder['wardAddress']) && isset($dataOder['districtAddress'])){
            //     $dataEmail_address= $orderAddress->address.','.$orderAddress->province.','. $orderAddress->ward.','. $orderAddress->district;

            // }else{
            //     $dataEmail_address='245B Trần Quang Khải, phường Tân Định, quận 1';
            // }

            //     $dataEmail=[
            //         'domain'=>'chinhnhan.com',
            //         'name'=>$orderNew ->d_name,
            //         'order_code'=>$orderNew ->order_code,
            //         'phone'=>$orderNew ->d_phone,
            //         'address'=> $dataEmail_address,
            //         'email'=>$orderNew ->d_email,
            //         'payment_method'=>'payment_method',
            //         'shipping_method'=>'shipping_method',
            //         'comment'=> $orderNew ->comment,
            //         'date'=>Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('HH:mm:ss, DD/MM/YYYY'),
            //         'table_cart'=>$test,

            //         'html'=>$MailTemplate->description
            //     ];
            //     $dataEmail['html'] = str_replace(
            //         ['[domain]', '[name]','[table_cart]','[order_code]', '[phone]',
            //         '[address]','[email]', '[payment_method]','[shipping_method]'
            //         ,'[comment]','[date]'
            //     ],
            //         [$dataEmail['domain'], $dataEmail['name'], $dataEmail['table_cart'],
            //         $dataEmail['order_code'], $dataEmail['phone'],
            //         $dataEmail['address'], $dataEmail['email'], $dataEmail['payment_method'],
            //         $dataEmail['shipping_method'], $dataEmail['comment'], $dataEmail['date']
            //     ],
            //         $dataEmail['html']
            //     );

            try {

                $email = $dataOder['email'];
                Mail::to($email)
                    ->send(new OrderEmail($dataEmail));

            } catch (\Exception $e) {

                $errorMessage = $e->getMessage();
                return response()->json([
                    'err' => $errorMessage,
                ]);
            }

            if ($memberId && !is_null($memberId)) {
                $date = Carbon::now('Asia/Ho_Chi_Minh');
                $dataSocket = [
                    'type' => 'orderStatus',
                    'socketId' => rand(9, 9999)
                    . Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('DDMMYYYY'),
                    'idOrder' => $orderNew->order_id ?? null,
                    'codeOrder' => $orderNew->order_code ?? null,
                    'statusOrder' => $orderNew->orderStatus->title ?? null,
                    'memberId' => $memberId ?? null,
                    'date' => $date,
                    'seen' => false,

                ];
                // try {
                //     $message=json_encode($dataSocket);

                //     $endpoint = 'https://socket.chinhnhan.net/api/notifies';
                //     $endpoint .= '?message='. urlencode($message);

                //     $response = Http::withHeaders([
                //         'Content-Type' => 'application/json',
                //         'Accept' => 'application/json',
                //     ])
                //         ->withoutVerifying()
                //         ->get($endpoint);

                //     if ($response->successful()) {
                //             $responseData = $response->json(); // Assuming response is JSON
                //             // Process $responseData if needed
                //         } else {
                //             $error = $response->toPsrResponse()->getReasonPhrase();
                //             echo "cURL Error: " . $error;
                //         }

                // } catch(Exception $e) {
                //     return ['error' => $e->getMessage()];
                // }
            }
            return response()->json([
                'status' => true,
                'orderId' => $orderNew->order_id,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function checkoutNew(Request $request)
    {
        try {
            $memberId = $request->userId ?? null;
            return $memberId;

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function inforOrder(Request $request, $orderId, $userId = null)
    {
        try {

            $order = OrderSum::with([
                'orderAddress' => function ($query) {
                    $query->select('order_id', 'district', 'ward', 'province', 'address', 'from_day', 'time');
                },
                'invoiceOrder' => function ($query) {
                    $query->select('order_id', 'taxCodeCompany', 'nameCompany', 'emailCompany', 'addressCompany');
                },
                'orderDetail' => function ($query) {
                    $query->with([
                        'product' => function ($query) {
                            $query->select('product_id', 'picture');
                        },
                    ]);
                },
            ])
                ->select('order_sum.*');
            if ($userId) {
                $order->where('mem_id', $userId);
            }

            $orderSum = $order->where('order_sum.order_id', $orderId)->first();
            $listProduct = $order->where('order_sum.order_id', $orderId)->first()->toArray();

            $listOrderDetail = OrderDetail::where('order_id', $orderId)->get()->groupBy('group_id');

            //return  $listOrderDetail;

            $arrProduct = [];
            foreach ($listOrderDetail as $key => $groupProduct) {
                if ($key == 0) {
                    foreach ($groupProduct as $product) {
                        $PresentDesUsing = PresentDesUsing::with('present')->where('IDOrderCode', $orderSum->order_code)
                            ->where('IdProduct', $product['item_id'])->first();
                        $arrProduct[] = [
                            'id' => $product['id'],
                            'order_id' => $product['order_id'],
                            'item_type' => $product['item_type'],
                            'item_id' => $product['item_id'],
                            'quantity' => $product['quantity'],
                            'ProductName' => $product['item_title'],
                            'Price' => $product['item_price'],
                            'subtotal' => $product['subtotal'],
                            'add_from' => $product['add_from'],
                            // 'checkPresent'=>$this->checkPresent($product['item_id']),
                            'presentOrder' => $PresentDesUsing,
                            'create_at' => $product['create_at'],
                            'Image' => $product['product']['picture'],
                            'typeCombo' => false,

                        ];
                    }
                } else {

                    $priceCombo = 0;
                    foreach ($groupProduct as $key1 => $product) {
                        $priceCombo += $product->item_price;
                        $PresentDesUsing = PresentDesUsing::with('present')->where('IDOrderCode', $orderSum->order_code)
                            ->where('IdProduct', $product['item_id'])
                            ->where('group_id', $key)
                            ->first();
                        $arrProduct[$key]["products"][] = [
                            'id' => $product['id'],
                            'order_id' => $product['order_id'],
                            'item_type' => $product['item_type'],
                            'item_id' => $product['item_id'],
                            'quantity' => $product['quantity'],
                            'ProductName' => $product['item_title'],
                            'Price' => $product['item_price'],
                            'subtotal' => $product['subtotal'],
                            'add_from' => $product['add_from'],
                            // 'checkPresent'=>$this->checkPresent($product['item_id']),
                            'presentOrder' => $PresentDesUsing,
                            'create_at' => $product['create_at'],
                            'Image' => $product['product']['picture'],

                        ];
                    }
                    $ProductGroup = ProductGroup::where('id_group', $key)->first();
                    if ($ProductGroup) {
                        $arrProduct[$key]['typeCombo'] = true;
                        $arrProduct[$key]["GroupId"] = $key;
                        $arrProduct[$key]['quantity'] = $groupProduct[0]['quantity'];
                        $arrProduct[$key]['title'] = $ProductGroup->titleGroup;
                        $arrProduct[$key]['Price'] = $priceCombo - $ProductGroup->discount;
                        $arrProduct[$key]['discountCombo'] = $ProductGroup->discount;

                    }

                }
            }
            $listCart = [];
            foreach ($arrProduct as $item) {
                $listCart[] = $item;
            }

            // $arrProduct=[];
            // foreach($listProduct['order_detail'] as $product){
            //     $arrProduct[]=[
            //         'id'=>$product['id'],
            //         'order_id'=>$product['order_id'],
            //         'item_type'=>$product['item_type'],
            //         'item_id'=>$product['item_id'],
            //         'quantity'=>$product['quantity'],
            //         'ProductName'=>$product['item_title'],
            //         'Price'=>$product['item_price'],
            //         'subtotal'=>$product['subtotal'],
            //         'add_from'=>$product['add_from'],
            //         'checkPresent'=>$this->checkPresent($product['item_id']),
            //         'create_at'=>$product['create_at'],
            //         'Image'=>$product['product']['picture']
            //     ];
            // }

            $voucher = CouponDesUsing::where('IDOrderCode', $orderSum->order_code)->first();
            $inforVoucher = null;
            if ($voucher) {
                $inforVoucher = CouponDes::with('coupon')->where('idCouponDes', $voucher->idCouponDes)->first();
            }
            if ($orderSum) {
                return response()->json([
                    'status' => true,
                    'orderSum' => $orderSum,
                    'listProduct' => $listCart,
                    'inforVoucher' => $inforVoucher,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Not found',
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }

    }
    public function updateOrder(Request $request)
    {
        try {

            $orderId = $request->orderId;

            $orderSum = OrderSum::where('order_id', $orderId)->first();
            $orderSum->payment_method = $request->payment_method;
            $orderSum->save();

            //     $MailTemplate=MailTemplate::where('name','OrdertoGuest')->first();

            //     $dataProduct=[];
            //     if($orderSum->orderDetail && count($orderSum->orderDetail)>0){
            //         foreach($orderSum->orderDetail as $product){
            //             $dataProduct[]=[
            //                 'productId'=>$product->item_id,
            //                 'order_id'=>$product ->order_id??null,
            //                 'item_price'=>$product ->item_price??null,
            //                 'item_id'=> $product->item_id??null,
            //                 'quantity'=>$product -> quantity??null,
            //                 'item_title'=>$product-> item_title??null,
            //                 'subtotal'=>$product ->subtotal??null,
            //                 'picture'=> Product::where('product_id', $product->item_id)->first()->picture??null
            //             ];
            //         }
            //     }

            //     $test='<div class="table_member">
            //     <table style="border-collapse: collapse; width: 100%;" class="table-order table table-bordered table-hover">
            //     <thead>
            //     <tr>
            //     <th style="border: 1px solid black;" scope="col">Id đơn hàng</th>
            //     <th style="border: 1px solid black; font-weight: bold;" scope="col">Hình ảnh sản phẩm</th>
            //         <th style="border: 1px solid black; font-weight: bold;"  scope="col">Tên sản phẩm</th>
            //         <th style="border: 1px solid black; font-weight: bold;" scope="col">Giá sản phẩm</th>
            //         <th style="border: 1px solid black; font-weight: bold;" scope="col">Số lượng</th>
            //         <th style="border: 1px solid black; font-weight: bold;" scope="col">Tổng cộng</th>
            //     </tr>
            //     </thead>
            //     <tbody>';
            //     foreach($dataProduct as $item){
            //         $test.='<tr style="border: 1px solid black;">
            //             <td style="border: 1px solid black; text-align: center;" class="center">'.$item['order_id'].'</td>
            //             <td style="border: 1px solid black; text-align: center;" class="center"><img width="100px" height="100px" src="http://api.chinhnhan.com/uploads/'.$item['picture'].'"></td>
            //             <td style="border: 1px solid black; text-align: center;" class="center">'.$item['item_title'].'</td>
            //             <td style="border: 1px solid black; text-align: center;" class="center">'.number_format($item['item_price'], 0, '', ',').'VNĐ</td>
            //             <td style="border: 1px solid black; text-align: center;" class="center">'.$item['quantity'].'</td>
            //             <td style="border: 1px solid black; text-align: center;" class="center">'.number_format( $item['subtotal'], 0, '', ',').'VNĐ</td>

            //     </tr>';
            // }
            //     $test.='</tbody>
            //     </table>
            // </div>';

            // if(is_null($orderSum->orderAddress->district) &&
            // is_null($orderSum->orderAddress->ward) &&
            //  is_null($orderSum->orderAddress->province)){
            //     $dataEmail_address='245B Trần Quang Khải, phường Tân Định, quận 1';
            // }else{
            //     $dataEmail_address= $orderSum->orderAddress->address.','.$orderSum->orderAddress->province.','. $orderSum->orderAddress->ward.','. $orderSum->orderAddress->district;
            // }

            // $dataEmail=[
            //     'domain'=>'http://web.chinhnhan.com/',
            //     'name'=>$orderSum->d_name,
            //     'order_code'=>$orderSum->order_code,
            //     'phone'=>$orderSum->d_phone,
            //     'address'=> $dataEmail_address,
            //     'email'=>$orderSum->d_email,
            //     'payment_method'=>$orderSum->payment_method,
            //     'shipping_method'=>$orderSum->shipping_method,
            //     'comment'=> $orderSum->comment,
            //     'date'=>Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('HH:mm:ss, DD/MM/YYYY'),
            //     'table_cart'=>$test,
            //     'html'=>$MailTemplate->description
            //     ];

            //     $dataEmail['html'] = str_replace(
            //         ['[domain]', '[name]','[table_cart]','[order_code]', '[phone]',
            //         '[address]','[email]', '[payment_method]','[shipping_method]'
            //         ,'[comment]','[date]'
            //     ],
            //         [$dataEmail['domain'], $dataEmail['name'], $dataEmail['table_cart'],
            //         $dataEmail['order_code'], $dataEmail['phone'],
            //         $dataEmail['address'], $dataEmail['email'], $dataEmail['payment_method'],
            //         $dataEmail['shipping_method'], $dataEmail['comment'], $dataEmail['date']
            //     ],
            //         $dataEmail['html']
            //     );
            //    Mail::to($orderSum->d_email)->send(new TestMail($dataEmail));
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
