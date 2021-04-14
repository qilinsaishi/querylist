<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChangeLogsModel extends Model
{
    protected $table = "change_logs";
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

    //判断字段是否已经更改,如果有变动则不更新
    public function checkData($data_id = 0, $field, $type = 'team')
    {
        //获取change_log日志表最后一条数据
        $changeLog =$this->getChangeLog($field, $data_id, $type);
        if(!empty($changeLog))
        {
            //如果为不为空的情况下，返回false
            return false;
        }
        return true;
    }

    /**
     * @param $field //更新字段
     * @param $data_id //队员id
     * @param $type  //更新类型
     * @return mixed
     */
    public function getChangeLog($field, $data_id, $type)
    {
        return $this->where(['type' => $type, 'data_id' => $data_id, 'fields' => $field,'status'=>0])->orderBy('id', 'desc')->get()->first();
    }


}
