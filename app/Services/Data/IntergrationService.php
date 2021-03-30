<?php

namespace App\Services\Data;
use App\Services\Data\RedisService;
use App\Models\TeamModel;
use App\Models\Team\TotalTeamModel as TotalTeamModel;
use App\Models\Team\TeamMapModel as TeamMapModel;
use App\Models\Team\TeamNameMapModel as TeamNameMapModel;

use App\Models\PlayerModel;
use App\Models\Player\TotalPlayerModel as TotalPlayerModel;
use App\Models\Player\PlayerMapModel as PlayerMapModel;
use App\Models\Player\PlayerNameMapModel as PlayerNameMapModel;

class IntergrationService
{
    //team_id:team_info表的主键
    //force:强制重新获取 1是0否
    public function getTeamInfo($team_id=0,$tid=0,$get_data = 0,$force = 1)
    {
        $return = ["data"=>[],"structure"=>[]];
        $sourceList = config('app.intergration.team');
        if($force==1)
        {
            $oTeam = new TeamModel();
            $oTeamMap = new TeamMapModel();
            $oTotalTeam = new TotalTeamModel();
            //获取表结构
            $table = $oTotalTeam->getTableColumns();
            $jsonList = $oTeam->toJson;
            $appendList = $oTeam->toAppend;
            $pk = $oTotalTeam->primaryKey;
            if($team_id>0)
            {
                //找到单条详情
                $singleTeamInfo = $oTeam->getTeamById($team_id);
                //找到
                if(isset($singleTeamInfo['team_id']))
                {
                    //获取当前映射
                    $singleMap = $oTeamMap->getTeamByTeamId($singleTeamInfo['team_id']);
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
            //获取集合所有详情
            $teamList = $oTeam->getTeamList(['tid'=>$tid,"fields"=>"*","sources"=>array_column($sourceList,"source")]);
            //获取集合数据
            $totalTeam = $oTotalTeam->getTeamById($tid);
            $teamIdList = array_column($teamList,"team_id");
            //复制映射结构
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
                        if(in_array($column,$source['detail_list']))
                        {
                            $selectedSource[] = $source['source'];
                        }
                    }
                    if(count($selectedSource)==0)
                    {
                        $selectedSource = array_column($sourceList,"source");
                    }
                    $table_source[$column] = $selectedSource;
                }
                elseif(in_array($column,$appendList))
                {
                    if(!isset($append[$column]))
                    {
                        $append[$column] = [];
                    }
                    //依次循环队伍
                    foreach($teamList as $teamInfo)
                    {
                        $append[$column] = array_unique(array_merge($append[$column],json_decode($teamInfo[$column],true)));
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
                echo "column:".$column."\n";
                if(isset($table_source[$column]))
                {
                    echo "column:".$column."\n";
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
                                if(strlen($teamInfo[$column])>strlen($temp))
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
                                if(count($temp)<$t)
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
            $return['data'] = $totalTeam;
            $return['structure'] = $totalTeamStructure;
        }
        if($get_data==0)
        {
            unset($return['data']);
        }
        return $return;
    }
}
