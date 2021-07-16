<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NameLogModel extends Model
{
    protected $table = "name_log";
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
    //新建改名记录
    public function insertNameLog($data)
    {
        $data['update_time'] = date("Y-m-d H:i:s");
        print_R($data);
        return $this->insertGetId($data);
    }
    //获取用户的改名记录
    public function getNameLogCountByUser($user_id)
    {
        echo "user_id:".$user_id;
        die();
        $count = $this->where("user_id",$user_id)->count();;
        return $count;
    }
}
