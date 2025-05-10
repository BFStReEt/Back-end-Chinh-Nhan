<?php

namespace App\Repositories;

use App\Models\Admin;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Hash;

class AdminRepository implements AdminRepositoryInterface
{
    public function findByAdmin($name){
        $admin = Admin::where('username',$name)->first();
        return $admin;
    }

    public function findByCondition($condition = []){
        // $admin = Admin::where('username',$name)->first();
        // return $admin;
        $query = Admin::query();
        foreach($condition as $key => $val){
            $query->where($val[0], $val[1] , $val[2]);
        }
        return $query->first();
    }
    public function all($relation=[]){
        $query = Admin::with($relation);
        
    }
    public function create($data=[]){
        $userAdmin = new Admin();
        $userAdmin->username = $data['username'];
        $userAdmin->password = Hash::make($data['password']);
        $userAdmin->email = $data['email'];
        $userAdmin->display_name = $data['display_name'];
        $userAdmin->avatar = isset($data['avatar']) ? $data['avatar'] : null;
        $userAdmin->skin = "";
        $userAdmin->is_default = 0;
        $userAdmin->lastlogin = 0;
        $userAdmin->code_reset = Hash::make($data['password']);
        $userAdmin->menu_order = 0;
        $userAdmin->phone = $data['phone'];
        $userAdmin->status = $data['status'];
        $userAdmin->depart_id = $data['depart_id'];
        $userAdmin->save();
        return $userAdmin;
    }
}


























































{
    
}