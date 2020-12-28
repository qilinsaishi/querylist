<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class SiteConfig extends Model
{

    protected $connection = 'query_admin';
    protected $table = "kite_site_config";
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


    public function getSiteConfig ($params)
    {
        $keys = $params['keys'] ?? [];
        $default_site_config_list=[];
        $default_field = ['id', 'k', 'v'];
        $field = isset($params['field']) && !empty($params['field']) ? $params['field'] : $default_field;
        $default_site_config_list = $this->select($field);
        if (!empty($keys)) {
            $default_site_config_list->whereIn('key', $keys);
        }
        $pageSizge = $params['page_size'] ?? 3;
        $page = $params['page'] ?? 1;
        $default_site_config_list = $default_site_config_list
            ->limit($pageSizge)
            ->offset(($page - 1) * $pageSizge)
            ->get()->toArray();

        return $default_site_config_list;
    }


    public function getSiteConfigById($id)
    {

        $get_site_config_info = $this->select("*")
            ->where("id", $id)->first();
        if (isset($get_site_config_info->id)) {
            $get_site_config_info = $get_site_config_info->toArray();
        } else {
            $get_site_config_info = [];
        }
        return $get_site_config_info;
    }




}
