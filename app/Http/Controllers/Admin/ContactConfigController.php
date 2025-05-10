<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactConfig;
use App\Models\ContactConfigDesc;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Gate;
class ContactConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showContactConfig(){
        try{
            $contactConfig = ContactConfig::first();
            $response = [
                'status' => true,
                'list' => $contactConfig,
            ];
            return response()->json( $response, 200 );
        } catch ( \Exception $e ) {
                    $errorMessage = $e->getMessage();
                    $response = [
                        'status' => 'false',
                        'error' => $errorMessage
                    ];
                    return response()->json( $response, 500 );
                }
        }
    public function index()
    {
        try {
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ địa chỉ.manage')){
            $contactConfig = ContactConfig::get();
            $response = [
                'status' => true,
                'list' => $contactConfig,
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
        $contactConfig = new ContactConfig();
        // $contactConfigDesc = new ContactConfigDesc();
        try {
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ địa chỉ.add')){
            $contactConfig->fill([
                'title'=> $request->input('title'),
                'company' => $request->input('company'),
                'address'=> $request->input('address'),
                'phone' =>  $request->input('phone'),
                'fax' => $request->input('fax'),
                'email' => $request->input('email'),
                'email_order' => $request->input('email_order'),
                'website' => $request->input('website'),
                'work_time' => $request->input('work_time'),
                // 'map_lat' => $request->input('map_lat'),
                // 'map_lng' => $request->input('map_lng'),
                'map'=> $request->input('map'),
                // 'menu_order' => $request->input('menu_order'),
                'display' => $request->input('display'),
                // 'adminid' => $request->input('adminid')
            ])->save();
            // $contactConfigDesc->contact_id = $contactConfig->contact_id;
            // $contactConfigDesc->title = $request->input('title');
            // $contactConfigDesc->map_desc = $request->input('map_desc');
            // $contactConfigDesc->map_address = $request->input('map_address');
            // // $contactConfigDesc->lang = $request->input('lang');
            // $contactConfigDesc->save();

            $response = [
                'status' => true,
                'faqs' => $contactConfig,
                // 'faqsDesc' => $contactConfigDesc,
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
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ địa chỉ.edit')){
            $contactConfig = ContactConfig::where('display',1)->first();
            return response()->json([
                'status'=> true,
                'data' => $contactConfig
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

        $listContactConfig = ContactConfig::Find($id);
        try {
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ địa chỉ.update')){
            $listContactConfig->fill([
                'title'=> $request->input('title'),
                'company' => $request->input('company'),
                'address'=> $request->input('address'),
                'phone' =>  $request->input('phone'),
                'fax' => $request->input('fax'),
                'email' => $request->input('email'),
                'email_order' => $request->input('email_order'),
                'website' => $request->input('website'),
                'work_time' => $request->input('work_time'),
                // 'map_lat' => $request->input('map_lat'),
                // 'map_lng' => $request->input('map_lng'),
                // 'menu_order' => $request->input('menu_order'),
                'display' => $request->input('display'),
                'map'=> $request->input('map'),
                // 'adminid' => $request->input('adminid')
            ])->save();
            // $contactConfigDesc = ContactConfigDesc::where('contact_id', $id)->first();
            // if ($contactConfigDesc) {
            //     $contactConfigDesc->title = $request->input('title');
            //     $contactConfigDesc->map_desc = $request->input('map_desc');
            //     $contactConfigDesc->map_address = $request->input('map_address');
            //     $contactConfigDesc->lang = $request->input('lang');
            //     $contactConfigDesc->save();
            // }

            $response = [
                'status' => true,
                'contactConfig' => $listContactConfig,
                // 'contactConfigDesc' => $contactConfigDesc,
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
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ địa chỉ.del')){
            $listContactConfig = ContactConfig::Find($id)->delete();
            return response()->json([
                'status'=>true
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
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ địa chỉ.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $listContactConfig = ContactConfig::Find($item)->delete();
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
