<?php

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TotalTeamModel extends Model
{
    protected $table = "team_list";
    public $primaryKey = "tid";
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
        "aka"=>[]
    ];
    protected $toJson = [
        "aka","redirect"
    ];
    protected $toAppend = [
    ];
    protected $keep = [
    ];

    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
    public function getTeamList($params)
    {
        $team_list =$this->select("*");
        //总表队伍ID
        if(isset($params['tid']) && !is_array($params['tid']) && intval($params['tid'])>=0)
        {
            $team_list = $team_list->where("tid",$params['tid']);
        }
        //总表队伍ID
        if(isset($params['tid']) && is_array($params['tid']) && count($params['tid'])>0)
        {
            $team_list = $team_list->whereIn("tid",$params['tid']);
        }
        //游戏类型
        if (isset($params['game']) && !is_array($params['game']) && strlen($params['game']) >= 3) {
            $team_list = $team_list->where("game", $params['game']);
        }
        //游戏类型
        if (isset($params['game']) && is_array($params['game'])) {
            $team_list = $team_list->whereIn("game", $params['game']);
        }
        //不所属战队
        if(isset($params['except_team']) && $params['except_team']>0)
        {
            $team_list = $team_list->where("tid","!=",$params['except_team']);
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
                ->orderBy("tid")
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
    public function getTeamById($tid,$fields = "*")
    {
        if(is_array($tid))
        {
            $tid = $tid['0']??0;
        }
        $team_info =$this->select(explode(",",$fields))
            ->where("tid",$tid)
            ->get()->first();
        if(isset($team_info->tid))
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
    public function updateTeam($tid=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        foreach($this->toJson as $key)
        {
            if(isset($data[$key]))
            {
                if(is_array($data[$key]))
                {
                    $data[$key] = json_encode($data[$key]);
                }
            }
        }
        foreach($this->keep as $key)
        {
            if(isset($data[$key]))
            {
                unset($data[$key]);
            }
        }
        return $this->where('tid',$tid)->update($data);
    }
    public function getTeamCount($params=[])
    {
        $team_count =$this;
        //总表队伍ID
        if(isset($params['tid']) && !is_array($params['tid']) && intval($params['tid'])>=0)
        {
            $team_count = $team_count->where("tid",$params['tid']);
        }
        //总表队伍ID
        if(isset($params['tid']) && is_array($params['tid']) && count($params['tid'])>0)
        {
            $team_count = $team_count->whereIn("tid",$params['tid']);
        }
        //游戏类型
        if (isset($params['game']) && !is_array($params['game']) && strlen($params['game']) >= 3) {
            $team_count = $team_count->where("game", $params['game']);
        }
        //游戏类型
        if (isset($params['game']) && is_array($params['game'])) {
            $team_count = $team_count->whereIn("game", $params['game']);
        }
        //不所属战队
        if(isset($params['except_team']) && $params['except_team']>0)
        {
            $team_count = $team_count->where("tid","!=",$params['except_team']);
        }
        return $team_count->count();
    }
}
