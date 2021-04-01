<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
    public $toJson = [
        "team_history","event_history","aka","stat"
    ];
    public $toAppend = [
        "aka"=>["player_name","en_name","cn_name","aka"]
    ];
    public function getPlayerList($params)
    {
        $fields = $params['fields']??"player_id,player_name,logo,position";
        $player_list =$this->select(explode(",",$fields));
        //总表队员ID
        if(isset($params['pid']) && intval($params['pid'])>=0)
        {
            $player_list = $player_list->where("pid",$params['pid']);
        }
        //游戏类型
        if(isset($params['game']) && strlen($params['game'])>=3)
        {
            $player_list = $player_list->where("game",$params['game']);
        }
        //数据来源
        if(isset($params['source']) && strlen($params['source'])>=2)
        {
            $player_list = $player_list->where("original_source",$params['source']);
        }
        //所属战队
        if(isset($params['team_id']) && $params['team_id']>0)
        {
            $player_list = $player_list->where("team_id",$params['team_id']);
        }
        //所属战队
        if(isset($params['team_ids']) && count($params['team_ids'])>0)
        {
            $player_list = $player_list->whereIn("team_id",$params['team_ids']);
        }
        //不所属战队
        if(isset($params['except_team']) && $params['except_team']>0)
        {
            $player_list = $player_list->where("team_id","!=",$params['except_team']);
        }
        //不是队员
        if(isset($params['except_player']) && $params['except_player']>0)
        {
            $player_list = $player_list->where("player_id","!=",$params['except_player']);
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
        $hot=$params['hot']??0;
        if($hot==1)
        {
            $player_list->where("hot",$hot);
        }
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        if(isset($params['rand']) && $params['rand'] >0)
        {
            $player_list = $player_list
                ->limit($pageSizge)
                ->offset(($page-1)*$pageSizge)
                ->inRandomOrder()
                ->get()->toArray();
        }
        else
        {
            $player_list = $player_list
                ->limit($pageSizge)
                ->offset(($page-1)*$pageSizge)
                ->orderBy("player_id","desc")
                ->get()->toArray();
        }
        return $player_list;
    }
    public function getPlayerByName($player_name,$game)
    {
        //echo $player_name."-".$game."\n";
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
        foreach($this->toJson as $key)
        {
            if(isset($data[$key]))
            {
                $data[$key] = json_encode($data[$key]);
            }
        }
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        unset($data['original_source']);
        return $this->where('player_id',$player_id)->update($data);
    }

    public function savePlayer($game,$data)
    {
        echo "player_name:".$data['player_name']."\n";
        $return  = ['player_id'=>0,"result"=>0];
        if($data['player_name'] == "")
        {
            echo "empty_player:\n";
            sleep(1);
            return $return;
        }
        $data['player_name'] = preg_replace("/\s+/", "",$data['player_name']);
        $data['player_name'] = trim($data['player_name']);
        if($data['aka']=="")
        {
            $data['aka'] = [];
        }
        else
        {
            $data['aka'] = is_array($data['aka'])?$data['aka']:[$data['aka']];
        }
        $currentPlayer = $this->getPlayerBySiteId($data['site_id'],$game,$data['original_source']);
        if(!isset($currentPlayer['player_id']))
        {
            echo "toInsertPlayer:\n";
            $insert = $this->insertPlayer(array_merge($data,["game"=>$game]));
            if($insert)
            {
                $return['player_id'] = $insert;
                $return['result'] = 1;
            }
            else
            {
                $return['player_id'] = 0;
                $return['result'] = 0;
            }
            return $return;
        }
        else
        {
            echo "source:".$currentPlayer['original_source'] ."-". $data['original_source']."\n";
            //非同来源不做覆盖
            if($currentPlayer['original_source'] != $data['original_source'])
            {
                echo "differentSorce4Team:pass\n";
                $return['player_id'] = $currentPlayer['player_id'];
                $return['result'] = 1;
                return $return;
            }
            echo "toUpdatePlayer:".$currentPlayer['player_id']."\n";
            //校验原有数据
            foreach($data as $key => $value)
            {
                if(isset($this->toAppend[$key]))
                {
                    $t = json_decode($currentPlayer[$key],true);

                    foreach($value as $k => $v)
                    {
                        if(!in_array($v,$t))
                        {
                            $t[] = $v;
                        }
                    }
                    $data[$key] = $t;
                }
                if(in_array($key,$this->toJson))
                {
                    $value = json_encode($value);
                }
                if(isset($currentPlayer[$key]) && ($currentPlayer[$key] == $value))
                {
                    //echo $currentPlayer[$key]."-".$value."\n";
                    //echo $key.":passed\n";
                    unset($data[$key]);
                }
                else
                {
                    //判断字段是否有后台手动更新
                    $changeLogsModel=new ChangeLogsModel();
                    $check_result=$changeLogsModel->checkData($currentPlayer['player_id'],$key,$type='player');
                    if(!$check_result){
                        unset($data[$key]);
                    }
                    echo $key.":difference:\n";
                }
            }
            if(count($data))
            {
                return $this->updatePlayer($currentPlayer['player_id'],$data);
            }
            else
            {
                return true;
            }
        }
    }

    public function getPlayerCount($params=[]){
        $player_count = $this;
        //总表队员ID
        if(isset($params['pid']) && intval($params['pid'])>=0)
        {
            $player_count = $player_count->where("pid",$params['pid']);
        }
        //游戏类型
        if(isset($params['game']) && strlen($params['game'])>=3)
        {
            $player_count = $player_count->where("game",$params['game']);
        }
        //数据来源
        if(isset($params['source']) && strlen($params['source'])>=2)
        {
            $player_count = $player_count->where("original_source",$params['source']);
        }
        //所属战队
        if(isset($params['team_id']) && $params['team_id']>0)
        {
            $player_count = $player_count->where("team_id",$params['team_id']);
        }
        //所属战队
        if(isset($params['team_ids']) && count($params['team_ids'])>0)
        {
            $player_count = $player_count->whereIn("team_id",$params['team_ids']);
        }
        //不所属战队
        if(isset($params['except_team']) && $params['except_team']>0)
        {
            $player_count = $player_count->where("team_id","!=",$params['except_team']);
        }
        //不是队员
        if(isset($params['except_player']) && $params['except_player']>0)
        {
            $player_count = $player_count->where("player_id","!=",$params['except_player']);
        }
        //战队名称
        if(isset($params['player_name']) && strlen($params['player_name'])>=3)
        {
            $player_count = $player_count->where("player_name",$params['player_name']);
        }
        //战队名称
        if(isset($params['en_name']) && strlen($params['en_name'])>=3)
        {
            $player_count = $player_count->where("en_name",$params['en_name']);
        }
        $hot=$params['hot']??0;
        if($hot==1)
        {
            $player_count->where("hot",$hot);
        }

        return $player_count->count();
    }

    public function getPlayerById($player_id){
        $player_info =$this->select("*")
            ->where("player_id",$player_id)
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
    public function getPlayerBySiteId($site_id,$game,$source){
        $player_info =$this->select("*")
            ->where("site_id",$site_id)
            ->where("game",$game)
            ->where("original_source",$source)
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
    public function getAllKeywords($game)
    {
        $keywords = [];
        $playerList = $this->getPlayerList(["game"=>$game,"fields"=>"player_id,player_name,en_name,aka","page_size"=>10000]);
        foreach($playerList as $player_info)
        {
            $t = array_unique(array_merge([$player_info['player_name']],[$player_info['en_name']],json_decode($player_info['aka'],1)??[]));
            foreach($t as $value)
            {
                if(trim($value) != "" && !isset($keywords[trim($value)]))
                {
                    $keywords[trim($value)] = $player_info['player_id'];
                }
            }
        }
        return $keywords;
    }
}
