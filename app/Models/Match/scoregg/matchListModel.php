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
        $fields = $params['fields'] ?? "match_id,game,match_status,round_id,tournament_id,home_id,away_id,home_score,away_score,start_time,match_data";
        $match_list =$this->select(explode(",",$fields));
        $pageSize = $params['page_size']??4;
        $page = $params['page']??1;
        if (isset($params['tournament_id']) && $params['tournament_id']>0) {
            $match_list = $match_list ->where("tournament_id", $params['tournament_id']);
        }

        if (isset($params['round_detailed'])) {//主要修复match_data里面的数据
            $match_list = $match_list ->where("round_detailed", $params['round_detailed']);
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
            //$end_time = date("Y-m-d H:i:s", time() - (8-4) * 3600);
            $match_list = $match_list->where("start_time", '<=', $start_time);//->where("start_time", '<', $end_time);
        }
        //比赛开始时间start=1表示启动开始时间条件
        if (isset($params['start']) && $params['start'] < 0) {
            $start_time = date("Y-m-d H:00:00", time());
            //$end_time = date("Y-m-d H:i:s", time() - (8-4) * 3600);
            $match_list = $match_list->where("start_time", '>', $start_time);//->where("start_time", '<', $end_time);
        }
        //比赛开始时间start=1表示启动开始时间条件
        if (isset($params['recent']) && $params['recent'] > 0) {
            $currentTime = time();
            $start_time = date("Y-m-d H:00:00", $currentTime);
            $end_time = date("Y-m-d", $currentTime+10*86400);
            $match_list = $match_list->where("start_time", '>=', $start_time);
            $match_list = $match_list->where("start_time", '<=', $end_time);
            $params['order'] = [["start_time","asc"]];
        }
        //比赛开始时间start=1表示启动开始时间条件
        if (isset($params['next_try']) && $params['next_try'] > 0) {
            $currentTime = time();
            $start_time = date("Y-m-d H:i:s", $currentTime);
            //$end_time = $params['start_time']+30*86400;
            $match_list = $match_list->where("next_try", '<=', $currentTime);
            $match_list = $match_list->where("try", '<', 15);
            $match_list = $match_list->whereRaw("((match_status != 2) or (round_detailed =0))");
            $params['order'] = [["start_time","desc"]];
            //$match_list = $match_list->whereRaw("unix_timestamp(start_time)+30*3600 <=".$currentTime);

        }
        //比赛日期
        if (isset($params['start_date']) && strtotime($params['start_date']) > 0)
        {
            $match_list = $match_list->where("start_time",">=" , date("Y-m-d H:i:s",strtotime($params['start_date'])));
            //$match_list = $match_list->where("start_time","<" , date("Y-m-d H:i:s",strtotime($params['start_date'])+8*3600+86400));
        }
        //比赛日期
        if (isset($params['end_date']) && strtotime($params['end_date']) > 0)
        {
            $match_list = $match_list->where("start_time","<" , date("Y-m-d H:i:s",strtotime($params['end_date'])+86400-1));
            //$match_list = $match_list->where("start_time","<" , date("Y-m-d H:i:s",strtotime($params['start_date'])+8*3600+86400));
        }
        //all表示查询所有比赛
        //否则要求确定的主客队双方
        if(!isset($params['all']) || $params['all'] <=0){
            $match_list=$match_list->where("home_id",">",0)->where("away_id",">",0);
        }
        //主客队双方
        if(isset($params['team_id']) && !is_array($params['team_id']) && $params['team_id'] >0){
            $match_list=$match_list->whereRaw('((home_id ='.$params['team_id'] .') or (away_id = '.$params['team_id'].'))');
        }
        //主客队双方
        if(isset($params['team_id']) && is_array($params['team_id']) && count($params['team_id']) >0){
            $match_list=$match_list->whereRaw('((home_id in ('.implode(",",$params['team_id']) .')) or (away_id in ('.implode(",",$params['team_id']).')))');
        }
        $match_list = $match_list->limit($pageSize)
        ->offset(($page-1)*$pageSize);
        if(!isset($params['order']) || !is_array($params['order']))
        {
            $orderByList = [["start_time","desc"]];
        }
        else
        {
            $orderByList = $params['order'];
        }
        foreach ($orderByList as $order)
        {
            $match_list = $match_list->orderBy($order["0"],$order["1"]??"desc");
        }
        $match_list  = $match_list
            ->get()->toArray();
        $end = microtime(true);
        return $match_list;
    }
    public function getMatchCount($params)
    {
        $match_count = $this;
        if (isset($params['tournament_id']) && $params['tournament_id']>0) {
            $match_count = $match_count ->where("tournament_id", $params['tournament_id']);
        }

        if (isset($params['round_detailed'])) {//主要修复match_data里面的数据
            $match_count = $match_count ->where("round_detailed", $params['round_detailed']);
        }
        //游戏类型
        if(isset($params['game']))
        {
            if(is_array($params['game']) && count($params['game'])>1)
            {//数组里面多个元素
                $match_count = $match_count->whereIn("game",$params['game']);
            }
            elseif(is_array($params['game']) && count($params['game'])==1)
            {
                $match_count = $match_count->where("game",$params['game']['0']);
            }
            else
            {
                $match_count = $match_count->where("game",$params['game']);
            }
        }
        //状态
        if (isset($params['match_status']))
        {
            if(!is_array($params['match_status']))
            {
                if(strpos($params['match_status'],',') !==false){
                    $status = explode(",", $params['match_status']);
                    $match_count=$match_count->whereIn("match_status", $status);
                }else{
                    if($params['match_status']>0){
                        $match_count=$match_count->where("match_status", $params['match_status']);
                    }else{
                        $match_count=$match_count->where("match_status",'<>', 3);
                    }
                }

            }
            else
            {
                $match_count=$match_count->whereIn("match_status", $params['match_status']);
            }
        }else{
            $match_count=$match_count->where("match_status",'<>', 3);
        }
        //比赛开始时间start=1表示启动开始时间条件
        if (isset($params['start']) && $params['start'] > 0) {
            $start_time = date("Y-m-d H:i:s", time());
            //$end_time = date("Y-m-d H:i:s", time() - (8-4) * 3600);
            $match_count = $match_count->where("start_time", '<=', $start_time);//->where("start_time", '<', $end_time);
        }
        //比赛开始时间start=1表示启动开始时间条件
        if (isset($params['start']) && $params['start'] < 0) {
            $start_time = date("Y-m-d H:00:00", time());
            //$end_time = date("Y-m-d H:i:s", time() - (8-4) * 3600);
            $match_count = $match_count->where("start_time", '>', $start_time);//->where("start_time", '<', $end_time);
        }
        //比赛开始时间start=1表示启动开始时间条件
        if (isset($params['recent']) && $params['recent'] > 0) {
            $currentTime = time();
            $start_time = date("Y-m-d H:00:00", $currentTime);
            $end_time = date("Y-m-d", $currentTime+4*86400);
            $match_count = $match_count->where("start_time", '>=', $start_time);
            $match_count = $match_count->where("start_time", '<=', $end_time);
            //$params['order'] = [["start_time","asc"]];
        }
        //比赛开始时间start=1表示启动开始时间条件
        if (isset($params['next_try']) && $params['next_try'] > 0) {
            $currentTime = time();
            $start_time = date("Y-m-d H:i:s", $currentTime);
            //$end_time = $params['start_time']+30*86400;
            $match_count = $match_count->where("next_try", '<=', $currentTime);
            $match_count = $match_count->where("try", '<', 10);
            //$match_list = $match_list->whereRaw("unix_timestamp(start_time)+30*3600 <=".$currentTime);

        }
        //比赛日期
        if (isset($params['start_date']) && strtotime($params['start_date']) > 0)
        {
            $match_count = $match_count->where("start_time",">=" , date("Y-m-d H:i:s",strtotime($params['start_date'])));
            //$match_list = $match_list->where("start_time","<" , date("Y-m-d H:i:s",strtotime($params['start_date'])+8*3600+86400));
        }
        //比赛日期
        if (isset($params['end_date']) && strtotime($params['end_date']) > 0)
        {
            $match_count = $match_count->where("start_time","<" , date("Y-m-d H:i:s",strtotime($params['end_date'])+86400-1));
            //$match_list = $match_list->where("start_time","<" , date("Y-m-d H:i:s",strtotime($params['start_date'])+8*3600+86400));
        }
        //all表示查询所有比赛
        //否则要求确定的主客队双方
        if(!isset($params['all']) || $params['all'] <=0){
            $match_count=$match_count->where("home_id",">",0)->where("away_id",">",0);
        }
        //主客队双方
        if(isset($params['team_id']) && !is_array($params['team_id']) && $params['team_id'] >0){
            $match_count=$match_count->whereRaw('((home_id ='.$params['team_id'] .') or (away_id = '.$params['team_id'].'))');
        }
        //主客队双方
        if(isset($params['team_id']) && is_array($params['team_id']) && count($params['team_id']) >0){
            $match_count=$match_count->whereRaw('((home_id in ('.implode(",",$params['team_id']) .')) or (away_id in ('.implode(",",$params['team_id']).')))');
        }
        $match_count = $match_count
            ->count();
        return $match_count;
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

        if(!isset($data['round_detailed']))
        {
            $data['round_detailed']=0;
        }
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
            return ['result'=>$insert,"site_id"=>$data['match_id'],"source"=>"scoregg","game"=>$data['game']];
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
                return ['result'=>$this->updateMatch($currentMatch['match_id'],$data),"site_id"=>$currentMatch['match_id'],"source"=>"scoregg","game"=>$currentMatch['game']];
            }
            else
            {
                return ['result'=>1,"site_id"=>$currentMatch['match_id'],"source"=>"scoregg","game"=>$currentMatch['game']];
            }
        }
    }
}
