<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\News;
use App\Models\NewsDesc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
use Illuminate\Support\Facades\Storage;
class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'show all news',
                'cat'=>'news',
            ]);

            if(Gate::allows('QUẢN LÝ TIN TỨC.Quản lý tin tức.manage')){
            $category=$request['category'];
            $query=News::with('newsDesc','categoryDesc');
            if($request->data == 'undefined' || $request->data =="")
            {
                $list = $query;
            }
            else{
                $list = $query->whereHas('newsDesc', function ($query) use ($request) {
                    $query->where("title", 'like', '%' . $request->data . '%');
                });
            }
            if(isset($category)){
                $list=$query->where('cat_id',$category);
            }

            $news=$list->orderBy('news_id','desc')->paginate(10);
            $response = [
                'status' => true,
                'list' => $news,
            ];
            return response()->json( $response, 200 );
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
            'action'=>'add a news',
            'cat'=>'news',
        ]);
        //return $request->picture;

        //$disPath = public_path();
       
        $news = new News();
        $newsDesc = new NewsDesc();
        try {
            if(Gate::allows('QUẢN LÝ TIN TỨC.Quản lý tin tức.add')){
            $filePath = '';
            if ( $request->picture != null )
            {
                //$DIR = $disPath.'\uploads\news';
                $DIR = 'uploads/news';
              
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];
                //return response()->json( $file_chunks );
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                // $file = $DIR .'\\'. $name . '.png';
                // $filePath = 'news/'.$name . '.png';
                // file_put_contents( $file,  $base64Img );
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'news/'.$name . '.png';
                file_put_contents( $file,  $base64Img );
            }
            $cat_list = implode( ',', $request->input( 'cat_id' ) );

            // $product = implode( ',', $request->input( 'product' ) );

            $cat_id = strtok( $cat_list, ',' );
           // $cat_id=$request->input( 'cat_id' );



            $news->fill( [
                'cat_id' => $cat_id?$cat_id:0,
                'cat_list'=> $cat_list?$cat_list:0,
                'picture' => $filePath,
                'focus' => '0',
                'focus_order' => '0',
                'views' => 0,
                'display' => $request->input( 'display' ),
                'menu_order' => 0,
                'adminid' => 1,
                'date_post'=>strtotime( 'now' ),
                'date_update'=>strtotime( 'now' )
            ] )->save();
            $newsDesc->news_id = $news->news_id;
            // $newsDesc->product_id = $product?$product:0;
            $newsDesc->title = $request->input( 'title' );
            $newsDesc->description = $request->input( 'description' );
            $newsDesc->short = $request->input( 'short' );

            $newsDesc->friendly_url = $request->input( 'friendly_url' ) ? $request->input( 'friendly_url' ) : Str::slug( $request->input( 'title' ) );
            $newsDesc->friendly_title = $request->input( 'friendly_title' );
            $newsDesc->metakey = $request->input( 'metakey' );
            $newsDesc->metadesc = $request->input( 'metadesc' );
            $newsDesc->lang = 'vi';
            $newsDesc->save();
            $response = [
                'status' => true,
                'news' => $news,
                'newsDesc' => $newsDesc,
            ];
            return response()->json( $response, 200 );
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
        try {
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'edit a news',
                'cat'=>'news',
            ]);
            if(Gate::allows('QUẢN LÝ TIN TỨC.Quản lý tin tức.edit')){
            $news = News::with('newsDesc')->find($id);
            $list_cate = explode(',',$news->cat_list);
            $i = count($list_cate);
            for($j=0; $j<$i; $j++)
            {
                $save[] = (int)$list_cate[$j];
            }
            $news['list_cate']=$save;

            $list_product = explode(',',$news->newsDesc->product_id);

            for($j=0; $j<count($list_product); $j++)
            {
                $product[] = (int)$list_product[$j];
            }
            $news['product']=$product;
            return response()->json([
                'status'=> true,
                'news' => $news
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        }
        catch ( \Exception $e ) {
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
            'action'=>'update a news',
            'cat'=>'news',
        ]);

        //https://api.chinhnhan.net
        //$disPath = public_path();
        //https://api.chinhnhan.net/
       // $disPath = 'https://api.chinhnhan.net';
       
        $news = News::where( 'news_id', $id )->first();
        //return $news;

        $newsDesc =  NewsDesc::where( 'news_id', $id )->first();

        //return  $newsDesc;
        try {

            if(Gate::allows('QUẢN LÝ TIN TỨC.Quản lý tin tức.update')){
            $filePath = '';
            if ( $request->picture != null && $request->picture != $news ->picture )
            {
                //return $request->picture;
                $DIR = 'uploads/news';
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];
                //return response()->json( $file_chunks );
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'news/'.$name . '.png';
                file_put_contents( $file,  $base64Img );
            } else {
                $filePath = $news->picture;
            }
            $cat_list = implode( ',', $request->input( 'cat_id' ) );

            // $product = implode( ',', $request->input( 'product' ) );
            $cat_id = strtok( $cat_list, ',' );
           // $cat_id=$request->input( 'cat_id' );


            $news->cat_id = $cat_id?$cat_id:0;

            $news->cat_list = $cat_list?$cat_list:0;
            $news->picture = $filePath;
            $news->focus = '0';
            $news->focus_order = '0';
            $news->views = 0;
            $news->display = $request->input( 'display' );
            $news->menu_order = 0;
            $news->adminid = 1;
            $news->date_post= strtotime( 'now' );
            $news->date_update = strtotime( 'now' );
            //'date_post'=>strtotime( 'now' ),
            //'date_update'=>strtotime( 'now' )
            $news->save();

            //$newsDesc->news_id = $news->news_id;
            // $newsDesc->product_id = $product?$product:0;
            $newsDesc->title = $request->input( 'title' );
            $newsDesc->description = $request->input( 'description' );
            $newsDesc->short = $request->input( 'short' );
            $newsDesc->friendly_url = $request->input( 'friendly_url' );
            $newsDesc->friendly_title = $request->input( 'friendly_title' );
            $newsDesc->metakey = $request->input( 'metakey' );
            $newsDesc->metadesc = $request->input( 'metadesc' );
            $newsDesc->lang = 'vi';
            $newsDesc->save();

            $response = [
                'status' => 'success',
                'news' => $news,
                'newsDesc' => $newsDesc,
            ];
            return response()->json( $response, 200 );
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
    public function destroy(Request $request,string $id)
    {
        try {
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'delete a news',
                'cat'=>'news',
            ]);
            if(Gate::allows('QUẢN LÝ TIN TỨC.Quản lý tin tức.del')){
            $list = News::Find($id)->delete();
            return response()->json([
                'status'=>true
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        }
        catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json( $response, 500 );
        }
    }
    public function deleteAllNews(Request $request){
        try{
            if(Gate::allows('QUẢN LÝ TIN TỨC.Quản lý tin tức.del')){
            $arr =$request->data;
            if( $arr)
            {
                foreach ($arr as $item) {
                    $list =News::where('news_id',$item)->delete();
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
