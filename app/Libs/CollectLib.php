<?php

namespace App\Libs;

class CollectLib
{
    //从数组映射中整理数据
    public function getDataFromMapping($data_map,$dataArr)
    {
        $return = [];
        foreach($data_map as $key => $map_info)
        {
            if($map_info['path']!="")
            {
                $value = self::getDataFromPath($map_info['path'],$dataArr,$map_info['default']);
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
    public function getDataFromPath($path,$data,$default="")
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
    public function uniqueData($array,$key_name,$operation = "append" )//rewrite
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
    public function cleanArr($arr = [],$toClean = [])
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
}
