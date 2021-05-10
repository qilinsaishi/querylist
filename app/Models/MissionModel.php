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

    public function getMissionByMachine($asign = 1, $count = 3, $game = '', $source = '', $mission_type = '', $status = [0, 1])
    {//echo 'game:'.$game.',mission_type:'.$mission_type.',source:'.$source."\n";
        $mission_list = $this->select("*");
        //接收客户端
        if ($asign > 0) {
            $mission_list = $mission_list->where("asign_to", $asign);
        }
        //游戏
        if ($game != "") {
            $mission_list = $mission_list->where("game", $game);
        }
        //爬取数据源
        if ($source != "") {
            $mission_list = $mission_list->where("source", $source);
        }
        if ($mission_type != "") {
            $mission_list = $mission_list->where("mission_type", $mission_type);
        }
        $mission_list = $mission_list
            ->whereIn("mission_status", $status)
            ->limit($count)
            ->orderBy('mission_id',"desc")
            ->get()->toArray();
        return $mission_list;
    }

    public function getMissionByTitle($title = '', $mission_type, $game, $source, $asign_to)
    {
        //echo $title . "-" . $mission_type . "-" . $game . "-" . $source . "\n";
        $mission_obj = $this->select("*");
        if (!empty($title)) {
            $mission_obj->where("title", $title);
        }
        $mission_info = $mission_obj->where("mission_type", $mission_type)
            ->where("game", $game)
            ->where("source", $source)
            ->where("asign_to", $asign_to)
            ->where("mission_status", [0, 1])
            ->get()->first();
        if (isset($mission_info->mission_id)) {
            $mission_info = $mission_info->toArray();
        } else {
            $mission_info = [];
        }
        return $mission_info;
    }
    public function getMissionbyId($mission_id){
        $mission_info = $this->where("mission_id",$mission_id)->get()->first();
        if (isset($mission_info->mission_id)) {
            $mission_info = $mission_info->toArray();
        } else {
            $mission_info = [];
        }
        return $mission_info;
    }

    public function insertMission($data)
    {
        $currentMission = $this->getMissionByTitle($data['title'] ??'', $data['mission_type'], $data['game'], $data['source'], $data['asign_to']);
        try{
            if (isset($currentMission) && $currentMission ) {
                return 1;
            } else {
                $currentTime = date("Y-m-d H:i:s");
                if (!isset($data['create_time'])) {
                    $data['create_time'] = $currentTime;
                }
                if (!isset($data['update_time'])) {
                    $data['update_time'] = $currentTime;
                }
                return $this->insertGetId($data);
            }
        }catch (\Exception $e){
           // print_r($e->getMessage());exit;
        }


    }

    public function updateMission($mission_id = 0, $data = [])
    {
        $currentTime = date("Y-m-d H:i:s");
        if (!isset($data['update_time'])) {
            $data['update_time'] = $currentTime ?? '';
        }
        return $this->where('mission_id', $mission_id)->update($data);
    }
    public function getMissionCount($params=[]){
        $missionModel = $this->where('mission_status','<>',2);
        $game=$params['game'] ?? '';
        $mission_type=$params['mission_type'] ?? '';
        $source_link=$params['source_link'] ?? '';
        $title=$params['title'] ?? '';
        //游戏
        $missionModel = $missionModel->where("game","=",$game);

        //类型

        $missionModel = $missionModel->where("mission_type",$mission_type);

        //采集来源
        if($source_link!="")
        {
            $missionModel = $missionModel->where("source_link",$source_link);
        }
        //标题
        if($title!="")
        {
            $missionModel = $missionModel->where("title",$title);
        }


        return $missionModel->count();
    }
}
