<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Links extends Model
{

    protected $connection = 'query_admin';
    protected $table = "kite_link";
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


    public function getLinkList($params)
    {
        $game = $params['game'] ?? '';
        $default_link_list=[];
        $default_field = ['id', 'name', 'url', 'logo','game'];
        $field = isset($params['field']) && !empty($params['field']) ? $params['field'] : $default_field;
        $default_link_list = $this->select($field);
        $pageSizge = $params['page_size'] ?? 3;
        $page = $params['page'] ?? 1;
        if(!empty($game)){
            $default_link_list->where('game',$game);
        }
        $default_link_list = $default_link_list

            ->limit($pageSizge)
            ->offset(($page - 1) * $pageSizge)
            ->orderBy("sort")
            ->get()->toArray();

        return $default_link_list;
    }

    public function getLinkByName($name, $type)
    {
        $default_config_info = $this->select("*")
            ->where("name", $name)
            ->where("type", $type)
            ->get()->first();
        if (isset($default_config_info->id)) {
            $default_config_info = $default_config_info->toArray();
        } else {
            $default_config_info = [];
        }
        return $default_config_info;
    }

    public function getLinkCount($params)
    {
        $link_count = $this;
        $game = $params['game'] ?? '';
        if(!empty($game)){
            $link_count->where('game',$game);
        }
        return $link_count->count();
        //return true;
    }

    public function getLinkById($id)
    {

        $get_link_info = $this->select("*")
            ->where("id", $id)->first();
        if (isset($get_link_info->id)) {
            $get_link_info = $get_link_info->toArray();
        } else {
            $get_link_info = [];
        }
        return $get_link_info;
    }

    public function getLinkByKey($keys = [])
    {
        $link_info = $this->select("*")
            ->whereIn("key", $keys)
            ->get()->first();
        if (isset($link_info->id)) {
            $link_info = $link_info->toArray();
        } else {
            $link_info = [];
        }
        return $link_info;
    }


}
