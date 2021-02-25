<?php

namespace App\Services;

use App\Models\Admin\Site;
use function PHPUnit\Framework\fileExists;

class LogService
{
    //切分日志
    public function cutLog($type="daily")
    {
        $logsRoot = config("app.logs_root");
        $siteList = (new Site())->getSiteList(['field'=>["id","domain"]]);
        foreach($siteList as $site)
        {
            $site['domain'] = str_replace("http://","",$site['domain']);
            $site['domain'] = str_replace("https://","",$site['domain']);
            $logName = $logsRoot.$site['domain'].".log";
            $fileExists = file_exists($logName);
            if($fileExists)
            {
                $fh = @fopen($logName, 'r');
                switch ($type){
                    case "daily":
                        //22/Feb/2021
                        $match = date("d/M/Y",time()+8*3600-86400);
                        $file = $logsRoot.$site['domain'].".".date('Y-m-d',time()+8*3600-86400).".log";
                        break;
                }
                $logs = [];
                if ($fh) {
                    while (! feof($fh)) {
                        $row = fgets($fh, 4096);
                        if (strpos($row,$match) !== false)
                        {
                            $logs[] = $row;
                        }
                    }
                }
                echo "file:".$file."\n";
                $myfile = fopen($file, "w") or die("Unable to open file!");
                $txt = implode("\n",$logs);
                fwrite($myfile, $txt);
                fclose($myfile);
                echo count($logs)." lines written\n";
//                print_R($logs);
                fclose($fh);
            }
        }
    }
}
