<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

use QL\QueryList;

class BannedWordService extends Command
{
    function LoadFromFile()
    {
        $banned = [];
        $redis = app("redis.connection");
        $redis_key = "bannedwordList";
        $toGet = 0;
        if(!($redis->exists($redis_key)))
        {
            $toGet = 1;
        }
        else
        {
            $banned = json_decode($redis->get($redis_key),true);
            if(!is_array($banned))
            {
                $toGet = 1;
            }
            else
            {
                //echo "cached";
            }
        }
        if($toGet == 1)
        {
            $banned = [];
            $filePath = ROOT_PATH."/storage/banword";
            $fileList = $this->getFile($filePath);
            foreach($fileList as $key => $file)
            {
                //echo "process:".$file."\n";
                $fp = fopen($file, "r");
                while(! feof($fp))
                {
                    $string = trim(fgets($fp),"\n");
                    $banned[] = $string;//fgets()函数从文件指针中读取一行
                }
                fclose($fp);
            }
            $redis->set($redis_key,json_encode($banned));
            $redis->expire($redis_key,86400);
        }
        return $banned;
        //$checkResult = $this->sensitive($banned,"胡锦涛");
    }
    function getFile($dir) {
        $fileArray[]=NULL;
        if (false != ($handle = opendir ( $dir ))) {
            $i=0;
            while ( false !== ($file = readdir ( $handle )) ) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if ($file != "." && $file != ".."&&strpos($file,".")) {
                    $fileArray[$i]= $dir."/".$file;
                    if($i==100){
                        break;
                    }
                    $i++;
                }
            }
            //关闭句柄
            closedir ( $handle );
        }
        return $fileArray;
    }
    public function sensitive($string)
    {
        $list = $this->LoadFromFile();
        $count = 0; //违规词的个数
        $sensitiveWord = ''; //违规词
        $stringAfter = $string; //替换后的内容
        $pattern = "/".implode("|",$list)."/i"; //定义正则表达式
        if(preg_match_all($pattern, $string, $matches)){ //匹配到了结果
            $patternList = $matches[0]; //匹配到的数组
            foreach($patternList as $key => $txt)
            {
                if($txt=="")
                {
                    unset($patternList[$key]);
                }
            }
            $count = count($patternList);
            $sensitiveWord = implode(',', $patternList); //敏感词数组转字符串
            $replaceArray = array_combine($patternList,array_fill(0,count($patternList),'***')); //把匹配到的数组进行合并，替换使用
            $stringAfter = strtr($string, $replaceArray); //结果替换
        }
        $log = "原句为 [ {$string} ]<br/>";
        if($count==0){
            $log .= "暂未匹配到敏感词！";
        }else{
            $log .= "匹配到 [ {$count} ]个敏感词：[ {$sensitiveWord} ]<br/>".
                "替换后为：[ {$stringAfter} ]";
        }
        return ['string'=>$stringAfter,'result'=>$count>0?0:1];
    }
}
