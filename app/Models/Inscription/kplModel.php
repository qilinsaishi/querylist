<?php

namespace App\Models\Inscription;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class kplModel extends Model
{
    protected $table = "kpl_inscription_info";
    protected $primaryKey = "inscription_id";
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
    public function getInscriptionList($params)
    {
        $inscription_list =$this->select('inscription_id', 'inscription_name','grade', 'logo','type');
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $inscription_list = $inscription_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("inscription_id")
            ->get()->toArray();
        return $inscription_list;
    }
    public function getInscriptionByName($inscription_name)
    {
        $inscription_info =$this->select("*")
                    ->where("inscription_name",$inscription_name)
                    ->get()->first();
        if(isset($inscription_info->inscription_id))
        {
            $inscription_info = $inscription_info->toArray();
        }
        else
        {
            $inscription_info = [];
        }
        return $inscription_info;
    }
    public function insertInscription($data)
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

    public function updateInscription($inscription_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('inscription_id',$inscription_id)->update($data);
    }

    public function saveInscription($data)
    {
        $data['inscription_name'] = preg_replace("/\s+/", "",$data['inscription_name']);
        $data['inscription_name'] = trim($data['inscription_name']);
        $data['aka'] = ($data['aka']=="")?[]:[$data['aka']];
        $currentInscription = $this->getInscriptionByName($data['inscription_name']);
        if(!isset($currentInscription['inscription_id']))
        {
            echo "toInsertInscription:"."\n";
            return $this->insertInscription($data);
        }
        else
        {
            echo "toUpdateInscription:".$currentInscription['inscription_id']."\n";
            //校验原有数据
            foreach($data as $key => $value)
            {
                if(in_array($key,$this->toAppend))
                {
                    $t = json_decode($currentInscription[$key],true);
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
                if(isset($currentInscription[$key]) && ($currentInscription[$key] == $value))
                {
                    //echo $currentInscription[$key]."-".$value."\n";
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
                return $this->updateInscription($currentInscription['inscription_id'],$data);
            }
            else
            {
                return true;
            }
        }
    }
    public function getInscriptionCount($params = []){
        $inscription_count = $this;
        $keys = $params['keys'] ?? [];
        if (!empty($keys)) {
            if (!empty($keys)) {
                $inscription_count = $inscription_count->whereIn('key', $keys);
            }

        }

        return $inscription_count->count();
    }

    public function getInscriptionById($skill_id){
        $inscription_info =$this->select("*")
            ->where("inscription_id",$skill_id)
            ->get()->first();
        if(isset($inscription_info->inscription_id))
        {
            $inscription_info = $inscription_info->toArray();
        }
        else
        {
            $inscription_info = [];
        }
        return $inscription_info;
    }

    public function getInscriptionByIds($inscriptionIds){
        $inscription_info = [];
        $inscription_info =$this->select('inscription_id', 'inscription_name', 'logo','description')
            ->whereIn("id",$inscriptionIds)
            ->orderBy("inscription_id")
            ->get()->toArray();

        return $inscription_info;
    }
}
