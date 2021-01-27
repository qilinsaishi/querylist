<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class GameConfig extends Model
{

    protected $connection = 'query_admin';
    protected $table = "kite_game_config";
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


    public function getGameConfigList($params)
    {
        $keys = $params['keys'] ?? [];
        $game = $params['game'] ?? '';
        $default_field = ['id', 'name', 'key', 'value'];
        $field = isset($params['fields']) && !empty($params['fields']) ? $params['fields'] : $default_field;
        $default_config_list = $this->select($field);
        $count=3;
        if (!empty($keys)) {
            $default_config_list->whereIn('key', $keys);
            $count=count($keys);
        }
        if($game){
            $default_config_list->where('game', $game);
        }

        $pageSizge = $params['page_size'] ?? $count;
        $page = $params['page'] ?? 1;
        $default_config_list = $default_config_list
            ->limit($pageSizge)
            ->offset(($page - 1) * $pageSizge)
            ->orderBy("id")
            ->get()->toArray();
        $data = [];
        if (isset($default_config_list) && !empty($default_config_list)) {
            foreach ($default_config_list as $val) {
                $data[$val['key']] = $val;
            }
        }

        return $data;
    }

    public function getGameConfigByName($name, $game)
    {
        $site_info = $this->select("*")
            ->where("name", $name)
            ->where("game", $game)
            ->get()->first();
        if (isset($site_info->id)) {
            $site_info = $site_info->toArray();
        } else {
            $site_info = [];
        }
        return $site_info;
    }

    public function getGameConfigCount($params)
    {
        $game=$params;
        $site_count = $this;
        if ($game) {
            $site_count = $site_count->where("game", $game);
        }

        return $site_count->count();
        //return true;
    }

    public function getGameConfigById($game)
    {

        $get_game_config_info = $this->select("id","title","logo","game","content","status")
            ->where(["game"=>$game,'status'=>0])->orderBy("id","desc")->first();
        if (isset($get_game_config_info->id)) {
            $get_game_config_info = $get_game_config_info->toArray();
            $get_game_config_info['content']=htmlspecialchars_decode($get_game_config_info['content']);
        } else {
            $get_game_config_info = [];
        }
        return $get_game_config_info;
    }




}
