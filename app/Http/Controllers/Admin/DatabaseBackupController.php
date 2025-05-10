<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Gate;
class DatabaseBackupController extends Controller
{
    private function getColumnSize($columnTypes, $rowCount)
    {
        $dataSize=0;
        foreach ($columnTypes as $column) {
            switch ($column->Type) {
                case 'int':
                case 'integer':
                    $dataSize += 4 * $rowCount; // 4 bytes cho mỗi cột kiểu int
                    break;
                case 'varchar':
                case 'char':
                    preg_match('/\((.*?)\)/', $column->Type, $matches);
                    $length = isset($matches[1]) ? (int)$matches[1] : 255;
                    $dataSize += $length * $rowCount;
                    break;
                case 'text':
                    $dataSize += 65535 * $rowCount;
                    break;
                case 'blob':
                    $dataSize += 65535 * $rowCount;
                    break;
                case 'datetime':
                case 'timestamp':
                    $dataSize += 8 * $rowCount;
                    break;
                default:
                    $dataSize += 10 * $rowCount;
                    break;
            }
        }

    }
    public function showNameTable(){
        if(Gate::allows('THÔNG TIN HỆ THỐNG.Quản lý dữ liệu.manage')){
            $listTable=array("admin","adminlogs","admin_role","advertise","ad_pos","comment","coupon","coupondes","coupondesusing","coupon_status","department",
            "gift_promotion","gift_promotiondesusing","guide","guide_desc","infor_address","invoice_order","list_cart","members","menu","menu_desc","news","news_category",
            "news_category_desc","news_desc","order_address","order_detail","order_status","order_sum","payment_method","permissions","present","presentdesusing","products",
            "product_advertise","product_brand","product_brand_desc","product_category","product_category_desc","product_cat_option","product_cat_option_desc","product_descs",
            "product_flash_sale","product_group","product_pictured","product_promotion","product_status","promotion","promotion_desc","roles","role_permission","service",
            "service_desc","shipping_method","statistics_pages","about","about_desc","cate_parent_permission","config","contact","contact_config","contact_config_desc","contact_qoute",
            "contact_staff","faqs","faqs_category","faqs_category_desc","faqs_desc","group_permissions","icon","maillist","mail_template","support","support_group");
            //return $table;
            $inforTable=[];
            $columnTypes=null;
            $databaseName = env('DB_DATABASE');

            foreach($listTable as $table){
                $columnTypes = DB::select('SHOW COLUMNS FROM ' . $table);
                $rowCount=DB::table($table)->count();
                $dataSize=0;
                foreach ($columnTypes as $column) {
                    switch ($column->Type) {
                        case 'int':
                        case 'integer':
                            $dataSize += 4 * $rowCount;
                            break;
                        case 'varchar':
                        case 'char':
                            preg_match('/\((.*?)\)/', $column->Type, $matches);
                            $length = isset($matches[1]) ? (int)$matches[1] : 255;
                            $dataSize += $length * $rowCount;
                            break;
                        case 'text':
                            $dataSize += 65535 * $rowCount;
                            break;
                        case 'blob':
                            $dataSize += 65535 * $rowCount;
                            break;
                        case 'datetime':
                        case 'timestamp':
                            $dataSize += 8 * $rowCount;
                            break;
                        default:
                            $dataSize += 10 * $rowCount;
                            break;
                    }
                }
                $creationTimeResult = DB::select("
                SELECT create_time, update_time
                FROM information_schema.TABLES
                WHERE table_schema = :databaseName
                AND table_name = :tableName
            ", [
                'databaseName' => $databaseName,
                'tableName' => $table
            ]);
            $creationTime = $creationTimeResult[0]->create_time ?? 'N/A';
            $updateTime = $creationTimeResult[0]->update_time ?? 'N/A';
            $inforTable[]=[
                    'nameTable'=>$table,
                    'rowCount'=> $rowCount,
                    'dataSize'=>$dataSize. ' bytes',
                    'creationTime' => $creationTime,
                    'updateTime' => $updateTime
                ];
            }
            return response()->json([
                'status'=>true,
                'data'=>$inforTable
            ]);
    } else {
        return response()->json([
            'status'=>false,
            'mess' => 'no permission',
        ]);
    }
    }
    public function backupDatabse($table){

        //ENTER THE RELEVANT INFO BELOW
        $mysqlHostName      = env('DB_HOST');
        $mysqlUserName      = env('DB_USERNAME');
        $mysqlPassword      = env('DB_PASSWORD');
        $DbName             = env('DB_DATABASE');
        $backup_name        = "mybackup.sql";

        $tables = array($table);

        $connect = new \PDO("mysql:host=$mysqlHostName;dbname=$DbName;charset=utf8", "$mysqlUserName", "$mysqlPassword",array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
        $get_all_table_query = "SHOW TABLES";
        $statement = $connect->prepare($get_all_table_query);
        $statement->execute();
        $result = $statement->fetchAll();

        $output = '';
        foreach($tables as $table)
        {
         $show_table_query = "SHOW CREATE TABLE " . $table . "";
         $statement = $connect->prepare($show_table_query);
         $statement->execute();
         $show_table_result = $statement->fetchAll();

         foreach($show_table_result as $show_table_row)
         {
          $output .= "\n\n" . $show_table_row["Create Table"] . ";\n\n";
         }
         $select_query = "SELECT * FROM " . $table . "";
         $statement = $connect->prepare($select_query);
         $statement->execute();
         $total_row = $statement->rowCount();

         for($count=0; $count<$total_row; $count++)
         {
          $single_result = $statement->fetch(\PDO::FETCH_ASSOC);
          $table_column_array = array_keys($single_result);
          $table_value_array = array_values($single_result);
          $output .= "\nINSERT INTO $table (";
          $output .= "" . implode(", ", $table_column_array) . ") VALUES (";
          $output .= "'" . implode("','", $table_value_array) . "');\n";
         }
        }

        $file_name = 'database_backup_on_' . date('y-m-d') . '.sql';

        $file_handle = fopen($file_name, 'w+');
        fwrite($file_handle, $output);
        fclose($file_handle);


        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file_name));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_name));
        ob_clean();
        flush();
        readfile($file_name);
        unlink($file_name);
    }



    public function index()
    {
        //
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
