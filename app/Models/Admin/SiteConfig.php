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


    public function getSiteConfig List($params)
    {
        $keys = $params['keys'] ?? [];
        $default_SiteConfig _list=[];
        $default_field = ['id', 'k', 'v'];
        $field = isset($params['field']) && !empty($params['field']) ? $params['field'] : $default_field;
        $default_SiteConfig _list = $this->select($field);
        if (!empty($keys)) {
            $default_SiteConfig _list->whereIn('key', $keys);
        }
        $pageSizge = $params['page_size'] ?? 3;
        $page = $params['page'] ?? 1;
        $default_SiteConfig _list = $default_SiteConfig _list
            ->limit($pageSizge)
            ->offset(($page - 1) * $pageSizge)
            ->get()->toArray();

        return $default_SiteConfig _list;
    }

    public function getSiteConfig ByName($name, $type)
    {
        $site_config _info = $this->select("*")
            ->where("name", $name)
            ->where("type", $type)
            ->get()->first();
        if (isset($site_config _info->id)) {
            $site_config _info = $site_config _info->toArray();
        } else {
            $site_config _info = [];
        }
        return $site_config _info;
    }

    public function getSiteConfig Count($params)
    {
        $Ssite_config _count = $this;
        $keys = $params['keys'] ?? [];
        if (!empty($keys)) {
            if (!empty($keys)) {
                Ssite_config = Ssite_config->whereIn('key', $keys);
            }

        }
        if (isset($params['game'])) {
            Ssite_config = Ssite_config->where("game", $params['game']);
        }

        return Ssite_config->count();
        //return true;
    }

    public function getSiteConfig ById($id)
    {

        $get_site_config _info = $this->select("*")
            ->where("id", $id)->first();
        if (isset($get_site_config _info->id)) {
            $get_site_config _info = $get_site_config _info->toArray();
        } else {
            $get_site_config _info = [];
        }
        return $get_site_config _info;
    }

    public function getSiteConfig ByKey($keys = [])
    {
        $get_site_config _info = $this->select("*")
            ->whereIn("key", $keys)
            ->get()->first();
        if (isset($get_site_config _info->id)) {
            $get_site_config _info = $get_site_config _info->toArray();
        } else {
            $get_site_config _info = [];
        }
        return $get_site_config _info;
    }


}
