<?php

namespace App\Models\Hero;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class lolModel extends Model
{
    protected $table = "lol_hero_info";
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
        "aka","ally_tips","enemy_tips","roles"
    ];
    public function getHeroList($params)
    {
        $hero_list =$this->select("*");
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $hero_list = $hero_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("id")
            ->get()->toArray();
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
        }
    }
}
