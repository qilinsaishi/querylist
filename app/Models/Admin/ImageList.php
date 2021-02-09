<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class ImageList extends Model
{

    protected $connection = 'query_admin';
    protected $table = "kite_image_list";
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


    public function getImageList($params)
    {
        $game = $params['game'] ?? '';
        $cid=$params['cid'] ?? 0;
        $site_id=$params['site_id'] ?? 1;
        $flag=$params['flag'] ?? '';
        $default_image_list=[];
        $default_field = ['id', 'name','game', 'url','cid', 'logo','sort','station_time','status','flag','content'];
        $field = isset($params['field']) && !empty($params['field']) ? $params['field'] : $default_field;
        $default_image_list = $this->select($field)->where('status',1);;
        $pageSizge = $params['page_size'] ?? 3;
        $page = $params['page'] ?? 1;
        if(!empty($game)){
            $default_image_list->where('game',$game);
        }
        if($cid > 0){
            $default_image_list->where('cid',$cid);
        }
        if($site_id !='' && strlen($flag)>3 ){
            //$cid=ImageCategory::getIdByName($flag,$site_id);
            $default_image_list->where(['flag'=>$flag,'site_id'=>$site_id]);
        }
        $default_image_list = $default_image_list
            ->with('category')
            ->limit($pageSizge)
            ->offset(($page - 1) * $pageSizge)
            ->orderBy("sort")
            ->get()->toArray();
        if(!empty($default_image_list)){
            foreach ($default_image_list as &$val){
                $val['cate_name']=$val['category']['name'] ?? '';
                unset($val['category']);
            }
        }

        return $default_image_list;
    }
    public function category(){
        return $this->hasOne(ImageCategory::class,'id','cid');
    }

    public function getImageByName($name, $type)
    {
        $default_image_info = $this->select("*")
            ->where("name", $name)
            ->where("type", $type)
            ->get()->first();
        if (isset($default_image_info->id)) {
            $default_image_info = $default_image_info->toArray();
        } else {
            $default_image_info = [];
        }
        return $default_image_info;
    }

    public function getImageCount($params)
    {
        $image_count = $this;
        $game = $params['game'] ?? '';
        $cid=$params['cid'] ?? 0;
        $site_id=$params['site_id'] ?? 1;
        $flag=$params['flag'] ?? '';
        if(!empty($game)){
            $image_count = $image_count->where('game',$game);
        }
        if($cid > 0){
            $image_count = $image_count->where('cid',$cid);
        }
        if($site_id !='' && strlen($flag)>3 ){
           // $cid=ImageCategory::getIdByName($cname,$site_id);
            $image_count = $image_count->where(['flag'=>$flag,'site_id'=>$site_id]);
        }

        return $image_count->count();
        //return true;
    }

    public function getImageById($id)
    {

        $get_image_info = $this->select("*")
            ->where("id", $id)->first();
        if (isset($get_image_info->id)) {
            $get_image_info = $get_image_info->toArray();
        } else {
            $get_image_info = [];
        }
        return $get_image_info;
    }

    public function getImageByKey($keys = [])
    {
        $image_info = $this->select("*")
            ->whereIn("key", $keys)
            ->get()->first();
        if (isset($image_info->id)) {
            $image_info = $image_info->toArray();
        } else {
            $image_info = [];
        }
        return $image_info;
    }


}
