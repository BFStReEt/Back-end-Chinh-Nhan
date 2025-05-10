<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactQoute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Gate;

class ContactQouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ báo giá.manage')){
                $query = ContactQoute::orderBy('id','asc');

                if(empty($request->input('data'))||$request->input('data')=='undefined' ||$request->input('data')=='')
                {
                    $query = $query;
                }
                else{
                    $query = $query->where("name", 'like', '%' . $request->input('data') . '%')
                    ->orWhere('phone', 'LIKE', '%' . $request->input('data') . '%')
                    ->orWhere('email', 'LIKE', '%' . $request->input('data') . '%')
                    ;
                }

                if($request->startDate!='' && $request->endDate!=''){
                    $start=$request->startDate;
                    $end=$request->endDate;
                    $query=$query->whereBetween('date_post',[$start,$end]);
                }

                $contactQoute= $query->paginate(10);
                $response = [
                    'status' => true,
                    'list' => $contactQoute,
                ];
                return response()->json( $response, 200 );
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch(\Throwable $th){
            return response()->json([
              'status' => false,
              'message' => $th->getMessage()
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
    public function addContactQoute(Request $request){

            $contactQoute = new ContactQoute();
            try {
                $disPath = public_path();
                $filePath = '';
                if ($request->hasFile('attach_file')) {

                    $file = $request->file('attach_file');
                    $extension = $file->getClientOriginalExtension();
                    $name = uniqid() . '.' . $extension;
                    $DIR = $disPath . '/uploads/ContactQoute';
                    $file->move($DIR, $name);
                    $filePath = 'ContactQoute/' . $name;

                }

                $contactQoute->fill([
                    'name' => $request->input('fullName'),
                    'phone'=> $request->input('numberPhone'),
                    'email' =>  $request->input('email'),
                    'company' => $request->input('typeCustomer'),
                    'address' => $request->input('address'),
                    'content' => $request->input('content'),
                    'attach_file' => $filePath,
                    // 'status' => $request->input('status'),
                    'display' => 1,
                    // 'menu_order' => $request->input('menu_order'),
                    'date_post'=>strtotime('now'),
                    'date_update'=>strtotime('now'),
                    'lang' =>'vn',
                ])->save();
                $response = [
                    'status' => true,
                    'contactQoute' => $contactQoute,
                ];
                return response()->json($response, 200);

        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $contactQoute = new ContactQoute();
        try {
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ báo giá.add')){
            $disPath = public_path();
            $filePath = '';
            if ($request->hasFile('attach_file')) {

                $file = $request->file('attach_file');
                $extension = $file->getClientOriginalExtension();
                $name = uniqid() . '.' . $extension;
                $DIR = $disPath . '/uploads/ContactQoute';
                $file->move($DIR, $name);
                $filePath = 'ContactQoute/' . $name;

                //return $request->picture;
                // $DIR = $disPath.'\uploads\ContactQoute';
                // $httpPost = file_get_contents( 'php://input' );
                // $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                // $fileType = explode( 'image/', $file_chunks[ 0 ] );
                // $image_type = $fileType[ 0 ];
                // //return response()->json( $file_chunks );
                // $base64Img = base64_decode( $file_chunks[ 1 ] );
                // $data = iconv( 'latin5', 'utf-8', $base64Img );
                // $name = uniqid();
                // $file = $DIR .'\\'. $name . '.png';
                // $filePath = 'ContactQoute/'.$name . '.png';
                // file_put_contents( $file,  $base64Img );
            }

            $contactQoute->fill([
                'name' => $request->input('name'),
                'phone'=> $request->input('phone'),
                'email' =>  $request->input('email'),
                'company' => $request->input('company'),
                'address' => $request->input('address'),
                'content' => $request->input('content'),
                'attach_file' => $filePath,
                'status' => $request->input('status'),
                // 'menu_order' => $request->input('menu_order'),
                'lang' =>'vn',
            ])->save();
            $response = [
                'status' => true,
                'contactQoute' => $contactQoute,
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
    public function edit(string $id)
    {
        try{
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ báo giá.edit')){
                $contactQoute = ContactQoute::where('id',$id)->first();
                if($contactQoute->display==0){
                    $contactQoute->display=1;
                    $contactQoute->save();
                }


                return response()->json([
                'status'=> true,
                'contactQoute' => $contactQoute
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        }catch (\Exception $e) {
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
        $contactQoute = new ContactQoute();
        $listContactQoute = ContactQoute::Find($id);
        try {
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ báo giá.update')){
            $disPath = public_path();

            if ( $request->hasFile('attach_file') != null &&  $listContactQoute->attach_file != $request->hasFile('attach_file') ) {


                $file = $request->file('attach_file');
                $extension = $file->getClientOriginalExtension();
                $name = uniqid() . '.' . $extension;
                $DIR = $disPath . '/uploads/ContactQoute';
                $file->move($DIR, $name);
                $filePath = 'ContactQoute/' . $name;

                // $DIR = $disPath.'\uploads\ContactQoute';
                // $httpPost = file_get_contents( 'php://input' );
                // $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                // $fileType = explode( 'image/', $file_chunks[ 0 ] );
                // $image_type = $fileType[ 0 ];
                // //return response()->json( $file_chunks );
                // $base64Img = base64_decode( $file_chunks[ 1 ] );
                // $data = iconv( 'latin5', 'utf-8', $base64Img );
                // $name = uniqid();
                // $file = $DIR .'\\'. $name . '.png';
                // $filePath = 'ContactQoute/'.$name . '.png';
                // file_put_contents( $file,  $base64Img );

            } else {
                $filePath = $listContactQoute->attach_file;
            }
            $listContactQoute->fill([
                'name' => $request->input('name'),
                'phone'=> $request->input('phone'),
                'email' =>  $request->input('email'),
                'company' => $request->input('company'),
                'address' => $request->input('address'),
                'content' => $request->input('content'),
                'attach_file' =>$filePath,
                'status' => $request->input('status'),

            ])->save();
            $response = [
                'status' => true,
                'contactQoute' => $listContactQoute,
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ báo giá.del')){
            $list =  ContactQoute::where('id',$id)->first()->delete();
            return response()->json([
                'status'=>true
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }
    public function deleteAll(Request $request)
    {
        $arr =$request->data;

        try {
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ báo giá.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $list =  ContactQoute::where('id',$item)->first()->delete();
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
}
