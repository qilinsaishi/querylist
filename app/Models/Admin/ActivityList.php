<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class ActivityList extends Model
{

    protected $connection = 'query_admin';
    protected $table = "kite_active_list";
    protected $primaryKey = "id";
    public $timestamps = false;

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


    public function getActivityList($params)
    {
        $activityList=[];
        $default_field = ['id', 'title', 'url', 'logo','site_id','description','game','start_time','end_time'];
        $field = isset($params['field']) && !empty($params['field']) ? $params['field'] : $default_field;
        $activityList = $this->select($field);
        $pageSizge = $params['page_size'] ?? 10;
        $page = $params['page'] ?? 1;

        if(isset($params['site_id']) && $params['site_id']>0){
            $activityList->where('site_id',$params['site_id']);
        }
        $activityList = $activityList
            ->limit($pageSizge)
            ->offset(($page - 1) * $pageSizge)
            ->orderBy("create_at","desc")
            ->get()->toArray();

        return $activityList;
    }



    public function getActivityCount($params)
    {
        $activity_list_count = $this;
        $game = $params['game'] ?? '';
        $site_id = $params['site_id'] ?? 0;
        if(!empty($game)){
            $activity_list_count->where('game',$game);
        }
        if(isset($params['site_id']) && $params['site_id']>0){
            $activity_list_count->where('site_id',$params['site_id']);
        }
        return $activity_list_count->count();
        //return true;
    }

    public function getActivityById($id)
    {

        $get_active_list_info = $this->select("*")
            ->where("id", $id)->first();
        if (isset($get_active_list_info->id)) {
            $get_active_list_info = $get_active_list_info->toArray();
        } else {
            $get_active_list_info = [];
        }
        return $get_active_list_info;
    }


}
