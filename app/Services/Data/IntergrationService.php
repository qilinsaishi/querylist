<?php

namespace App\Services\Data;
use App\Services\Data\RedisService;
use App\Models\TeamModel;
use App\Models\Team\TotalTeamModel as TotalTeamModel;
use App\Models\Team\TeamNameMapModel as TeamNameMapModel;

use App\Models\PlayerModel;
use App\Models\Player\TotalPlayerModel as TotalPlayerModel;
use App\Models\Player\PlayerMapModel as PlayerMapModel;
use App\Models\Player\PlayerNameMapModel as PlayerNameMapModel;
use function AlibabaCloud\Client\json;

class IntergrationService
{
    //team_id:team_info表的主键
    //tid:team_list表的主键
    //force:强制重新获取 1是0否
    public function getTeamInfo($team_id=0,$tid=0,$get_data = 0,$force = 0)
    {
        $return = ["data"=>[],"structure"=>[]];
        $redis = app("redis.connection");
        $redis_key = "intergrated_team_".$team_id."-".$tid;
        $toGet = 0;
        if($force == 1)
        {
            $toGet = 1;
        }
        else
        {
            $exists = $redis->exists($redis_key);
            if ($exists)
            {
                $data = json_decode($redis->get($redis_key), true);
                if(isset($data['data']['tid']))
                {
                    $return = $data;
                    //echo "cached\n";
                    $toGet = 0;
                }
                else
                {
                    $toGet = 1;
                }
            }
            else
            {
                $toGet = 1;
            }
        }
        //echo "toGet:".$toGet."\n";
        if($toGet==1)
        {
            $oTeam = new TeamModel();
            $oTotalTeam = new TotalTeamModel();
            //获取表结构
            $table = $oTotalTeam->getTableColumns();
            $jsonList = $oTeam->toJson;
            $appendList = $oTeam->toAppend;
            $pk = $oTotalTeam->primaryKey;
            if($team_id>0)
            {
                //找到单条详情
                $singleTeamInfo = $oTeam->getTeamById($team_id,"team_id,tid");
                //找到
                if(isset($singleTeamInfo['team_id']))
                {
                    //获取当前映射
                    //$singleMap = $oTeam->getTeamByTeamId($singleTeamInfo['team_id']);
                    $singleMap = $singleTeamInfo;
                    //找到映射
                    if(isset($singleMap['tid']))
                    {
                        $tid = $singleMap['tid'];
                    }
                    else//没找到
                    {
                        $tid = 0;
                        //$return = [];
                        //创建映射
                    }
                }
                else//没找到
                {
                    $tid = 0;
                }
            }
            //获取集合数据
            $totalTeam = $oTotalTeam->getTeamById($tid);
            $sourceList = config('app.intergration.team.'.$totalTeam['game']);
            //获取集合所有详情
            $teamList = $oTeam->getTeamList(['tid'=>$tid,"fields"=>"*","sources"=>array_column($sourceList,"source")]);

            //----------检查是否需要跳转
            /*
            $totalTeam['redirect'] = json_decode($totalTeam['redirect'],true);
            if(isset($totalTeam['redirect']['tid']) && $totalTeam['redirect']['tid']>0)
            {
                return $this->getTeamInfo(0,$totalTeam['redirect']['tid'],$get_data,$force);
            }
            elseif(isset($totalTeam['redirect']['team_id']) && $totalTeam['redirect']['team_id']>0)
            {
                return $this->getTeamInfo($totalTeam['redirect']['team_id'],0,$get_data,$force);
            }
            */
            //----------检查是否需要跳转
            $teamIdList = array_column($teamList,"team_id");
            //复制映射结构
            $siteIdList = array_column($teamList,"site_id");
            $totalTeamStructure = $totalTeam;
            $append = [];
            $table_source = [];
            foreach($table as $column)
            {
                if(in_array($totalTeam[$column],$teamIdList))
                {
                    $currentKey = array_flip($teamIdList)[$totalTeam[$column]];
                    //不在需要json的列表中
                    if(!in_array($column,$jsonList))
                    {
                        $totalTeam[$column] = $teamList[$currentKey][$column];
                    }
                    else
                    {
                        $totalTeam[$column] = json_decode($teamList[$currentKey][$column],true);
                    }
                }
            }
            //生成字段与来源的对应表
            foreach($table as $column)
            {
                if(($totalTeam[$column] == "0") && ($column != $pk))
                {
                    $selectedSource = [];
                    //按照来源逐一扫描
                    foreach($sourceList as $key => $source)
                    {
                        //如果有注明高优先级
                        if(in_array($column,$source['detail_list']??[]))
                        {
                            if(in_array($source['source'],array_column($teamList,"original_source")))
                            {
                                $selectedSource[] = $source['source'];
                            }
                            else
                            {
                                $selectedSource = [];
                            }
                        }
                    }
                    if(count($selectedSource)==0)
                    {
                        $selectedSource = array_column($sourceList,"source");
                    }
                    $table_source[$column] = $selectedSource;
                }
                elseif(isset($appendList[$column]))
                {
                    if(!isset($append[$column]))
                    {
                        $append[$column] = [];
                    }
                    //依次循环队员
                    foreach($teamList as $teamInfo)
                    {
                        foreach($appendList[$column] as $appendKey)
                        {
                            if(!in_array($appendKey,$jsonList))
                            {
                                $append[$column][] = $teamInfo[$appendKey];
                            }
                            else
                            {
                                $teamInfo[$column] = json_decode($teamInfo[$column],true);
                                $teamInfo[$column] = is_array($teamInfo[$column])?$teamInfo[$column]:[];
                                $append[$column] = array_merge($append[$column]??[],$teamInfo[$column]);
                            }
                        }
                        $append[$column] = array_unique($append[$column]);
                    }
                }
            }
            foreach($append as $key => $value)
            {
                $totalTeam[$key] = $value;
                $totalTeamStructure[$key] = $value;
            }
            //生成字段与来源的对应表
            foreach($table as $column)
            {
                //echo "column:".$column."\n";
                if(isset($table_source[$column]))
                {
                    //echo "column:".$column."\n";
                    $sList =  $table_source[$column];
                    $temp = "";
                    $current_team = 0;
                    foreach($teamList as $teamInfo)
                    {
                        if(in_array($teamInfo['original_source'],$sList))
                        {
                            //不在需要json的列表中
                            if(!in_array($column,$jsonList))
                            {
                                if(isset($teamInfo[$column]) && strlen($teamInfo[$column])>strlen($temp))
                                {
                                    $temp = $teamInfo[$column];
                                    $current_team = $teamInfo['team_id']."|".$source['source'];
                                    $current_team = $teamInfo['team_id'];//."|".$source['source'];
                                }
                            }
                            else
                            {
                                //json解码，比较数组大小
                                $t = json_decode($teamInfo[$column],true);
                                if($temp == "")
                                {
                                    $temp = [];
                                }
                                if(count($temp)<is_array($t)?count($t):0)
                                {
                                    $temp = $t;
                                    $current_team = $teamInfo['team_id']."|".$source['source'];
                                    $current_team = $teamInfo['team_id'];//."|".$source['source'];
                                }
                            }
                            $totalTeam[$column] = $temp;
                            $totalTeamStructure[$column] = $current_team;
                        }
                    }
                }
            }
            foreach($teamList as $team)
            {
                $totalTeam['intergrated_site_id_list'][$team['original_source']][] = $team['site_id'];
            }
            $totalTeam['intergrated_id_list'] = ($teamIdList);
            $return['data'] = $totalTeam;
            $return['structure'] = $totalTeamStructure;
        }
        //有数据原样缓存，没数据缓存时间减少为1/10
        $expire = 86400;
        $redis->set($redis_key, json_encode($return));
        $redis->expire($redis_key, $expire);
        if($get_data==0)
        {
            unset($return['data']);
        }
        return $return;
    }

    //player_id:player_info表的主键
    //tid:player_list表的主键
    //force:强制重新获取 1是0否
    public function getPlayerInfo($player_id=0,$pid=0,$get_data = 0,$force = 0)
    {
        $return = ["data"=>[],"structure"=>[]];
        $redis = app("redis.connection");
        $redis_key = "intergrated_player_".$player_id."-".$pid;
        $toGet = 0;
        if($force == 1)
        {
            $toGet = 1;
        }
        else
        {
            $exists = $redis->exists($redis_key);
            if ($exists)
            {
                $data = json_decode($redis->get($redis_key), true);
                if(isset($data['data']['pid']))
                {
                    $return = $data;
                    //echo "cached\n";
                    $toGet = 0;
                }
                else
                {
                    $toGet = 1;
                }
            }
            else
            {
                $toGet = 1;
            }
        }
        //echo "toGet:".$toGet."\n";
        if($toGet==1)
        {
            $oPlayer = new PlayerModel();
            //$oPlayerMap = new PlayerMapModel();
            $oTotalPlayer = new TotalPlayerModel();
            //获取表结构
            $table = $oTotalPlayer->getTableColumns();
            $jsonList = $oPlayer->toJson;
            $appendList = $oPlayer->toAppend;
            $pk = $oTotalPlayer->primaryKey;
            if($player_id>0)
            {
                //找到单条详情
                $singlePLayerInfo = $oPlayer->getPlayerById($player_id);
                //找到
                if(isset($singlePLayerInfo['player_id']))
                {
                    //获取当前映射
                    $singleMap = $oPlayer->getPlayerById($singlePLayerInfo['player_id']);
                    //找到映射
                    if(isset($singleMap['pid']))
                    {
                        $pid = $singleMap['pid'];
                    }
                    else//没找到
                    {
                        $pid = 0;
                        //$return = [];
                        //创建映射
                    }
                }
                else//没找到
                {
                    $pid = 0;
                }
            }
            //获取集合数据
            $totalPlayer = $oTotalPlayer->getPlayerById($pid);
            $sourceList = config('app.intergration.player.'.$totalPlayer['game']);
            //获取集合所有详情
            $playerList = $oPlayer->getPLayerList(['pid'=>$pid,"fields"=>"*","sources"=>array_column($sourceList,"source")]);
            //如果有重定向
            if(isset($totalPlayer['redirect'])){
                $totalPlayer['redirect'] = json_decode($totalPlayer['redirect'],true);
                if(isset($totalPlayer['redirect']['pid']) && $totalPlayer['redirect']['pid']>0)
                {
                    //返回新数据
                    return $this->getPlayerInfo(0,$totalPlayer['redirect']['pid'],$get_data,$force);
                }
            }

            $playerIdList = array_column($playerList,"player_id");
            //$playerSiteIdList = array_column($playerList,"site_id");
            //复制映射结构
            $totalPlayerStructure = $totalPlayer;
            $append = [];
            $table_source = [];
            foreach($table as $column)
            {
                if(in_array($totalPlayer[$column],$playerIdList))
                {
                    $currentKey = array_flip($playerIdList)[$totalPlayer[$column]];
                    //不在需要json的列表中
                    if(!in_array($column,$jsonList))
                    {
                        $totalPlayer[$column] = $playerList[$currentKey][$column];
                    }
                    else
                    {
                        $totalPlayer[$column] = json_decode($playerList[$currentKey][$column],true);
                    }
                }
            }
            //生成字段与来源的对应表
            foreach($table as $column)
            {
                if(($totalPlayer[$column] == "0") && ($column != $pk))
                {
                    $selectedSource = [];
                    //按照来源逐一扫描
                    foreach($sourceList as $key => $source)
                    {
                        //如果有注明高优先级
                        if(in_array($column,$source['detail_list']))
                        {
                            if(in_array($source['source'],array_column($playerList,"original_source")))
                            {
                                $selectedSource[] = $source['source'];
                            }
                            else
                            {
                                $selectedSource = [];
                            }
                        }
                    }
                    if(count($selectedSource)==0)
                    {
                        $selectedSource = array_column($sourceList,"source");
                    }
                    $table_source[$column] = $selectedSource;
                }
                elseif(isset($appendList[$column]))
                {
                    if(!isset($append[$column]))
                    {
                        $append[$column] = [];
                    }
                    //依次循环队员
                    foreach($playerList as $playerInfo)
                    {
                        foreach($appendList[$column] as $appendKey)
                        {
                            if(!in_array($appendKey,$jsonList))
                            {
                                $append[$column][] = $playerInfo[$appendKey];
                            }
                            else
                            {
                                $playerInfo[$column] = json_decode($playerInfo[$column],true);
                                $playerInfo[$column] = is_array($playerInfo[$column])?$playerInfo[$column]:[];
                                $append[$column] = array_merge($append[$column],$playerInfo[$column]);
                            }
                        }
                        $append[$column] = array_unique($append[$column]);
                    }
                }
            }
            foreach($append as $key => $value)
            {
                $totalPlayer[$key] = array_values($value);
                $totalPlayerStructure[$key] = array_values($value);
            }
            //生成字段与来源的对应表
            foreach($table as $column)
            {
                //echo "column:".$column."\n";
                if(isset($table_source[$column]))
                {
                    //echo "column:".$column."\n";
                    $sList =  $table_source[$column];
                    $temp = "";
                    $current_player = 0;
                    foreach($playerList as $playerInfo)
                    {
                        if(in_array($playerInfo['original_source'],$sList))
                        {
                            //不在需要json的列表中
                            if(!in_array($column,$jsonList))
                            {
                                if(isset($playerInfo[$column]) && strlen($playerInfo[$column])>strlen($temp))
                                {
                                    $temp = $playerInfo[$column];
                                    $current_player = $playerInfo['player_id']."|".$source['source'];
                                    $current_player = $playerInfo['player_id'];//."|".$source['source'];
                                }
                            }
                            else
                            {
                                //json解码，比较数组大小
                                $t = json_decode($playerInfo[$column],true);
                                if($temp == "")
                                {
                                    $temp = [];
                                }

                                if(count($temp)<is_array($t)?count($t):0)
                                {
                                    $temp = $t;
                                    $current_player = $playerInfo['player_id']."|".$source['source'];
                                    $current_player = $playerInfo['player_id'];//."|".$source['source'];
                                }
                            }
                            $totalPlayer[$column] = $temp;
                            $totalPlayerStructure[$column] = $current_player;
                        }
                    }
                }
            }
            foreach($playerList as $player)
            {
                $totalPlayer['intergrated_site_id_list'][$player['original_source']][] = $player['site_id'];
            }
            $totalPlayer['intergrated_id_list'] = ($playerIdList);
            //$totalPlayer['intergrated_site_id_list'] = ($playerSiteIdList);
            $return['data'] = $totalPlayer;
            $return['structure'] = $totalPlayerStructure;
        }
        //有数据原样缓存，没数据缓存时间减少为1/10
        $expire = 86400;
        $redis->set($redis_key, json_encode($return));
        $redis->expire($redis_key, $expire);
        if($get_data==0)
        {
            unset($return['data']);
        }
        return $return;
    }

}
