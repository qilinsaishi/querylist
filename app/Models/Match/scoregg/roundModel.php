<?php

namespace App\Models\Match\scoregg;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class roundModel extends Model
{
    protected $table = "scoregg_round_info";
    //protected $primaryKey = "round_id";
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
    public function getRoundList($params)
    {
        $fields = isset($params["fields"])?explode(",",$params["fields"],true):["*"];
        $round_list =$this->select($fields);
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $round_list = $round_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("create_time","desc")
            ->get()->toArray();
        return $round_list;
    }
    public function getRoundCount($params)
    {
        $round_count =$this->count();
        return $round_count;
    }
    public function getRoundByName($round_name,$game)
    {
        $round_info =$this->select("*")
            ->where("round_name",$round_name)
            ->where("game",$game)
            ->get()->first();
        if(isset($round_info->round_id))
        {
            $round_info = $round_info->toArray();
        }
        else
        {
            $round_info = [];
        }
        return $round_info;
    }
    public function getRoundById($round_id)
    {
        $round_id = is_array($round_id)?$round_id['round_id']:$round_id;
        $round_info =$this->select("*")
            ->where("round_id",$round_id)
            ->get()->first();
        if(isset($round_info->round_id))
        {
            $round_info = $round_info->toArray();
        }
        else
        {
            $round_info = [];
        }
        return $round_info;
    }
    public function insertRound($data)
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

    public function updateRound($round_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('round_id',$round_id)->update($data);
    }

    public function saveRound($data)
    {
        $data['round_name'] = preg_replace("/\s+/", "",$data['round_name']);
        $data['round_name'] = trim($data['round_name']);
        $currentRound = $this->getRoundById($data['round_id']);
        if(!isset($currentRound['round_id']))
        {
            echo "toInsertRound:"."\n";
            $insert = $this->insertRound($data);
            $insert = ($insert==0)?$data['round_id']:0;
            return $insert;
        }
        else
        {
            echo "toUpdateRound:".$currentRound['round_id']."\n";
                //校验原有数据
                foreach($data as $key => $value)
            {
                if(in_array($key,$this->toAppend))
                {
                    $t = json_decode($currentRound[$key],true);
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
                if(isset($currentRound[$key]) && ($currentRound[$key] == $value))
                {
                    //echo $currentRound[$key]."-".$value."\n";
                    echo $key.":passed\n";
                    unset($data[$key]);
                }
                else
                {
                    echo $key.":difference:\n";
                }
                if(count($data))
                {
                    return $this->updateRound($currentRound['round_id'],$data);
                }
                else
                {
                    return true;
                }
            }
        }
    }
}
