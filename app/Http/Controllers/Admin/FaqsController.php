<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaqsCategoryDesc;
use App\Models\FaqsCategory;
use App\Models\Faqs;
use App\Models\FaqsDesc;
use Illuminate\Support\Facades\DB;
use Gate;
use App\Models\MailTemplate;
use App\Mail\TestMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;


class FaqsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showDetailFaqs(Request $request,$slug){
        try{
            if($slug!='tu-van')
            {
                $faqsCategoryDesc=FaqsCategoryDesc::where('friendly_url',$slug)->first();
                $faqs=null;
                if( $faqsCategoryDesc){
                    $faqs=Faqs::with('faqsDesc')->where('cat_id', $faqsCategoryDesc->cat_id)->get();

                    $faqs=DB::table('faqs')
                    ->where('faqs.cat_id', $faqsCategoryDesc->cat_id)
                    ->join('faqs_desc', 'faqs_desc.faqs_id', '=', 'faqs.faqs_id')
                    ->select('faqs_desc.faqs_id','faqs_desc.title','faqs_desc.description')
                    ->orderBy('faqs_desc.faqs_id','asc')
                    ->get();
                }
                return response()->json([
                    'status'=>true,
                    'nameCategory'=>$faqsCategoryDesc->cat_name,
                    'data'=>  $faqs
                ]);
            }else{
                $faqs=DB::table('faqs')

                ->join('faqs_desc', 'faqs_desc.faqs_id', '=', 'faqs.faqs_id')
                ->select('faqs_desc.faqs_id','faqs_desc.title','faqs_desc.description')
                ->orderBy('faqs_desc.faqs_id','asc')
                ->get();
                    return response()->json([
                        'status'=>true,
                        'nameCategory'=>'Tư vấn',
                        'data'=>  $faqs
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
    public function addFaqs(Request $request){
        try{
            $MailTemplate=MailTemplate::where('name','faqs')->first();
            $FaqsCategoryDesc=FaqsCategoryDesc::where('cat_id',$request->input('category'))->first();
            $cat_name=$FaqsCategoryDesc->cat_name;
            $dataEmail=[
                'domain'=>'http://web.chinhnhan.com/',
                'poster'=>$request->input('fullName'),
                'email'=>$request->input('email'),
                'cat_name'=>$cat_name,
                'content' =>  $request->input('contentQuestion'),
                'date'=>Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('HH:mm:ss, DD/MM/YYYY'),
                'html'=>$MailTemplate->description
                ];

            $dataEmail['html'] = str_replace(
                ['[domain]', '[poster]','[email]','[cat_name]', '[content]',
                '[date]'
            ],
                [$dataEmail['domain'], $dataEmail['poster'], $dataEmail['email'],
                $dataEmail['cat_name'], $dataEmail['content'],
                $dataEmail['date']
            ],
                $dataEmail['html']
            );
            Mail::to($request->input('email'))->send(new TestMail($dataEmail));



            $faqs = new Faqs();
            $faqsDesc = new FaqsDesc();
            $faqs->fill([
                'cat_id' => $request->input('category'),
                'cat_list'=>$request->input('category'),
                'poster' =>  $request->input('fullName'),
                'email_poster' => $request->input('email'),
                'phone_poster' => $request->input('phone_poster'),
                'answer_by' => $request->input('answer_by'),
                'views' => $request->input('views'),
                'display' => $request->input('display'),
                'menu_order' => $request->input('menu_order'),
                'date_post'=>strtotime( 'now' ),
                'date_update'=> strtotime( 'now' ),
                'adminid' => 0
            ])->save();
            $faqsDesc->faqs_id = $faqs->faqs_id;
            $faqsDesc->title = $request->input('contentQuestion');
            $faqsDesc->description = $request->input('description');
            $faqsDesc->lang = $request->input('lang');
            $faqsDesc->save();
            return response()->json([
                'status'=>true
            ]);
        }catch ( \Exception $e ) {
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

            if(Gate::allows('QUẢN LÝ TƯ VẤN.Quản lý danh mục.manage')){
                $cat_id=$request->cat_id;
                $queryFaqs=Faqs::with('faqsDesc','faqsCate')->orderBy('faqs_id','desc');
                if(isset($cat_id)){
                    $queryFaqs= $queryFaqs->where('cat_id',$cat_id);
                }


                if($request->data == 'undefined' || $request->data =="")
                {
                    $faqs =  $queryFaqs->paginate(10);
                }
                else{
                    $faqs= $queryFaqs->whereHas('faqsDesc', function ($query) use ($request) {
                        $query->where("title", 'like', '%' . $request->data . '%');
                    })->paginate(10);
                }


                // $faqs = Faqs::with('faqsDesc')->get();
                $response = [
                    'status' => true,
                    'list' => $faqs,
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

        $faqs = new Faqs();
        $faqsDesc = new FaqsDesc();
        try {
            if(Gate::allows('QUẢN LÝ TƯ VẤN.Quản lý tư vấn.add')){
                $faqs->fill([
                    'cat_id' => $request->input('cat_id'),
                    'cat_list'=>$request->input('cat_id'),
                    'poster' =>  $request->input('poster'),
                    'email_poster' => $request->input('email_poster'),
                    'phone_poster' => $request->input('phone_poster'),
                    'answer_by' => $request->input('answer_by'),
                    'views' => $request->input('views'),
                    'display' => $request->input('display'),
                    'menu_order' => $request->input('menu_order'),
                    'date_post'=>strtotime( 'now' ),
                    'date_update'=> strtotime( 'now' ),
                    'adminid' => 0
                ])->save();
                $faqsDesc->faqs_id = $faqs->faqs_id;
                $faqsDesc->title = $request->input('title');
                $faqsDesc->description = $request->input('description');
                $faqsDesc->lang = $request->input('lang');
                $faqsDesc->save();

                $response = [
                    'status' => true,
                    'faqs' => $faqs,
                    'faqsDesc' => $faqsDesc,
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
            if(Gate::allows('QUẢN LÝ TƯ VẤN.Quản lý tư vấn.edit')){
                $faqs = Faqs::with('faqsDesc')->find($id);
                return response()->json([
                'status'=> true,
                'data' => $faqs
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
            if(Gate::allows('QUẢN LÝ TƯ VẤN.Quản lý tư vấn.update')){
            $faqs = new Faqs();
            $faqsDesc = new FaqsDesc();
            $listFaqs = Faqs::Find($id);

            $listFaqs->fill([
                'cat_id' => $request->input('cat_id'),
                'cat_list'=> $request->input('cat_list'),
                'poster' =>  $request->input('poster'),
                'email_poster' => $request->input('email_poster'),
                'phone_poster' => $request->input('phone_poster'),
                'answer_by' => $request->input('answer_by'),
                'views' => $request->input('views'),
                'display' => $request->input('display'),
                'menu_order' => $request->input('menu_order'),
                'adminid' => $request->input('adminid'),
                'date_post'=>strtotime( 'now' ),
                'date_update'=> strtotime( 'now' )
            ])->save();

            $faqsDesc = FaqsDesc::where('faqs_id', $id)->first();
            if ($faqsDesc) {
                $faqsDesc->title = $request->input('title');
                $faqsDesc->description = $request->input('description');
                $faqsDesc->lang = $request->input('lang');
                $faqsDesc->save();
            }
            return response()->json( [
                'status'=>true,
            ] );
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
            if(Gate::allows('QUẢN LÝ TƯ VẤN.Quản lý tư vấn.del')){
            $list = Faqs::Find($id)->delete();
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
            if(Gate::allows('QUẢN LÝ TƯ VẤN.Quản lý tư vấn.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $list = Faqs::Find($item)->delete();
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
