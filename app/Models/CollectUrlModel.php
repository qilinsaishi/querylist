<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Self_;

class CollectUrlModel extends Model
{
    protected $table = "collect_url";
    protected $connection = "query_list";
    public $timestamps = true;


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
    public function getDataFromUrl($game='kpl',$limt=10,$mission_type='team',$source='baidu_baike'){
        return $this->where(['game'=>$game,'mission_type'=>$mission_type,'source'=>$source])
            ->select('url','id','game','mission_type','source','title')
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
