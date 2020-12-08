<?php

namespace App\Models\Equipment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class kplModel extends Model
{
    protected $table = "kpl_equipment_info";
    protected $primaryKey = "equipment_id";
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
    public function getEquipmentList($params)
    {
        $equipment_list =$this->select("*");
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $equipment_list = $equipment_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("id")
            ->get()->toArray();
        return $equipment_list;
    }
    public function getEquipmentByName($equipment_name,$type)
    {
        $equipment_info =$this->select("*")
                    ->where("equipment_name",$equipment_name)
                    ->where("type",$type)
            ->get()->first();
        if(isset($equipment_info->equipment_id))
        {
            $equipment_info = $equipment_info->toArray();
        }
        else
        {
            $equipment_info = [];
        }
        return $equipment_info;
    }
    public function insertEquipment($data)
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

    public function updateEquipment($equipment_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('equipment_id',$equipment_id)->update($data);
    }

    public function saveEquipment($data)
    {
        $data['equipment_name'] = preg_replace("/\s+/", "",$data['equipment_name']);
        $data['equipment_name'] = trim($data['equipment_name']);
        $data['aka'] = ($data['aka']=="")?[]:[$data['aka']];
        $currentEquipment = $this->getEquipmentByName($data['equipment_name'],$data['type']);
        if(!isset($currentEquipment['equipment_id']))
        {
            echo "toInsertEquip:"."\n";
            return $this->insertEquipment($data);
        }
        else
        {
            echo "toUpdateEquip:".$currentEquipment['equipment_id']."\n";
        }
    }
}
