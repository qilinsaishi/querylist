<?php

namespace App\Models\Player;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TotalPlayerModel extends Model
{
    protected $table = "player_list";
    protected $primaryKey = "pid";
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
    public function getPlayerList($params)
    {
        $fields = $params['fields']??"pid,player_name,logo,position";
        $player_list =$this->select(explode(",",$fields));
        /*
        if(isset($params['game']) && !is_array($params['game']))
        {
            $sourceList = config('app.intergration.player.'.$params['game']);
            $player_list = $player_list->whereIn("original_source",array_column($sourceList,"source"));
        }
        */
        //显示状态，负值全部 0不显示 1显示
        $params['display'] = $params['display'] ?? 1;
        if ($params['display'] >= 0)
        {
            $player_list = $player_list->where("display",$params['display']);
        }
        //总表队员ID
        if(isset($params['pids']) && count($params['pid'])>=1)
        {
            $player_list = $player_list->whereIn("pid", $params['pid']);
        }
        //总表队员ID
        if(isset($params['pid']) && intval($params['pid'])>=0)
        {
            $player_list = $player_list->where("pid",$params['pid']);
        }
        //游戏类型
        if(isset($params['game']) && !is_array($params['game']) && strlen($params['game'])>=3)
        {
            $player_list = $player_list->where("game",$params['game']);
        }
        //游戏类型
        if (isset($params['game']) && is_array($params['game'])) {
            $player_list = $player_list->whereIn("game", $params['game']);
        }

        //不是队员
        if(isset($params['except_player']) && $params['except_player']>0)
        {
            $player_list = $player_list->where("pid","!=",$params['except_player']);
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
                ->orderBy("pid","desc")
                ->get()->toArray();
        }
        return $player_list;
    }
    public function getPlayerById($pid,$fields = "*")
    {
        if(is_array($pid))
        {
            $pid = $pid['0']??0;
        }
        $player_info =$this->select(explode(",",$fields))
            ->where("pid",$pid)
            ->get()->first();
        if(isset($player_info->pid))
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
    public function updatePlayer($pid=0,$data=[])
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
        return $this->where('pid',$pid)->update($data);
    }
    public function getPlayerCount($params=[]){
        $player_count = $this;

        /*if(isset($params['game']))
        {
            $sourceList = config('app.intergration.player.'.$params['game']);
            $player_count = $player_count->whereIn("original_source",array_column($sourceList,"source"));
        }
        */
        //显示状态，负值全部 0不显示 1显示
        $params['display'] = $params['display'] ?? 1;
        if ($params['display'] >= 0)
        {
            $player_count = $player_count->where("display",$params['display']);
        }
        //总表队员ID
        if(isset($params['pids']) && count($params['pid'])>=1)
        {
            $player_count = $player_count->whereIn("pid", $params['pid']);
        }
        //总表队员ID
        if(isset($params['pid']) && intval($params['pid'])>=0)
        {
            $player_count = $player_count->where("pid",$params['pid']);
        }
        //游戏类型
        if(isset($params['game']) && !is_array($params['game']) && strlen($params['game'])>=3)
        {
            $player_count = $player_count->where("game",$params['game']);
        }
        //游戏类型
        if (isset($params['game']) && is_array($params['game'])) {
            $player_count = $player_count->whereIn("game", $params['game']);
        }

        //不是队员
        if(isset($params['except_player']) && $params['except_player']>0)
        {
            $player_count = $player_count->where("pid","!=",$params['except_player']);
        }
        return $player_count->count();
    }
}
