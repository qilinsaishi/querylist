<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ActionLogModel extends Model
{
    protected $table = "action_log";
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
    public function insertActionLog($data)
    {
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['add_time']))
        {
            $data['add_time'] = $currentTime;
        }
        return $this->insertGetId($data);
    }
    public function getActionLogCountByAction($user_id,$action_id,$start_date,$end_date)
    {
        $count = $this
            ->where("user_id",$user_id)->where("action_id",$action_id);
        if(strtotime($start_date)>0)
        {
            $count = $count->where("start_date",$start_date);
        }
        if(strtotime($end_date)>0)
        {
            $count = $count->where("end_date",$end_date);
        }
        $count = $count->count();
        return $count;
    }
}
