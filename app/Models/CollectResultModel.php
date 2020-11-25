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
    /*const TYPE_WZRY               = 1; //王者荣耀
    const TYPE_YXLM               =2; //英雄联盟
    const TYPE_FKJY               =3; //反恐精英
    const TYPE_DOTA              =4; //dota*/

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * @param string $type kpl,lol,csgo,dota2
     * @param int $limt
     * @return mixed
     */
    public function getDataFromUrl($type='kpl',$limt=1){
        return $this->where(['game'=>$type,'mission_id'=>0])
            ->select('source_link','id')
            ->orderBy('id', 'ASC')
            ->limit($limt)
            ->get()
            ->toArray();
    }
    //保存采集数据批量插入到数据库
    public function insertAll($data){
        return DB::table('team_collect')->insert($data);
    }
    //更新数据
    public function updateStatus($id,$data){
        return $this->where('id',$id)->update($data);
    }
    //




}
