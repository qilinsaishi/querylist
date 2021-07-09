<?php

namespace App\Services\Data;

use App\Models\Admin\Site;
use App\Models\InformationModel;
use App\Models\PlayerModel;
use App\Models\TeamModel;
use App\Services\PlayerService;
use App\Services\TeamService;
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
            "baiduInformaitonList" => [
                'prefix' => "baiduInformaitonList",
                'expire' => 3600,
            ],
            "5118InformaitonList" => [
                'prefix' => "5118InformaitonList",
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
            "intergratedTeamListByTeam" => [//整合队伍列表(来自队员）
                'prefix' => "intergratedTeamListByTeam",
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
            "intergratedPlayerListByPlayer" => [//整合队员列表(来自队员）
                'prefix' => "intergratedPlayerListByPlayer",
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
        $redis = app("redis.connection");
        $cacheConfig = $this->getCacheConfig();
        if (isset($cacheConfig[$dataType])) {
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
            //echo "toGetKey:".$keyConfig."\n";
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
            //echo "toSavekey:".$keyConfig."saved to expire:".$expire."\n";
            return true;
        } else {
            return true;
        }
    }

    public function refreshCache($dataType, $params = [], $keyName = '')
    {
        $main = config("app.main");
        $cacheConfig = $this->getCacheConfig();
        if (isset($cacheConfig[$dataType])) {
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
                    if ($data['params']['dataType'] == 'matchDetail' ) {
                        if(isset($params['game']) && isset($params['match_id']) && $params['match_id']>0)
                        {
                            $game = $data['data']['data']['game'];
                            $match_id = $data['data']['data']['match_id'];
                            if($params['match_id'] == $match_id && $params['game'] == $game)
                            {
                                $redis->del($key);
                            }
                        }
                        else
                        {
                            $home_id = $data['data']['data']['home_team_info']['tid']??0;
                            $away_id = $data['data']['data']['away_team_info']['tid']??0;
                            $tid = $params["tid"];
                            if($home_id == $tid || $away_id == $tid)
                            {
                                $redis->del($key);
                            }
                        }

                    }
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
                    if ($dataType == 'intergratedTeam')
                    {
                        $tid = $params[0];
                        if((isset($data['params']['0']) && $data['params']['0'] == $tid) || (isset($data['params']['tid']) && $data['params']['tid'] == $tid))
                        {
                            $redis->del($key);
                            $params_list[] = $data['params'];
                        }
                        $redis_key = "intergrated_team_0-".$tid;
                        $redis->del($redis_key);
                    }
                    if ($dataType == 'intergratedPlayer')
                    {
                        $pid = $params[0];
                        if((isset($data['params']['0']) && $data['params']['0'] == $pid) || (isset($data['params']['tid']) && $data['params']['pid'] == $pid))
                        {
                            $redis->del($key);
                            $params_list[] = $data['params'];
                        }
                        $redis_key = "intergrated_player_0-".$pid;
                        $redis->del($redis_key);
                    }
                    if ($dataType == 'tournament')
                    {
                        if($data['data']['data']['tournament_id']==$params['tournament_id'] && $data['params']['source']==$params['source'])
                        {
                            $redis->del($key);
                            $params_list[] = $data['params'];
                        }
                    }
                    if (in_array($dataType,[ "matchList"])) {
                        $toDelete = 0;
                        $dataParams = $data['params'];
                        if (isset($dataParams['game']) && is_array($dataParams['game'])) {
                            if (count(array_intersect($dataParams['game'], $params['game']))) {
                                $toDelete = 1;
                            }
                        } elseif(isset($dataParams['game']) && !is_array($dataParams['game'])) {
                            if (in_array($dataParams['game'], $params['game'])) {
                                $toDelete = 1;
                            }
                        }
                        if ($toDelete == 1)
                        {
                            $redis->del($key);
                            $params_list[] = $data['params'];
                        }
                    }
                    if(in_array($dataType,[ "informationList"]))
                    {
                        $toDelete = 0;
                        $gameList = array_unique(array_column($data['data']['data']??[],"game"));
                        $count = count(array_intersect($gameList, $params['game']??[]));
                        if($count>0)
                        {
                            $toDelete = 1;
                        }
                        if ($toDelete == 1)
                        {
                            $redis->del($key);
                            //echo "toDelete:".$key."\n";
                            $params_list[] = $data['params'];
                        }
                    }

                } else//没有，删除等待重建
                {
                    $redis->del($key);
                }
            }
            //如果是整合队伍，要再调用一次处理关联比赛
            if($dataType == "intergratedTeam")
            {
                $tid=$tid??$params[0];
                $this->refreshCache("matchDetail",['tid'=>$tid]);
            }
            //队伍
            if($dataType == "totalTeamInfo")
            {
                $team_id = $params[0];
                //处理队伍关联的显示状态
                (new TeamService())->processTeamDisplay($team_id);
                $cacheConfig = $this->getCacheConfig();
                //清空比赛列表
                $this->truncate($cacheConfig['matchList']['prefix']);
                //清空赛事详情
                $this->truncate($cacheConfig['tournament']['prefix']);

                //清空所有整合队伍关联的缓存
                $this->truncate($cacheConfig['intergratedTeamList']['prefix']);
            }
            //如果是整合队伍，要再调用一次处理关联比赛
            if($dataType == "totalPlayerInfo")
            {
                $player_id = $params[0];
                (new PlayerService())->processPlayerDisplay(0,$player_id,-1);

                $cacheConfig = $this->getCacheConfig();
                //清空所有整合队员关联的缓存
                $this->truncate($cacheConfig['intergratedPlayerList']['prefix']);
                //清空战队详情关联缓存
                $playerModel=new PlayerModel();
                $playerInfo=$playerModel->getPlayerById($player_id);
                $team_id=$playerInfo['team_id'] ?? 0;
                //通过team_id获取tid
                $teamInfo=(new TeamModel())->getTeamById($team_id);
                $tid=$teamInfo['tid']??0;
                $params=[$tid];
                $this->refreshCache("intergratedTeam",($params));
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
    public function getDataByKey($connection=null,$key="")
    {

    }
}
