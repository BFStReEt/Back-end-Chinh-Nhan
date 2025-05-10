<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\GiftPromotion;
use App\Models\Menu;
use App\Models\MenuDes;
use Illuminate\Support\Facades\Auth;
use Gate;
class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            if(Gate::allows('HÌNH THỨC, NỘI DUNG.Quản lý Menu.manage')){
            $name=$request->data;
            $menu = Menu::select('menu_id','parentid')
            ->whereHas('menuDesc', function ($query) use ($name) {
                $query->where("title", 'like', '%' .$name.'%');
            })->with('menuDesc')->where('pos', 'primary')->where('parentid',0)->get();
            $menu->each(function ($item) {
                $link = $item->menuDesc->link;

            // Remove base URL if present
            if (strpos($link, "https://vitinhnguyenkim.vn/") === 0) {
                $link = substr($link, strlen("https://vitinhnguyenkim.vn/"));
            }

            // Remove leading slash if present
            if (strpos($link, '/') === 0) {
                $link = substr($link, 1);
            }

            $item->menuDesc->link = $link;
            });
            // return $name;
            $result = [];
            if (isset($menu)) {
                foreach ($menu as $value) {
                    $data = $value;
                    $dataParent = [];
                    $menuChild = Menu::with('menuDesc')->select('menu_id','parentid')
                    ->where('pos', 'primary')->where('parentid',$value->menu_id)->get();
                    //return $menuChild;
                    if (isset($menuChild)) {
                        foreach ($menuChild as $value2) {
                            $dataParent2 = $value2;
                            $menuSubChild = Menu::with('menuDesc')->select('menu_id','parentid')
                            ->where('pos', 'primary')->where('parentid',$value2->menu_id)->get();
                            $parent = $menuSubChild ?? [];
                            $dataParent2['parentx'] = $parent;
                            $dataParent[] = $dataParent2;
                        }
                    }

                    $data['parenty'] = $dataParent;
                    $result[] = $data;
                }
            }
           return response()->json([
                'message' => 'Fetched from database',
                'data' => $result,
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
        try{
            if(Gate::allows('HÌNH THỨC, NỘI DUNG.Quản lý Menu.add')){
            $disPath = public_path();
            $menu=new Menu();
            $menuDes=new MenuDes();

            $filePath = '';
            if ( $request->picture != null )
            {
                // $DIR = $disPath.'\uploads\weblink';
                $DIR = 'uploads/weblink';

                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                // $file = $DIR .'\\'. $name . '.png';
                // $filePath = 'weblink/'.$name . '.png';
                // file_put_contents( $file,  $base64Img );
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'weblink/'.$name . '.png';
                file_put_contents( $file,  $base64Img );
            }


            $menu->fill( [
                'target' =>$request->target??'_self',
                'parentid'=>$request->parentid,
                'pos' => 'primary',
                'menu_icon' => $filePath,
                'menu_class'=>0,
                'menu_order'=>110,
                'display' => $request->input( 'display' ),
                'date_post' => strtotime( 'now' ),
                'date_update' => strtotime( 'now' ),
                'adminid' => 1,
            ] )->save();

            $menuDes->menu_id= $menu->menu_id;
            $menuDes->name= $request->name;
            $menuDes->title= $request->title;
            $menuDes->link= $request->link;
            $menuDes->lang="vi";
            $menuDes->save();
            return response()->json([
                'status'=>true,
                'menu'=>$menu,
                'menuDes'=>$menuDes
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
                    'status' => false,
                    'error' => $errorMessage,
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
            if(Gate::allows('HÌNH THỨC, NỘI DUNG.Quản lý Menu.edit')){
            $menu=Menu::where('menu_id',$id)->first();
            $menuDes=MenuDes::where('menu_id',$id)->first();
            return response()->json([
                'status'=>true,
                'menu'=> $menu,
                'menuDes'=>$menuDes
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
                    'status' => false,
                    'error' => $errorMessage,
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
            if(Gate::allows('HÌNH THỨC, NỘI DUNG.Quản lý Menu.update')){
            $disPath = public_path();

            $menu=Menu::where('menu_id',$id)->first();
            $menuDes=MenuDes::where('menu_id',$id)->first();

            $filePath = '';


            if ( $request->picture != null && $request->picture != $menu ->menu_icon )
            {
                //return $request->picture;
                // $DIR = $disPath.'\uploads\weblink';
                $DIR = 'uploads/weblink';

                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];

                //return response()->json( $file_chunks );
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                // $file = $DIR .'\\'. $name . '.png';
                // $filePath = 'weblink/'.$name . '.png';
                //file_put_contents( $file,  $base64Img );
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'weblink/'.$name . '.png';
                file_put_contents( $file,  $base64Img );

            } else {
                $filePath = $menu ->menu_icon;
            }


            $menu->fill( [
                'target' =>$request->target??'_self',
                'parentid'=>$request->parentid,
                'pos' => 'primary',
                'menu_icon' => $filePath,
                'menu_class'=>0,
                'menu_order'=>110,
                'display' => $request->input( 'display' ),
                'date_post' => strtotime( 'now' ),
                'date_update' => strtotime( 'now' ),
                'adminid' => 1,
            ] )->save();

            $menuDes->menu_id= $menu->menu_id;
            $menuDes->name= $request->name;
            $menuDes->title= $request->title;
            $menuDes->link= $request->link;
            $menuDes->lang="vi";
            $menuDes->save();
            return response()->json([
                'status'=>true,
                'menu'=>$menu,
                'menuDes'=>$menuDes
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
                    'status' => false,
                    'error' => $errorMessage,
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
            if(Gate::allows('HÌNH THỨC, NỘI DUNG.Quản lý Menu.del')){
                $menu=Menu::where('menu_id',$id)->first();
                $menu->delete();
                $menuDes=MenuDes::where('menu_id',$id)->first();
                $menuDes->delete();
                return response()->json([
                    'status'=>true,
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
                    'status' => false,
                    'error' => $errorMessage,
                ];
            return response()->json($response, 500);
        }
    }
}
