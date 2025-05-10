<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryDesc;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' => $stringTime,
            'ip' => $request->ip(),
            'action' => 'show all category',
            'cat' => 'category',
        ]);
        if (Gate::allows('QUẢN LÝ SẢN PHẨM.Danh mục sản phẩm.manage')) {
            $name = $request->data;
            $categories = Category::with('categoryDesc')->whereHas('categoryDesc', function ($query) use ($name) {
                $query->where("cat_name", 'like', '%' . $name . '%');
            })
                ->select('cat_id', 'parentid', 'show_home', 'background')->where('parentid', 0)->get();
            $result = [];
            if ($categories) {
                foreach ($categories as $value) {
                    $data = $value;
                    $dataParent = [];
                    $cateChild = Category::with('categoryDesc')->select('cat_id', 'parentid')
                        ->where('parentid', $value->cat_id)->get();
                    if (isset($cateChild)) {
                        foreach ($cateChild as $value2) {
                            $dataParent2 = $value2;
                            $menuSubChild = Category::with('categoryDesc')->select('cat_id', 'parentid')
                                ->where('parentid', $value2->cat_id)->get();
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
                'status' => true,
                'data' => $result,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'mess' => 'no permission',
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

    public function showCategory(Request $request)
    {
        $catId = $request->catId;

        $categoryDesc = \DB::table('product_category_desc')
            ->where('cat_id', $catId)
            ->select('cat_id', 'cat_name')
            ->first();

        if (!$categoryDesc) {
            return response()->json([
                'status' => 'false',
                'message' => 'Category not found',
            ], 404);
        }

        $propertiesCategory = \DB::table('properties_category')
            ->where('cat_id', $catId)
            ->where('parentid', 0)
            ->orderBy('stt', 'asc')
            ->pluck('properties_id');

        $properties = \DB::table('properties')
            ->whereIn('id', $propertiesCategory)
            ->select('id', 'title')
            ->get();

        $propertiesValues = \DB::table('properties_value')
            ->whereIn('properties_id', $propertiesCategory)
            ->select('id', 'name', 'properties_id')
            ->get();

        $groupedPropertiesValues = $propertiesValues->groupBy('properties_id');

        $properties = $properties->map(function ($property) use ($groupedPropertiesValues) {
            $property->op_id = $property->id;
            unset($property->id);
            $property->slug = Str::slug($property->title);
            $property->optionChild = $groupedPropertiesValues->get($property->op_id)
            ? $groupedPropertiesValues->get($property->op_id)->map(function ($value) {
                $value->slug = Str::slug($value->name);
                $value->op_id = $value->id;
                $value->title = $value->name;
                unset($value->id);
                unset($value->name);
                unset($value->properties_id);
                return $value;
            })
            : [];
            return $property;
        });

        $response = [
            'status' => true,
            'listOption' => $properties,
        ];

        return response()->json($response);
    }

    public function store(Request $request)
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' => $stringTime,
            'ip' => $request->ip(),
            'action' => 'add category',
            'cat' => 'category',
        ]);

        // $disPath = public_path();
        $category = new Category();
        $categoryDesc = new CategoryDesc();
        try {
            if (Gate::allows('QUẢN LÝ SẢN PHẨM.Danh mục sản phẩm.add')) {
                $filePath = '';
                if ($request->picture != null) {

                    $DIR = 'uploads/category';
                    $httpPost = file_get_contents('php://input');
                    $file_chunks = explode(';base64,', $request->picture[0]);
                    $fileType = explode('image/', $file_chunks[0]);
                    $image_type = $fileType[0];

                    //return response()->json( $file_chunks );
                    $base64Img = base64_decode($file_chunks[1]);
                    $data = iconv('latin5', 'utf-8', $base64Img);
                    $name = uniqid();
                    // $file = $DIR .'\\'. $name . '.png';
                    $file = public_path($DIR) . '/' . $name . '.png';
                    $filePath = 'category/' . $name . '.png';

                    file_put_contents($file, $base64Img);
                }

                $filePath1 = '';
                if ($request->background != null) {
                    $DIR = 'uploads/background';
                    $httpPost = file_get_contents('php://input');
                    $file_chunks = explode(';base64,', $request->background[0]);
                    $fileType = explode('image/', $file_chunks[0]);
                    $image_type = $fileType[0];

                    //return response()->json( $file_chunks );
                    $base64Img = base64_decode($file_chunks[1]);
                    $data = iconv('latin5', 'utf-8', $base64Img);
                    $name1 = uniqid();
                    // $file = $DIR .'\\'. $name1 . '.png';
                    $file = public_path($DIR) . '/' . $name1 . '.png';
                    $filePath1 = 'background/' . $name1 . '.png';

                    file_put_contents($file, $base64Img);

                }

                $pattern = '/[^0-9]/';
                $replacement = '';
                $numberParentid = preg_replace($pattern, $replacement, $request->input('parentid'));

                // $cat = Category::max( 'cat_id' )+1;

                // if ( $numberParentid == 0 ) {
                //     $cat_code = $cat;
                // } else {
                //     $cat_code = $numberParentid.'_'.$cat;
                // }

                $category->fill([
                    // 'cat_code' =>  $cat_code,
                    'parentid' => $numberParentid,
                    'picture' => $filePath,
                    'background' => $filePath1,
                    'color' => $request->input('color'),
                    'psid' => '1',
                    'is_default' => '1',
                    'is_buildpc' => '1',
                    'show_home' => $request->input('show_home'),
                    // 'list_brand' => implode( ',', $request->input( 'list_brand' ) ),
                    // 'list_price' => serialize( $request->input( 'list_price' ) ),
                    // 'list_support' => implode( ',', $request->input( 'list_support' ) ),
                    'menu_order' => '1',
                    'views' => '0',
                    'display' => $request->input('display'),
                    'date_post' => 0,
                    'date_update' => 0,
                    'adminid' => 1,
                ])->save();
                $cat = Category::max('cat_id');
                if ($numberParentid == 0) {
                    $cat_code = $cat;
                } else {
                    $cat_code = $numberParentid . '_' . $cat;
                }

                $categoryUpdate = Category::where('cat_id', $category->cat_id)->first();
                $categoryUpdate->cat_code = $cat_code;
                $categoryUpdate->save();

                $categoryDesc->cat_id = $category->cat_id;
                $categoryDesc->cat_name = $request->input('cat_name');
                $categoryDesc->home_title = $request->input('home_title');
                $categoryDesc->description = $request->input('description');
                $categoryDesc->friendly_url = $request->input('friendly_url');
                $categoryDesc->friendly_title = $request->input('friendly_title');
                $categoryDesc->metakey = $request->input('metakey');
                $categoryDesc->metadesc = $request->input('metadesc');
                $categoryDesc->lang = 'vi';
                $categoryDesc->script_code = $request->input('script_code');
                $categoryDesc->save();
                $response = [
                    'status' => true,
                    'category' => $categoryUpdate,
                    'categoryDesc' => $categoryDesc,
                ];
                return response()->json($response, 200);
            } else {
                return response()->json([
                    'status' => false,
                    'mess' => 'no permission',
                ]);
            }

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
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
    public function edit(Request $request, string $id)
    {
        try {
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' => $stringTime,
                'ip' => $request->ip(),
                'action' => 'edit category',
                'cat' => 'category',
            ]);
            if (Gate::allows('QUẢN LÝ SẢN PHẨM.Danh mục sản phẩm.edit')) {
                $listCategoryDesc = Category::with('categoryDesc')->find($id);
                return response()->json([
                    'status' => true,
                    'category' => $listCategoryDesc]);
            } else {
                return response()->json([
                    'status' => false,
                    'mess' => 'no permission',
                ]);
            }

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
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
        try {

            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' => $stringTime,
                'ip' => $request->ip(),
                'action' => 'update category',
                'cat' => 'category',
            ]);

            if (Gate::allows('QUẢN LÝ SẢN PHẨM.Danh mục sản phẩm.update')) {
                //$disPath = public_path();
                // $validator = CategoryValidate::validate( $request->all() );
                //     if ( $validator->fails() ) {
                //         return response()->json( [
                //             'message'=>'Validations fails',
                //             'errors'=>$validator->errors()
                // ], 422 );
                //     }
                $category = new Category();
                $categoryDesc = new CategoryDesc();
                $list = Category::Find($id);

                if ($request->picture != null && $list->picture != $request->picture) {
                    $filePath = '';
                    $DIR = 'uploads/category';
                    $httpPost = file_get_contents('php://input');
                    $file_chunks = explode(';base64,', $request->picture[0]);
                    $fileType = explode('image/', $file_chunks[0]);
                    $image_type = $fileType[0];

                    //return response()->json( $file_chunks );
                    $base64Img = base64_decode($file_chunks[1]);
                    $data = iconv('latin5', 'utf-8', $base64Img);
                    $name = uniqid();
                    // $file = $DIR .'\\'. $name . '.png';
                    $file = public_path($DIR) . '/' . $name . '.png';
                    $filePath = 'category/' . $name . '.png';

                    file_put_contents($file, $base64Img);
                    // $file = $request->file('picture');
                    // $path = public_path('uploads/category'); // Đường dẫn đến thư mục public/uploads

                    // $fileName = uniqid(). '.png';
                    // //return $file;
                    // $name = $file->getClientOriginalName();

                    // $filePath = 'category/'.$fileName;
                    // $file->move($path, $fileName);
                } else {

                    $filePath = $list->picture;

                }

                $filePath1 = '';
                if ($request->background != null && $list->background != $request->background) {

                    $DIR = 'uploads/background';
                    $httpPost = file_get_contents('php://input');
                    $file_chunks = explode(';base64,', $request->background[0]);
                    $fileType = explode('image/', $file_chunks[0]);
                    $image_type = $fileType[0];

                    //return response()->json( $file_chunks );
                    $base64Img = base64_decode($file_chunks[1]);
                    $data = iconv('latin5', 'utf-8', $base64Img);
                    $name1 = uniqid();
                    // $file = $DIR .'\\'. $name1 . '.png';
                    $file = public_path($DIR) . '/' . $name1 . '.png';
                    $filePath1 = 'background/' . $name1 . '.png';

                    file_put_contents($file, $base64Img);

                } else {

                    $filePath1 = $list->background;

                }

                $numberParentid = preg_replace('/[^0-9]/', '', $request->input('parentid'));

                $cat = Category::max('cat_id') + 1;
                if ($numberParentid == 0) {
                    $cat_code = $cat;
                } else {
                    $cat_code = $numberParentid . '_' . $cat;
                }
                $list->fill([
                    'cat_code' => $cat_code,
                    'parentid' => $numberParentid,
                    'picture' => $filePath,
                    'background' => $filePath1,
                    'color' => $request->input('color'),
                    'psid' => '1',
                    'is_default' => '1',
                    'is_buildpc' => '1',
                    'show_home' => $request->input('show_home'),
                    // 'list_brand' => implode( ',', $request->input( 'list_brand' ) ),
                    // 'list_price' => serialize( $request->input( 'list_price' ) ),
                    // 'list_support' => implode( ',', $request->input( 'list_support' ) ),
                    'menu_order' => '1',
                    'views' => '0',
                    'display' => $request->input('display'),
                    'date_post' => 0,
                    'date_update' => 0,
                ])->save();

                $listCategory = CategoryDesc::where('cat_id', $id)->first();
                if ($listCategory) {
                    $listCategory->cat_name = $request->input('cat_name');
                    $listCategory->home_title = $request->input('home_title');
                    $listCategory->description = $request->input('description');
                    $listCategory->friendly_url = $request->input('friendly_url');
                    $listCategory->friendly_title = $request->input('friendly_title');
                    $listCategory->metakey = $request->input('metakey');
                    $listCategory->metadesc = $request->input('metadesc');
                    $listCategory->lang = 'vi';
                    $listCategory->script_code = $request->input('script_code');
                    $listCategory->save();
                }
                return response()->json([
                    'status' => true,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'mess' => 'no permission',
                ]);
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage,

            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {

            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' => $stringTime,
                'ip' => $request->ip(),
                'action' => 'delete category',
                'cat' => 'category',
            ]);

            if (Gate::allows('QUẢN LÝ SẢN PHẨM.Danh mục sản phẩm.del')) {
                $list = Category::Find($id)->delete();
                CategoryDesc::where('cat_id', $id)->delete();
                return response()->json([
                    'status' => true,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'mess' => 'no permission',
                ]);
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage,

            ];
            return response()->json($response, 500);
        }
    }
    public function deleteAllCategory(string $id)
    {
        try {
            $arr = explode(",", $id);
            if (Gate::allows('QUẢN LÝ SẢN PHẨM.Danh mục sản phẩm.del')) {
                if ($id) {
                    foreach ($arr as $item) {
                        $list = Category::where('cat_id ', $item)->delete();
                        CategoryDesc::where('cat_id', $item)->delete();
                    }
                } else {
                    return response()->json([
                        'status' => false,
                    ], 422);
                }
                return response()->json([
                    'status' => true,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'mess' => 'no permission',
                ]);
            }

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage,
            ];

            return response()->json($response, 500);
        }

    }
}
