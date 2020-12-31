<?php

namespace App\Models\Match\cpseo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class matchListModel extends Model
{
    protected $table = "cpseo_match_list";
    //protected $primaryKey = "match_id";
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
    public function getMatchList($params)
    {
        $match_list =$this->select("*");
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        if(isset($params['game']) && $params['game']!="")
        {
            $match_list = where("game",$params['game']);
        }
        if(isset($params['hot']) && $params['hot']>0)
        {
            $match_list = where("hot",1);
        }
        $match_list = $match_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("match_id")
            ->get()->toArray();
        return $match_list;
    }
    public function getMatchByTeam($home_id,$away_id,$tournament_id,$start_time,$game)
    {
        $match_info =$this->select("*")
            ->where("home_id",$home_id)
            ->where("away_id",$away_id)
            ->where("tournament_id",$tournament_id)
            ->where("start_time",$start_time)
            ->where("game",$game)
            ->get()->first();
        if(isset($match_info->match_id))
        {
            $match_info = $match_info->toArray();
        }
        else
        {
            $match_info = [];
        }
        return $match_info;
    }
    public function getMatchById($match_id)
    {
        $match_info =$this->select("*")
            ->where("match_id",$match_id)
            ->get()->first();
        if(isset($match_info->match_id))
        {
            $match_info = $match_info->toArray();
        }
        else
        {
            $match_info = [];
        }
        return $match_info;
    }
    public function insertMatch($data)
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

    public function updateMatch($match_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('match_id',$match_id)->update($data);
    }

    public function saveMatch($data)
    {
        $currentMatch = $this->getMatchByTeam($data['home_id'],$data['away_id'],$data['tournament_id'],$data['start_time'],$data['game']);
        if(!isset($currentMatch['match_id']))
        {
            echo "toInsertMatch:"."\n";
            return $this->insertMatch($data);
        }
        else
        {
            echo "toUpdateMatch:".$currentMatch['match_id']."\n";
            //校验原有数据
            foreach($data as $key => $value)
            {
                if(in_array($key,$this->toAppend))
                {
                    $t = json_decode($currentMatch[$key],true);
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
                if(isset($currentMatch[$key]) && ($currentMatch[$key] == $value))
                {
                    //echo $currentMatch[$key]."-".$value."\n";
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
                return $this->updateMatch($currentMatch['match_id'],$data);
            }
            else
            {
                return true;
            }
        }
    }

    public function getTournamentList(){
        return true;
    }
}
