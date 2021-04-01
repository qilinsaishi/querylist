<?php

namespace App\Models\Player;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TotalPlayerModel extends Model
{
    protected $table = "player_list";
    protected $primaryKey = "pid";
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
    ];
    protected $keep = [
    ];
    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
    public function getPlayerById($player_id,$fields = "*")
    {
        $player_info =$this->select(explode(",",$fields))
            ->where("pid",$player_id)
            ->get()->first();
        if(isset($player_info->pid))
        {
            $player_info = $player_info->toArray();
        }
        else
        {
            $player_info = [];
        }
        return $player_info;
    }
    public function insertPlayer($data)
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
    public function updatePlayer($player_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        foreach($this->keep as $key)
        {
            if(isset($data[$key]))
            {
                unset($data[$key]);
            }
        }
        return $this->where('pid',$player_id)->update($data);
    }
}
