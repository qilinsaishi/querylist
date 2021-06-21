<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DataMapModel extends Model
{
    protected $table = "data_map";
    public $primaryKey = "id";
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
    public $toJson = [

    ];
    public $toAppend = [

    ];
    public function saveMap($data)
    {
        $currentMap = $this->getMapBySiteId($data['api_id'],$data['data_type'],$data['site_id'],$data['source'],$data['game']);
        if($currentMap)
        {
            return $currentMap;
        }
        else
        {
            return $this->insertMap($data);
        }

    }
    public function insertMap($data)
    {
        return $this->insertGetId($data);
    }
    public function getMapBySiteId($api_id,$data_type,$site_id,$source,$game){
        $fields = "*";
        $mapInfo =$this->select($fields)
            ->where("api_id",$api_id)
            ->where("data_type",$data_type)
            ->where("site_id",$site_id)
            ->where("source",$source)
            ->where("game",$game)
            ->get()->first();
        if(isset($mapInfo->id))
        {
            return $mapInfo->id;
        }
        else
        {
            return false;
        }
    }
}
