<?php

namespace App\Services\Data;

class RedisService
{
    //获取各个数据类型的缓存数据
    public function getCacheConfig()
    {
        $cacheConfig = [
            "matchList" => [
                'prefix'=>"matchList",
                'expire'=>3600,
            ],

            "teamList" => [//团队列表
                'prefix'=>"teamList",
                'expire'=>3600,
            ],

            "defaultConfig" => [//通用配置
                'prefix'=>"defaultConfig",
                'expire'=>86400,
            ],

            "links" => [//友链
                'prefix'=>"links",
                'expire'=>86400,
            ],

            "information" => [//资讯
                'prefix'=>"info",
                'expire'=>30,
            ],

            "totalPlayerList" => [//队员总表
                'prefix'=>"totalPlayerList",
                'expire'=>60,
            ],

            "totalTeamList" => [//队员总表
                'prefix'=>"totalTeamList",
                'expire'=>60,
            ],

        ];
        return $cacheConfig;
    }
    //尝试从缓存获取
    public function processCache($dataType,$params)
    {
        $cacheConfig = $this->getCacheConfig();
        if(isset($cacheConfig[$dataType]))
        {
            $redis = app("redis.connection");
            ksort($params);
            $keyConfig = $cacheConfig[$dataType]['prefix']."_".md5(json_encode($params));
            $exists = $redis->exists($keyConfig);
            if($exists)
            {
                $data = json_decode($redis->get($keyConfig),true);
                if(is_array($data))
                {
                    //echo "key:".$keyConfig."exists\n";
                    return $data;
                }
                else
                {
                    return false;
                }
            }
            return false;
        }
        else
        {
            return false;
        }
    }
    //保存到缓存
    public function saveCache($dataType,$params,$data)
    {
        $cacheConfig = $this->getCacheConfig();
        if(isset($cacheConfig[$dataType]))
        {
            $redis = app("redis.connection");
            ksort($params);
            $keyConfig = $cacheConfig[$dataType]['prefix']."_".md5(json_encode($params));
            //有数据原样缓存，没数据缓存时间减少为1/10
            $expire = $cacheConfig[$dataType]['expire'] * (count($data['data'])>0?1:0.5) + rand(1,100);
            $redis->set($keyConfig,json_encode($data));
            $redis->expire($keyConfig,$expire);
            //echo "key:".$keyConfig."saved to expire:".$expire."\n";
            return true;
        }
        else
        {
            return true;
        }
    }
}
