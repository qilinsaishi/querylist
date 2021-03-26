<?php

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TeamNameMapModel extends Model
{
    protected $table = "team_name_map";
    protected $primaryKey = "id";
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
    ];
    protected $toAppend = [
    ];
    protected $keep = [
    ];

    public function getTeamByNameHash($name_hash,$game,$fields = "*")
    {
        $team_info =$this->select(explode(",",$fields))
            ->where("game",$game)
            ->where("name_hash",$name_hash)
            ->get()->first();
        if(isset($team_info->id))
        {
            $team_info = $team_info->toArray();
        }
        else
        {
            $team_info = [];
        }
        return $team_info;
    }
    public function insertMap($data)
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
    public function saveMap($data)
    {
        $currentMap = $this->getTeamByNameHash($data['name_hash'],$data['game']);
        if(isset($currentMapp['tid']))
        {
            //echo "insert";
            //已经存在
            return true;
        }
        else
        {
            //echo "existed";
            $insert = $this->insertMap($data);
            if(!$insert)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
    }
}
