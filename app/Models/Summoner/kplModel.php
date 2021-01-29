<?php

namespace App\Models\Summoner;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class kplModel extends Model
{
    protected $table = "kpl_summoner_info";
    protected $primaryKey = "skill_id";
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
        "aka"
    ];
    protected $toAppend = [
        "aka"
    ];
    public function getSkillList($params)
    {
        $skill_list =$this->select("*");
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $skill_list = $skill_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("skill_id")
            ->get()->toArray();
        return $skill_list;
    }
    public function getSkillByName($skill_name)
    {
        $skill_info =$this->select("*")
                    ->where("skill_name",$skill_name)
                    ->get()->first();
        if(isset($skill_info->skill_id))
        {
            $skill_info = $skill_info->toArray();
        }
        else
        {
            $skill_info = [];
        }
        return $skill_info;
    }
    public function insertSkill($data)
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

    public function updateSkill($skill_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('skill_id',$skill_id)->update($data);
    }

    public function saveSkill($data)
    {
        $data['skill_name'] = preg_replace("/\s+/", "",$data['skill_name']);
        $data['skill_name'] = trim($data['skill_name']);
        $data['aka'] = ($data['aka']=="")?[]:[$data['aka']];
        $currentSkill = $this->getSkillByName($data['skill_name']);
        if(!isset($currentSkill['skill_id']))
        {
            echo "toInsertSkill:"."\n";
            return $this->insertSkill($data);
        }
        else
        {
            echo "toUpdateSkill:".$currentSkill['skill_id']."\n";
            //校验原有数据
            foreach($data as $key => $value)
            {
                if(in_array($key,$this->toAppend))
                {
                    $t = json_decode($currentSkill[$key],true);
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
                if(isset($currentSkill[$key]) && ($currentSkill[$key] == $value))
                {
                    //echo $currentSkill[$key]."-".$value."\n";
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
                return $this->updateSkill($currentSkill['skill_id'],$data);
            }
            else
            {
                return true;
            }
        }
    }

    public function getSkillCount($params = []){
        $skill_count = $this;
        $keys = $params['keys'] ?? [];
        if (!empty($keys)) {
            if (!empty($keys)) {
                $skill_count = $skill_count->whereIn('key', $keys);
            }

        }

        return $skill_count->count();
    }

    public function getSkillById($skill_id){
        $skill_info =$this->select("*")
            ->where("skill_id",$skill_id)
            ->get()->first();
        if(isset($skill_info->skill_id))
        {
            $skill_info = $skill_info->toArray();
        }
        else
        {
            $skill_info = [];
        }
        return $skill_info;
    }

    public function getSkillByIds($skillIds){
        $skill_info = [];
        $skill_info =$this->select('skill_id', 'skill_name', 'logo')
            ->whereIn("id",$skillIds)
            ->orderBy("skill_id")
            ->get()->toArray();

        return $skill_info;
    }
}
