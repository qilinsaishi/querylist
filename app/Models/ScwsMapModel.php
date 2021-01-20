<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ScwsMapModel extends Model
{
    protected $table = "scws_map";
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
    public function deleteByContent($id=0)
    {
        return $this->where('content_id',$id)->delete();
    }

    public function saveMap($id,$type,$mapList,$time)
    {
        $this->deleteByContent($id,$type);
        foreach($mapList as $keyword_info)
        {
            $map = ['keyword'=>$keyword_info['word'],
                    "weight"=>$keyword_info['weight'],
                    "attr"=>$keyword_info['attr'],
                    "content_id"=>$id,
                    "count"=>$keyword_info['times'],
                    "content_time"=>$time,
                ];
            $this->insert($map);
        }
        return;
    }
    public function getList($params)
    {
        $fields = $params['fields']??"source_id,source_type,content_id,content_type,count";
        $keyword_list =$this->select(explode(",",$fields));
        //目标ID
        if(isset($params['content_id']) && ($params['content_id'])>0)
        {
            $keyword_list = $keyword_list->where("content_id",$params['content_id']);
        }
        //来源ID
        if(isset($params['word']) && strlen($params['word'])>0)
        {
            $keyword_list = $keyword_list->where("word",$params['word']);
        }
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $keyword_list = $keyword_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("content_time","desc")
            ->get()->toArray();
        return $keyword_list;
    }
}
