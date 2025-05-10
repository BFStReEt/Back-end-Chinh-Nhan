<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Gate;
use App\Models\MailTemplate;
use App\Mail\TestMail;
use App\Models\ContactStaff;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function addContactShow(Request $request){
        try{

            // return $request->all();
            $contact = new Contact();
            try{
                $MailTemplate=MailTemplate::where('name','contact')->first();
                $ContactStaff=ContactStaff::where('staff_id',$request->input('deparment'))->first();
                $staff=isset($ContactStaff) ??$ContactStaff->title;

                $dataEmail=[
                    'domain'=>'http://web.chinhnhan.com/',
                    'name'=>$request->input('fullName'),
                    'staff'=>$staff??null,
                    'address'=>$request->input('address'),
                    'email'=>$request->input('email'),
                    'phone' => $request->input('numberPhone'),
                    'subject' => $request->input('title'),
                    'content' =>  $request->input('content'),
                    'date'=>Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('HH:mm:ss, DD/MM/YYYY'),
                    'html'=>$MailTemplate->description
                    ];


                $dataEmail['html'] = str_replace(
                    ['[domain]', '[name]','[staff]','[address]', '[email]',
                    '[phone]','[subject]', '[content]','[date]'
                ],
                    [$dataEmail['domain'], $dataEmail['name'], $dataEmail['staff'],
                    $dataEmail['address'], $dataEmail['email'],
                    $dataEmail['phone'], $dataEmail['subject'], $dataEmail['content'],
                    $dataEmail['date']
                ],
                    $dataEmail['html']
                );
                Mail::to($dataEmail['email'])->send(new TestMail($dataEmail));





                $contact->fill([
                    'subject' => $request->input('title'),
                    'staff_id'=> $request->input('deparment'),
                    'content' =>  $request->input('content'),
                    'name' => $request->input('fullName'),
                    'email' => $request->input('email'),
                    'phone' => $request->input('numberPhone'),
                    'address' => $request->input('address'),
                    'status' => 1,
                    'date_post'=>strtotime('now'),
                    'date_update'=>strtotime('now'),
                    // 'menu_order' => $request->input('menu_order'),
                    'lang' =>'vn',
                ])->save();


                $response = [
                    'status' => true,
                    'contact' =>  $contact,
                ];

                return response()->json($response, 200);

            } catch ( \Exception $e ) {
                $errorMessage = $e->getMessage();
                $response = [
                    'status' => 'false',
                    'error' => $errorMessage
                ];

                return response()->json( $response, 500 );
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
    public function index(Request $request)
    {
        try {
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ liên hệ.manage')){
            $query = Contact::with('contactStaff')->orderBy('id','desc');

            if(empty($request->input('data'))||$request->input('data')=='undefined' ||$request->input('data')=='')
            {
               $query = $query;
            }
            else{
                $query = $query->where("subject", 'like', '%' . $request->input('data') . '%')
                ->orWhere('name', 'LIKE', '%' . $request->input('data') . '%')
                ->orWhere('phone', 'LIKE', '%' . $request->input('data') . '%')
                ->orWhere('email', 'LIKE', '%' . $request->input('data') . '%')
                ;
            }

            if($request->startDate!='' && $request->endDate!=''){
                $start=$request->startDate;
                $end=$request->endDate;
                $query=$query->whereBetween('date_post',[$start,$end]);
            }

            $contact= $query->paginate(10);


            $response = [
                'status' => true,
                'list' => $contact,
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
                'status' => false,
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
        $contact = new Contact();
        try{
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ liên hệ.add')){
            $contact->fill([
                'subject' => $request->input('subject'),
                'staff_id'=> $request->input('staff_id'),
                'content' =>  $request->input('content'),
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'status' => $request->input('status'),
                'date_post'=>strtotime('now'),
                'date_update'=>strtotime('now'),
                // 'menu_order' => $request->input('menu_order'),
                'lang' =>'vn',
            ])->save();
            $response = [
                'status' => true,
                'contact' =>  $contact,
            ];
            return response()->json($response, 200);

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
    public function edit(string $id)
    {
        try {
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ liên hệ.edit')){
            $contact = Contact::with('contactStaff')->where('id',$id)->first();
            if($contact->display==0){
                $contact->display=1;
                $contact->save();
            }

            $response = [
                'status' => true,
                'list' => $contact,
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ liên hệ.update')){
            $contact = Contact::with('contactStaff')->where('id',$id)->first();
            $contact->subject=$request->subject??$request->subject;
            $contact->staff_id=$request->staff_id??$request->staff_id;
            $contact->content=$request->content??$request->content;
            $contact->name=$request->name??$request->name;
            $contact->email=$request->email??$request->email;
            $contact->phone=$request->phone??$request->phone;

            $contact->address=$request->address??$request->address;
            $contact->status=$request->status??$request->status;
            $contact->date_post=strtotime('now');
            $contact->date_update=strtotime('now');
            $contact->save();
            return response()->json([
                'status'=>true
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try{

           // return 111;
            if(Gate::allows('QUẢN LÝ LIÊN HỆ.Quản lý sổ liên hệ.del')){
            $contact = Contact::where('id',$id)->first();
            $contact->delete();
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
                if($arr)
                {
                    foreach ($arr as $item) {
                        $contact = Contact::where('id',$item)->first();
                        $contact->delete();
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
