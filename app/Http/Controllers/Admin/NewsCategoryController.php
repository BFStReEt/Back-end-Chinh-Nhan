<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\NewsCategory;
use App\Models\NewsCategoryDesc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class NewsCategoryController extends Controller
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
                'action'=>'show all newsCategory',
                'cat'=>'newsCategory',
            ]);

            if(Gate::allows('QUẢN LÝ TIN TỨC.Quản lý danh mục.manage')){
                if($request->data == 'undefined' || $request->data =="")
                {
                    $newsCategory = NewsCategory::with('newsCategoryDesc')->get();
                }
                else
                {
                    $newsCategory = NewsCategory::with('newsCategoryDesc')->whereHas('newsCategoryDesc', function ($query) use ($request) {
                        $query->where("cat_name", 'like', '%' . $request->data . '%');
                    })->get();
                }
                $response = [
                    'status' => true,
                    'list' => $newsCategory,
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
        $newsCategory = new NewsCategory();
        $newsCategoryDesc = new NewsCategoryDesc();

        try {

            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'add a newsCategory',
                'cat'=>'newsCategory',
            ]);
            if(Gate::allows('QUẢN LÝ TIN TỨC.Quản lý danh mục.add')){
                $newsCategory->fill([
                    'cat_code' => NewsCategory::max('cat_id')+1,
                    'parentid'=> 0,
                    'picture' => "",
                    'is_default' => 0,
                    'show_home' => 0,
                    'focus_order' => 0,
                    'menu_order' => 0,
                    'views' => 0,
                    'display' => $request->input('display'),
                    'adminid' => 1,
                ])->save();
                $newsCategoryDesc->cat_id = $newsCategory->cat_id;
                $newsCategoryDesc->cat_name = $request->input('cat_name');
                $newsCategoryDesc->description = $request->input('description');
                $newsCategoryDesc->friendly_url = $request->input('friendly_url');
                $newsCategoryDesc->friendly_title = $request->input('friendly_title');
                $newsCategoryDesc->metakey = $request->input('metakey');
                $newsCategoryDesc->metadesc = $request->input('metadesc');
                $newsCategoryDesc->lang = "vi";
                $newsCategoryDesc->save();

                $response = [
                    'status' => true,
                    'newsCategory' => $newsCategory,
                    'newsCategoryDesc' => $newsCategoryDesc,
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
    public function edit(Request $request,string $id)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'edit a newsCategory',
                'cat'=>'newsCategory',
            ]);
            if(Gate::allows('QUẢN LÝ TIN TỨC.Quản lý danh mục.edit')){
                $newsCategory = NewsCategory::with('newsCategoryDesc')->find($id);
                return response()->json([
                'status'=> true,
                'newsCategory' => $newsCategory
            ]);
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
            'action'=>'update a newsCategory',
            'cat'=>'newsCategory',
        ]);
        if(Gate::allows('QUẢN LÝ TIN TỨC.Quản lý danh mục.update')){
            $newsCategory = new NewsCategory();
            $newsCategoryDesc = new NewsCategoryDesc();
            $listNewsCategory = NewsCategory::Find($id);

            $listNewsCategory->cat_code = $listNewsCategory->cat_code;
            $listNewsCategory->parentid = 0;
            $listNewsCategory->picture = "";
            $listNewsCategory->is_default = 0;
            $listNewsCategory->show_home = 0;
            $listNewsCategory->focus_order = 0;
            $listNewsCategory->menu_order = 0;
            $listNewsCategory->views = $listNewsCategory->views;
            $listNewsCategory->display = $request->input('display');
            $listNewsCategory->adminid = 1;
            $listNewsCategory->save();

            $newsCategoryDesc = NewsCategoryDesc::where('cat_id', $id)->first();
            if ($newsCategoryDesc) {
                $newsCategoryDesc->cat_name = $request->input('cat_name');
                $newsCategoryDesc->description = $request->input('description');
                $newsCategoryDesc->friendly_url = $request->input('friendly_url');
                $newsCategoryDesc->friendly_title = $request->input('friendly_title');
                $newsCategoryDesc->metakey = $request->input('metakey');
                $newsCategoryDesc->metadesc = $request->input('metadesc');
                $newsCategoryDesc->lang = "vi";
                $newsCategoryDesc->save();
            }
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'delete a newsCategory',
                'cat'=>'newsCategory',
            ]);
            if(Gate::allows('QUẢN LÝ TIN TỨC.Quản lý danh mục.del')){
                $list = NewsCategory::Find($id)->delete();
                return response()->json([
                    'status'=>true
                ]);
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
    public function deleteAll(Request $request){
        try{
            if(Gate::allows('QUẢN LÝ TIN TỨC.Quản lý danh mục.del')){
            $arr =$request->data;
            if( $arr)
            {
                foreach ($arr as $item) {
                    $list = NewsCategory::Find($item)->delete();
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
