<?php
function curl_get($url,$referer=''){

    $header = array(
        'Accept: application/json',
        //'Referer: '.$referer,        'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36'
    );
    if($referer){
        array_push($header,$referer);
    }
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    // 超时设置,以秒为单位
    curl_setopt($curl, CURLOPT_TIMEOUT, 1);

    // 超时设置，以毫秒为单位
    // curl_setopt($curl, CURLOPT_TIMEOUT_MS, 500);

    // 设置请求头
   // curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    //执行命令
    $data = curl_exec($curl);

    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $headers = substr($data, 0, $header_size);
    $body = substr($data, $header_size);

   dd($data);

    if(strpos($data,'if(!LOLsummonerjs)var LOLsummonerjs=') !==false){
        $data=str_replace('if(!LOLsummonerjs)var LOLsummonerjs=','',$data);
        $data=str_replace(';','',$data);
    }
    if(strpos($data,'callback(') !==false){
        $data=str_replace('callback(','',$data);
        $data=str_replace(');','',$data);
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
function curl_post( $url='', $postdata='' ) {
    $postdata=[
        'playerid'=>'15263',
        'gametype'=>'2',
        '_gtk'=>'1368290349'
    ];
    $url='https://www.wanplus.com/ajax/statelist/player';
    $header = array(
        'Accept: application/json',
        "x-requested-with:XMLHttpRequest",
        "x-csrf-token:1368290349",
    );
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    // 超时设置
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);

    // 超时设置，以毫秒为单位
    // curl_setopt($curl, CURLOPT_TIMEOUT_MS, 500);

    // 设置请求头
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE );
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE );

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
        $res=json_decode($data,true);
        curl_close($curl);
        return $res;
    }
}

//毫秒级时间戳
function msectime(){
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return intval($msectime);

}
//计算分页数
function getLastPage($a,$c){
    $n=1;
    if(is_float($a/$c)) //判断符点型
    {
        $n=$a/$c;
        $n++;
        return (int)($n); //强制转换整型
    }else{
        $n=$a/$c;

      return  ++$n;
    }

}
/**
 * 安全过滤函数
 *
 * @param $string
 * @return string
 */
function safe_replace($string) {
    $string = str_replace('%20','',$string);
    $string = str_replace('%27','',$string);
    $string = str_replace('%2527','',$string);
    $string = str_replace('*','',$string);
    $string = str_replace('"','&quot;',$string);
    $string = str_replace("'",'',$string);
    $string = str_replace('"','',$string);
    $string = str_replace(';','',$string);
    $string = str_replace('<','&lt;',$string);
    $string = str_replace('>','&gt;',$string);
    $string = str_replace("{",'',$string);
    $string = str_replace('}','',$string);
    $string = str_replace('\\','',$string);
    return $string;
}


function time33($a)
{
    $a = substr($a, 0, 7);

    for ($b = 0, $c = strlen($a), $d = 5381; $c > $b; ++$b) {

        $d += ($d << 5) + hexdec(bin2hex($a[$b]));
    }

    return 2147483647 & $d;
}

function getToken($wanplus_token){
    $token=time33($wanplus_token);

    return $token;
}

//从数组映射中整理数据
function getDataFromMapping($data_map,$dataArr)
{
    $return = [];
    foreach($data_map as $key => $map_info)
    {
        if($map_info['path']!="")
        {
            $value = getDataFromPath($map_info['path'],$dataArr,$map_info['default']);
            if(!$value)
            {
                $return[$key] = $map_info['default'];
            }
            else
            {
                $return[$key] = $value;
            }
        }
        else
        {
            $return[$key] = $map_info['default'];
        }
    }
    return $return;
}
//从字符串路径中提取数据
function getDataFromPath($path,$data,$default="")
{
    $t = explode(".",$path);
    foreach($t as $key)
    {
        if(isset($data[$key]))
        {
            $data = $data[$key];
        }
        else
        {
            $data = $default;
        }
    }
    return $data;
}
//数据去重 添加append或者新数据覆盖老数据rewrite
function uniqueData($array,$key_name,$operation = "append" )//rewrite
{
    //初始化空的结果
    $list = [];
    //获取key列表
    $keys = array_column($array,$key_name);
    //循环
    foreach($keys as $key => $name)
    {
        //一次把name分组索引
        if(!isset($list[$name]))
        {
            $list[$name] = [];
        }
        $list[$name][] = $key;
    }
    //如果分类后数量未减少（没有重复）
    if(count($list) == count($array))
    {

    }
    else
    {
        foreach($list as $name => $key_list)
        {
            //如果数量超过1（有重复）
            if(count($key_list)>1)
            {
                $count = count($key_list);
                foreach($key_list as $k2 => $v2)
                {
                    //添加模式
                    if($operation == "append")
                    {
                        //第一个不操作
                        if($k2 != 0)
                        {
                            //数组模式
                            if(is_array($array[$key_list[0]]))
                            {
                                $array[$key_list[0]] = array_merge($array[$key_list[0]],$array[$v2]);
                            }
                            else//字符串模式
                            {
                                $array[$key_list[0]] = "\n".$array[$v2];
                            }
                            unset($array[$v2]);
                        }

                    }
                    else
                    {
                        if(($k2+1) != $count)
                        {
                            unset($array[$v2]);
                        }
                    }
                }
            }
        }
    }
    return $array;
}
function cleanArr($arr = [],$toClean = [])
{
    foreach($arr as $key => $value)
    {
        if(in_array(trim($value),$toClean) || strlen(trim($value))<=2)
        {
            unset($arr[$key]);
        }
        else
        {
            //echo "value:".$value."\n";
        }
    }
    return array_values($arr);
}








