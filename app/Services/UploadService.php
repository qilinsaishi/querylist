<?php

namespace App\Services;

use App\Helpers\Jwt;
use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use Illuminate\Support\Facades\Redis;
use phpDocumentor\Reflection\Element;
use QL\QueryList;

class UploadService
{
    public function __construct()
    {
        $this->user_redis = Redis::connection('user_redis');
        $this->allowedFileExt = [
            "image"=>["gif","jpg","jpeg","bmp","png"],
            "video"=>["mp4","avi"]
        ];
        $this->allowedFileSize = [
            "image"=>[100,9999999],
            "video"=>[1000,88888888]
        ];
    }
    //检查手机号码是否已经注册
    public function upload($file,$type = "image")
    {
        $ext = $file->getClientOriginalExtension();
        $allowedFileExt = $this->allowedFileExt[$type];
        if (!in_array($file->getClientOriginalExtension(), $allowedFileExt))
        {
            $return = ['result'=>0,"msg"=>"文件类型错误,只允许".implode(",",$allowedFileExt)."类型的文件"];
        }
        else
        {
            $allowedFileSize = $this->allowedFileSize[$type];
            $fileSize = $file->getSize();
            if($fileSize>$allowedFileSize['1'] || $fileSize<$allowedFileSize['0'])
            {
                $return = ['result'=>0,"msg"=>"文件尺寸错误错误,只允许".implode("～",$allowedFileSize)."尺寸的文件"];
            }
            else
            {
                $destinationPath = '../storage/uploads/'; //public 文件夹下面建 storage/uploads 文件夹
                $fileName=md5(time().rand(1,1000)).'.'.$ext;
                $move = $file->move($destinationPath,$fileName);
                $new_name = md5_file($destinationPath.$fileName) .".". $ext;
                rename($destinationPath.$fileName, $destinationPath.$new_name);
                $upload = (new AliyunService())->upload2Oss([$destinationPath.$new_name]);
                if(isset($upload['0']) && strlen($upload['0'])>10)
                {
                    $return = ['result'=>1,"url"=>$upload['0']];
                }
                else
                {
                    $return = ['result'=>1,"msg"=>"上传失败"];
                }
            }
        }
        return $return;
    }
}
