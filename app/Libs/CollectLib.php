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
}
