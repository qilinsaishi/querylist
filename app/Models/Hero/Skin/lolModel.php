<?php

namespace App\Models\Hero\Skin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class lolModel extends Model
{
    protected $table = "lol_hero_skin_info";
    protected $primaryKey = "skin_id";
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
        "data"=>[]
    ];
    protected $toJson = [
        "data"
    ];
    protected $toAppend = [
    ];
    public function getSkinByHero($params)
    {
        $skin_list =$this->select("*");
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        if(isset($params['hero_id']) && $params['hero_id']>0)
        {
            $skin_list = $skin_list->where("hero_id",$params['hero_id']);
        }
        $skin_list = $skin_list->orderBy("skin_id") ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->get()->toArray();
        return $skin_list;
    }
    public function getSkinById($skin_id)
    {
        $skin_info =$this->select("*")
                    ->where("skin_id",$skin_id)
                    ->get()->first();
        if(isset($skin_info->skin_id))
        {
            $skin_info = $skin_info->toArray();
        }
        else
        {
            $skin_info = [];
        }
        return $skin_info;
    }
    public function insertSkin($data)
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

    public function updateSkin($skin_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('skin_id',$skin_id)->update($data);
    }

    public function saveSkin($data)
    {
        $currentSkin = $this->getSkinById($data['skin_id']);
        if(!isset($currentSkin['skin_id']))
        {
            echo "toInsertSkin:"."\n";
            return $this->insertSkin($data);
        }
        else
        {
            echo "toUpdateSkin:".$currentSkin['skin_id']."\n";
            //校验原有数据
            foreach($data as $key => $value)
            {
                if(in_array($key,$this->toAppend))
                {
                    $t = json_decode($currentSkin[$key],true);
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
                if(isset($currentSkin[$key]) && ($currentSkin[$key] == $value))
                {
                    //echo $currentSkin[$key]."-".$value."\n";
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
                return $this->updateSkin($currentSkin['skin_id'],$data);
            }
            else
            {
                return true;
            }
        }
    }
    public function getSkinCount($params=[]){
        $skin_count = $this;
        $keys = $params['keys'] ?? [];
        if (!empty($keys)) {
            if (!empty($keys)) {
                $skin_count = $skin_count->whereIn('key', $keys);
            }
        }
        return $skin_count->count();
    }
}
