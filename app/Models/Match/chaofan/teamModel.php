<?php

namespace App\Models\Match\chaofan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class teamModel extends Model
{
    protected $table = "chaofan_team_info";
    //protected $primaryKey = "team_id";
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
    ];
    protected $toAppend = [
    ];
    public function getTeamList($params=[])
    {
        $fields = $params['fields']??"team_id,team_name,logo";
        $team_list =$this->select(explode(",",$fields));
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        if(isset($params['game']))
        {
            $team_list = $team_list->where("game",$params['game']);
        }
        $team_list = $team_list->orderBy("team_id")
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->get()->toArray();
        return $team_list;
    }
    public function getTeamCount($params=[])
    {
        $team_count =$this;
        if(isset($params['game']))
        {
            $team_count = $team_count->where("game",$params['game']);
        }
        return $team_count->count();
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

    public function saveTeam($data)
    {
        $data['team_name'] = preg_replace("/\s+/", "",$data['team_name']);
        $data['team_name'] = trim($data['team_name']);
        $currentTeam = $this->getTeamById($data['team_id']);
        if(!isset($currentTeam['team_id']))
        {
            echo "toInsertTeam:"."\n";
            return $this->insertTeam($data);
        }
        else
        {
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
                    echo $key.":passed\n";
                    unset($data[$key]);
                }
                else
                {
                    echo $key.":difference:\n";
                }
            }
            if(count($data))
            {
                return $this->updateTeam($currentTeam['team_id'],$data);
            }
            else
            {
                return true;
            }
        }
    }
}

