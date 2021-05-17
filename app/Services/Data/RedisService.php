<?php

namespace App\Services\Data;

use App\Models\Admin\Site;
use App\Models\InformationModel;
use PhpParser\Node\Stmt\Else_;

class RedisService
{
    //获取各个数据类型的缓存数据
    public function getCacheConfig()
    {
        $cacheConfig = [

            "informationList" => [
                'prefix' => "informationList",
                'expire' => 3600,
            ],
            "information" => [
                'prefix' => "information",
                'expire' => 86400,
                'views'=> 1
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
                'expire' => 3600,
            ],
            "links" => [//友链
                'prefix' => "links",
                'expire' => 3600,
            ],
            "totalPlayerList" => [//队员总表
                'prefix' => "totalPlayerList",
                'expire' => 3600,
            ],
            "totalPlayerInfo" => [//队员总表
                'prefix' => "totalPlayerInfo",
                'expire' => 3600,
                'views'=>1,
            ],
            "totalTeamInfo" => [//战队总表
                'prefix' => "totalTeamInfo",
                'expire' => 86400,
                'views'=> 1
            ],

            "totalTeamList" => [//战队总表
                'prefix' => "totalTeamList",
                'expire' => 3600,
            ],

            "tournamentList" => [//赛事总表
                'prefix' => "tournamentList",
                'expire' => 3600,
            ],
            "tournament" => [//赛事
                'prefix' => "tournament",
                'expire' => 86400,
            ],

            "intergratedTeam" => [//整合队伍
                'prefix' => "intergratedTeam",
                'expire' => 86400,
            ],
            "intergratedTeamList" => [//整合队伍列表
                'prefix' => "intergratedTeamList",
                'expire' => 86400,
            ],
            "intergratedPlayer" => [//整合队员
                'prefix' => "intergratedPlayer",
                'expire' => 86400,
            ],
            "intergratedPlayerList" => [//整合队员列表
                'prefix' => "intergratedPlayerList",
                'expire' => 86400,
            ],
            "matchList" => [//比赛列表
                'prefix' => "matchList",
                'expire' => 86400,
            ],

            "matchDetail" => [//比赛详情
                'prefix' => "matchDetail",
                'expire' => 86400,
            ],


            "lolHero" => [//lolHero
                'prefix' => "lolHero",
                'expire' => 86400,
            ],
            "kplHero" => [//kplHero
                'prefix' => "kplHero",
                'expire' => 86400,
            ],
            "dota2Hero" => [//dota2Hero
                'prefix' => "dota2Hero",
                'expire' => 86400,
            ],
            "lolHeroList" => [//lolHeroList
                'prefix' => "lolHeroList",
                'expire' => 86400,
            ],
            "kplHeroList" => [//kplHeroList
                'prefix' => "kplHeroList",
                'expire' => 86400,
            ],
            "dota2HeroList" => [//dota2HeroList
                'prefix' => "dota2HeroList",
                'expire' => 86400,
            ],
            "lolEquipmentList" => [//lolEquipmentList
                'prefix' => "lolEquipmentList",
                'expire' => 86400,
            ],
            "lolSummonerList" => [//lolSummonerList
                'prefix' => "lolSummonerList",
                'expire' => 86400,
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
            //echo "get:".$dataType.":".$expire."\n";
            //如果指定缓存时间为非正整数，跳出，不保存
            if($expire<=0 || (isset($params['reset']) && $params['reset']>0))
            {
                return false;
            }
            if(isset($params['reset']))
            {
                unset($params['reset']);
            }
            $keyConfig = $cacheConfig[$dataType]['prefix'] . "_" . md5(json_encode($params));
            if(isset($params['game']) && !is_array($params['game']) && trim($params['game'])!="")
            {
                $keyConfig = $keyConfig."_".trim($params['game']);
            }
            if(isset($params['game']) && is_array($params['game']))
            {
                $keyConfig = $keyConfig."_".implode("_",$params['game']);
            }
            $exists = $redis->exists($keyConfig);
            if ($exists) {
                $data = json_decode($redis->get($keyConfig), true);
                if (is_array($data)) {
                    if (isset($data['data'])) {
                        $data = $data['data'];
                    }
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
            if(isset($params['reset']))
            {
                unset($params['reset']);
            }
            ksort($params);
            $keyConfig = $cacheConfig[$dataType]['prefix'] . "_" . md5(json_encode($params));
            if(isset($params['game']) && !is_array($params['game']) && trim($params['game'])!="")
            {
                $keyConfig = $keyConfig."_".trim($params['game']);
            }
            if(isset($params['game']) && is_array($params['game']))
            {
                $keyConfig = $keyConfig."_".implode("_",$params['game']);
            }
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
            if($dataType=="information")
            {
                $keyList = $redis->keys($cacheConfig[$dataType]['prefix'] . "_".md5(json_encode($params)));
            }
            else
            {
                $keyList = $redis->keys($cacheConfig[$dataType]['prefix'] . "_*");
            }
            $params_list = [];
            foreach ($keyList as $key) {
                $data = $redis->get($key);
                $data = json_decode($data, true);
                //有参数，尝试刷新数据
                if (isset($data['params'])) {
                    $data['params']['dataType']=$dataType;
                    if ($data['params']['dataType'] == 'defaultConfig' ) {
                        $redis->del($key);
                        $params_list[] = $data['params'];
                    }
                    if ($dataType == 'imageList' ) {
                        $redis->del($key);
                        $params_list[] = $data['params'];
                    }
                    if ($dataType == 'links') {
                        $redis->del($key);
                        $params_list[] = $data['params'];
                    }
                    if ($dataType == 'information')
                    {
                        $redis->del($key);
                        $params_list[] = $data['params'];
                    }
                    if ($dataType == 'totalTeamInfo')
                    {
                        $redis->del($key);
                        $params_list[] = $data['params'];
                    }
                    if ($dataType == 'totalPlayerInfo')
                    {
                        $redis->del($key);
                        $params_list[] = $data['params'];
                    }
                } else//没有，删除等待重建
                {
                    $redis->del($key);
                }

            }
            //资讯类型数据需要刷新相关站点列表页第一页
            if($dataType == "information")
            {
                //查找数据，获取类型和对应游戏
                $info = (new InformationModel())->getInformationById($params['0'],["id","type","game"]);
                if(isset($info['id']))
                {
                    $type = "";
                    if($info['type']==4)
                    {
                        $type="/strategylist/1/reset";
                    }
                    elseif(in_array($info['type'],[1,2,3,5]))
                    {
                        $type = "/newslist/1/reset";
                    }
                    if($type!="")
                    {
                    switch($info['game'])
                    {
                        case "lol":
                            $id=1;
                            break;
                        case "kpl":
                            $id=3;
                            break;
                        case "dota2":
                            $id=4;
                            break;
                    }
                    //请求浏览器刷新缓存
                    $siteInfo=(new Site())->getSiteById($id);
                    $domain=$siteInfo['domain'] ?? '';
                    $url=$domain.$type;
                    $rt=file_get_contents($url);
                    }

                }
            }
            return $params_list;

        }
    }
    public function truncate($prefix="")
    {
        $redis = app("redis.connection");
        $keyList = $redis->keys($prefix . "_*");
        $params_list = [];
        $return = ["cleard"=>0,"cleard_list"=>[]];
        foreach ($keyList as $key)
        {
            $return["cleard_list"][] = $key;
            $redis->del($key);
        }
        $return["cleard"] = count($return["cleard_list"]);
        return $return;
    }
    //更新缓存中的浏览数量
    public function addViews($dataType = "",$params = [])
    {
        $cacheConfig = $this->getCacheConfig();
        if (isset($cacheConfig[$dataType]))
        {
            if(($cacheConfig[$dataType]['views']??0) == 1)
            {
                $id = 0;
                switch ($dataType)
                {
                    case "totalTeamInfo":
                        if(is_array($params))
                        {
                            $id = $params['0']??($params['team_id']??0);
                        }
                        break;
                    case "totalPlayerInfo":
                        if(is_array($params))
                        {
                            $id = $params['0']??($params['player_id']??0);
                        }
                        break;
                    case "information":
                        if(is_array($params))
                        {
                            $id = $params['0']??($params['id']??0);
                        }
                        break;
                    case "lolHero":
                        if(is_array($params))
                        {
                            $id = $params['0']??($params['id']??0);
                        }
                        break;
                    case "kplHero":
                        if(is_array($params))
                        {
                            $id = $params['0']??($params['id']??0);
                        }
                        break;
                    case "dota2Hero":
                        if(is_array($params))
                        {
                            $id = $params['0']??($params['id']??0);
                        }
                        break;
                }
                $redis = app("redis.connection");
                $redis_key = "views_".$cacheConfig[$dataType]["prefix"]."_".$id;
                $redis->incr($redis_key);
                return $redis_key;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    //保存缓存中的浏览数量
    public function saveViews()
    {
        $redis = app("redis.connection");
        $redisKey = "views_*_*";
        $keys = $redis->keys($redisKey);
        $priviligeList = (new PrivilegeService())->getPriviliege();
        foreach($keys as $redisKey)
        {
            $views = intval($redis->get($redisKey));
            if($views>0)
            {
                $t = explode("_",$redisKey);
                if(isset($priviligeList[$t['1']]))
                {
                    $modelName = $priviligeList[$t['1']]['list']['0']['model'];
                    $functionGet = $priviligeList[$t['1']]['functionSingle'];
                    $functionUpdate = $priviligeList[$t['1']]['functionUpdate'];
                    $model = new $modelName;
                    $current = $model->$functionGet($t['2'],($model->primaryKey.","."views"));
                    if(isset($current[$model->primaryKey]))
                    {
                        $current['views'] += $views;
                        print_R($current);
                        $update = $model->$functionUpdate($t['2'],$current);
                        if($update)
                        {
                            $redis->del($redisKey);
                        }
                    }
                }
            }

        }
    }
}
