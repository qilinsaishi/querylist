<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ActionModel extends Model
{
    protected $table = "action";
    public $primaryKey = "action_id";
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
    public function getActionList($params=[])
    {
        $action_list =$this->select("*");
        $currentTime = date("Y-m-d H:i:s");
        $action_list = $action_list->where("end_time",">=",$currentTime);
        $action_list = $action_list
            ->orderBy("start_time")
            ->get()->toArray();
        return $action_list;
    }
}
