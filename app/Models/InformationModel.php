<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InformationModel extends Model
{
    protected $table = "information";
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
        "keywords_list","scws_list"
    ];
    protected $toAppend = [
    ];
    public function getInformationList($params)
    {
        $fields = $params['fields']??"id,title,author,author_id,logo,create_time,status";
        $information_list =$this->select(explode(",",$fields))->where("status",1);
        //最后更新时间
        if(isset($params['recent']))
        {
            $time = date("Y-m-d H:i:s",time()-$params['recent']);
            $information_list = $information_list->where("update_time", '>=',$time);
        }
        //是否需要处理关键字
        if(isset($params['keywords']))
        {
            $information_list = $information_list->where("keywords",$params['keywords']>0?1:0);
        }
        //是否需要处理关键字
        if(isset($params['scws']))
        {
            $information_list = $information_list->where("scws",$params['scws']>0?1:0);
        }
        //游戏类型
        if(isset($params['game']) && strlen($params['game'])>=3)
        {
            $information_list = $information_list->where("game",$params['game']);
        }
        $hot=$params['hot']??0;
        if($hot==1)
        {
            $information_list->where("hot",$hot);
        }
        if(isset($params['author_id']) && $params['author_id']>0)
        {
            $information_list->where("author_id",$params['author_id']);
        }
        if(isset($params['type']))
        {
            $types = explode(",",$params['type']);
            $information_list->whereIn("type",$types);
        }
        if(isset($params['ids']))
        {
            //$types = explode(",",$params['type']);
            $information_list->whereIn("id",$params['ids']);
        }
        if(isset($params['expect_id']))
        {
            $information_list->where("id","!=",$params['expect_id']);
        }
        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $information_list = $information_list
            ->limit($pageSizge)
            ->offset(($page-1)*$pageSizge)
            ->orderBy("id",
                "desc")
            ->get()->toArray();
        return $information_list;
    }
    public function getInformationBySiteId($id,$game,$source)
    {
        $information =$this->select("*")
                    ->where("site_id",$id)
                    ->where("game",$game)
                    ->where("source",$source)
                    ->get()->first();
        if(isset($information->id))
        {
            $information = $information->toArray();
        }
        else
        {
            $information = [];
        }
        return $information;
    }
    public function getInformationById($id,$fields = "*")
    {
        $information =$this->select($fields)
            ->where("id",$id)
            ->get()->first();
        if(isset($information->id))
        {
            $information = $information->toArray();
        }
        else
        {
            $information = [];
        }
        return $information;
    }
    public function insertInformation($data)
    {
        foreach($this->attributes as $key => $value)
        {
            if(!isset($data[$key]))
            {
                $data[$key] = $value;
            }

        }
        foreach($this->toJson as $key)
        {
            if(isset($data[$key]))
            {
                $data[$key] = json_encode($data[$key]);
            }
        }
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['create_time']))
        {
            $data['create_time'] = $currentTime;
        }
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->insertGetId($data);
    }

    public function updateInformation($id=0,$data=[])
    {
        foreach($this->toJson as $key)
        {
            if(isset($data[$key]))
            {
                $data[$key] = json_encode($data[$key]);
            }
        }
        $currentTime = date("Y-m-d H:i:s");
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->where('id',$id)->update($data);
    }

    public function saveInformation($game,$data)
    {
        echo "title:".$data['title']."\n";
        if($data['title'] == "")
        {
            echo "empty_title:\n";
            sleep(1);
            return false;
        }
        $data['title'] = preg_replace("/\s+/", "",$data['title']);
        $data['title'] = trim($data['title']);
        $currentInformation = $this->getInformationBySiteId($data['site_id'],$game,$data['source']);
        if(!isset($currentInformation['id']))
        {
            echo "toInsertInformation:\n";
            return  $this->insertInformation(array_merge($data,["game"=>$game]));
        }
        else
        {
            echo "toUpdateInformation:".$currentInformation['id']."\n";
            //校验原有数据
            foreach($data as $key => $value)
            {
                if(in_array($key,$this->toAppend))
                {
                    $t = json_decode($currentInformation[$key],true);

                    foreach($value as $k => $v)
                    {
                        if(!in_array($v,$t))
                        {
                            $t[] = $v;
                        }
                    }
                    $data[$key] = $t;
                }
                if(in_array($key,$this->toJson))
                {
                    $value = json_encode($value);
                }
                if(isset($currentInformation[$key]) && ($currentInformation[$key] == $value))
                {
                    //echo $currentInformation[$key]."-".$value."\n";
                    //echo $key.":passed\n";
                    unset($data[$key]);
                }
                else
                {
                    echo $key.":difference:\n";
                }
            }
            if(count($data))
            {
                return $this->updateInformation($currentInformation['id'],$data);
            }
            else
            {
                return true;
            }
        }
    }

    public function getInformationCount($params=[])
    {
        $information_count =$this;
        //是否需要处理关键字
        if(isset($params['keywords']))
        {
            $information_count = $information_count->where("keywords",$params['keywords']>0?1:0);
        }
        //是否需要处理关键字
        if(isset($params['scws']))
        {
            $information_count = $information_count->where("scws",$params['scws']>0?1:0);
        }
        //游戏类型
        if(isset($params['game']) && strlen($params['game'])>=3)
        {
            $information_count = $information_count->where("game",$params['game']);
        }
        $hot=$params['hot']??0;
        if($hot==1)
        {
            $information_count = $information_count->where("hot",$hot);
        }
        if(isset($params['author_id']) && $params['author_id']>0)
        {
            $information_count = $information_count->where("author_id",$params['author_id']);
        }
        if(isset($params['type']))
        {
            $types = explode(",",$params['type']);

            $information_count = $information_count->whereIn("type",$types);
        }
        if(isset($params['ids']))
        {
            //$types = explode(",",$params['type']);
            $information_count = $information_count->whereIn("id",$params['ids']);
        }
        if(isset($params['expect_id']))
        {
            $information_count = $information_count->where("id","!=",$params['expect_id']);
        }
        $information_count = $information_count->count();
        return $information_count;
    }
}
