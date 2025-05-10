<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderSum;
use App\Models\Member;
use App\Models\Product;
use Carbon\Carbon;
use App\Models\StatisticsPages;
use App\Models\Comment;
use App\Models\ContactQoute;
use App\Models\HirePost;
use App\Models\Candidates;
use App\Models\MailList;
use Gate;
class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $countProduct=count(Product::get());
            $countMember=count(Member::get());
            $countOrder=count(OrderSum::where('status',5)->get());
            $countStatistics=count(StatisticsPages::get());
            return response()->json([
                'status'=>true,
                'product'=> $countProduct,
                'member'=> $countMember,
                'order'=>$countOrder,
                'statistics'=>$countStatistics
            ]);
        }catch(\Throwable $th){
            return response()->json([
            'status' => false,
            'message' => $th->getMessage()
            ]);
        }
    }
    public function showStatisticsPage(Request $request){
        try{
            if(Gate::allows('THỐNG KÊ TRUY CẬP.Thống kê truy cập.manage')){
                $fromDate =  $request['fromDate'];
                $toDate = $request['toDate'];
                $query=StatisticsPages::with('member')->orderBy('id_static_page','desc');
                if(isset($fromDate) && isset($toDate)){


                   $query->whereBetween('date', [$fromDate, $toDate])->paginate(20);
                }

                $StatisticsPages=$query->paginate(20);

                return response()->json([
                    'status'=>true,
                    'data'=>$StatisticsPages
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
    public function noApprovedStatistics(){
        try{
            $OrderSum=OrderSum::where('status',1)->get();
            $Comment = Comment::doesntHave('subcomments')->orderBy( 'comment_id', 'DESC' )->get();
            $ContactQoute=ContactQoute::where('display',0)->get();
            $HirePost=HirePost::where('status',0)->get();
            $Candidates=Candidates::where('status',0)->get();
            $MailList=MailList::where('status',0)->get();

            return response()->json([
                'status'=>true,
                'countOrderSum'=>count($OrderSum),
                'countComment'=>count($Comment),
                'countContactQoute'=>count($ContactQoute),
                'countHirePost'=>count($HirePost),
                'countCandidates'=>count($Candidates),
                'countMailList'=>count($MailList)
            ]);
        }catch(\Throwable $th){
            return response()->json([
            'status' => false,
            'message' => $th->getMessage()
            ]);
        }
    }
    public function chartStatisticsPage(){
        try{
            $url = 'http://192.168.245.190:8000/uploads/no-image.jpg111';

            if (@file_get_contents($url)) {
                // File tồn tại
                return 'File exists';
            } else {
                // File không tồn tại
                return 'File does not exist';
            }


            $StatisticsPages=StatisticsPages::orderBy('id_static_page','desc')->first()->date;
            $date = Carbon::createFromTimestamp( $StatisticsPages);
            $formattedDate = $date->format('d/m/Y');
            return  $formattedDate ;
            $day = $date->day;
            $month = $date->month;
            $year = $date->year;
            foreach( $StatisticsPages as $page){
                $date = Carbon::createFromTimestamp( $page);
            }

            return $year;
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
