<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductDesc;
use App\Models\OrderSum;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\BrandDesc;
use App\Models\Brand;
use App\Models\StatisticsPages;
use Illuminate\Support\Str;
use App\Exports\productTechnologyExport;
use App\Exports\AllProductProperties;
use App\Exports\StatisticsFileExport;
use App\Exports\OrderSumExcelExport;
use Illuminate\Support\Facades\Auth;
use Gate;
use Carbon\Carbon;

class ImportExportController extends Controller
{
    public function getTechnology($id){
        $dataTechnology = [];
        $options = DB::table('product_cat_option_desc')->pluck('title', 'op_id')->all();
        $product = Product::where('product_id', $id)->first();

        //-------------------------------------------------
        $data = $product->technology;
        $data = preg_replace_callback(
                '/(?<=^|\{|;)s:(\d+):\"(.*?)\";(?=[asbdiO]\:\d|N;|\}|$)/s',
                function($m){
                    return 's:' . strlen($m[2]) . ':"' . $m[2] . '";';
                },
                $data);

            $data = unserialize($data);
        //------------------------------------------------

        $techs =$data;

        if (is_array($techs)) {
            foreach ($techs as $key => $techValue) {
                if (isset($options[$key])) {
                    $dataTechnology[] = [
                        'catOption' => $options[$key],
                        'nameCatOption' => $techValue !== "" ? $techValue : null
                    ];
                }
            }
        }
        return $dataTechnology;
    }
    public function exportStatisticsPagesExcel(Request $request){
        try{
            $fromDate =  $request['fromDate'];
            $toDate = $request['endDate'];
            $StatisticsPages=StatisticsPages::with('member')->orderBy('id_static_page','desc');
            if(isset($fromDate) && isset($toDate)){

                $StatisticsPages->whereBetween('date', [$fromDate, $toDate]);
            }

            $StatisticsPages= $StatisticsPages->get();

            $data=[];
            foreach( $StatisticsPages as $Statistics){
                $data[]=[
                    'url'=>$Statistics->url,
                    'date'=>Carbon::createFromTimestamp($Statistics->date)->toDateString(),
                    'count'=>$Statistics->count,
                    'module'=>$Statistics->module,
                    'action'=>$Statistics->action,
                    'ip'=>$Statistics->ip,
                    'name'=>$Statistics->member->username??'Unknow'
                ];

            }
            $fileName = 'statistics_'.date('Y_m_d_H_i_s').'.xlsx';
            $export = new StatisticsFileExport($data);
            $fileContents = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
            $headers = [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
            ];
            return response($fileContents, 200, $headers);


        }catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json( $response, 500 );
        }

    }
    public function exportTechnologyExcel(Request $request){



        $categoryId=$request['categoryId'];
        $brandId=$request['brandId'];

        $product=Product::select('product_id','cat_id','cat_list','maso','macn','brand_id','technology')
        ->where(function ($query) use ($categoryId, $brandId) {
            $query->whereRaw('FIND_IN_SET(?, cat_list)', [$categoryId]);
        });


        if($brandId != 0)
        {
            $product= $product->where('brand_id',$brandId)->get();
        }
        else{
            $product=$product->get();
        }
        //return $product;
        foreach($product as $key=>$value)
        {

            $dataValue=$this->getTechnology($value->product_id);

            $product[$key]['technology'] = $dataValue;

        }

        $data=[];
        $listTech=[];

        foreach($product as $key => $item){
            foreach ($item['technology'] as $key => $value) {
                if(!in_array($value['catOption'], $listTech)){
                    $listTech[]=$value['catOption'];
                }
            }
            $data['infoProduct'][]=[
                "makho"=>isset($item['macn'])?$item['macn']: '',
                "tensanpham"=>isset($item->productDesc) ?$item->productDesc->title: $item['TenHH'],
                'catOp'=>''
            ];
        }
        $data['listTech']=$listTech;

        foreach($product as $key => $item){
            $catOp=[];
            foreach($listTech as $key1=>$item1){
                foreach ($item['technology'] as $key2 => $value2) {
                    if($item1==$value2['catOption']){
                        // $catOp[]=$value2['nameCatOption'];
                        array_push($catOp,[
                            'catOption'=> $value2['catOption'],
                            'nameCatOption' => $value2['nameCatOption']]);
                        }
                    }
                }
            $data['infoProduct'][$key]['catOp']=$catOp;
        }
        $fileName = 'productListTechnology_' . date('Y_m_d_H_i_s') . '.xlsx';
        $export = new productTechnologyExport($data);
        $fileContents = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
        ];
        return response($fileContents, 200, $headers);
    }
    public function exportAllProductProperties(Request $request){
        $categoryId=$request['categoryId'];
        $brandId=$request['brandId'];


        $product=Product::with('productDesc','categoryDes')
        ->where(function ($query) use ($categoryId, $brandId) {
            $query->whereRaw('FIND_IN_SET(?, cat_list)', [$categoryId]);
        });
        if($brandId != 0)
        {
            $product= $product->where('brand_id',$brandId)->get();
        }
        else{
            $product=$product->get();
        }
        foreach($product as $key=>$value)
        {
            $dataValue=$this->getTechnology($value->product_id);
            $product[$key]['technology'] = $dataValue;
        }
        $data=[];
        $listTech=[];

        foreach($product as $key => $item){

            foreach ($item['technology'] as $key => $value) {
                if(!in_array($value['catOption'], $listTech)){
                    $listTech[]=$value['catOption'];
                }
            }

            $data['infoProduct'][]=[
                "makho"=>isset($item['macn'])?$item['macn']:null,
                "tensanpham"=>isset($item['productDesc']) ?$item['productDesc']['title']:$item['TenHH'],
                "catname"=>$item['categoryDes']['cat_name']??null,
                "maso"=>$item['maso']??null,
                "macn"=>$item['macn']??null,
                "price"=>$item['price']??null,
                "price_old"=>$item['price_old']??null,
                "brand_name"=>$item['brandDesc']['title']??null,
                "title"=>isset($item['productDesc']) ?$item['productDesc']['title']:$item['TenHH'],
                "picture"=>count($item['productPicture'])==0 ? " không có hình" :"có " .count($item['productPicture'])." hình",
                "static"=>$item['stock']==1 ? "còn hàng":"hết hàng",
                "display"=>$item['display']==1?"có":"không",
                'technology'=>count($this->getTechnology($item['product_id']))>0 ? "có":"không",
                'describe'=>isset($item['productDesc']) ? Str::length($item['productDesc']['description'])>300 ? "có" : "quá ngắn" : "không",
                'catOp'=>''
            ];

        }
        $data['listTech']=$listTech;


        foreach($product as $key => $item){
            $catOp=[];
            foreach($listTech as $key1=>$item1){
                foreach ($item['technology'] as $key2 => $value2) {
                    if($item1==$value2['catOption']){
                        // $catOp[]=$value2['nameCatOption'];
                        array_push($catOp,[
                            'catOption'=> $value2['catOption'],
                            'nameCatOption' => $value2['nameCatOption']]);
                        }
                    }
                }
            $data['infoProduct'][$key]['catOp']=$catOp;
        }
        $fileName = 'productProperties_' . date('Y_m_d_H_i_s') . '.xlsx';
        $export = new AllProductProperties($data);
        $fileContents = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Security-Policy'=> "upgrade-insecure-requests" ,
        ];
        return response($fileContents, 200, $headers);
    }
    public function exportOrder(Request $request){
        try{
            $search= $request->all();
            $orderSum=OrderSum::select('order_id','order_code','MaKH','gender','d_name','d_phone','d_email',
            'total_cart','shipping_method','payment_method','status','date_order','mem_id',
            'date_order_status1', 'date_order_status2', 'date_order_status3', 'date_order_status4',
             'date_order_status5', 'date_order_status6', 'date_order_status7'
            )->orderBy('order_id','desc');

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

            $orderSum=$orderSum->orderBy('order_id','desc')->get();
            $data=[];

            foreach($orderSum as $item){
                $data[]=[
                    'order_id'=>$item->order_id,
                    'order_code'=>$item->order_code,
                    'gender'=>$item->gender,
                    'd_name'=>$item->d_name,
                    'd_phone'=>$item->d_phone,
                    'd_email'=>$item->d_email,
                    'total_cart'=>$item->total_cart,
                    'shipping_method'=>$item->shippingMethod->title??'',
                    'payment_method'=>$item->paymentMethod->title??'',
                    'status'=>$item->orderStatus->title??'',
                    'date_order'=>Carbon::createFromTimestamp($item->date_order)->format('d/m/Y'),
                    'date_order_status1'=>$item->date_order_status1??'',
                    'date_order_status2'=>$item->date_order_status2??'',
                    'date_order_status3'=>$item->date_order_status3??'',
                    'date_order_status4'=>$item->date_order_status4??'',
                    'date_order_status5'=>$item->date_order_status5??'',
                    'date_order_status6'=>$item->date_order_status6??'',
                    'date_order_status7'=>$item->date_order_status7??'',

                ];
            }
            //OrderSumExcelExport
            //return  $data;
            $fileName = 'order_' . date('Y_m_d_H_i_s') . '.xlsx';
            $export = new OrderSumExcelExport($data);

            $fileContents = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

            $headers = [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
            ];

            return response($fileContents, 200, $headers);
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
