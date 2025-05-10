<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Gate;
class CommentController extends Controller
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
                'action'=>'show all comment',
                'cat'=>'comment',
            ]);
            if(Gate::allows('QUẢN LÝ COMMENT.Quản lý bình luận.manage')){
                if($request->data == 'undefined' || $request->data =="")
                {
                    $listComment = Comment::with( 'subcomments','productDesc')->orderBy( 'comment_id', 'DESC' )->where( 'parentid', 0 )->paginate(20);
                }
                else{
                    $listComment = Comment::with( 'subcomments','productDesc')
                    ->where("content", 'like', '%' . $request->data . '%')
                    ->orWhere("name", 'like', '%' . $request->data . '%')
                    ->orderBy( 'comment_id', 'DESC' )->where( 'parentid', 0 )->paginate(20);
                }
                return response()->json( [
                    'listComment' => $listComment,
                    'status' => true
                ] );
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        } catch( Exception $e ) {
            return response()->json( [
                'status' => false,
                'message' => $e->getMessage()
            ] );
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
    public function edit(Request $request,string $id)
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' =>  $stringTime,
            'ip'=> $request->ip(),
            'action'=>'edit a comment',
            'cat'=>'comment',
        ]);
        if(Gate::allows('QUẢN LÝ COMMENT.Quản lý bình luận.edit')){
            $listComment = Comment::with( 'subcomments','productDesc')->where( 'parentid', 0 )->get()->find( $id );
            $commentParentid = Comment::with( 'subcomments','productDesc')->where( 'parentid', $id )->first();
            return response()->json( [
                'listComment' => $listComment,
                'commentParentid' =>$commentParentid,
                'status' => true
            ] );
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
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
            'action'=>'update a comment',
            'cat'=>'comment',
        ]);
        if(Gate::allows('QUẢN LÝ COMMENT.Quản lý bình luận.update')){
            $list = Comment::find( $id );
            $list->display = $request->input('display');
            $list->date_update =  strtotime( 'now' );
            $list->save();
            $reply = Comment::where('parentid',$id)->first();
            if($reply)
            {
                if($reply =="")
                {
                    $reply->delete();
                }
                else{
                    $reply->content = $request->input('reply');
                    $reply->save();
                }
            }
            else if($request->input('reply'))
            {
                $idAdmin = Auth::guard('admin')->user();
                $comment = new Comment;
                $comment->module = $list->module;
                $comment->post_id = $list->product_id;
                // $comment->post_id = $list->product_id;
                $comment->parentid = $list->comment_id;
                $comment->mem_id = 0;
                $comment->name = $idAdmin->display_name;
                $comment->email = $idAdmin->email;
                $comment->phone = $idAdmin->phone;
                $comment->hidden_email = 1;
                $comment->content = $request->input('reply');
                $comment->avatar = "";
                $comment->mark = 5;
                $comment->menu_order = 0;
                $comment->address_IP = "";
                $comment->display = 1;
                $comment->date_post =  strtotime( 'now' );
                $comment->date_update =  strtotime( 'now' );
                $comment->adminid = $idAdmin->adminid;
                $comment->lang = "vi";
                $comment->save();
            }

            return response()->json( [
                'status' => true
            ] );
    } else {
        return response()->json([
            'status'=>false,
            'mess' => 'no permission',
        ]);
    }
    }

    /**
     * Remove the specified resource from storage.
     */

     public function deleteAll(Request $request)
     {


         $arr =$request->data;
         try {
            if(Gate::allows('QUẢN LÝ COMMENT.Quản lý bình luận.del')){
                if($arr)
                {

                    foreach ($arr as $item) {

                        $listComment = Comment::where( 'parentid', 0 )->where('comment_id',$item)->delete();
                        // $listComment->delete();
                        $commentParentid = Comment::where( 'parentid',$item)->delete();
                        // $commentParentid->delete();

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
         } catch (\Exception $e) {
             $errorMessage = $e->getMessage();
             $response = [
                 'status' => 'false',
                 'error' => $errorMessage
             ];
             return response()->json($response, 500);
         }
     }


    public function destroy(Request $request,string $id)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'delete a comment',
                'cat'=>'comment',
            ]);
            if(Gate::allows('QUẢN LÝ COMMENT.Quản lý bình luận.del')){
            $listComment = Comment::where( 'parentid', 0 )->get()->find( $id );
            $listComment->delete();
            $commentParentid = Comment::where( 'parentid', $id )->first();
            $commentParentid->delete();
            return response()->json([
                'status'=>true,
                'message'=>'delete success'
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        }catch( Exception $e ) {
            return response()->json( [
                'status' => false,
                'message' => $e->getMessage()
            ] );
        }
    }
    public function addCommentForUser(Request $request){

        $disPath = public_path();
        $filePath = '';
        if ( $request->avatar != null ) {

            $DIR = $disPath.'\uploads\comment';
            $httpPost = file_get_contents( 'php://input' );
            $file_chunks = explode( ';base64,', $request->avatar[ 0 ] );
            $fileType = explode( 'image/', $file_chunks[ 0 ] );
            $image_type = $fileType[ 0 ];

            //return response()->json( $file_chunks );
            $base64Img = base64_decode( $file_chunks[ 1 ] );
            $data = iconv( 'latin5', 'utf-8', $base64Img );
            $name = uniqid();
            $file = $DIR .'\\'. $name . '.png';
            $filePath = 'comment/'.$name . '.png';

            file_put_contents( $file,  $base64Img );
        }
        //   if(isset($request->macn))
        //   {
        //       $product_ids = Product::where( 'maso', $request->macn )->first()->product_id;
        //   }
        //   else{
        //       $product_ids =  $request->post_id;
        //   }
        //   if(isset($request->mpydvtnk))
        //   {
        //       $mem_id = $request->mpydvtnk/99999;
        //       $member = Member::find($mem_id);
        //   }
        $fullName=null;
        $email=null;
        if($request->userId){
            $member = Member::find($request->userId);
            $fullName=$member->full_name;
            $email=$member->email;
        }
        try {
            $comment = new Comment();
            $comment->module = $request->module??"product";
            $comment->post_id = $request->productId??0;
            // $comment->product_id =0;
            $comment->parentid = 0;
            $comment->mem_id = $request->userId??0;
            $comment->name = $request->fullName?$request->fullName: $fullName;
            $comment->email = $request->email?$request->email:$email;
            $comment->hidden_email = 0;
            $comment->content = $request->content;
            $comment->avatar = $filePath;
            $comment->phone = $request->numberPhone?$request->numberPhone:$member->phone;
            $comment->mark = $request->star?$request->star:0;
            $comment->menu_order = 0;
            $comment->address_IP = 0;
            $comment->display = 0;
            $comment->date_post = strtotime( 'now' );
            $comment->date_update = strtotime( 'now' );
            $comment->adminid = 0;
            $comment->lang = 'vi';
            $comment->save();
            return response()->json( [
                'comment' =>$comment,
                'status' => true
            ] );



        } catch( Exception $e ) {
            return response()->json( [
                'status' => false,
                'message' => $e->getMessage()
            ] );
        }
    }
}
