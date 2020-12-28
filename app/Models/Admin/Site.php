<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{

    protected $connection = 'query_admin';
    protected $table = "kite_site";
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


    public function getSiteList($params)
    {
        $keys = $params['keys'] ?? [];
        $default_site_list=[];
        $default_field = ['id', 'name', 'logo', 'title','keywords','description','game','content'];
        $field = isset($params['field']) && !empty($params['field']) ? $params['field'] : $default_field;
        $default_site_list = $this->select($field);
        if (!empty($keys)) {
            $default_site_list->whereIn('key', $keys);
        }
        $pageSizge = $params['page_size'] ?? 3;
        $page = $params['page'] ?? 1;
        $default_site_list = $default_site_list
            ->limit($pageSizge)
			 ->orderBy("sort")
            ->offset(($page - 1) * $pageSizge)
            ->get()->toArray();

        return $default_site_list;
    }

    public function getSiteByName($name, $type)
    {
        $site_info = $this->select("*")
            ->where("name", $name)
            ->where("type", $type)
            ->get()->first();
        if (isset($site_info->id)) {
            $site_info = $site_info->toArray();
        } else {
            $site_info = [];
        }
        return $site_info;
    }

    public function getSiteCount($params)
    {
        $site_count = $this;
        $keys = $params['keys'] ?? [];
        if (!empty($keys)) {
            if (!empty($keys)) {
                $site_count = $site_count->whereIn('key', $keys);
            }

        }
        if (isset($params['game'])) {
            $site_count = $site_count->where("game", $params['game']);
        }

        return $site_count->count();
        //return true;
    }

    public function getSiteById($game)
    {

        $get_site_info = $this->select("id","name","title","keywords","description","content")
            ->where(["game"=>$game,'status'=>0])->orderBy("sort","desc")->first();
        if (isset($get_site_info->id)) {
            $get_site_info = $get_site_info->toArray();
            $get_site_info['content']=htmlspecialchars_decode($get_site_info['content']);
        } else {
            $get_site_info = [];
        }
        return $get_site_info;
    }




}
