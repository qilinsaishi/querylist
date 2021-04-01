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
        $rt_count = 0;
        $rt_count = $this->getCount($field, $data_id, $type);
        if ($rt_count > 0) {
            return false;
        }
        return $field;
    }

    public function getCount($field, $data_id, $type)
    {
        return $this->where(['type' => $type, 'data_id' => $data_id, 'fields' => $field])->count();

    }


}
