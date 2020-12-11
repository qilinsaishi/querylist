<?php

namespace App\Models\Match\chaofan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class eventModel extends Model
{
    protected $table = "chaofan_event_list";
    //protected $primaryKey = "event_id";
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
        "pics"
    ];
    protected $toAppend = [
    ];
    public function getEventList($params)
    {
        $event_list =$this->select("*");
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $event_list = $event_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("event_id")
            ->get()->toArray();
        return $event_list;
    }
    public function getEventById($event_id)
    {
        $event_info =$this->select("*")
            ->where("event_id",$event_id)
            ->get()->first();
        if(isset($event_info->event_id))
        {
            $event_info = $event_info->toArray();
        }
        else
        {
            $event_info = [];
        }
        return $event_info;
    }
    public function insertEvent($data)
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

    public function updateEvent($event_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('event_id',$event_id)->update($data);
    }

    public function saveEvent($data)
    {
        $data['event_title'] = preg_replace("/\s+/", "",$data['event_title']);
        $data['event_title'] = trim($data['event_title']);
        $currentEvent = $this->getEventById($data['event_id']);
        if(!isset($currentEvent['event_id']))
        {
            echo "toInsertEvent:"."\n";
            return $this->insertEvent($data);
        }
        else
        {
            echo "toUpdateEvent:".$currentEvent['event_id']."\n";
            //校验原有数据
            foreach($data as $key => $value)
            {
                if(in_array($key,$this->toAppend))
                {
                    $t = json_decode($currentEvent[$key],true);
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
                if(isset($currentEvent[$key]) && ($currentEvent[$key] == $value))
                {
                    //echo $currentEvent[$key]."-".$value."\n";
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
                return $this->updateEvent($currentEvent['event_id'],$data);
            }
            else
            {
                return true;
            }
        }
    }
}
