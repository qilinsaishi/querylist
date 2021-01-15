<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Self_;

class ImageCategory extends Model
{

    protected $connection = 'query_admin';
    protected $table = "kite_image_category";
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

    static public function getIdByName($name,$site_id){
        $obj=self::where(['name'=>$name,'site_id'=>$site_id])->first();
        $id=$obj['id'] ?? '';
        return $id;

    }







}
