<?php

namespace App\Models\Rune;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Libs\CollectLib;

class lolModel extends Model
{
    protected $table = "lol_rune_info";
    protected $primaryKey = "rune_id";
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
        "aka","slots","bonuses"
    ];
    public function getRuneList($params)
    {
        $rune_list =$this->select("*");
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $rune_list = $rune_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("id")
            ->get()->toArray();
        return $rune_list;
    }
    public function getRuneByName($rune_name)
    {
        $rune_info =$this->select("*")
                    ->where("rune_name",$rune_name)
                    ->get()->first();
        if(isset($rune_info->rune_id))
        {
            $rune_info = $rune_info->toArray();
        }
        else
        {
            $rune_info = [];
        }
        return $rune_info;
    }
    public function insertRune($data)
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

    public function updateRune($rune_id=0,$data=[])
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('rune_id',$rune_id)->update($data);
    }

    public function saveRune($data)
    {
        $data['rune_name'] = preg_replace("/\s+/", "",$data['rune_name']);
        $data['rune_name'] = trim($data['rune_name']);
        $data['aka'] = ($data['aka']=="")?[]:[$data['aka']];
        $currentRune = $this->getRuneByName($data['rune_name']);
        if(!isset($currentRune['rune_id']))
        {
            echo "toInsertRune:"."\n";
            return $this->insertRune($data);
        }
        else
        {
            echo "toUpdateRune:".$currentRune['rune_id']."\n";
        }
    }
}
