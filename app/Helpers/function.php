<?php

use App\Services\AliyunService;

function curl_get($url, $referer = '')
{

    $header = array(
        'Accept: application/json',
        //'Referer: '.$referer,
    );
    if ($referer) {
        array_push($header, $referer);
    }
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    // 超时设置,以秒为单位
    curl_setopt($curl, CURLOPT_TIMEOUT, 1);

    // 超时设置，以毫秒为单位
   curl_setopt($curl, CURLOPT_TIMEOUT_MS, 5000);

    // 设置请求头
     curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    //执行命令
    $data = curl_exec($curl);

    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $headers = substr($data, 0, $header_size);
    $body = substr($data, $header_size);

    if(strpos($data,'if(!LOLsummonerjs)var LOLsummonerjs=') !==false){
        $data=str_replace('if(!LOLsummonerjs)var LOLsummonerjs=','',$data);
        $data=str_replace(';','',$data);
    }
    if(strpos($data,'callback(') !==false){
        $data=str_replace('callback(','',$data);
        $data=str_replace(');','',$data);
    }
    if(strpos($data,'searchObj=') !==false){
        $data=str_replace('var searchObj=','',$data);
        $data=str_replace(';','',$data);
    }
    if(strpos($data,'newbee_hero_list_') !==false){
        $data=str_replace('newbee_hero_list_','',$data);
        $data=str_replace(')','',$data);
    }
    if(strpos($data,'web_hero_list_v3') !==false){
        $data=str_replace('web_hero_list_v3(','',$data);
        $data=str_replace(')','',$data);
    }



        // 显示错误信息
    if (curl_error($curl)) {
        print "Error: " . curl_error($curl);
    } else {
        // 打印返回的内容

        $res=json_decode($data,true);
        curl_close($curl);

        return $res;

    }
}


// $url 是请求的链接
// $postdata 是传输的数据，数组格式
function curl_post($url = '', $postdata = '',$header = [])
{

    //初始化
    $curl = curl_init();
    if(count($header)>0)
    {
        $headers = array();
        foreach($header as  $value)
        {
            array_push($headers,$value);
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    // 超时设置
    curl_setopt($curl, CURLOPT_TIMEOUT, 15000);

    // 超时设置，以毫秒为单位
    // curl_setopt($curl, CURLOPT_TIMEOUT_MS, 500);

    // 设置请求头
   // curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

    //设置post方式提交
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
    //执行命令
    $data = curl_exec($curl);

    // 显示错误信息
    if (curl_error($curl)) {
        print "Error: " . curl_error($curl);
    } else {
        // 打印返回的内容
        $res = json_decode($data, true);
        curl_close($curl);
        return $res;
    }
}

//毫秒级时间戳
function msectime()
{
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return intval($msectime);

}

//计算分页数
function getLastPage($a, $c)
{
    $n = 1;
    if (is_float($a / $c)) //判断符点型
    {
        $n = $a / $c;
        $n++;
        return (int)($n); //强制转换整型
    } else {
        $n = $a / $c;

        return ++$n;
    }

}


function time33($a)
{
    $a = substr($a, 0, 7);

    for ($b = 0, $c = strlen($a), $d = 5381; $c > $b; ++$b) {

        $d += ($d << 5) + hexdec(bin2hex($a[$b]));
    }

    return 2147483647 & $d;
}

function getToken($wanplus_token)
{
    $token = time33($wanplus_token);

    return $token;
}

//从数组映射中整理数据
function getDataFromMapping($data_map, $dataArr)
{
    $return = [];
    foreach ($data_map as $key => $map_info) {
        if ($map_info['path'] != "") {
            $value = getDataFromPath($map_info['path'], $dataArr, $map_info['default']);
            if (!$value) {
                $return[$key] = $map_info['default'];
            } else {
                $return[$key] = $value;
            }
        } else {
            $return[$key] = $map_info['default'];
        }
    }
    return $return;
}

//从字符串路径中提取数据
function getDataFromPath($path, $data, $default = "")
{
    $t = explode(".", $path);
    foreach ($t as $key) {
        if (isset($data[$key])) {
            $data = $data[$key];
        } else {
            $data = $default;
        }
    }
    return $data;
}

//数据去重 添加append或者新数据覆盖老数据rewrite
function uniqueData($array, $key_name, $operation = "append")//rewrite
{
    //初始化空的结果
    $list = [];
    //获取key列表
    $keys = array_column($array, $key_name);
    //循环
    foreach ($keys as $key => $name) {
        //一次把name分组索引
        if (!isset($list[$name])) {
            $list[$name] = [];
        }
        $list[$name][] = $key;
    }
    //如果分类后数量未减少（没有重复）
    if (count($list) == count($array)) {

    } else {
        foreach ($list as $name => $key_list) {
            //如果数量超过1（有重复）
            if (count($key_list) > 1) {
                $count = count($key_list);
                foreach ($key_list as $k2 => $v2) {
                    //添加模式
                    if ($operation == "append") {
                        //第一个不操作
                        if ($k2 != 0) {
                            //数组模式
                            if (is_array($array[$key_list[0]])) {
                                $array[$key_list[0]] = array_merge($array[$key_list[0]], $array[$v2]);
                            } else//字符串模式
                            {
                                $array[$key_list[0]] = "\n" . $array[$v2];
                            }
                            unset($array[$v2]);
                        }

                    } else {
                        if (($k2 + 1) != $count) {
                            unset($array[$v2]);
                        }
                    }
                }
            }
        }
    }
    return $array;
}

function cleanArr($arr = [], $toClean = [])
{
    foreach ($arr as $key => $value) {
        if (in_array(trim($value), $toClean) || strlen(trim($value)) <= 2) {
            unset($arr[$key]);
        } else {
            //echo "value:".$value."\n";
        }
    }
    return array_values($arr);
}

//去除换行和特殊符号
function delZzts($value, $replace = '')
{
    $value = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", " ", strip_tags($value));
    if (!empty($replace)) {
        $value = preg_replace('/\[\d+\]/', '', trim(str_replace($replace, '', $value)));
    }
    return $value;

}

/**
 * 随机产生六位数
 *
 * @param int $len
 * @param string $format
 * @return string
 */
if (!function_exists('randStr')) {
    function randStr($len = 6, $format = 'ALL')
    {
        switch ($format) {
            case 'ALL':
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
                break;
            case 'CHAR':
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-@#~';
                break;
            case 'NUMBER':
                $chars = '0123456789';
                break;
            default :
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                break;
        }
        //mt_srand((double)microtime() * 1000000 * getmypid());
        $password = "";
        while (strlen($password) < $len)
            $password .= substr($chars, (mt_rand() % strlen($chars)), 1);
        return $password;
    }
}
//获取远程图片，并以文件的hash作为文件名保存
function getImage($url, $save_dir = 'storage/downloads', $filename = '', $type = 1)
{
    $f_name = $filename;
    if(substr($url,0,2)=='//')
    {
        if(substr_count($url,'shp.qpic.cn')>0)
        {
            $url = 'https:'.$url;
        }
        else
        {
            $url = 'http:'.$url;
        }
    }
    elseif(substr($url,0,5)==':http')
    {
        $url = substr($url,1);
    }
    elseif(substr($url,0,18)=='http://shp.qpic.cn')
    {
        $url = str_replace('http://','https://',$url);
    }
    elseif(substr($url,0,4)!='http')
    {
        $url = 'http://'.$url;
    }
    $url = explode('?',$url);
    $url = $url['0'];
    if(substr($url,-1)=='/')
    {
        $url = substr($url,0,strlen($url)-1);
    }
    $start_time = microtime(true);
    $redis = app("redis.connection");
    $fileKey = "file_get_" . $url;
    $currentFile = $redis->get($fileKey);
    echo "url:".$url."\n";
    echo "cache:".$currentFile."\n";
    if ($currentFile && strlen($currentFile) > 5) {
        return $currentFile;
    }
    if (trim($url) == '') {
        return $url;
    }
    if (trim($save_dir) == '') {
        $save_dir = './';
    }
    if (trim($filename) == '') {//保存文件名
        $ext = strrchr($url, '.');
        $length = strlen($ext);
        if ($length > 6)//拆不开有效的扩展明，如无扩展名
        {
            $filename = substr(strrchr($url, '/') . '.jpg', 1);
            $ext = '.jpg';
        } else {
            if (!in_array($ext, ['.gif','.jpg','.png'])) {
                return $url;
            } else {
                $filename = substr(strrchr($url, '/'), 1);
            }
        }
    }
    if (0 !== strrpos($save_dir, '/')) {
        $save_dir .= '/';
    }
    //创建保存目录
    if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
        return $url;
    }
    try{
        //获取远程文件所采用的方法
        if ($type) {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $user_agent = 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Mobile Safari/537.36';
            curl_setopt($ch, CURLOPT_USERAGENT, $user_agent); // 模拟用户使用的浏览器
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            $img = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start();
            readfile($url);
            $img = ob_get_contents();
            ob_end_clean();
        }
        //文件大小
        $fp2 = @fopen($save_dir . $filename, 'a');
        fwrite($fp2, $img);
        fclose($fp2);
        {
            //生成文件内容hash作为文件名
            $new_name = md5_file($save_dir . $filename) . $ext;
            rename($save_dir . $filename, $save_dir . $new_name);
            //存储到redis,一天内不再重新获取
            //$redis->set($fileKey, $save_dir . $new_name);
            //$redis->expire($fileKey, 86400);
        }
        unset($img/*, $url*/);
        $root =  $save_dir . $new_name;
        $fileSize = filesize($root);
        if($fileSize<=100)
        {
           // echo "get img FileSize error:".$url."\n";
          //  echo "process time:".(microtime(true)-$start_time)."\n";

            echo "get img FileSize error:".$url."\n";
            echo "process time:".(microtime(true)-$start_time)."\n";
            if($type==1)
            {
                return getImage($url, $save_dir, $f_name, 2);
            }
            return $url;
        }
        $upload = (new AliyunService())->upload2Oss([$root]);
        //存储到redis,一天内不再重新获取
        if(strlen($upload[0])>10)
        {
            $redis->set($fileKey, $upload[0]);
            $redis->expire($fileKey, 86400);
        }
        echo "process time:".(microtime(true)-$start_time)."\n";
        echo "result:".$upload[0]."\n";
        return $upload[0];
    }
    catch (\Exception $e)
    {
        echo "get img error:".$url."\n";
        echo "process time:".(microtime(true)-$start_time)."\n";
        return $url;
    }

}
    //判断url是否正常打开
    function httpcode($url){
        $ch = curl_init();
        $timeout = 3;
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_exec($ch);
        return $httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
    }
    function getAttrFromHTML($html,$attr_name)
    {
        $t = explode($attr_name,$html);
        $t2 = explode('"',$t[1]);
        return $t2[1];
    }
    function string_split($str,$split_length=1,$charset="UTF-8")
    {
        if (func_num_args() == 1) {
            return preg_split('/(?<!^)(?!$)/u', $str);
        }
        if ($split_length < 1) return false;
        $len = mb_strlen($str, $charset);
        return mb_substr($str,0,$split_length,$charset);
    }
    function sort_split_array($array = [],$sort = "count",$count = 5)
    {
        if(count($array)<=$count)
        {
            return $array;
        }
        else
        {
            array_multisort(array_column($array,$sort),SORT_DESC,$array);
            $array = array_slice($array,0,$count);
            return $array;
        }
    }
    function getFieldsFromArray($array=[],$fields="*")
    {
        if($fields!="*")
        {
            $fieldsList = explode(",",$fields);
            $keyList = array_keys($array);
            foreach($keyList as $key)
            {
                if(!in_array($key,$fieldsList))
                {
                    unset($array[$key]);
                }
            }
        }
        return $array;
    }
    //从数组中拆解名称
    function getNames($data,$keys=['team_name','cn_name','en_name'],$toJson=["aka"])
    {
        $return = [];
        foreach($keys as $key => $value)
        {
            $value=generateNameHash($data[$value]);
            if($value!="")
            {
                $return[] = $value;
            }
        }
        foreach($toJson  as $key)
        {
            $value=is_array($data[$key])?$data[$key]:json_decode($data[$key],true);
            foreach($value??[] as $value_detail)
            {
                $value_detail = generateNameHash($value_detail);
                if($value_detail!="")
                {
                    $return[] = $value_detail;
                }
            }
        }
        $return = array_unique($return);
        return $return;
    }
    function generateNameHash($name = "")
    {
        $name = strtolower($name);
        $name = trim($name);
        $replaceList = [" ","."];
        foreach($replaceList as $key)
        {
            $name = str_replace($key,"",$name);
        }
        $name = removeEmoji($name);
        //echo "hash:".$name."\n";
        return $name;
    }
    function removeEmoji($text) {
        $clean_text = "";
        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $text);
        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);
        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);
        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);
        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);
        return $clean_text;
    }








