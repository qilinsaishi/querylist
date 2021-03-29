<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BaiduKeywordModel extends Model
{
    protected $table = "baidu_keyword";
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
    public function insert($data)
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['create_time']))
        {
            $data['create_time'] = $currentTime;
        }
        return $this->insertGetId($data);
    }
    public function getByKeyword($keyword)
    {
        $word =$this->select("id")
            ->where("keyword",$keyword)
            ->get()->first();
        if(isset($word->id))
        {
            $word = $word->toArray();
        }
        else
        {
            $word = [];
        }
        return $word;
    }
    public function getById($id)
    {
        $word =$this->select("*")
            ->where("id",$id)
            ->get()->first();
        if(isset($word->id))
        {
            $word = $word->toArray();
        }
        else
        {
            $word = [];
        }
        return $word;
    }
    public function saveMap($mapList)
    {
        $return = [];
        foreach($mapList as $keyword)
        {
            $word = $this->getByKeyword($keyword['tag']);
            if(!isset($word["id"]))
            {
                $id = $this->insert(['keyword'=>$keyword['tag']]);
                if($id>0)
                {
                    $return[$keyword['tag']] = $id;
                }
            }
            else
            {
                $return[$keyword['tag']] = $word["id"];
            }
        }
        return $return;
    }
    public function getDisableList()
    {
        $keyword_list =$this->select("id");
        $keyword_list = $keyword_list->where("available",0)->get()->toArray();
        return array_column($keyword_list,"id");
    }
}
