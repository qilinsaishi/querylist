<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreditLogModel extends Model
{
    protected $table = "credit_log";
    public $primaryKey = "log_id";
    public $timestamps = false;
    protected $connection = "user";

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
    public $toJson = [
    ];
    public $toAppend = [
    ];
    protected $keep = [
    ];
    public function getCreditLog($params)
    {
        $fields = $params['fields']??"log_id,type,credit,add_time,content";
        $fields = explode(",",$fields);
        if($fields!=["*"] && !in_array("log_id",$fields))
        {
            $fields[] = "log_id";
        }
        $credit_log =$this->select($fields);
        //用户ID
        if(isset($params['user_id']) && intval($params['user_id'])>0)
        {
            $credit_log = $credit_log->where("user_id",$params['user_id']);
        }
        //积分类型
        if(isset($params['type']) && intval($params['type'])>0)
        {
            $credit_log = $credit_log->where("type",$params['type']);
        }
        $pageSizge = $params['page_size']??10;
        $page = $params['page']??1;
        $credit_log = $credit_log
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("log_id","desc")
            ->get()->toArray();
        foreach ($credit_log as &$val)
        {
            if(isset($val['content']))
            {
                $val['content']=json_decode($val['content'],true);
            }
        }
        return $credit_log;
    }
    public function getCreditCount($params)
    {
        $credit_count =$this;
        //用户ID
        if(isset($params['user_id']) && intval($params['user_id'])>0)
        {
            $credit_count = $credit_count->where("user_id",$params['user_id']);
        }
        //积分类型
        if(isset($params['type']) && intval($params['type'])>0)
        {
            $credit_count = $credit_count->where("type",$params['type']);
        }
        $credit_count = $credit_count
            ->count();
        return $credit_count;
    }
    //新增积分变更记录
    public function insertCreditLog($data)
    {
        $currentTime = time();
        $data['credit'] = intval($data['credit']);
        if(!isset($data['add_time']))
        {
            $data['add_time'] = date("Y-m-d H:i:s",$currentTime);
            $data['add_date'] = date("Y-m-d",$currentTime);
        }
        return $this->insertGetId($data);
    }
    //获取用户在指定范围内的消费汇总
    public function getSumAmountByUser($user_id,$start_date,$end_date)
    {
        $sum = $this->selectRaw("sum(credit) as credit,count(1) as count,type")
            ->where("user_id",$user_id);
        if(strtotime($start_date)>0)
        {
            $sum = $sum->where("add_date",">=",$start_date);
        }
        if(strtotime($end_date)>0)
        {
            $sum = $sum->where("add_date","<=",$end_date);
        }
        $sum = $sum->groupBy(["type"])->get()->toArray();
        return $sum;
    }

}
