<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CoinLogModel extends Model
{
    protected $table = "coin_log";
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
    public function insertCoinLog($data)
    {
        $currentTime = date("Y-m-d H:i:s");
        $data['coin'] = intval($data['coin']);
        if(!isset($data['add_time']))
        {
            $data['add_time'] = $currentTime;
        }
        return $this->insertGetId($data);
    }
}
