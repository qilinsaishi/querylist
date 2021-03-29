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
                    $teamList = $oTeam->getTeamList(['tid'=>$singleMap['tid'],"fields"=>"*","sources"=>array_column($sourceList,"source")]);

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
