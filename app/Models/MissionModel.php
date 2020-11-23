<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MissionModel extends Model
{
    protected $table = "mission_list";
    protected $primaryKey = "mission_id";
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

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
  /*  protected $casts = [
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];*/

    public function getMissionByMachine($asign,$count = 3)
    {
        $mission_list =$this->select("*")->where("asign_to",$asign)
            ->limit($count)
            ->get()->toArray();
        return $mission_list;
    }
}
