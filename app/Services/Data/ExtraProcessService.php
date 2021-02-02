<?php

namespace App\Services\Data;

class ExtraProcessService
{
    //获取各个数据类型的缓存数据
    public function getConfig()
    {
        $config = [
            "informationList" => [
                'functionList'=>["processInvisiable"]
            ],



        ];
        return $config;
    }
    //尝试从缓存获取
    public function process($dataType,$data)
    {
        $config = $this->getConfig();
        if(isset($config[$dataType]))
        {
            $functionName = $config[$dataType]['functionList'][0];
            $data = self::$functionName($data);
            return  $data;
        }
        else
        {
            return false;
        }
    }

    static function processInvisiable($data)
    {
        if(!empty($data)){
            foreach ($data as $key=>$val){
                if(isset($val['status']) && $val['status']==2) {
                    unset($data[$key]);
                }
            }
        }
        $data=$data ?? [];
        return $data;

    }


}
