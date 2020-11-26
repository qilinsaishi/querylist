<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MissionModel extends Model
{
    protected $table = "mission_list";
    protected $primaryKey = "mission_id";
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
  /*  protected $casts = [
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];*/

    public function getMissionByMachine($asign=1,$count = 3,$game='',$source='',$status = 1)
    {
        $mission_list =$this->select("*");
        //接手客户端
        if($asign>0)
        {
            $mission_list = $mission_list->where("asign_to",$asign);
        }
        //游戏
        if($game!="")
        {
            $mission_list = $mission_list->where("game",$game);
        }
        //爬取数据源
        if($source!="")
        {
            $mission_list = $mission_list->where("source",$source);
        }
        $mission_list = $mission_list
            ->where("status",$status)
            ->limit($count)
            ->get()->toArray();
        return $mission_list;
    }

    public function insertMission($data)
    {
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

    public function updateMission($mission_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('mission_id',$mission_id)->update($data);
    }
}
