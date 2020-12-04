<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Libs\CollectLib;

class PlayerModel extends Model
{
    protected $table = "player_info";
    protected $primaryKey = "player_id";
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
    ];
    protected $toJson = [
        "team_history","event_history","aka","stat"
    ];
    public function getPlayerList($params)
    {
        $player_list =$this->select("*");
        //游戏类型
        if(isset($params['game']) && strlen($params['game'])>=3)
        {
            $player_list = $player_list->where("game",$params['game']);
        }
        //战队名称
        if(isset($params['player_name']) && strlen($params['player_name'])>=3)
        {
            $player_list = $player_list->where("player_name",$params['player_name']);
        }
        //战队名称
        if(isset($params['en_name']) && strlen($params['en_name'])>=3)
        {
            $player_list = $player_list->where("en_name",$params['en_name']);
        }
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $player_list = $player_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("id")
            ->get()->toArray();
        return $player_list;
    }
    public function getPlayerByName($player_name,$game)
    {
        echo $player_name."-".$game."\n";
        $player_info =$this->select("*")
                    ->where("player_name",$player_name)
                    ->where("game",$game)
                    ->get()->first();
        if(isset($player_info->player_id))
        {
            $player_info = $player_info->toArray();
        }
        else
        {
            $player_info = [];
        }
        return $player_info;
    }
    public function insertPlayer($data)
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

    public function updatePlayer($player_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('player_id',$player_id)->update($data);
    }

    public function savePlayer($game,$data)
    {
        $return  = ['player_id'=>0,"result"=>0];
        $data['player_name'] = preg_replace("/\s+/", "",$data['player_name']);
        $data['player_name'] = trim($data['player_name']);
        $data['aka'] = ($data['aka']=="")?[]:[$data['aka']];
        $currentPlayer = $this->getPlayerByName($data['player_name'],$game);
        if(!isset($currentPlayer['player_id']))
        {
            echo "toInsertPlayer:\n";
            return  $this->insertPlayer(array_merge($data,["game"=>$game]));
        }
        else
        {
            echo "toUpdatePlayer:".$currentPlayer['player_id']."\n";
            return false;
        }
    }
}
