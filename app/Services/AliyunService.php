<?php

namespace App\Services;
use OSS\Core\OssException;
use OSS\OssClient;

class AliyunService
{
    //file文件名 root完整文件路径 type文件类型（子目录）
    public function upload2Oss($fileArr = [])
    {
        $bucket = config('aliyun.oss.bucket');
        $client = self::getOssClient();
        $returnArr = [];
        foreach($fileArr as $key => $file)
        {
            try {
                $res = $client->uploadFile($bucket, $file/*$object*/, $file/*$local_file*/);
                $returnArr[$key] = $res['info']['url']??"";
            }catch(\OSS\Core\OssException $e) {
                $returnArr[$key] = false;
            }
        }
        return $returnArr;
    }
    public function getOssClient()
    {
        try {
            $ossClient = new OssClient(
                config("aliyun.accessKey"),
                config("aliyun.secret"),
                config("aliyun.oss.endpoint"),
                 false);
        } catch (OssException $e) {
            //Log::Info(__FUNCTION__ . "creating OssClient instance: FAILED\n");
            //Log::Info($e->getMessage() . "\n");
            return null;
        }
        return $ossClient;
    }
}
