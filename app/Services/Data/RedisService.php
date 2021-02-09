<?php

namespace App\Services\Data;

class RedisService
{
    //获取各个数据类型的缓存数据
    public function getCacheConfig()
    {
        $cacheConfig = [
            "matchList" => [
                'prefix' => "matchList",
                'expire' => 3600,
            ],
            "teamList" => [//团队列表
                'prefix' => "teamList",
                'expire' => 3600,
            ],
            "defaultConfig" => [//通用配置
                'prefix' => "defaultConfig",
                'expire' => 86400,
            ],
            "imageList" => [//图片列表
                'prefix' => "imageList",
                'expire' => 86400,
            ],
            "links" => [//友链
                'prefix' => "links",
                'expire' => 86400,
            ],
            "information" => [//资讯
                'prefix'=>"info",
                'expire'=>86400,
            ],
            "totalPlayerList" => [//队员总表
                'prefix' => "totalPlayerList",
                'expire' => 60,
            ],

            "totalTeamList" => [//队员总表
                'prefix' => "totalTeamList",
                'expire' => 60,
            ],

        ];
        return $cacheConfig;
    }

    //尝试从缓存获取
    public function processCache($dataType, $params)
    {
        $cacheConfig = $this->getCacheConfig();
        if (isset($cacheConfig[$dataType])) {
            $redis = app("redis.connection");
            ksort($params);
//如果接口指定了缓存时间
            if(isset($params['cache_time']))
            {
                $expire = $params['cache_time'];
            }
            else
            {
                $expire =  $cacheConfig[$dataType]['expire'];
            }
            //如果指定缓存时间为非正整数，跳出，不保存
            if($expire<=0)
            {
                return false;
            }
            $keyConfig = $cacheConfig[$dataType]['prefix'] . "_" . md5(json_encode($params));
            $exists = $redis->exists($keyConfig);
            if ($exists) {
                $data = json_decode($redis->get($keyConfig), true);
                if (is_array($data)) {
                    if (isset($data['data'])) {
                        $data = $data['data'];
                    }
                    //echo "key:".$keyConfig."exists\n";
                    return $data;
                } else {
                    return false;
                }
            }
            return false;
        } else {
            return false;
        }
    }

    //保存到缓存
    public function saveCache($dataType, $params, $data)
    {
        $cacheConfig = $this->getCacheConfig();
        if (isset($cacheConfig[$dataType])) {
            $redis = app("redis.connection");
            ksort($params);
            $keyConfig = $cacheConfig[$dataType]['prefix'] . "_" . md5(json_encode($params));
            //如果接口指定了缓存时间
            if(isset($params['cache_time']))
            {
                $expire = $params['cache_time'];
            }
            else
            {
                $expire =  $cacheConfig[$dataType]['expire'];
            }
            //如果指定缓存时间为非正整数，跳出，不保存
            if($expire<=0)
            {
                return true;
            }
            //有数据原样缓存，没数据缓存时间减少为1/10
            $expire = $expire * (count($data['data']) > 0 ? 1 : 0.1) + rand(1, 100);
            $redis->set($keyConfig, json_encode(['params' => $params, 'data' => $data]));
            $redis->expire($keyConfig, $expire);
            //echo "key:".$keyConfig."saved to expire:".$expire."\n";
            return true;
        } else {
            return true;
        }
    }

    public function refreshCache($dataType, $params, $keyName = '')
    {

        $cacheConfig = $this->getCacheConfig();
        if (isset($cacheConfig[$dataType])) {
            $privilegeService = new PrivilegeService();
            $functionList = $privilegeService->getFunction([$dataType => $params]);
            {
                $functionInfo = $functionList[$dataType];
                $class = $functionInfo['class'];
                $function = $functionInfo['function'];
                //$params = $data[$dataType];
                $functionCount = $functionInfo['functionCount'];
                $functionProcess = $functionInfo['functionProcess'] ?? "";

            }

            //$functionList = $privilegeService->getFunction($data);

            $redis = app("redis.connection");
            $keyList = $redis->keys($cacheConfig[$dataType]['prefix'] . "_*");

            foreach ($keyList as $key) {
                $data = $redis->get($key);
                $data = json_decode($data, true);
                //有参数，尝试刷新数据
                if (isset($data['params'])) {

                    if ($dataType == 'defaultConfig' && isset($data['params']['keys']) && $keyName && in_array($keyName, $data['params']['keys'])) {
                        $redis->del($key);
                    }
                    if ($dataType == 'imageList' && isset($data['params']['flag']) && $keyName) {
                        $redis->del($key);
                    }
                    $d = $class->$function($data['params']);
                    if (!$functionCount || $functionCount == "") {
                        $count = 0;
                    } else {

                        $count = $class->$functionCount($data['params']);
                    }
                    if ($functionProcess != "") {
                        $d = $privilegeService->$functionProcess($d, $functionList);
                    }


                    $dataArr = ['data' => $d, 'count' => $count];
                    $this->saveCache($dataType, $params, $dataArr);

                } else//没有，删除等待重建
                {
                    echo "toDelete:" . $key;
                    $redis->del($key);
                }
            }
        }
    }

}
