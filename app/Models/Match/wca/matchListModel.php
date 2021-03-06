<?php

namespace App\Models\Match\wca;

use App\Models\TeamModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class matchListModel extends Model
{
    protected $table = "wca_match_list";
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
      "match_data"
    ];
    protected $toAppend = [
    ];
    public function getMatchList($params)
    {
        //\DB::connection()->enableQueryLog();
        $start = microtime(true);
        $fields = $params['fields'] ?? "match_id,game,match_status,tournament_id,home_id,away_id,home_name,away_name,home_logo,away_logo,home_score,away_score,start_time,match_data";
        $match_list =$this->select(explode(",",$fields));
        $pageSizge = $params['page_size']??4;
        $page = $params['page']??1;
        if (isset($params['tournament_id']) && $params['tournament_id']>0) {
            $match_list = $match_list ->where("tournament_id", $params['tournament_id']);
        }


        //游戏类型
        if(isset($params['game']))
        {
            if(is_array($params['game']) && count($params['game'])>1)
            {//数组里面多个元素
                $match_list = $match_list->whereIn("game",$params['game']);
            }
            elseif(is_array($params['game']) && count($params['game'])==1)
            {
                $match_list = $match_list->where("game",$params['game']['0']);
            }
            else
            {
                $match_list = $match_list->where("game",$params['game']);
            }
        }
        //状态
        if (isset($params['match_status']))
        {
            if(!is_array($params['match_status']))
            {
                if(strpos($params['match_status'],',') !==false){
                    $status = explode(",", $params['match_status']);
                    $match_list=$match_list->whereIn("match_status", $status);
                }else{
                    if($params['match_status']>0){
                        $match_list=$match_list->where("match_status", $params['match_status']);
                    }else{
                        $match_list=$match_list->where("match_status",'<>', 3);
                    }
                }

            }
            else
            {
                $match_list=$match_list->whereIn("match_status", $params['match_status']);
            }
        }else{
            $match_list=$match_list->where("match_status",'<>', 3);
        }
        //比赛开始时间start=1表示启动开始时间条件
        if (isset($params['start']) && $params['start'] > 0) {
            $start_time = date("Y-m-d H:i:s", time());
            $match_list = $match_list->where("start_time", '<=', $start_time);//->where("start_time", '<', $end_time);
        }
        //比赛日期
       if (isset($params['start_date']) && strtotime($params['start_date']) > 0)
        {
            $match_list = $match_list->where("start_time",">=" , date("Y-m-d H:i:s",strtotime($params['start_date'])));

        }
        //比赛日期
        if (isset($params['end_date']) && strtotime($params['end_date']) > 0)
        {
            $match_list = $match_list->where("start_time","<" , date("Y-m-d H:i:s",strtotime($params['end_date'])+86400-1));

        }
        //all表示查询所有比赛

        //主客队双方
        if(isset($params['team_id']) && !is_array($params['team_id']) && $params['team_id'] >0){
            $match_list=$match_list->whereRaw('((home_id ='.$params['team_id'] .') or (away_id = '.$params['team_id'].'))');
        }
        //主客队双方
        if(isset($params['team_id']) && is_array($params['team_id']) && count($params['team_id']) >0){
            $match_list=$match_list->whereRaw('((home_id in ('.implode(",",$params['team_id']) .')) or (away_id in ('.implode(",",$params['team_id']).')))');
        }


        $match_list = $match_list->limit($pageSizge)
        ->offset(($page-1)*$pageSizge)
            ->orderBy("start_time","desc")
            ->get()->toArray();
        $end = microtime(true);
        //print_r(\DB::getQueryLog());exit;
        return $match_list;
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
        if(is_array($match_id))
        {
            $match_id = $match_id['0']??($match_id['match_id']??0);
        }
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
        $data['game_bo']=intval($data['game_bo']) ?? 0;
        if(isset($data['tournament_name']))
        {
           unset($data['tournament_name']);
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
        if(isset($data['tournament_name']))
        {
            unset($data['tournament_name']);
        }
        $data['game_bo']=isset($data['game_bo']) ?intval($data['game_bo']): 0;

        return $this->where('match_id',$match_id)->update($data);
    }

    public function saveMatch($data)
    {
        if(isset($data['game_bo']) && strpos($data['game_bo'],'BO') !==false){
            $data['game_bo']=str_replace('BO','',$data['game_bo']);
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
