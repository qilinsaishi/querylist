<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TeamModel extends Model
{
    protected $table = "team_info";
    public $primaryKey = "team_id";
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
    public $toJson = [
        "race_stat","honor_list","aka","team_stat","team_history"
    ];
    public $toAppend = [
        "aka"=>["team_name","en_name","cn_name","aka"]
    ];
    protected $keep = [
        "original_source",//"team_history"
    ];
    public function getTeamList($params)
    {
        $fields = $params['fields']??"team_id,team_name,logo,status";
        $fields = explode(",",$fields);
        if($fields!=["*"] && !in_array("team_id",$fields))
        {
            $fields[] = "team_id";
        }
        $team_list =$this->select($fields);
        //队伍ID
        if(isset($params['ids']) && count($params['ids'])>0)
        {
            $team_list = $team_list->whereIn("team_id",$params['ids']);
        }
        //显示状态， 0不显示 1显示
        $params['status'] = $params['status'] ?? 1;
        if ($params['status'] >= 0)
        {
            $team_list = $team_list->where("status",$params['status']);
        }
        //总表队伍ID
        if(isset($params['tid']) && intval($params['tid'])>=0)
        {
            $team_list = $team_list->where("tid",$params['tid']);
        }
        //队伍ID
        if(isset($params['team_ids']) && count($params['team_ids'])>=0)
        {
            $team_list = $team_list->whereIn("team_id",$params['team_ids']);
        }
        //数据来源
        if(isset($params['source']) && strlen($params['source'])>=2)
        {
            $team_list = $team_list->where("original_source",$params['source']);
        }
        //数据来源
        if(isset($params['sources']) && count($params['sources'])>=1)
        {
            $team_list = $team_list->whereIn("original_source",$params['sources']);
        }
        //游戏类型
        if(isset($params['game']) && !is_array($params['game']) && strlen($params['game'])>=3)
        {
            $team_list = $team_list->where("game",$params['game']);
        }
        //游戏类型
        if (isset($params['game']) && is_array($params['game'])) {
            $team_list = $team_list->whereIn("game", $params['game']);
        }
        //不所属战队
        if(isset($params['except_team']) && $params['except_team']>0)
        {
            $team_list = $team_list->where("team_id","!=",$params['except_team']);
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
        if(isset($params['rand']) && $params['rand'] >0)
        {
            $team_list = $team_list
                ->limit($pageSizge)
                ->offset(($page-1)*$pageSizge)
                ->inRandomOrder()
                ->get()->toArray();
        }
        else
        {
            $team_list = $team_list
                ->limit($pageSizge)
                ->offset(($page-1)*$pageSizge)
                ->orderBy("team_id")
                ->get()->toArray();
        }
        foreach ($team_list as &$val){
            if(isset($val['team_history']))
            {
                $val['team_history']=htmlspecialchars_decode($val['team_history']);
            }
        }
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
    public function getTeamById($team_id,$fields = "*")
    {
        if(is_array($team_id))
        {
            $team_id = $team_id['0']??($team_id['team_id']??0);
        }
        $fields = is_array($fields)?$fields:explode(",",$fields);
        $team_info =$this->select($fields)
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
    public function getTeamBySiteId($team_id,$original_source='',$game='',$fields = "team_id,team_name,logo,tid,game,original_source,site_id")
    {
        $team_info =$this->select(explode(",",$fields))
            ->where("site_id",$team_id);
        if($original_source !=''){
            $team_info=$team_info->where("original_source",$original_source);
        }
        if($game !=''){
            $team_info=$team_info->where("game",$game);
        }
        $team_info=$team_info->get()->first();
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

        $return  = ['team_id'=>0,"result"=>0,'site_id'=>0];
        $site_id = $data['site_id']??0;
        $data['team_name'] = preg_replace("/\s+/", "",$data['team_name']);
        $data['team_name'] = trim($data['team_name']);
        $data['aka'] = ($data['aka']=="")?[]:$data['aka'];
        if(trim($data['team_name'])=="")
        {
            if(is_array($data['aka']))
            {
                $data['aka'] = $data['aka'][0] ?? [];
            }
            else
            {
                return $return;
            }
        }
        if($data['site_id'] != "" || $data['site_id'] != 0)
        {
            $currentTeam = $this->getTeamBySiteId($data['site_id'],$data['original_source'],$game,"*");
        }
        else
        {
            $currentTeam = $this->getTeamByName($data['team_name'],$game);
        }
        if(!isset($currentTeam['team_id']))
        {
            $return['team_id'] = $this->insertTeam(array_merge($data,["game"=>$game]));
            $return['source'] = $data['original_source'];
            $return['game'] = $game;
            $return['result'] =  $return['team_id']?1:0;
            $return['site_id'] =  $return['team_id']?$data['site_id']:0;
            return $return;
        }
        else
        {
           // echo "source:".$currentTeam['original_source'] ."-". $data['original_source']."\n";
            //非同来源不做覆盖
            if($currentTeam['original_source'] != $data['original_source'])
            {
                echo "differentSource4Team:pass\n";
                $return['team_id'] = $currentTeam['team_id'];
                $return['site_id'] = $currentTeam['site_id'];
                $return['source'] = $currentTeam['original_source'];
                $return['game'] = $currentTeam['game'];
                $return['result'] = 1;
                return $return;
            }
            unset($data['original_source']);
            $return['team_id'] = $currentTeam['team_id'];
            echo "toUpdateTeam:".$currentTeam['team_id']."\n";
            //校验原有数据
            foreach($data as $key => $value)
            {
                if(isset($this->toAppend[$key]))
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
                    //判断字段是否有后台手动更新
                    $changeLogsModel=new ChangeLogsModel();
                    $check_result=$changeLogsModel->checkData($currentTeam['team_id'],$key,$type='team');
                    if(!$check_result)
                    {
                        unset($data[$key]);
                        echo $key.":difference by modified,pass:\n";
                    }
                    else
                    {
                        echo $key.":difference:\n";
                    }
                }
            }
            if(count($data))
            {
                if(!isset($data['logo']) || (isset($data['logo']) && strlen($data['logo'])<=10)){
                    $data['logo']=$currentTeam['logo'] ?? '';
                }
                if(!isset($data['aka']) || (isset($data['aka']) && $data['aka']=='') || is_null($data['aka']) )
                {
                    $data['aka']=$currentTeam['aka'] ?? [];
                }
                $return['result'] = $this->updateTeam($currentTeam['team_id'],$data);
                $return['site_id'] = $currentTeam['site_id'];
                $return['source'] = $currentTeam['original_source'];
                $return['game'] = $currentTeam['game'];
                return $return;
            }
            else
            {
                $return['result'] = 1;
                $return['site_id'] = $currentTeam['site_id'];
                $return['source'] = $currentTeam['original_source'];
                $return['game'] = $currentTeam['game'];
                return $return;
            }
        }
    }
    public function getTeamCount($params=[])
    {
        $team_count =$this;
        //显示状态， 0不显示 1显示
        $params['status'] = $params['status'] ?? 1;
        if ($params['status'] >= 0)
        {
            $team_count = $team_count->where("status",$params['status']);
        }
        //队伍ID
        if(isset($params['ids']) && count($params['ids'])>0)
        {
            $team_count = $team_count->whereIn("team_id",$params['ids']);
        }
        //总表队伍ID
        if(isset($params['tid']) && intval($params['tid'])>=0)
        {
            $team_count = $team_count->where("tid",$params['tid']);
        }
        //队伍ID
        if(isset($params['team_ids']) && count($params['team_ids'])>=0)
        {
            $team_count = $team_count->whereIn("team_id",$params['team_ids']);
        }
        //数据来源
        if(isset($params['source']) && strlen($params['source'])>=2)
        {
            $team_count = $team_count->where("original_source",$params['source']);
        }
        //数据来源
        if(isset($params['sources']) && count($params['sources'])>=1)
        {
            $team_count = $team_count->whereIn("original_source",$params['sources']);
        }
        //游戏类型
        if(isset($params['game']) && !is_array($params['game']) && strlen($params['game'])>=3)
        {
            $team_count = $team_count->where("game",$params['game']);
        }
        //游戏类型
        if (isset($params['game']) && is_array($params['game']))
        {
            $team_count = $team_count->whereIn("game", $params['game']);
        }
        //不所属战队
        if(isset($params['except_team']) && $params['except_team']>0)
        {
            $team_count = $team_count->where("team_id","!=",$params['except_team']);
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
            $aka = json_decode($team_info['aka'],true);
            if(!is_array($aka))
            {
                $aka = [];
            }
            $t = array_unique(array_merge([$team_info['team_name']],[$team_info['en_name']],$aka));
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
    //查询不全的队伍去更新任务
    public function getUpdateTeam($game)
    {
        if($game=='dota2'){
            $source='shangniu';
            $table='shangniu_match_list';
        }else{
            $source='scoregg';
            $table='scoregg_match_list';
        }
        $homeSql="SELECT distinct(home_id) FROM ".$table." WHERE home_id  not in (select DISTINCT(site_id) from team_info where original_source ='".$source."' and game='".$game."')";
        $awaySql="SELECT distinct(away_id) FROM ".$table." WHERE away_id not in (select DISTINCT(site_id) from team_info where original_source ='".$source."' and game='".$game."')";
        $homeTeamInfo=array_column(json_decode(json_encode(\DB::select($homeSql)),true),"home_id");
        $awayTeamInfo=array_column(json_decode(json_encode(\DB::select($awaySql)),true),"away_id");

        $team_info =  array_unique(array_merge($homeTeamInfo,$awayTeamInfo));

        return $team_info??[];
    }
}
