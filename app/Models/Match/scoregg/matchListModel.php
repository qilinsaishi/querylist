<?php

namespace App\Models\Match\scoregg;

use App\Models\TeamModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class matchListModel extends Model
{
    protected $table = "scoregg_match_list";
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
        "match_pre","match_data","match_live"
    ];
    protected $toAppend = [
    ];
    public function getMatchList($params)
    {
        $start = microtime(true);
        $fields = $params['fields'] ?? "match_id,game,match_status,round_id,tournament_id,home_id,away_id,home_score,away_score,start_time";
        $match_list =$this->select(explode(",",$fields));
        $pageSizge = $params['page_size']??4;
        $page = $params['page']??1;
        /*
        if (isset($params['tournament_id']) && $params['tournament_id']!="") {
            $match_list = $match_list ->where("tournament_id", $params['tournament_id']);
        }
        */
        $match_list = $match_list->where("home_id",">",0)->where("away_id",">",0)
            ->limit($pageSizge)
        /*    ->whereHas('getHomeInfo', function($query){
                return $query->select('team_name');
            })->whereHas('getawayInfo', function($query){
                return $query->select('team_name');
            })
        */
        ->offset(($page-1)*$pageSizge)
            ->orderBy("start_time","desc")
            ->get()->toArray();
        $end = microtime(true);
        return $match_list;
    }
    public function getHomeInfo(){
        return $this->belongsTo(TeamModel::class,'home_id','site_id');
    }
    public function getawayInfo(){
        return $this->belongsTo(TeamModel::class,'away_id','site_id');
    }
    public function getTournamentInfo(){
        return $this->belongsTo(tournamentModel::class,'tournament_id','tournament_id');
    }
    public function getMatchByName($match_name,$game)
    {
        $match_info =$this->select("*")
            ->where("match_name",$match_name)
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
        if(isset($data['round']))
        {
            unset($data['round']);
        }
        $currentMatch = $this->getMatchById($data['match_id']);
        if(!isset($currentMatch['match_id']))
        {
            echo "toInsertMatch:"."\n";
            $insert = $this->insertMatch($data);
            $insert = ($insert==0)?$data['match_id']:0;
            return $insert;
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
}
