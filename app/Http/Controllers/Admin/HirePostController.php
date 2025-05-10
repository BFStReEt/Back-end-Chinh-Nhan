<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HirePost;
use App\Models\Candidates;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Gate;
use Illuminate\Support\Str;
class HirePostController extends Controller
{
     public function showHireRelated(Request $request){
        try {
            $hireCateId= $request->hireCateId;
            $postId=$request->postId;

            $query=HirePost::where('id','!=', $postId)->where('hire_cate_id', $hireCateId)->where('status',1)
            ->orderBy('id','desc')->take(5)->get();
            return response()->json([
                'status'=>true,
                'data'=>$query
            ]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }
    public function showHirePost(Request $request){
        try {
            $query=HirePost::where('status',1)->orderBy('id','desc');
            if(isset($request->hire_cate_id) ){
                $query=$query->where('hire_cate_id',$request->hire_cate_id);
            }
            if(isset($request->name) ){
                $query=$query->where("name", 'like', '%' . $request->name . '%');
            }
            $list=$query->paginate(5);
            return response()->json([
            'status'=>true,
            'data'=>$list
           ]);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }
    public function updateCandidates(Request $request,$id){

        try{
            $Candidates=Candidates::where('id',$id)->first();
            $Candidates->status=$request->status;
            $Candidates->save();
            return response()->json([
                'status'=>true,
                'data'=> $Candidates
            ]);
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }

    }
    public function candidateInfo(Request $request){
        try {
            $filePath = public_path('uploads/cv/thong_tin_ung_vien_du_tuyen.doc');
           
            if (!file_exists($filePath)) {
                return response()->json(['error' => 'File not found.'], 404);
            }
           
            return response()->download($filePath, 'cv.doc');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }
    public function createCandidates( Request $request ) {

        try {

            //return $request ->all();
            $candidates = new Candidates();
            $candidates->name = $request->fullName??'';
            $candidates->gmail = $request->email??'';
            $candidates->phone = $request->numberPhone?? '';
            $candidates->hire_post_id = $request->idJob?? '';
            $candidates->message = $request->letterIntroduce?? '';
            $filePath = '';
            if ( $request->hasFile( 'fileCV' ) ) {
                $file = $request->file( 'fileCV' );
                $path = public_path( 'uploads/candidate' );
                // Đường dẫn đến thư mục public/uploads
                $extension = $file->getClientOriginalExtension();
                $allowedExtensions = [ 'pdf', 'doc' ];
                if ( in_array( $extension, $allowedExtensions ) ) {
                    $currentDateTime =  Carbon::now( 'Asia/Ho_Chi_Minh' );
                    $name = $currentDateTime->format( 'Ymd-His' );
                    $fileName =  $name . '.' . $extension;
                    $name = $file->getClientOriginalName();
                    $filePath = 'candidate/' . $fileName;
                    $file->move( $path, $fileName );

                } else {
                    return response()->json( [
                        'status' => false,
                        'message' => 'Chỉ cho phép tải lên tệp PDF hoặc DOC.'
                    ], 422 );
                }
            }
            $candidates->cv = $filePath;

            $filePathDoc = '';
            if ( $request->hasFile( 'fileCandidate' ) ) {
                $file = $request->file( 'fileCandidate' );
                $path = public_path( 'uploads/candidate-doc' );
                // Đường dẫn đến thư mục public/uploads
                $currentDateTime =  Carbon::now( 'Asia/Ho_Chi_Minh' );
                $name = $currentDateTime->format( 'Ymd-His' );

                $fileName = $name.'.doc';
                $name = $file->getClientOriginalName();

                $filePathDoc = 'candidate-doc/'.$fileName;
                $file->move( $path, $fileName );
            }
            $candidates->fileInfo =  $filePathDoc;
            $candidates->status = 0;
            $candidates->date_post=strtotime( 'now' );

            $candidates->save();

            return response()->json( [
                'status'=>true
            ] );

        } catch( \Exception $e ) {
            return response()->json( [
                'status' => false,
                'message' => $e->getMessage()
            ], 422 );
        }
    }
    public function downloadFile( Request $request ) {
        try {

            $filePath = public_path( 'uploads/'.$request->url );
            $fileName = Str::afterLast( $request->url, '/' );

            if ( !file_exists( $filePath ) ) {
                return response()->json( [ 'error' => 'File not found.' ], 404 );
            }
            return response()->download( $filePath,$fileName );

        } catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json( $response, 500 );
        }
    }
    public function index(Request $request)
    {
        try {
            if(Gate::allows('Quản lý tuyển dụng.Bài đăng tuyển dụng.manage')){
            $query=HirePost::with('HireCategory')->orderBy('id','desc');
            if($request->data == 'undefined' || $request->data =="")
            {
                $query = $query;
            }
            else{
                $searchKeywords=$request->data;
                $query = $query->where("name", 'like', '%' .  $searchKeywords . '%');
            }
            $hire_cate_id=$request->cat_id;
            if(isset($hire_cate_id)){
                $query=$query->where('hire_cate_id',$hire_cate_id);
            }
            $list=$query;
            $hirePost= $list->paginate(10);
            return response()->json([
                'status'=>true,
                'data'=>$hirePost
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
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
        //$disPath = public_path();
        try {
            if(Gate::allows('Quản lý tuyển dụng.Bài đăng tuyển dụng.add')){
            $filePath = '';
            if ( $request->image != null ) {
                $DIR = 'uploads/hirePost';
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->image[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];

                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'hirePost/'.$name . '.png';
                file_put_contents( $file,  $base64Img );
            }
            $hirePost = new HirePost();
            $hirePost->name = $request->name;
            $hirePost->salary = $request->salary;

            $hirePost->display= $request->display;
            $hirePost->address = $request->address;
            $hirePost->experience = $request->experience;
            $hirePost->deadline = $request->deadline;
            $hirePost->information = $request->information;
            $hirePost->rank = $request->rank;
            $hirePost->number = $request->number;

            $hirePost->form = $request->form;
            $hirePost->degree = $request->degree;
            $hirePost->department = $request->department;
            $hirePost->slug = $request->slug;
            $hirePost->meta_keywords = $request->meta_keywords;
            $hirePost->meta_description = $request->meta_description;
            $hirePost->hire_cate_id = $request->hire_cate_id;
            $hirePost->image =  $filePath;
            $hirePost->save();
            return response()->json( [
                'status'=>true,
                'mess'=>'success create hirePost'
            ] );
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        } catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
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
    public function edit(string $id)
    {
        try {
            if(Gate::allows('Quản lý tuyển dụng.Bài đăng tuyển dụng.edit')){
            $hirePost=HirePost::where('id',$id)->first();
            if($hirePost &&  $hirePost->status==0){
                $hirePost->status=1;
                $hirePost->save();
            }
            return response()->json([
                'status'=>true,
                'data'=>$hirePost
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
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
        $disPath = public_path();
        try {
            if(Gate::allows('Quản lý tuyển dụng.Bài đăng tuyển dụng.update')){
            $hirePost = HirePost::where( 'id', $id )->first();
            $filePath = '';

            if ( $request->image != null && $request->image != $hirePost ->image ) {

                $DIR = 'uploads/hirePost';
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->image[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];
                //return response()->json( $file_chunks );
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'hirePost/'.$name . '.png';
                file_put_contents( $file,  $base64Img );
            } else {
                $filePath = $hirePost->image;
            }

            $hirePost->name = $request->name;
            $hirePost->salary = $request->salary;
            $hirePost->display= $request->display;
            $hirePost->address = $request->address;
            $hirePost->experience = $request->experience;
            $hirePost->deadline = $request->deadline;
            $hirePost->information = $request->information;
            $hirePost->rank = $request->rank;
            $hirePost->number = $request->number;
            $hirePost->form = $request->form;
            $hirePost->degree = $request->degree;
            $hirePost->department = $request->department;
            $hirePost->slug = $request->slug;
            $hirePost->meta_keywords = $request->meta_keywords;
            $hirePost->meta_description = $request->meta_description;
            $hirePost->hire_cate_id = $request->hire_cate_id;
            $hirePost->image = $filePath;
            $hirePost->save();
            return response()->json( [
                'status'=>true,
                'mess'=>'success update hire Post'
            ] );
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        } catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
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
        try {
            if(Gate::allows('Quản lý tuyển dụng.Bài đăng tuyển dụng.del')){
            $hirePost=HirePost::where('id',$id)->first();
            if($hirePost) {
                $hirePost->delete();
            }
            return response()->json([
                'status'=>true,
                'data'=>$hirePost
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }
    public function deleteAll(Request $request)
    {
        $arr =$request->data;
        try {
            if(Gate::allows('Quản lý tuyển dụng.Bài đăng tuyển dụng.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $hirePost=HirePost::where('id',$item)->first();
                        if($hirePost) {
                            $hirePost->delete();
                        }
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


    public function getMetaHirePost($slug){
        try{
            $hirePost=HirePost::where('slug',$slug)->first();
            $metaDescription=$hirePost->meta_keywords??$hirePost->name;
            $metaKeywords=$hirePost->meta_description??$hirePost->name;

            return response()->json([
                'status'=>true,
                'metaDescription'=>$metaDescription,
                'metaKeywords'=>$metaKeywords
            ]);
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }

    }
    public function detail($slug){
        try{
            $hirePost=HirePost::where('slug',$slug)->first();
            return response()->json([
                'status'=>true,
                'data'=>$hirePost
            ]);
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }
    public function showCandidates(Request $request){
        try {
            $query=Candidates::with('hirePost.HireCategory')->orderBy('id','desc');
            if($request->data == 'undefined' || $request->data =="")
            {
                $list = $query;
            }
            else{
                $list = $query->where("name", 'like', '%' . $request->data . '%')
                ->orWhere('gmail','like', '%' . $request->data . '%');
            }
            $Candidates= $list->paginate(10);
            return response()->json([
                'status'=>true,
                'data'=> $Candidates
            ]);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }
    public function detailCandidates($id){
        try{

            $Candidates = DB::table('candidates')
            ->join('hire_post', 'hire_post.id', '=', 'candidates.hire_post_id')
            ->join('hire_category', 'hire_category.id', '=', 'hire_post.hire_cate_id')
            ->where('candidates.id',$id)
            ->select('candidates.*','hire_category.name as titleCategory','hire_post.name as titlePost')
            ->first();
            $Candidate=Candidates::where('id',$id)->first();
            if( $Candidate->status==0){
                $Candidate->status=1;
                $Candidate->save();
            }
            return response()->json([
                'status'=>true,
                'data'=> $Candidates
            ]);
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }
}
