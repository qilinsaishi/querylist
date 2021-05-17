<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KeywordMapModel extends Model
{
    protected $table = "keyword_map";
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
    public function deleteByContent($id=0,$game="kpl",$type="information")
    {
        return $this->where('content_id',$id)->where('game',$game)->where('content_type',$type)->delete();
    }

    public function saveMap($id,$game,$type,$mapList,$time)
    {
        $this->deleteByContent($id,$game,$type);
        foreach($mapList as $source_type => $list)
        {
            foreach($list as $keyword => $keyword_info)
            {
                $map = ['keyword'=>$keyword,
                    'game'=>$game,
                    "source_type"=>$source_type,"content_type"=>$type,
                    "source_id"=>$keyword_info['id'],"content_id"=>$id,
                    "count"=>$keyword_info['count'],
                    "content_time"=>$time,
                    ];
                $this->insert($map);
            }
        }
        return;
    }
    public function getList($params)
    {
        $fields = $params['fields']??"source_id,game,source_type,content_id,content_type,count";
        $keyword_list =$this->select(explode(",",$fields));
        //对应游戏
        if(isset($params['game']) && ($params['game'])!="")
        {
            $keyword_list = $keyword_list->where("game",$params['game']);
        }
        //目标ID
        if(isset($params['content_id']) && ($params['content_id'])>0)
        {
            $keyword_list = $keyword_list->where("content_id",$params['content_id']);
        }
        //来源ID
        if(isset($params['source_id']) && !is_array($params['source_id']) && ($params['source_id'])>0)
        {
            $keyword_list = $keyword_list->where("source_id",$params['source_id']);
        }
        //来源ID
        if(isset($params['source_id']) && (is_array($params['source_id']) && count($params['source_id'])>0) )
        {
            $keyword_list = $keyword_list->whereIn("source_id",$params['source_id']);
        }
        //目标类型
        if(isset($params['content_type']) && strlen($params['content_type'])>0)
        {
            $keyword_list = $keyword_list->where("content_type",$params['content_type']);
        }
        //来源类型
        if(isset($params['source_type']) && strlen($params['source_type'])>0)
        {
            $keyword_list = $keyword_list->where("source_type",$params['source_type']);
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
