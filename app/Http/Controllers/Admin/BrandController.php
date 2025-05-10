<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Admin;
use App\Models\BrandDesc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {



        try {

            $admin=Admin::where('id',Auth::guard('admin')->user()->id)->first();
           //return Gate::allows('QUẢN LÝ SẢN PHẨM.Thương hiệu sản phẩm.manage');
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Thương hiệu sản phẩm.manage')){
                $now = date('d-m-Y H:i:s');
                $stringTime = strtotime($now);
                $now = date('d-m-Y H:i:s');
                $stringTime = strtotime($now);
                DB::table('adminlogs')->insert([
                    'admin_id' => Auth::guard('admin')->user()->id,
                    'time' =>  $stringTime,
                    'ip'=> $request->ip(),
                    'action'=>'show all brand',
                    'cat'=>'brand',
                ]);

                if($request->input('data')==""){
                    $query = Brand::with('brandDesc')->orderBy('brand_id','desc');
                }
                else{
                    $query = Brand::with('brandDesc')
                    ->whereHas('brandDesc', function ($query) use ($request) {$query->where("title", 'like', '%' . $request->input('data') . '%');})->orderBy('brand_id','desc');
                }
                $offset = $request->page ? $request->page : 1;
                $total = count($query->get());
                if($request->type=="all"){
                    $list=  $query->get();
                }else{
                    $list=  $query->limit(15)
                    ->offset(($offset-1)*15)->get();
                }


                $data=[];
                foreach($list as $key => $value) {

                    $id= $value->brand_id;
                    $data[] = [
                        'brandId' => $id,
                        'title' => $value->brandDesc->title,
                        'friendlyUrl' => $value->brandDesc->friendly_url,
                        'picture'=>$value->picture

                    ];
                }
                $response = [
                    'status' => true,
                    'total'=>$total,
                    'list' => $data,
                ];
                return response()->json($response, 200);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json($response, 500);
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
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'add a brand',
                'cat'=>'brand',
            ]);


        $brand = new Brand();
        $brandDesc = new BrandDesc();
        //$disPath = public_path();
        try {
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Thương hiệu sản phẩm.add')){
            $filePath = '';
            if ( $request->picture != null ) {

                //return $request->picture;
                $DIR = 'uploads/brand';
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];

                //return response()->json( $file_chunks );
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                // $file = $DIR .'\\'. $name . '.png';
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'brand/'.$name . '.png';

                file_put_contents( $file,  $base64Img );
            }
            //--------------
            // $image = $request->file('picture');

            // // Define the upload path
            // $uploadPath = '/uploads/brand';

            // // Generate a unique name for the image
            // $imageName = time() . '.' . $image->getClientOriginalExtension();

            // // Move the image to the upload path
            // $image->move(public_path($uploadPath), $imageName);


            //return $request->file('picture');
            // $file = $request->file('picture');
            // $path = public_path('uploads/brand');

            // $fileName = uniqid(). '.png';

            // $name = $file->getClientOriginalName();

            // $filePath = 'brand/'.$fileName;
            // $file->move($path, $fileName);


            $brand->fill([
                // 'cat_id' => 0,
                'picture' =>$filePath,
                'focus' => '0',
                'menu_order' => Brand::max('cat_id')+1,
                'views' => '1',
                'display' => $request->input('display'),
                'date_post' => strtotime('now'),
                'date_update' => strtotime('now'),
                // 'id' => 0,
            ])->save();

            $brandDesc->brand_id = $brand->brand_id;
            $brandDesc->title = $request->input('title');
            $brandDesc->description = $request->input('description');
            $brandDesc->friendly_url = $request->input('friendly_url');
            $brandDesc->friendly_title = $request->input('friendly_title');
            $brandDesc->metakey = $request->input('metakey');
            $brandDesc->metadesc = $request->input('metadesc');
            $brandDesc->lang ='vi';
            $brandDesc->save();

            // DB::table('adminlogs')->insert([
            //     'id' => Auth::guard('admin')->user()->id,
            //     'time' => Carbon::now(),
            //     'ip'=> $request->ip(),
            //     'action'=>'add brand',
            //     'cat'=>'order',
            //     'nameOrId'=> $brandDesc->brand_id
            // ]);
            $response = [
                'status' => true,
                'brand' => $brand,
                'brandDesc' => $brandDesc
            ];
            return response()->json($response, 200);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
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

        // return $id;

        try {

            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'edit a brand',
                'cat'=>'brand',
            ]);
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Thương hiệu sản phẩm.edit')){
            $listBrandDesc = Brand::with('brandDesc')->find($id);
            return response()->json([
            'status'=> true,
            'brand' => $listBrandDesc
            ]);
            }else{
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        try {
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'update a brand',
                'cat'=>'brand',
            ]);

            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Thương hiệu sản phẩm.update')){
            //$disPath = public_path();
            $listBrand = Brand::Find($id);
            if ( $request->picture != null &&  $listBrand->picture != $request->picture ) {



                $DIR = 'uploads/brand';
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];

                //return response()->json( $file_chunks );
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'brand/'.$name . '.png';

                file_put_contents( $file,  $base64Img );
                // $file = $request->file('picture');

                // $path = public_path('uploads/brand');

                // $fileName = uniqid(). '.png';


                // $filePath = 'brand/'.$fileName;
                // $file->move($path, $fileName);
            } else {
                $filePath =  $listBrand->picture;
            }


            // $listBrand->cat_id = 0;
            $listBrand->picture = $filePath;
            $listBrand->focus = '0';
            $listBrand->views = '1';
            $listBrand->display = $request->input('display');
            $listBrand->date_post = strtotime('now');
            $listBrand->date_update = strtotime('now');
            // $listBrand->id = 0;
            $listBrand->save();

            $brandDesc = BrandDesc::where('brand_id', $id)->first();
            if ($brandDesc) {

                $brandDesc->title = $request->input('title')??$brandDesc->title;
                $brandDesc->description = $request->input('description')?? $brandDesc->description;
                $brandDesc->friendly_url = $request->input('friendly_url')??$brandDesc->friendly_url;
                $brandDesc->friendly_title = $request->input('friendly_title')??$brandDesc->friendly_title;
                $brandDesc->metakey = $request->input('metakey')??$brandDesc->metakey ;
                $brandDesc->metadesc = $request->input('metadesc')??$brandDesc->metadesc;
                $brandDesc->lang ='vi';
                $brandDesc->save();
            }
            return response()->json([
                'status'=>true,
                'data'=> $listBrand
            ]);
        }else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' =>  $stringTime,
            'ip'=> $request->ip(),
            'action'=>'delete a brand',
            'cat'=>'brand',
        ]);
        try {
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Thương hiệu sản phẩm.del')){
            $list = Brand::Find($id)->delete();
            BrandDesc::where('brand_id',$id)->delete();

            return response()->json([
                'status'=>true
            ]);
        }else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }
    public function deleteAllBrand(Request $request){
        try{
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Thương hiệu sản phẩm.del')){
            $arr =$request->data;
            if($arr)
            {
                foreach ($arr as $item) {
                    $list = Brand::where('brand_id',$item)->delete();
                    BrandDesc::where('brand_id',$item)->delete();

                }
            }
            else
            {
                return response()->json([
                    'status'=>false,
                ],422);
            }
            return response()->json([
                'status'=>true,
            ],200);
        }else {
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
