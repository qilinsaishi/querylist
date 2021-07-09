<?php

namespace App\Services;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
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
    public static function sms($phone = "18621758237",$type = "common",$params = ["code"=>123456])
    {
        //$params = ["code"=>rand(111111,999999)];
        $client = self::createSmsClient();
        $typeList = ["common"=>"SMS_218630389"];
        $sendSmsRequest = new SendSmsRequest([
            "signName" => "电竞人",
            "phoneNumbers" => $phone,
            "templateCode" => $typeList[$type]??$typeList["common"],
            "templateParam" => json_encode($params)
        ]);
        // 复制代码运行请自行打印 API 的返回值
        $return = $client->sendSms($sendSmsRequest);
        if($return->body->code=="OK")
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    public static function createSmsClient()
    {
        $config = new Config([
            // 您的AccessKey ID
            "accessKeyId" => config("aliyun.accessKey"),
            // 您的AccessKey Secret
            "accessKeySecret" => config("aliyun.secret")
        ]);
        // 访问的域名
        $config->endpoint = config("aliyun.sms.endpoint");
        return new Dysmsapi($config);
    }
}
