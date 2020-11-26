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
