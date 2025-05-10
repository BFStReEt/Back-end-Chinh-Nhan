<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductDesc;
use App\Models\ServiceDesc;
use App\Models\PromotionDesc;
use App\Models\GuideDesc;
use App\Models\NewsDesc;
use App\Models\AboutDesc;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;
use App\Models\MailTemplate;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class SeoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public static function paginate($items, $perPage = 5, $page = null)
    {
         $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
         $total = count($items);
         $currentpage = $page;
         $offset = ($currentpage * $perPage) - $perPage ;
         $itemstoshow = array_slice($items , $offset , $perPage);

         return new LengthAwarePaginator($itemstoshow ,$total   ,$perPage);
    }
    public function index(Request $request)
    {
        try {
            $data = [];
            $data1=[];
            $data2=[];
            $data3=[];
            $data4=[];
            $data5=[];

            if(Gate::allows('SEO-MẠNG XÃ HỘI.Quản lý link website.manage')){

            $ProductDesc = ProductDesc::with('product')
                ->where("friendly_url", 'like', '%' . $request->data . '%')
                ->select('product_id', 'friendly_url')
                ->get();
            $ServiceDesc=ServiceDesc::with('service')
            ->where("friendly_url", 'like', '%' . $request->data . '%')
            ->select('service_id','friendly_url')->get();

            $PromotionDesc=PromotionDesc::with('promotion')
            ->where("friendly_url", 'like', '%' . $request->data . '%')
            ->select('promotion_id','friendly_url')->get();

            $GuideDesc=GuideDesc::with('guide')
            ->where("friendly_url", 'like', '%' . $request->data . '%')
            ->select('guide_id','friendly_url')->get();

            $NewsDesc=NewsDesc::with('news')
            ->where("friendly_url", 'like', '%' . $request->data . '%')
            ->select('news_id','friendly_url')->get();

            $AboutDesc=AboutDesc::with('about')
            ->where("friendly_url", 'like', '%' . $request->data . '%')
            ->select('about_id','friendly_url')->get();




            foreach ($ProductDesc as $item) {
                $data[] = [
                    'slug' => $item->friendly_url,
                    // 'friendly_url'=>'product-detail/'.$item->friendly_url,
                    'module'=>'product',
                    'action' => 'detail',
                    'itemId' => $item->product_id,
                    'date' => $item->product->date_post ?? null,
                ];
            }


            foreach ($ServiceDesc as $item) {
                $data1[] = [
                    'slug' => $item->friendly_url,
                    // 'friendly_url'=>'detail-service/'.$item->friendly_url,
                    'module'=>'service',
                    'action' => 'detail',
                    'itemId' => $item->service_id,
                    'date' => $item->service->date_post ?? null,
                ];
            }

            foreach ($PromotionDesc as $item) {
                $data2[] = [
                    'slug' => $item->friendly_url,
                    // 'friendly_url'=>'promotion/'.$item->friendly_url,
                    'module'=>'promotion',
                    'action' => 'detail',
                    'itemId' => $item->promotion_id,
                    'date' => $item->promotion->date_post ?? null,
                ];
            }

            //detail-guide/
            foreach ($GuideDesc as $item) {
                $data3[] = [
                    'slug' => $item->friendly_url,
                    // 'friendly_url'=>'detail-guide/'.$item->friendly_url,
                    'module'=>'guide',
                    'action' => 'detail',
                    'itemId' => $item->guide_id,
                    'date' => $item->guide->date_post ?? null,
                ];
            }

            foreach ($NewsDesc as $item) {
                $data4[] = [
                    'slug' => $item->friendly_url,
                    'module'=>'news',
                    'action' => 'detail',
                    'itemId' => $item->news_id,
                    'date' => $item->news->date_post ?? null,
                ];
            }
            //detail-about/
            foreach ($AboutDesc as $item) {
                $data5[] = [
                    'slug' => $item->friendly_url,
                    // 'friendly_url'=>'detail-about/'.$item->friendly_url,
                    'module'=>'about',
                    'action' => 'detail',
                    'itemId' => $item->about_id,
                    'date' => $item->about->date_post ?? null,
                ];
            }

            if($request->module=="About"){
                $mergedArray = array_merge($data5);

            }else if($request->module=="Guide"){
                $mergedArray = array_merge($data3);

            }else if($request->module=="News"){
                $mergedArray = array_merge($data4);

            }else if($request->module=="Product"){
                $mergedArray = array_merge($data);

            }else if($request->module=="Service"){
                $mergedArray = array_merge($data1);
            }else if($request->module=="Promotion"){
                $mergedArray = array_merge($data2);
            }else if($request->module=="All"){
                $mergedArray = array_merge($data, $data1,$data2,$data3,$data4,$data5);
            }


            shuffle($mergedArray);

            $mergedArray=$this->paginate($mergedArray,30);
            // /return $mergedArray;

            return response()->json([
                'status'=>true,
                'data'=>$mergedArray
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
    public function testdb(){

        return $options = Admin::get();
    }
    public function testMail(){
        //return 1111;
        return $options = DB::table('admin')->all();

        $MailTemplate=MailTemplate::where('name','OrdertoGuest')->first();

        $data = [
            'listProduct' => [
                ['order_id' => 1, 'picture' => 'product/09_2024/lenovo-v15-g4-iru-i5-83a100f3vn.jpg',
                'item_title'=>'Laptop Asus ExpertBook B1402CB I7-1255U/ 8GB / 512GB SSD/14INCH FHD/WIN 11 HOME/B1402CBANK1717W - 71027653',
                'item_price'=>'15622000','quantity'=>1,'subtotal'=>'15622000'],
                ['order_id' => 2, 'picture' => 'product/09_2024/lenovo-v15-g4-iru-i5-83a100f3vn.jpg',
                'item_title'=>'Laptop Asus ExpertBook B1402CB I7-1255U/ 8GB / 512GB SSD/14INCH FHD/WIN 11 HOME/B1402CBANK1717W - 71027653',
                'item_price'=>'15622000','quantity'=>1,'subtotal'=>'15622000'],

            ]
        ];
        $test='<div class="table_member">
        <table style="border-collapse: collapse; width: 100%;" class="table-order table table-bordered table-hover">
        <thead>
        <tr>
        <th style="border: 1px solid black;" scope="col">Id đơn hàng</th>
        <th style="border: 1px solid black; font-weight: bold;" scope="col">Hình ảnh sản phẩm</th>
            <th style="border: 1px solid black; font-weight: bold;"  scope="col">Tên sản phẩm</th>
            <th style="border: 1px solid black; font-weight: bold;" scope="col">Giá sản phẩm</th>
            <th style="border: 1px solid black; font-weight: bold;" scope="col">Số lượng</th>
            <th style="border: 1px solid black; font-weight: bold;" scope="col">Tổng cộng</th>
        </tr>
        </thead>
        <tbody>';
        foreach($data['listProduct'] as $item){
            $test.='<tr style="border: 1px solid black;">
                <td style="border: 1px solid black; text-align: center;" class="center">'.$item['order_id'].'</td>
                <td style="border: 1px solid black; text-align: center;" class="center"><img width="100px" height="100px" src="http://api.chinhnhan.com/uploads/'.$item['picture'].'"></td>
                <td style="border: 1px solid black; text-align: center;" class="center">'.$item['item_title'].'</td>
                <td style="border: 1px solid black; text-align: center;" class="center">'.number_format($item['item_price'], 0, '', ',').'VNĐ</td>
                <td style="border: 1px solid black; text-align: center;" class="center">'.$item['quantity'].'</td>
                <td style="border: 1px solid black; text-align: center;" class="center">'.number_format( $item['subtotal'], 0, '', ',').'VNĐ</td>

        </tr>';
    }
        $test.='</tbody>
        </table>
    </div>';


        $dataEmail=[
            'domain'=>'nguyenkim.com',
            'name'=>'longhoang',
            'order_code'=>'1123',
            'phone'=>'7685545656',
            'address'=>'hcm',
            'email'=>'long@gmail.com',
            'payment_method'=>'payment_method',
            'shipping_method'=>'shipping_method',
            'comment'=>'comment',
            'date'=>'date',
            'table_cart'=>$test,

            'html'=>$MailTemplate->description
        ];
        $dataEmail['html'] = str_replace(
            ['[domain]', '[name]','[table_cart]','[order_code]', '[phone]',
            '[address]','[email]', '[payment_method]','[shipping_method]'
            ,'[comment]','[date]'
        ],
            [$dataEmail['domain'], $dataEmail['name'], $dataEmail['table_cart'],
            $dataEmail['order_code'], $dataEmail['phone'],
            $dataEmail['address'], $dataEmail['email'], $dataEmail['payment_method'],
            $dataEmail['shipping_method'], $dataEmail['comment'], $dataEmail['date']
        ],
            $dataEmail['html']
        );


        try {

            $email ='long542.nt@gmail.com';
            Mail::to($email)
               ->send(new TestMail($dataEmail));
            return 111;
        } catch (\Exception $e) {

            $errorMessage = $e->getMessage();
            return response()->json([
                 'err'=> $errorMessage
            ]);
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
