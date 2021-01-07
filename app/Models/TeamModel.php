<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TeamModel extends Model
{
    protected $table = "team_info";
    protected $primaryKey = "team_id";
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
    protected $toAppend = [
        "aka"
    ];
    protected $keep = [
      "original_source","team_history"
    ];
    public function getTeamList($params)
    {
        $fields = $params['fields']??"team_id,team_name,logo";
        $team_list =$this->select(explode(",",$fields));
        //数据来源
        if(isset($params['source']) && strlen($params['source'])>=2)
        {
            $team_list = $team_list->where("original_source",$params['source']);
        }
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
            ->orderBy("team_id")
            ->get()->toArray();
        return $team_list;
    }
    public function getTeamByName($team_name,$game)
    {
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
    public function getTeamById($team_id)
    {
        $team_info =$this->select("*")
            ->where("team_id",$team_id)
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
    public function getTeamBySiteId($team_id)
    {
        $team_info =$this->select("*")
            ->where("site_id",$team_id)
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
        foreach($this->keep as $key)
        {
            if(isset($data[$key]))
            {
                unset($data[$key]);
            }
        }
        return $this->where('team_id',$team_id)->update($data);
    }

    public function saveTeam($game,$data)
    {
        $return  = ['team_id'=>0,"result"=>0];
        $data['team_name'] = preg_replace("/\s+/", "",$data['team_name']);
        $data['team_name'] = trim($data['team_name']);
        $data['aka'] = ($data['aka']=="")?[]:[$data['aka']];
        if(trim($data['team_name'])=="")
        {
            if(is_array($data['aka']))
            {
                $data['team_name'] = $data['aka'][0];
            }
            else
            {
                return $return;
            }
        }
        $currentTeam = $this->getTeamByName($data['team_name'],$game);
        if(!isset($currentTeam['team_id']))
        {
            $return['team_id'] = $this->insertTeam(array_merge($data,["game"=>$game]));
            $return['result'] =  $return['team_id']?1:0;
        }
        else
        {
            echo "source:".$currentTeam['original_source'] ."-". $data['original_source']."\n";
            //非同来源不做覆盖
            if($currentTeam['original_source'] != $data['original_source'])
            {
                echo "differentSorce4Team:pass\n";
                $return['team_id'] = $currentTeam['team_id'];
                $return['result'] = 1;
                return $return;
            }
            unset($data['original_source']);
            $return['team_id'] = $currentTeam['team_id'];
            echo "toUpdateTeam:".$currentTeam['team_id']."\n";
            //校验原有数据
            foreach($data as $key => $value)
            {
                if(in_array($key,$this->toAppend))
                {
                    $t = json_decode($currentTeam[$key],true);
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
                if(isset($currentTeam[$key]) && ($currentTeam[$key] == $value))
                {
                    //echo $currentTeam[$key]."-".$value."\n";
                    //echo $key.":passed\n";
                    unset($data[$key]);
                }
                else
                {
                    echo $key.":difference:\n";
                }
            }
            if(count($data))
            {
                $return['result'] = $this->updateTeam($currentTeam['team_id'],$data);
                return $return;
            }
            else
            {
                $return['result'] = 1;
                return $return;
            }
        }
    }
    public function getTeamCount($params=[])
    {
        $team_count =$this;
        //数据来源
        if(isset($params['source']) && strlen($params['source'])>=2)
        {
            $team_count = $team_count->where("original_source",$params['source']);
        }
        //游戏类型
        if(isset($params['game']) && strlen($params['game'])>=3)
        {
            $team_count = $team_count->where("game",$params['game']);
        }
        //战队名称
        if(isset($params['team_name']) && strlen($params['team_name'])>=3)
        {
            $team_count = $team_count->where("team_name",$params['team_name']);
        }
        //战队名称
        if(isset($params['en_name']) && strlen($params['en_name'])>=3)
        {
            $team_count = $team_count->where("en_name",$params['en_name']);
        }
        return $team_count->count();
    }
    public function getAllKeywords($game)
    {
        $keywords = [];
        $teamList = $this->getTeamList(["game"=>$game,"fields"=>"team_id,team_name,en_name,aka","page_size"=>10000]);
        foreach($teamList as $team_info)
        {
            $t = array_unique(array_merge([$team_info['team_name']],[$team_info['en_name']],json_decode($team_info['aka'])));
            foreach($t as $value)
            {
                if(trim($value) != "" && !isset($keywords[trim($value)]))
                {
                    $keywords[trim($value)] = $team_info['team_id'];
                }
            }
        }
        return $keywords;
    }
}
