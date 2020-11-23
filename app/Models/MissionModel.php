<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class MissionModel extends Model
{
    protected $table = "test_table";
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
    protected $casts = [
        'create_time' => 'datetime',
        'updat_time' => 'datetime',
    ];
}
