<?php

namespace App\Models\Match\cpseo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class teamModel extends Model
{
    protected $table = "cpseo_team_info";
    //protected $primaryKey = "team_id";
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
    public function getTeamList($params=[])
    {
        $team_list =$this->select("*");
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $team_list = $team_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("team_id")
            ->get()->toArray();
        return $team_list;
    }
    public function getTeamById($team_id)
    {
        $team_info =$this->select("*")
            ->where("team_id",$team_id)
            ->get()->first();
        if(isset($team_info->team_id))
        {
            $team_info = $team_info->toArray();
        }
        else
        {
            $team_info = [];
        }
        return $team_info;
    }
    public function getTeamByName($team_name)
    {
        $team_info =$this->select("*")
            ->where("team_name",$team_name)
            ->get()->first();
        if(isset($team_info->team_id))
        {
            $team_info = $team_info->toArray();
        }
        else
        {
            $team_info = [];
        }
        return $team_info;
    }
    public function insertTeam($data)
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

    public function updateTeam($team_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('team_id',$team_id)->update($data);
    }

    public function saveTeam($data)
    {
        $currentTeam = $this->getTeamByName($data['team_name']);
        if(!isset($currentTeam['team_id']))
        {
            echo "toInsertTeam:"."\n";
            return $this->insertTeam($data);
        }
        else
        {
            echo "teamExist:"."\n";
            return $currentTeam['team_id'];
        }
    }
}
