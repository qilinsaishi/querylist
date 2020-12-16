<?php

namespace App\Models\Match\cpseo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tournamentModel extends Model
{
    protected $table = "cpseo_tournament_info";
    //protected $primaryKey = "tournament_id";
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
    public function getTournamentList($params=[])
    {
        $tournament_list =$this->select("*");
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $tournament_list = $tournament_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("tournament_id")
            ->get()->toArray();
        return $tournament_list;
    }
    public function getTournamentById($tournament_id)
    {
        $tournament_info =$this->select("*")
            ->where("tournament_id",$tournament_id)
            ->get()->first();
        if(isset($tournament_info->tournament_id))
        {
            $tournament_info = $tournament_info->toArray();
        }
        else
        {
            $tournament_info = [];
        }
        return $tournament_info;
    }
    public function getTournamentByName($tournament_name)
    {
        $tournament_info =$this->select("*")
            ->where("tournament_name",$tournament_name)
            ->get()->first();
        if(isset($tournament_info->tournament_id))
        {
            $tournament_info = $tournament_info->toArray();
        }
        else
        {
            $tournament_info = [];
        }
        return $tournament_info;
    }
    public function insertTournament($data)
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

    public function updateTournament($tournament_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('tournament_id',$tournament_id)->update($data);
    }

    public function saveTournament($data)
    {
        $currentTournament = $this->getTournamentByName($data['tournament_name']);
        if(!isset($currentTournament['tournament_id']))
        {
            echo "toInsertTournament:"."\n";
            return $this->insertTournament($data);
        }
        else
        {
            echo "tournamentExist:"."\n";
            return $currentTournament['tournament_id'];
        }
    }
}
