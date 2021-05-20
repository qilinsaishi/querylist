<?php

namespace App\Models\Match\scoregg;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tournamentModel extends Model
{
    protected $table = "scoregg_tournament_info";
    //protected $primaryKey = "tournament_id";
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
        "extra"
    ];
    protected $toAppend = [
    ];
    public function getTournamentList($params)
    {
        $fields = isset($params["fields"])?explode(",",$params["fields"],true):["*"];
        $tournament_list =$this->select($fields);
        //游戏类型
        if(isset($params['game']) && strlen($params['game'])>=3)
        {
            $tournament_list = $tournament_list->where("game",$params['game']);
        }
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $tournament_list = $tournament_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("tournament_id","desc")
            ->get()->toArray();
        return $tournament_list;
    }
    public function getTournamentCount($params)
    {
        $tournament_count =$this->count();
        return $tournament_count;
    }
    public function getTournamentByName($tournament_name,$game)
    {
        $tournament_info =$this->select("*")
            ->where("tournament_name",$tournament_name)
            ->where("game",$game)
            ->get()->first();
        if(isset($tournament_info->tournament_id))
        {
            $tournament_info = $tournament_info->toArray();
        }
        else
        {
            $tournament_info = [];
        }
        return $tournament_info;
    }
    public function getTournamentById($tournament_id)
    {
        $tournament_id = is_array($tournament_id)?($tournament_id['0']??$tournament_id['tournament_id']):$tournament_id;
        $tournament_info =$this->select("*")
            ->where("tournament_id",$tournament_id)
            ->get()->first();
        if(isset($tournament_info->tournament_id))
        {
            $tournament_info = $tournament_info->toArray();
        }
        else
        {
            $tournament_info = [];
        }
        return $tournament_info;
    }
    public function insertTournament($data)
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

    public function updateTournament($tournament_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('tournament_id',$tournament_id)->update($data);
    }

    public function saveTournament($data)
    {
        $data['tournament_name'] = preg_replace("/\s+/", "",$data['tournament_name']);
        $data['tournament_name'] = trim($data['tournament_name']);
        $currentTournament = $this->getTournamentById($data['tournament_id']);
        if(!isset($currentTournament['tournament_id']))
        {
            echo "toInsertTournament:"."\n";
            $insert = $this->insertTournament($data);
            $insert = ($insert==0)?$data['tournament_id']:0;
            return $insert;
        }
        else
        {
            echo "toUpdateTournament:".$currentTournament['tournament_id']."\n";
            if($data['start_time']>0)
            {
                //校验原有数据
                foreach($data as $key => $value)
                {
                    if(in_array($key,$this->toAppend))
                    {
                        $t = json_decode($currentTournament[$key],true);
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
                    if(isset($currentTournament[$key]) && ($currentTournament[$key] == $value))
                    {
                        //echo $currentTournament[$key]."-".$value."\n";
                        echo $key.":passed\n";
                        unset($data[$key]);
                    }
                    else
                    {
                        echo $key.":difference:\n";
                    }
                    if(count($data))
                    {
                        return $this->updateTournament($currentTournament['tournament_id'],$data);
                    }
                    else
                    {
                        return true;
                    }
                }
            }
            else
            {
                return true;
            }
        }
    }
}
