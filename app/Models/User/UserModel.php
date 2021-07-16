<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserModel extends Model
{
    protected $table = "user_info";
    public $primaryKey = "user_id";
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
    public function getUserById($user_id)
    {
        $user_info =$this->select("*")
            ->where("user_id",$user_id)
            ->get()->first();
        if(isset($user_info->user_id))
        {
            $user_info = $user_info->toArray();
        }
        else
        {
            $user_info = [];
        }
        return $user_info;
    }
    public function getUserByMobile($mobile)
    {
        $user_info =$this->select("*")
            ->where("mobile",$mobile)
            ->get()->first();
        if(isset($user_info->user_id))
        {
            $user_info = $user_info->toArray();
        }
        else
        {
            $user_info = [];
        }
        return $user_info;
    }
    public function getUserByNickName($nick_name)
    {
        $user_info =$this->select("*")
            ->where("nick_name",$nick_name)
            ->get()->first();
        if(isset($user_info->user_id))
        {
            $user_info = $user_info->toArray();
        }
        else
        {
            $user_info = [];
        }
        return $user_info;
    }
    public function getUserByReference($referenceCode)
    {
        $user_info =$this->select("*")
            ->where("reference_code",$referenceCode)
            ->get()->first();
        if(isset($user_info->user_id))
        {
            $user_info = $user_info->toArray();
        }
        else
        {
            $user_info = [];
        }
        return $user_info;
    }
    public function getUserCountByReference($user_id)
    {
        $user_count =$this->where("reference_user_id",$user_id)->count();
        return $user_count;
    }
    public function insertUser($data)
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
            $data['reg_time'] = $currentTime;
        }
        if(!isset($data['last_login_time']))
        {
            $data['last_login_time'] = $currentTime;
        }
        if(!isset($data['update_time']))
        {
            $data['update_time'] = $currentTime;
        }
        return $this->insertGetId($data);
    }

    public function updateUser($user_id=0,$data=[])
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
        return $this->where('user_id',$user_id)->update($data);
    }
    //金币金额处理
    public function coinModify($user_id,$type,$amount)
    {
        if($amount>0)
        {
            $update = $this->where("user_id",$user_id)->increment($type,$amount);
        }
        else
        {
            $update = $this->where("user_id",$user_id)->where($type,">=",-1*$amount)->increment($type,$amount);
        }
        return $update;
    }

    public function saveUser($user_id,$data)
    {
        $currentUser = $this->getUserById($data['user_id']);
        if(!isset($currentUser['user_id']))
        {
            $user_id = $this->insertUser($data);
            if($user_id)
            {
                return $this->getUserById($data['user_id']);
            }
            else
            {
                return false;
            }
        }
        else
        {
            //校验原有数据
            foreach($data as $key => $value)
            {
                if(isset($this->toAppend[$key]))
                {
                    $t = json_decode($currentUser[$key],true);
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
                if(isset($currentUser[$key]) && ($currentUser[$key] == $value))
                {
                    unset($data[$key]);
                }
                else
                {
                    echo $key.":difference:\n";
                }
            }
            if(count($data))
            {
                $update = $this->updateUser($currentUser['user_id'],$data);
            }
            return $this->getUserById($currentUser['user_id']);
        }
    }

}
