<?php

namespace App\Models\Hero\Spell;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class lolModel extends Model
{
    protected $table = "lol_hero_spell_info";
    protected $primaryKey = "spell_id";
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
    public function getSpellByHero($params)
    {
        $spell_list =$this->select("*");
        $pageSizge = $params['page_size']??10;
        $page = $params['page']??1;
        if(isset($params['hero_id']) && $params['hero_id']>0)
        {
            $spell_list = $spell_list->where("hero_id",$params['hero_id']);
        }
        $spell_list = $spell_list->orderBy("spell_id") ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->get()->toArray();
        return $spell_list;
    }
    public function getSpellByName($spell_name)
    {
        $spell_info =$this->select("*")
                    ->where("spell_name",$spell_name)
                    ->get()->first();
        if(isset($spell_info->spell_id))
        {
            $spell_info = $spell_info->toArray();
        }
        else
        {
            $spell_info = [];
        }
        return $spell_info;
    }
    public function insertSpell($data)
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

    public function updateSpell($spell_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('spell_id',$spell_id)->update($data);
    }

    public function saveSpell($data)
    {
        $currentSpell = $this->getSpellByName($data['spell_name']);
        if(!isset($currentSpell['spell_id']))
        {
            echo "toInsertSpell:"."\n";
            return $this->insertSpell($data);
        }
        else
        {
            echo "toUpdateSpell:".$currentSpell['spell_id']."\n";
            //校验原有数据
            foreach($data as $key => $value)
            {
                if(in_array($key,$this->toAppend))
                {
                    $t = json_decode($currentSpell[$key],true);
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
                if(isset($currentSpell[$key]) && ($currentSpell[$key] == $value))
                {
                    //echo $currentSpell[$key]."-".$value."\n";
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
                return $this->updateSpell($currentSpell['spell_id'],$data);
            }
            else
            {
                return true;
            }
        }
    }
    public function getSpellCount($params=[]){
        $spell_count = $this;
        $keys = $params['keys'] ?? [];
        if (!empty($keys)) {
            if (!empty($keys)) {
                $spell_count = $spell_count->whereIn('key', $keys);
            }
        }
        return $spell_count->count();
    }
}
