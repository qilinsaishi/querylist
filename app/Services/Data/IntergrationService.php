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
    public function getTeamInfo($team_id,$force = 1)
    {
        $sourceList = config('app.intergration.team');
        if($force==1)
        {
            $oTeam = new TeamModel();
            $oTeamMap = new TeamMapModel();
            $oTotalTeam = new TotalTeamModel();
            //获取表结构
            $table = $oTotalTeam->getTableColumns();
            $pk = $oTotalTeam->primaryKey;
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
                    //获取集合所有详情
                    $teamList = $oTeam->getTeamList(['tid'=>$singleMap['tid'],"fields"=>"*","sources"=>array_column($sourceList,"source")]);
                    //获取集合数据
                    $totalTeam = $oTotalTeam->getTeamById($singleMap['tid']);
                    //按照来源逐一扫描
                    foreach($sourceList as $key => $source)
                    {
                        $first_source = 0;
                        if($key==0)
                        {
                            echo "first_source:";
                            $first_source = 1;
                        }
                        echo $source['source']."\n";
                        //按照字段逐一扫描
                        foreach($table as $column)
                        {
                            //字段尚未被指定 + 不是主键 + (第一个来源（默认全部）/ 在来源列表中）
                            if (($totalTeam[$column] == 0) && ($column != $pk) && (($first_source==1) || in_array($column,$source['detail_list'])))
                            {
                                $temp = "";
                                //依次循环队伍
                                foreach($teamList as $teamInfo)
                                {
                                    //如果和当前循环到的来源相同
                                    if($teamInfo['original_source'] == $source['source'])
                                    {
                                        echo "column:".$column.", use source:".$source['source']."\n";
                                        if(strlen($teamInfo[$column])>strlen($temp))
                                        {
                                            $temp = $teamInfo[$column];
                                        }
                                    }
                                }
                                $totalTeam[$column] = $temp;
                            }
                        }
                    }
                    print_R($totalTeam);
                    die();
                }
                else//没找到
                {
                    //创建映射
                }
            }
            else//没找到
            {
                $return = [];
            }
        }
    }

}
