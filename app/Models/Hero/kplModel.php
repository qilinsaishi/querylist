<?php

namespace App\Models\Hero;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class kplModel extends Model
{
    protected $table = "kpl_hero_info";
    protected $primaryKey = "hero_id";
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
        "aka","stat","skin_list","skill_list","inscription_tips","skill_tips","equipment_tips","hero_tips",'summoner_skill'
    ];
    protected $toAppend = [
        "aka"
    ];
    public function getHeroList($params)
    {
        $fields = isset($params['fields'])?explode(",",$params['fields']):['hero_id', 'hero_name', 'cn_name', 'type','logo'];
        $hero_list =$this->select($fields);
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        if(isset($params['rand']) && $params['rand'] >0)
        {
            $hero_list = $hero_list
                ->limit($pageSizge)
                ->offset(($page-1)*$pageSizge)
                ->inRandomOrder()
                ->get()->toArray();
        }
        else
        {
            $hero_list = $hero_list
                ->limit($pageSizge)
                ->offset(($page-1)*$pageSizge)
                ->orderBy("id")
                ->get()->toArray();
        }
        return $hero_list;
    }
    public function getHeroByName($hero_name)
    {
        $hero_info =$this->select("*")
                    ->where("hero_name",$hero_name)
                    ->get()->first();
        if(isset($hero_info->hero_id))
        {
            $hero_info = $hero_info->toArray();
        }
        else
        {
            $hero_info = [];
        }
        return $hero_info;
    }
    public function insertHero($data)
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

    public function updateHero($hero_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('hero_id',$hero_id)->update($data);
    }

    public function saveHero($data)
    {
        $data['hero_name'] = preg_replace("/\s+/", "",$data['hero_name']);
        $data['hero_name'] = trim($data['hero_name']);
        $data['aka'] = ($data['aka']=="")?[]:[$data['aka']];
        $currentHero = $this->getHeroByName($data['hero_name']);
        if(!isset($currentHero['hero_id']))
        {
            echo "toInsertHero:"."\n";
            return $this->insertHero($data);
        }
        else
        {
            echo "toUpdateHero:".$currentHero['hero_id']."\n";
            //校验原有数据
            foreach($data as $key => $value)
            {
                if(in_array($key,$this->toAppend))
                {
                    $t = json_decode($currentHero[$key],true);
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
                if(isset($currentHero[$key]) && ($currentHero[$key] == $value))
                {
                    //echo $currentHero[$key]."-".$value."\n";
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
                return $this->updateHero($currentHero['hero_id'],$data);
            }
            else
            {
                return true;
            }
        }
    }
    public function getHeroCount($params=[]){
        $hero_count = $this;
        $keys = $params['keys'] ?? [];
        if (!empty($keys)) {
            if (!empty($keys)) {
                $hero_count = $hero_count->whereIn('key', $keys);
            }

        }

        return $hero_count->count();
    }

    public function getHeroById($hero_id){
        if(is_array($hero_id))
        {
            $hero_id = $hero_id['0']??($hero_id['id']??0);
        }
        $hero_info =$this->select("*")
            ->where("hero_id",$hero_id)
            ->get()->first();
        if(isset($hero_info->hero_id))
        {
            $hero_info = $hero_info->toArray();
        }
        else
        {
            $hero_info = [];
        }
        return $hero_info;
    }
    public function getHeroInfoById($id){
        $hero_info =$this->select('hero_id', 'hero_name', 'logo')
            ->where("id",$id)
            ->get()->first();
        if(isset($hero_info->hero_id))
        {
            $hero_info = $hero_info->toArray();
        }
        else
        {
            $hero_info = [];
        }
        return $hero_info;
    }
    public function getAllKeywords($game)
    {
        $keywords = [];
        $heroList = $this->getHeroList(["game"=>$game,"fields"=>"hero_id,hero_name,en_name,aka","page_size"=>10000]);
        foreach($heroList as $hero_info)
        {
            $t = array_unique(array_merge([$hero_info['hero_name']],[$hero_info['en_name']],json_decode($hero_info['aka'])));
            foreach($t as $value)
            {
                if(trim($value) != "" && !isset($keywords[trim($value)]))
                {
                    $keywords[trim($value)] = $hero_info['hero_id'];
                }
            }
        }
        return $keywords;
    }
}
