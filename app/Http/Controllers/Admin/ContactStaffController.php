<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactStaff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Gate;
class ContactStaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showAllContactStaff(){
        try{
            $query =ContactStaff::where('display',1)->orderBy('staff_id','asc')->get();
            return response()->json([
                'status'=>true,
                'data'=>$query
            ]);
        }catch(\Throwable $th){
            return response()->json([
              'status' => false,
              'message' => $th->getMessage()
            ]);
        }
    }
    public function index(Request $request)
    {
        try{

            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý phòng bàn.manage')){
            $query =ContactStaff::orderBy('staff_id','asc');


            if(empty($request->input('data'))||$request->input('data')=='undefined' ||$request->input('data')=='')
            {
                $query = $query;
            }
            else{
                $query = $query->where('title', 'like', '%' . $request->input('data') . '%')
                ->orWhere('phone', 'LIKE', '%' . $request->input('data') . '%')
                ->orWhere('email', 'LIKE', '%' . $request->input('data') . '%');
            }
            $query = $query->paginate(10);
            return response()->json([
                'status'=>true,
                'data'=> $query
            ]);
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $contactStaff = new ContactStaff();
        try {
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý phòng bàn.add')){
            $contactStaff->title = $request->input('title');
            $contactStaff->email = $request->input('email');
            $contactStaff->phone = $request->input('phone');
            $contactStaff->description = $request->input('description');
            //$contactStaff->menu_order = $request->input('menu_order');
            $contactStaff->date_post =  strtotime('now');
            $contactStaff->date_update =  strtotime('now');

            $contactStaff->display = $request->input('display');
            //$contactStaff->lang = $request->input('lang');
            $contactStaff->save();

            $response = [
                'status' => true,
                'contactStaff' => $contactStaff,
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
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý phòng bàn.edit')){
            $contactStaff = ContactStaff::where('staff_id',$id)->first();
            return response()->json([
              'status'=> true,
              'contactStaff' => $contactStaff
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
        try{
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý phòng bàn.update')){
            $contactStaff = ContactStaff::where('staff_id',$id)->first();
            $contactStaff->title = $request->input('title')?? $contactStaff->title;
            $contactStaff->email = $request->input('email')?? $contactStaff->email;
            $contactStaff->phone = $request->input('phone') ?? $contactStaff->phone;
            $contactStaff->description = $request->input('description') ??  $contactStaff->description;
            $contactStaff->date_post =  strtotime('now');
            $contactStaff->date_update =  strtotime('now');
            $contactStaff->display = $request->input('display')?? $contactStaff->display;
            $contactStaff->save();
            return response()->json([
                'status'=>true,
                'data'=>$contactStaff
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý phòng bàn.del')){
            $contactStaff = ContactStaff::where('staff_id',$id)->first();
            $contactStaff->delete();
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
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý phòng bàn.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $contactStaff = ContactStaff::where('staff_id',$item)->first();
                        $contactStaff->delete();
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
