<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AuthorModel extends Model
{
    protected $table = "author_info";
    protected $primaryKey = "author_id";
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
    public function getAuthorByName($author_name,$source)
    {
        $author =$this->select("*")
                    ->where("author",$author_name)
                    ->where("source",$source)
                    ->get()->first();
        if(isset($author->author_id))
        {
            $author = $author->toArray();
        }
        else
        {
            $author = [];
        }
        return $author;
    }
    public function getAuthorById($id)
    {
        $author =$this->select("*")
            ->where("author_id",$id)
            ->get()->first();
        if(isset($author->author_id))
        {
            $author = $author->toArray();
        }
        else
        {
            $author = [];
        }
        return $author;
    }
    public function insertAuthor($data)
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

    public function updateAuthor($id=0,$data=[])
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

    public function saveAuthor($auther_name,$source,$author_id)
    {
        if($author_id>0)
        {
            $currentAuthor = $this->getAuthorById($author_id);
            if(!isset($currentAuthor['author_id']))
            {
                $this->insertAuthor(['source'=>$source,'author'=>$auther_name,'author_id'=>$author_id]);

            }
            return $author_id;
        }
        $currentAuthor = $this->getAuthorByName($auther_name,$source);
        if(!isset($currentAuthor['author_id']))
        {
            echo "toInsertAuthor:\n";
            return  $this->insertAuthor(['source'=>$source,'author'=>$auther_name]);
        }
        else
        {
            return $currentAuthor['author_id'];
        }
    }

}
