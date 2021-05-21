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
    public function saveMap($id,$game,$type,$content_type,$mapList,$keywordMapList,$time)
    {
        echo "content_id:".$id."\n";
        echo "deleted:".$this->deleteByContent($id,$type)."\n";
        foreach($mapList as $keyword_info)
        {
            {
                $map = ['keyword'=>$keyword_info['word'],
                    'keyword_id'=>$keyword_info['keyword_id'],
                    "weight"=>$keyword_info['weight'],
                    "attr"=>$keyword_info['attr'],
                    "content_id"=>$id,
                    "count"=>$keyword_info['times'],
                    "content_time"=>$time,
                    "content_type"=>$content_type,
                    "game"=>$game,
                ];
                $this->insert($map);
            }
        }
        return;
    }
    public function getList($params)
    {
        $connection = DB::connection($this->connection);
        $keyword_list =$this->select("content_id",$connection->raw('sum(weight) as weight'));
        //游戏类型
        if(isset($params['game']) && !is_array($params['game']) && strlen($params['game'])>=3)
        {
            $keyword_list = $keyword_list->where("game",$params['game']);
        }
        //游戏类型
        if (isset($params['game']) && is_array($params['game']))
        {
            $keyword_list = $keyword_list->whereIn("game", $params['game']);
        }
        //类型
        if(isset($params['type']) && strlen($params['type'])>0)
        {
            $types = explode(",",$params['type']);
            $keyword_list = $keyword_list->whereIn("content_type",$types);
        }
        //目标ID
        if(isset($params['content_id']) && ($params['content_id'])>0)
        {
            $keyword_list = $keyword_list->where("content_id",$params['content_id']);
        }
        //来源ID
        if(isset($params['ids']))
        {
            $ids = explode(",",$params['ids']);
            if(count($ids)==1)
            {
                $keyword_list = $keyword_list->where("keyword_id",$ids[0]);
            }
            else
            {
                $keyword_list = $keyword_list->whereIn("keyword_id",$ids);
            }
        }
        if(isset($params['expect_id']))
        {
            $keyword_list->where("content_id","!=",$params['expect_id']);
        }
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $keyword_list = $keyword_list
            //->limit($pageSizge)
            //->offset(($page-1)*$pageSizge)
            ->groupBy('content_id')
            ->orderBy("weight","desc")
            ->get()->toArray();
        return $keyword_list;
    }
    public function getCount($params)
    {
        $keyword_count =$this;
        //游戏类型
        if(isset($params['game']) && !is_array($params['game']) && strlen($params['game'])>=3)
        {
            $keyword_count = $keyword_count->where("game",$params['game']);
        }
        //游戏类型
        if (isset($params['game']) && is_array($params['game']))
        {
            $keyword_count = $keyword_count->whereIn("game", $params['game']);
        }
        //类型
        if(isset($params['type']) && strlen($params['type'])>0)
        {
            $types = explode(",",$params['type']);
            $keyword_count = $keyword_count->whereIn("content_type",$types);
        }
        //目标ID
        if(isset($params['content_id']) && ($params['content_id'])>0)
        {
            $keyword_count = $keyword_count->where("content_id",$params['content_id']);
        }
        if(isset($params['expect_id']))
        {
            $keyword_count = $keyword_count->where("content_id","!=",$params['expect_id']);
        }
        //来源ID
        if(isset($params['ids']))
        {
            $ids = explode(",",$params['ids']);
            if(count($ids)==1)
            {
                $keyword_count = $keyword_count->where("keyword_id",$ids[0]);
            }
            else
            {
                $keyword_count = $keyword_count->whereIn("keyword_id",$ids);
            }
        }
        $keyword_count = $keyword_count->count();
        return $keyword_count;
    }
}
