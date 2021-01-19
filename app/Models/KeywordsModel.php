<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KeywordsModel extends Model
{
    protected $table = "keywords_list";
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

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $attributes = [

    ];
    protected $toJson = [
    ];
    protected $toAppend = [
    ];
    protected $keep = [
    ];
    public function getKeywordsList($params)
    {
        $fields = $params['fields']??"id,word,url";
        $keywords_list =$this->select(explode(",",$fields));
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        if(isset($params['ids']))
        {
            $keywords_list = $keywords_list->whereIn("id",$params['ids']);
        }
        $keywords_list = $keywords_list
                ->limit($pageSizge)
                ->offset(($page-1)*$pageSizge)
                ->orderBy("id")
                ->get()->toArray();
        return $keywords_list;
    }
    public function getKeywordsCount($params=[])
    {
        $keywords_count =$this;
        if(isset($params['ids']))
        {
            $keywords_count = $keywords_count->whereIn("id",$params['ids']);
        }
        return $keywords_count->count();
    }
    public function getAllKeywords()
    {
        $keywords = [];
        $keywordsList = $this->getKeywordsList(["page_size"=>10000]);
        foreach($keywordsList as $keywords_info)
        {
            $keywords[trim($keywords_info['word'])] = $keywords_info['id'];
        }
        return $keywords;
    }
}
