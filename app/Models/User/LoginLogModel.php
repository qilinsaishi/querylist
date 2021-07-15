<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LoginLogModel extends Model
{
    protected $table = "login_log";
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
    public function insertLoginLog($data)
    {
        $currentTime = time();
        $data['login_date'] = date("Y-m-d",$currentTime);
        $data['login_time'] = date("Y-m-d H:i:s",$currentTime);
        return $this->insertGetId($data);
    }
    public function getUserLoginDateCountByReference($reference_user_id,$start_date,$end_date)
    {
        $count = $this->selectRaw("user_id,count(distinct(login_date)) as date")
            ->where("reference_user_id",$reference_user_id);
        if(strtotime($start_date)>0)
        {
            $count = $count->where("login_date",">=",$start_date);
        }
        if(strtotime($end_date)>0)
        {
            $count = $count->where("login_date","<=",$end_date);
        }
        $count = $count->groupBy("user_id")->get()->toArray();
        return $count;
    }
}
