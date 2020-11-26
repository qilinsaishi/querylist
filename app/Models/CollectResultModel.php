<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Self_;

class CollectResultModel extends Model
{
    protected $table = "collect_result";
    protected $connection = "query_list";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mission_id',
        'mission_type',
        'game',
        'source',
        'title',
        'source_link',
        'content',
        'status'
    ];
    public function getResult($count = 3,$game='',$source='',$status = 1)
    {
        $result_list =$this->select("*");
        //游戏
        if($game!="")
        {
            $result_list = $result_list->where("game",$game);
        }
        //爬取数据源
        if($source!="")
        {
            $result_list = $result_list->where("source",$source);
        }
        $result_list = $result_list
            ->where("status",$status)
            ->limit($count)
            ->get()->toArray();
        return $result_list;
    }
    //保存采集数据插入到数据库
    public function insertCollectResult($data){
        return $this->create($data);
    }
    //更新爬取结果的状态
    public function updateStatus($id,$data){
        return $this->where('id',$id)->update($data);
    }
    //




}
