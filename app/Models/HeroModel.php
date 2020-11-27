<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Libs\CollectLib;

class HeroModel extends Model
{
    protected $table = "hero_info";
    protected $primaryKey = "hero_info";
    public $timestamps = false;
    protected $connection = "query_list";

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

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $attributes = [
        "location"=>"",
        "description"=>"",
        "aka"=>[],
        "honor_list"=>[],
        "race_stat"=>[],
    ];
    protected $toJson = [
        "race_stat","honor_list","aka","race_stat"
    ];
    public function getTeamList($params)
    {
        $team_list =$this->select("*");
        //游戏类型
        if(isset($params['game']) && strlen($params['game'])>=3)
        {
            $team_list = $team_list->where("game",$params['game']);
        }
        //战队名称
        if(isset($params['team_name']) && strlen($params['team_name'])>=3)
        {
            $team_list = $team_list->where("team_name",$params['team_name']);
        }
        //战队名称
        if(isset($params['en_name']) && strlen($params['en_name'])>=3)
        {
            $team_list = $team_list->where("en_name",$params['en_name']);
        }
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $team_list = $team_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("id")
            ->get()->toArray();
        return $team_list;
    }
    public function getTeamByName($team_name,$game)
    {
        echo $team_name."-".$game."\n";
        $team_info =$this->select("*")
                    ->where("team_name",$team_name)
                    ->where("game",$game)
                    ->get()->first();
        if(isset($team_info->team_id))
        {
            $team_info = $team_info->toArray();
        }
        else
        {
            $team_info = [];
        }
        return $team_info;
    }
    public function insertTeam($data)
    {
        foreach($this->attributes as $key => $value)
        {
            if(!isset($data[$key]))
            {
                $data[$key] = $value;
            }

        }
        foreach($this->toJson as $key)
        {
            if(isset($data[$key]))
            {
                $data[$key] = json_encode($data[$key]);
            }
        }
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['create_time']))
        {
            $data['create_time'] = $currentTime;
        }
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->insertGetId($data);
    }

    public function updateTeam($team_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('team_id',$team_id)->update($data);
    }

    public function saveHero($game,$data)
    {
        $data['team_name'] = preg_replace("/\s+/", "",$data['team_name']);
        $data['team_name'] = trim($data['team_name']);
        $data['aka'] = ($data['aka']=="")?[]:[$data['aka']];
        $currentTeam = $this->getTeamByName($data['team_name'],$game);
        if(!isset($currentTeam['team_id']))
        {
            echo "toInsert:"."\n";
            return $this->insertTeam(array_merge($data,["game"=>$game]));
        }
        else
        {
            echo "toUpdate:".$currentTeam['team_id']."\n";
        }
    }
}
