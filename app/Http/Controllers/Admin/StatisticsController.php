<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StatisticsPages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class StatisticsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{

            $fromDate =  $request['fromDate'];
            $toDate = $request['toDate'];
            $query = StatisticsPages::with('member');
            if(!isset($fromDate) && !isset($toDate)){

                $listStatistics=$query->paginate(10);
            }
            else if(isset($fromDate) && isset($toDate)){

                $listStatistics=$query->whereBetween('date', [$fromDate, $toDate])->paginate(10);
            }
            else if($request->input('data')=='undefined' && $request->input('data')==''){
                $listStatistics=$query->whereBetween('date', [$fromDate, $toDate])->paginate(10);
            }
            return response()->json([
                'status' => true,
                'data' =>  $listStatistics
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
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
