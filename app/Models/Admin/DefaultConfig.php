<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class DefaultConfig extends Model
{

    protected $connection = 'query_admin';
    protected $table = "kite_default_config";
    protected $primaryKey = "id";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];


    public function getDefaultConfigList($params)
    {
        $default_config_list =$this->select("*");
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $default_config_list = $default_config_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("id")
            ->get()->toArray();
        return $default_config_list;
    }
    public function getDefaultConfigByName($name,$type)
    {
        $default_config_info =$this->select("*")
                    ->where("name",$name)
                    ->where("type",$type)
            ->get()->first();
        if(isset($default_config_info->id))
        {
            $default_config_info = $default_config_info->toArray();
        }
        else
        {
            $default_config_info = [];
        }
        return $default_config_info;
    }

    public function getDefaultCount(){
        return true;
    }
    public function getDefaultById($id)
    {

        $get_default_info =$this->select("*")
            ->where("id",$id)->first();
        if(isset($get_default_info->id))
        {
            $get_default_info = $get_default_info->toArray();
        }
        else
        {
            $get_default_info = [];
        }
        return $get_default_info;
    }

    public function getDefaultConfigByKey($keys=[]){
        $default_config_info =$this->select("*")
            ->whereIn("key",$keys)
            ->get()->first();
        if(isset($default_config_info->id))
        {
            $default_config_info = $default_config_info->toArray();
        }
        else
        {
            $default_config_info = [];
        }
        return $default_config_info;
    }



}
