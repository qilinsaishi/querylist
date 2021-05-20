<?php

namespace App\Services\Data;

use App\Collect\hero\dota2\gamedota2;
use App\Models\PlayerModel;
use function AlibabaCloud\Client\json;

class PrivilegeService
{
    //public $id_map = ["teamList"=>["dota2"=>""]];
    public $kpl_hero_type = [
        1=>'战士',
        2=>'法师',
        3=>'坦克',
        4=>'刺客',
        5=>'射手',
        6=>'辅助',
        10=>'限免',
        11=>'新手'
    ];
    //获取各个数据类型对应的类库优先级列表以及获取方法
    public function getPriviliege()
    {
        $privilegeList = [
            "intergratedTeam"=>
            [
                'list' => [
                    ['model' => 'App\Models\Team\TotalTeamModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getTeamById",
                'functionSingle' => "getTeamById",
                'functionProcess' => "processIntergratedTeam",
            ],
            "intergratedTeamList"=>
                [
                    'list' => [
                        ['model' => 'App\Models\Team\TotalTeamModel', 'source' => ''],
                    ],
                    'withSource' => 0,
                    'function' => "getTeamList",
                    'functionCount' => "getTeamCount",
                    'functionSingle' => "getTeamByTeamId",
                    'functionProcess' => "processIntergratedTeamList",
                ],
            "intergratedPlayer"=>
                [
                    'list' => [
                        ['model' => 'App\Models\Player\TotalPlayerModel', 'source' => ''],
                    ],
                    'withSource' => 0,
                    'function' => "getPlayerById",
                    'functionSingle' => "getPlayerById",
                    'functionProcess' => "processIntergratedPlayer",
                ],
            "intergratedPlayerList"=>
                [
                    'list' => [
                        ['model' => 'App\Models\Player\TotalPlayerModel', 'source' => ''],
                    ],
                    'withSource' => 0,
                    'function' => "getPlayerList",
                    'functionCount' => "getPlayerCount",
                    'functionSingle' => "getPlayerById",
                    'functionProcess' => "processIntergratedPlayerList",
                ],
            "matchList" => [
                'list' => [
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'cpseo'],
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'chaofan'],
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'gamedota2'],
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'scoregg'],
                ],
                'withSource' => 1,
                'function' => "getMatchList",//获取数据方法
                'functionCount' => "getMatchCount",//获取列表方法
                'functionProcess' => "processMatchList",//格式化的处理方法
            ],
            "matchDetail" => [
                'list' => [
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'scoregg'],
                ],
                'withSource' => 1,
                'function' => "getMatchById",//获取数据方法
                'functionCount' => "getMatchCount",//获取列表方法
                'functionProcess' => "processMatch",//格式化的处理方法
            ],
            "tournamentList" => [
                'list' => [
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => "cpseo"],
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => "chaofan"],
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => "gamedota2"],
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => 'scoregg'],
                ],
                'withSource' => 1,
                'function' => "getTournamentList",
                'functionCount' => "getTournamentCount",
                'functionSingle' => "getTournamentById",
            ],
            "tournament" => [
                'list' => [
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => "cpseo"],
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => "chaofan"],
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => "gamedota2"],
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => 'scoregg'],
                ],
                'withSource' => 1,
                'function' => "getTournamentById",
                'functionSingle' => "getTournamentById",
                'functionProcess' => "processTournament",
            ],
            "roundList" => [
                'list' => [
                    ['model' => 'App\Models\Match\#source#\roundModel', 'source' => 'scoregg'],
                ],
                'withSource' => 1,
                'function' => "getRoundList",
                'functionSingle' => "getRoundById",
            ],
            "teamList" => [//团队列表
                'list' => [
                    ['model' => 'App\Models\Match\#source#\teamModel', 'source' => 'cpseo'],
                    ['model' => 'App\Models\Match\#source#\teamModel', 'source' => 'chaofan'],
                ],
                'withSource' => 1,
                'function' => "getTeamList",
                'functionCount' => "getTeamCount",
                'functionSingle' => "getTeamById",
            ],
            "totalTeamList" => [//团队总列表
                'list' => [
                    ['model' => 'App\Models\TeamModel'],
                ],
                'withSource' => 0,
                'function' => "getTeamList",
                'functionCount' => "getTeamCount",
                'functionSingle' => "getTeamById",
                'functionSingleBySite' => "getTeamBySiteId",
                'functionSingleByName' => "getTeamByName"
            ],
            "totalTeamInfo" => [//团队总列表
                'list' => [
                    ['model' => 'App\Models\TeamModel'],
                ],
                'withSource' => 0,
                'function' => "getTeamById",
                'functionCount' => "getTeamCount",
                'functionSingle' => "getTeamById",
                'functionProcess' => "processTotalTeam",
                'functionUpdate' => "updateTeam",
                'functionSingleBySite' => "getTeamBySiteId",
            ],
            "totalPlayerList" => [//队员总列表
                'list' => [
                    ['model' => 'App\Models\PlayerModel'],
                ],
                'withSource' => 0,
                'function' => "getPlayerList",
                'functionCount' => "getPlayerCount",
                'functionSingle' => "getPlayerById",
                'functionProcess' => "processTotalPlayerList",
            ],
            "team" => [//团队列表
                'list' => [
                    ['model' => 'App\Models\Match\#source#\teamModel', 'source' => 'cpseo'],
                    ['model' => 'App\Models\Match\#source#\teamModel', 'source' => 'chaofan'],
                ],
                'withSource' => 1,
                'function' => "getTeamById",
                //'functionCount' => "getTeamCount",
                'functionSingle' => "getTeamById",
                'functionProcess' => "processTeam",
            ],
            "defaultConfig" => [//通用配置
                'list' => [
                    ['model' => 'App\Models\Admin\DefaultConfig', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getDefaultConfigList",
                'functionCount' => "getDefaultCount",
                'functionSingle' => "getDefaultConfigByKey",
            ],
            "informationList" => [//资讯列表
                'list' => [
                    ['model' => 'App\Models\InformationModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getInformationList",
                'functionCount' => "getInformationCount",
                'functionSingle' => "getInformationById",
                'functionProcess' => "processInformationList",
            ],
            "totalPlayerInfo" => [//队员总表
                'list' => [
                    ['model' => 'App\Models\PlayerModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getPlayerById",
                'functionCount' => "getPlayerCount",
                'functionSingle' => "getPlayerById",
                'functionProcess' => "processTotalPlayer",
                'functionUpdate' => "updatePlayer",
                'functionSingleBySite' => "getPlayerBySiteId",
            ],
            "information" => [//资讯
                'list' => [
                    ['model' => 'App\Models\InformationModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getInformationById",
                'functionCount' => "",
                'functionSingle' => "getInformationById",
                'functionProcess' => "processInformation",
                'functionUpdate' => "updateInformation",
            ],
            "lolHeroList" => [//lol英雄列表
                'list' => [
                    ['model' => 'App\Models\Hero\lolModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getHeroList",
                'functionCount' => "getHeroCount",
                'functionSingle' => "getHeroById",
            ],
            "lolHero" => [//lol英雄详情
                'list' => [
                    ['model' => 'App\Models\Hero\lolModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getHeroById",
                'functionSingle' => "getHeroById",
                'functionProcess' => "processLolHero",
            ],
            "lolEquipmentList" => [//lol装备列表
                'list' => [
                    ['model' => 'App\Models\Equipment\lolModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getEquipmentList",
                'functionCount' => "getEquipmentCount",
                'functionSingle' => "getEquipmentById",
            ],
            "lolEquipment" => [//lol装备详情
                'list' => [
                    ['model' => 'App\Models\Equipment\lolModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getEquipmentById",
                'functionSingle' => "getEquipmentById",
            ],
            "lolSummonerList" => [//lol召唤师列表
                'list' => [
                    ['model' => 'App\Models\Summoner\lolModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getSkillList",
                'functionCount' => "getSkillCount",
                'functionSingle' => "getSkillById",
            ],
            "lolSummoner" => [//lol召唤师详情
                'list' => [
                    ['model' => 'App\Models\Summoner\lolModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getSkillById",
                'functionSingle' => "getSkillById",
            ],
            "lolRuneList" => [//lol符文列表
                'list' => [
                    ['model' => 'App\Models\Rune\lolModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getRuneList",
                'functionCount' => "getRuneCount",
                //'functionProcess' => "processRuneList",//格式化的处理方法
                'functionSingle' => "getRuneById",
            ],
            "lolRune" => [//lol符文详情
                'list' => [
                    ['model' => 'App\Models\Rune\lolModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getRuneById",
                //'functionProcess' => "processRuneList",//格式化的处理方法
                'functionSingle' => "getRuneById",
            ],
            "lolHeroSkin" => [//lol英雄皮肤详情
                'list' => [
                    ['model' => 'App\Models\Hero\Skin\lolModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getSkinByHero",
                'functionSingle' => "getRuneById",
            ],
            "lolHeroSpell" => [//lol英雄技能详情
                'list' => [
                    ['model' => 'App\Models\Hero\Spell\lolModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getSpellByHero",
                'functionSingle' => "getRuneById",
            ],
            "playerList" => [//队员
                'list' => [
                    ['model' => 'App\Models\PlayerModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getPlayerList",
                'functionCount' => "getPlayerCount",
                'functionSingle' => "getPlayerById",
                'functionProcess' => "processPlayerList",//格式化的处理方法
            ],
            "links" => [//友链
                'list' => [
                    ['model' => 'App\Models\Admin\Links', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getLinkList",
                'functionCount' => "getLinkCount",
                'functionSingle' => "getLinkById",
            ],
            "imageList" => [//轮播图
                'list' => [
                    ['model' => 'App\Models\Admin\ImageList', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getImageList",
                'functionCount' => "getImageCount",
                'functionSingle' => "getImageById",
            ],
            "gameConfig" => [//lol游戏配置
                'list' => [
                    ['model' => 'App\Models\Admin\GameConfig', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getGameConfigById",
                'functionCount' => "getGameConfigCount",
                'functionSingle' => "getGameConfigById",
            ],
            "keywordMapList" => [//关键字对应
                'list' => [
                    ['model' => 'App\Models\KeywordMapModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getList",
                'functionProcess' => "processkeywordMapList",
                'functionProcessCount' => "processkeywordMapCount",
            ],
            "anotherKeyword" => [//本地维护关键字
                'list' => [
                    ['model' => 'App\Models\KeywordsModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getKeywordsList",
                'functionCount' => "getKeywordsCount",
            ],
            "scwsInformaitonList" => [//由scws分词索引生成的中文分词
                'list' => [
                    ['model' => 'App\Models\ScwsMapModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getList",
                'functionCount' => "getCount",
                'functionProcess' => "processScwsInformationList",
            ],
            "5118InformaitonList" => [//由5118分词索引生成的中文分词
                'list' => [
                    ['model' => 'App\Models\CorewordMapModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getList",
                'functionCount' => "getCount",
                'functionProcess' => "process5118InformationList",
            ],
            "scwsKeyword" => [//由scws分词索引生成的中文分词
                'list' => [
                    ['model' => 'App\Models\ScwsKeywordMapModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getById",
                //'functionProcess'=>"getDisableList",
            ],
            "kplHeroList" => [//王者荣耀英雄列表
                'list' => [
                    ['model' => 'App\Models\Hero\kplModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getHeroList",
                'functionCount' => "getHeroCount",
                'functionSingle' => "getHeroById",
            ],
            "kplHero" => [//王者荣耀英雄详情
                'list' => [
                    ['model' => 'App\Models\Hero\kplModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getHeroById",
                'functionSingle' => "getHeroById",
                'functionProcess' => "processKplHero",
            ],
            "kplEquipmentList" => [//王者荣耀装备列表
                'list' => [
                    ['model' => 'App\Models\Equipment\kplModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getEquipmentList",
                'functionCount' => "getEquipmentCount",
                'functionSingle' => "getEquipmentById",
            ],
            "kplEquipment" => [//王者荣耀装备详情
                'list' => [
                    ['model' => 'App\Models\Equipment\kplModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getEquipmentById",
                'functionSingle' => "getEquipmentById",
            ],
            "kplSummonerList" => [//王者荣耀召唤师列表
                'list' => [
                    ['model' => 'App\Models\Summoner\kplModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getSkillList",
                'functionCount' => "getSkillCount",
                'functionSingle' => "getSkillById",
            ],
            "kplSummoner" => [//王者荣耀召唤师详情
                'list' => [
                    ['model' => 'App\Models\Summoner\kplModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getSkillById",
                'functionSingle' => "getSkillById",
            ],
            "kplInscriptionList" => [//王者荣耀铭文列表
                'list' => [
                    ['model' => 'App\Models\Inscription\kplModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getInscriptionList",
                'functionCount' => "getInscriptionCount",
                'functionSingle' => "getInscriptionById",
            ],
            "kplInscription" => [//王者荣耀铭文详情
                'list' => [
                    ['model' => 'App\Models\Inscription\kplModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getInscriptionById",
                'functionSingle' => "getInscriptionById",
            ],
            "dota2HeroList" => [//王者荣耀英雄列表
                'list' => [
                    ['model' => 'App\Models\Hero\dota2Model', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getHeroList",
                'functionCount' => "getHeroCount",
                'functionSingle' => "getHeroById",
            ],
            "dota2Hero" => [//dota2英雄详情
                'list' => [
                    ['model' => 'App\Models\Hero\dota2Model', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getHeroById",
                'functionSingle' => "getHeroById",
                'functionProcess'=>"processDota2Hero",
            ],
        ];
        return $privilegeList;
    }

    public function getFunction($data, $currentSource = "")
    {
        //获取各个数据类型对应的类库优先级列表以及获取方法
        $priviliegeList = $this->getPriviliege();
        $classList = [];
        $functionList = [];
        foreach ($data as $name => $params) {
            $dataType = $params['dataType']??$name;
            //echo "try to find type:".$dataType."\n";
            //echo "currentSource:".$currentSource."\n";
            $sourceFound = 0;
            if(isset($priviliegeList[$dataType]["withSource"]) && $priviliegeList[$dataType]["withSource"]==1 && isset($params['source']))
            {
                $availableSource = array_column($priviliegeList[$dataType]['list'],"source");
                if(in_array($params['source'],$availableSource))
                {
                    $sourceFound = 1;
                }
            }
            //echo "sourceFound:".$sourceFound."\n";
            //默认没找到
            $found = 0;
            //数据类型出现在优先级列表
            if (isset($priviliegeList[$dataType])) {
                //尚未初始化数据来源 且 当前数据类型需要包含数据来源
                if ($currentSource == "" && $priviliegeList[$dataType]['withSource'] == 1) {
                    //循环所列的类库列表
                    foreach ($priviliegeList[$dataType]['list'] as $detail) {
                        $modelName = $detail['model'];
                        $currentSource = $currentSource == "" ? $detail['source'] : $currentSource;
                        //替换成包含数据来源的的类库路径
                        $modelName = str_replace("#source#", $detail['source'], $modelName);
                        //初始化
                        $classList = $this->getClass($classList, $modelName);
                        if($sourceFound == 1 && ($params['source'] != $detail['source']))
                        {
                            continue;
                        }
                        //如果之前没初始化过
                        if (!isset($functionList[$dataType])) {
                            //如果类库初始化成功
                            if (isset($classList[$modelName])) {
                                //检查基础function方法存在
                                if (method_exists($classList[$modelName], $priviliegeList[$dataType]['function'])) {
                                    //echo "class:".$modelName.",function:".$priviliegeList[$dataType]['function']." found\n";

                                    $functionList[$dataType] = ["className" => $modelName, "class" => $classList[$modelName], "function" => $priviliegeList[$dataType]['function']];
                                    if (isset($priviliegeList[$dataType]['functionCount']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionCount'])) {
                                        $functionList[$dataType]['functionCount'] = $priviliegeList[$dataType]['functionCount'];
                                    } else {
                                        $functionList[$dataType]['functionCount'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionSingle']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionSingle'])) {
                                        $functionList[$dataType]['functionSingle'] = $priviliegeList[$dataType]['functionSingle'];
                                    } else {
                                        $functionList[$dataType]['functionSingle'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionUpdate']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionUpdate'])) {
                                        $functionList[$dataType]['functionUpdate'] = $priviliegeList[$dataType]['functionUpdate'];
                                    } else {
                                        $functionList[$dataType]['functionUpdate'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionProcess'])) {
                                        $functionList[$dataType]['functionProcess'] = $priviliegeList[$dataType]['functionProcess'];
                                    } else {
                                        $functionList[$dataType]['functionProcess'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionProcessCount'])) {
                                        $functionList[$dataType]['functionProcessCount'] = $priviliegeList[$dataType]['functionProcessCount'];
                                    } else {
                                        $functionList[$dataType]['functionProcessCount'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionSingleBySite'])) {
                                        $functionList[$dataType]['functionSingleBySite'] = $priviliegeList[$dataType]['functionSingleBySite'];
                                    } else {
                                        $functionList[$dataType]['functionSingleBySite'] = "";
                                    }
                                    $found = 1;
                                } else {
                                    //echo "class:".$modelName.",function:".$priviliegeList[$dataType]['function']." not found\n";
                                }
                            } else {
                                //echo "class:".$modelName.",not found\n";
                            }
                            $functionList[$dataType]['source'] = $sourceFound==1?$params['source']:$currentSource;//$priviliegeList[$dataType]['source'];
                        }
                    }
                } //已经初始化数据来源 且 当前数据类型需要包含数据来源
                elseif ($currentSource != "" && $priviliegeList[$dataType]['withSource'] == 1) {
                    if($sourceFound ==1)
                    {
                        $currentSource = $params['source'];
                    }
                    //调用已有的数据类型
                    $functionList[$dataType]['source'] = $currentSource;
                    //获取当前数据了行的类库列表
                    $list = array_combine(array_column($priviliegeList[$dataType]['list'], "source"), array_column($priviliegeList[$dataType]['list'], "model"));
                    //如果包含已经被初始化的数据来源
                    if (isset($list[$currentSource])) {
                        $modelName = $list[$currentSource];
                        $modelName = str_replace("#source#", $currentSource, $modelName);
                        //初始化
                        $classList = $this->getClass($classList, $modelName);
                        if($sourceFound == 1 && ($params['source'] != $currentSource))
                        {
                            continue;
                        }
                        //检查方法存在
                        if (method_exists($classList[$modelName] ?? [], $priviliegeList[$dataType]['function'])) {
                            $functionList[$dataType] = ["className" => $modelName, "class" => $classList[$modelName], "function" => $priviliegeList[$dataType]['function']];
                            //标记为找到
                            $found = 1;
                            if (isset($priviliegeList[$dataType]['functionCount']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionCount'])) {
                                $functionList[$dataType]['functionCount'] = $priviliegeList[$dataType]['functionCount'];
                            } else {
                                $functionList[$dataType]['functionCount'] = "";
                            }
                            if (isset($priviliegeList[$dataType]['functionSingle']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionSingle'])) {
                                $functionList[$dataType]['functionSingle'] = $priviliegeList[$dataType]['functionSingle'];
                            } else {
                                $functionList[$dataType]['functionSingle'] = "";
                            }
                            if (isset($priviliegeList[$dataType]['functionUpdate']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionUpdate'])) {
                                $functionList[$dataType]['functionUpdate'] = $priviliegeList[$dataType]['functionUpdate'];
                            } else {
                                $functionList[$dataType]['functionUpdate'] = "";
                            }
                            if (isset($priviliegeList[$dataType]['functionProcess'])) {
                                $functionList[$dataType]['functionProcess'] = $priviliegeList[$dataType]['functionProcess'];
                            } else {
                                $functionList[$dataType]['functionProcess'] = "";
                            }
                            if (isset($priviliegeList[$dataType]['functionProcessCount'])) {
                                $functionList[$dataType]['functionProcessCount'] = $priviliegeList[$dataType]['functionProcessCount'];
                            } else {
                                $functionList[$dataType]['functionProcessCount'] = "";
                            }
                            if (isset($priviliegeList[$dataType]['functionSingleBySite'])) {
                                $functionList[$dataType]['functionSingleBySite'] = $priviliegeList[$dataType]['functionSingleBySite'];
                            } else {
                                $functionList[$dataType]['functionSingleBySite'] = "";
                            }
                        }
                    }
                    //如果没找到
                    if ($found == 0) {
                        //循环
                        foreach ($priviliegeList[$dataType]['list'] as $detail) {
                            $modelName = $detail['model'];
                            $modelName = str_replace("#source#", $detail['source'], $modelName);
                            $classList = $this->getClass($classList, $modelName);
                            if($sourceFound == 1 && ($params['source'] != $detail['source']))
                            {
                                continue;
                            }
                            if (!isset($functionList[$dataType])) {
                                if (isset($classList[$modelName])) {
                                    if (method_exists($classList[$modelName], $priviliegeList[$dataType]['function'])) {
                                        //echo "class:".$modelName.",function:".$priviliegeList[$dataType]['function']." found\n";
                                        $functionList[$dataType] = ["className" => $modelName, "class" => $classList[$modelName], "function" => $priviliegeList[$dataType]['function']];
                                        if (method_exists($classList[$modelName], $priviliegeList[$dataType]['functionCount'])) {
                                            $functionList[$dataType]['functionCount'] = $priviliegeList[$dataType]['functionCount'];
                                        } else {
                                            $functionList[$dataType]['functionCount'] = "";
                                        }
                                        if (isset($priviliegeList[$dataType]['functionSingle']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionSingle'])) {
                                            $functionList[$dataType]['functionSingle'] = $priviliegeList[$dataType]['functionSingle'];
                                        } else {
                                            $functionList[$dataType]['functionSingle'] = "";
                                        }
                                        if (isset($priviliegeList[$dataType]['functionUpdate']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionUpdate'])) {
                                            $functionList[$dataType]['functionUpdate'] = $priviliegeList[$dataType]['functionUpdate'];
                                        } else {
                                            $functionList[$dataType]['functionUpdate'] = "";
                                        }
                                        if (isset($priviliegeList[$dataType]['functionProcess'])) {
                                            $functionList[$dataType]['functionProcess'] = $priviliegeList[$dataType]['functionProcess'];
                                        } else {
                                            $functionList[$dataType]['functionProcess'] = "";
                                        }
                                        if (isset($priviliegeList[$dataType]['functionProcessCount'])) {
                                            $functionList[$dataType]['functionProcessCount'] = $priviliegeList[$dataType]['functionProcessCount'];
                                        } else {
                                            $functionList[$dataType]['functionProcessCount'] = "";
                                        }
                                        if (isset($priviliegeList[$dataType]['functionSingleBySite'])) {
                                            $functionList[$dataType]['functionSingleBySite'] = $priviliegeList[$dataType]['functionSingleBySite'];
                                        } else {
                                            $functionList[$dataType]['functionSingleBySite'] = "";
                                        }
                                        //标记为找到
                                        $found = 1;
                                    } else {
                                        //echo "class:".$modelName.",function:".$priviliegeList[$dataType]['function']." not found\n";
                                    }
                                } else {
                                    //echo "class:".$modelName.",not found\n";
                                }
                            }
                        }
                    }
                    //调用当前的数据来源
                    $functionList[$dataType]['source'] = $currentSource;
                }
                elseif($priviliegeList[$dataType]['withSource'] == 0)
                {
                    foreach ($priviliegeList[$dataType]['list'] as $detail) {
                        $modelName = $detail['model'];
                        //$currentSource = $currentSource == "" ? $detail['source'] : $currentSource;
                        $classList = $this->getClass($classList, $modelName);
                        if($sourceFound == 1 && ($params['source'] != $detail['source']))
                        {
                            continue;
                        }
                        if (!isset($functionList[$dataType])) {
                            if (isset($classList[$modelName])) {
                                if (method_exists($classList[$modelName], $priviliegeList[$dataType]['function'])) {
                                    //echo "class:".$modelName.",function:".$priviliegeList[$dataType]['function']." found\n";exit;
                                    $functionList[$dataType] = ["className" => $modelName, "class" => $classList[$modelName], "function" => $priviliegeList[$dataType]['function']];
                                    if (isset($priviliegeList[$dataType]['functionCount']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionCount'])) {
                                        $functionList[$dataType]['functionCount'] = $priviliegeList[$dataType]['functionCount'];
                                    } else {
                                        $functionList[$dataType]['functionCount'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionSingle']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionSingle'])) {
                                        $functionList[$dataType]['functionSingle'] = $priviliegeList[$dataType]['functionSingle'];
                                    } else {
                                        $functionList[$dataType]['functionSingle'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionUpdate']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionUpdate'])) {
                                        $functionList[$dataType]['functionUpdate'] = $priviliegeList[$dataType]['functionUpdate'];
                                    } else {
                                        $functionList[$dataType]['functionUpdate'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionProcess'])) {
                                        $functionList[$dataType]['functionProcess'] = $priviliegeList[$dataType]['functionProcess'];
                                    } else {
                                        $functionList[$dataType]['functionProcess'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionProcessCount'])) {
                                        $functionList[$dataType]['functionProcessCount'] = $priviliegeList[$dataType]['functionProcessCount'];
                                    } else {
                                        $functionList[$dataType]['functionProcessCount'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionSingleBySite'])) {
                                        $functionList[$dataType]['functionSingleBySite'] = $priviliegeList[$dataType]['functionSingleBySite'];
                                    } else {
                                        $functionList[$dataType]['functionSingleBySite'] = "";
                                    }
                                    $found = 1;
                                } else {
                                    echo "class:" . $modelName . ",function:" . $priviliegeList[$dataType]['function'] . " not found\n";
                                }
                            } else {
                                //echo "class:".$modelName.",not found\n";
                            }
                            $functionList[$dataType]['source'] = $currentSource;//$priviliegeList[$dataType]['source'];
                        }
                    }

                }
            }
            if ($found == 0) {
                //echo "dataType:".$dataType.",function not found\n";
            }
        }
        return $functionList;
    }

    public function getClass($classList, $modelClassName)
    {
        //判断类库存在
        $exist = class_exists($modelClassName);
        if (!$exist) {

        } else {
            //之前没有初始化过
            if (!isset($classList[$modelClassName])) {
                //初始化，存在列表中
                $modelClass = new $modelClassName;
                $classList[$modelClassName] = $modelClass;
            } else {
                ////直接调用
                //$modelClass = $classList[$modelClassName];
            }
        }
        return $classList;
    }

    public function processMatchList($data, $functionList,$params = [])
    {
        $intergrationService = new IntergrationService();
        //判断赛事
        if (isset($functionList['tournament']) && isset($functionList['tournament']['functionSingle'])) {

        } else {
            $f = $this->getFunction(['tournament' => []], $functionList['matchList']['source']);
            if (isset($f['tournament']['class'])) {
                $functionList["tournament"] = $f['tournament'];
            }
        }
        if (!isset($functionList["tournament"]["class"]) || !isset($functionList['tournament']['functionSingle'])) {
            // return $data;(没有跳过)
        }
        $modelTournamentClass = $functionList["tournament"]["class"];
        $functionTournamentSingle = $functionList["tournament"]['functionSingle'];
        //判断战队
        if (isset($functionList['teamList']) && isset($functionList['teamList']['functionSingle'])) {

        } else {
            $f = $this->getFunction(['totalTeamList' => []], $functionList['matchList']['source']);
            if (isset($f['totalTeamList']['class'])) {
                $functionList["totalTeamList"] = $f['totalTeamList'];
            }
        }
        if (!isset($functionList["totalTeamList"]["class"]) || !isset($functionList['totalTeamList']['functionSingleBySite'])) {
            //return $data;
        }
        $modelClass = $functionList["totalTeamList"]["class"];
        $functionSingle = $functionList["totalTeamList"]['functionSingleBySite'];

        //判断队员
        if (isset($functionList['totalPlayerInfo']) && isset($functionList['totalPlayerInfo']['functionSingleBySite'])) {

        } else {
            $f = $this->getFunction(['totalPlayerInfo' => []], $functionList['matchList']['source']);
            if (isset($f['totalPlayerInfo']['class'])) {
                $functionList["totalPlayerInfo"] = $f['totalPlayerInfo'];
            }
        }
        if (!isset($functionList["totalPlayerInfo"]["class"]) || !isset($functionList['totalPlayerInfo']['functionSingleBySite'])) {
            //return $data;
        }
        //判断队员
        if (isset($functionList['totalPlayerList']) && isset($functionList['totalPlayerList']['function'])) {

        } else {
            $f = $this->getFunction(['totalPlayerList' => []], $functionList['matchList']['source']);
            if (isset($f['totalPlayerList']['class'])) {
                $functionList["totalPlayerList"] = $f['totalPlayerList'];
            }
        }
        if (!isset($functionList["totalPlayerList"]["class"]) || !isset($functionList['totalPlayerList']['function'])) {
            //return $data;
        }
        $playerModelClass = $functionList["totalPlayerInfo"]["class"];
        $functionPlayerSingle = $functionList["totalPlayerInfo"]['functionSingleBySite'];

        $teamList = [];
        $playerList = [];
        $tournament = [];
        $teamMap = ["a"=>"home","b"=>"away"];
        foreach ($data as $key => $matchInfo)
        {
            //赛事信息
            if (!isset($tournament[$matchInfo['tournament_id']])) {
                $tournamentInfo = $modelTournamentClass->$functionTournamentSingle($matchInfo['tournament_id']);
                if (isset($tournamentInfo['tournament_id'])) {
                    $tournament[$matchInfo['tournament_id']] = $tournamentInfo;
                }
            }
            foreach($teamMap as $side => $color)
            {
                $data[$key][$color.'_player_id_list'] = [];
            }
            //战队信息
            if (!isset($teamList[$matchInfo['home_id']]))
            {
                $teamInfo = $modelClass->$functionSingle($matchInfo['home_id'],$functionList['matchList']['source'],$matchInfo['game']);
                if (isset($teamInfo['team_id']))
                {
                    if(isset($teamInfo['tid']) && $teamInfo['tid']>0)
                    {
                        $teamInfo = getFieldsFromArray($intergrationService->getTeamInfo(0,$teamInfo['tid'],1,0)['data'],"tid,team_name,logo,intergrated_id_list");
                    }
                    $teamList[$matchInfo['home_id']] = $teamInfo;
                }
            }
            if (!isset($teamList[$matchInfo['away_id']]))
            {
                $teamInfo = $modelClass->$functionSingle($matchInfo['away_id'],$functionList['matchList']['source'],$matchInfo['game']);
                if (isset($teamInfo['team_id']))
                {
                    if(isset($teamInfo['tid']) && $teamInfo['tid']>0)
                    {
                        $teamInfo = getFieldsFromArray($intergrationService->getTeamInfo(0,$teamInfo['tid'],1,0)['data'],"tid,team_name,logo,intergrated_id_list");
                    }
                    $teamList[$matchInfo['away_id']] = $teamInfo;
                }
            }
            $matchData = json_decode($matchInfo['match_data'],true);
            $playerIdList = [];
            if(is_array($matchData['result_list']))
            {
                foreach($teamMap as $side => $color)
                {
                    foreach($matchData['result_list'] as $round => $round_info)
                    {
                        if(isset($round_info['record_list_' . $side]))
                        {
                            $data[$key][$color.'_player_id_list'] = array_unique(array_merge($data[$key][$color.'_team_info']??[],array_column($round_info['record_list_' . $side],"playerID")));
                        }
                    }
                }
                $data[$key]['home_team_info'] = $teamList[$matchInfo['home_id']] ?? [];//战队
                $data[$key]['away_team_info'] = $teamList[$matchInfo['away_id']] ?? [];

                foreach($teamMap as $side => $color)
                {
                    foreach($data[$key][$color.'_player_id_list'] as $k => $player_id)
                    {
                        if (!isset($playerList[$player_id]))
                        {
                            $playerInfo = $playerModelClass->$functionPlayerSingle($player_id,$matchInfo['game'],$functionList['matchList']['source']);
                            if(isset($playerInfo['player_id']))
                            {
                                if(isset($playerInfo['pid']) && $playerInfo['pid']>0)
                                {
                                    $playerInfo = getFieldsFromArray($intergrationService->getPlayerInfo(0,$playerInfo['pid'],1,0)['data'],"pid,player_name,logo");
                                }
                                $playerList[$player_id] = $playerInfo;
                            }
                        }
                        if(isset($playerList[$player_id]))
                        {
                            $data[$key][$color.'_player_list'][] = $playerList[$player_id];
                        }
                    }
                    //如果没有对阵的队员
                    if(count($data[$key][$color.'_player_list']??[])==0)
                    {
                        $teamId = $data[$key][$color.'_team_info']['intergrated_id_list']??$data[$key][$color.'_id'];
                        $functionPlayerList =$functionList["totalPlayerList"]["function"];
                        $playerList = $functionList["totalPlayerList"]["class"]->$functionPlayerList(['team_ids'=>$teamId,"pageSize"=>999]);
                        foreach($playerList as $player)
                        {
                            if($player['pid']>0)
                            {
                                $data[$key][$color.'_player_id_list'][] = $player['player_id'];
                                $data[$key][$color.'_player_list'][] = getFieldsFromArray($intergrationService->getPlayerInfo(0,$player['pid'],1,0)['data'],"pid,player_name,logo");
                                $countPlayer = count($data[$key][$color.'_player_list']);
                                if($countPlayer>5)
                                {
                                    break;
                                }
                            }
                        }
                    }
                }
                $data[$key]['game_count'] = $matchData['game_count'];
            }
            $data[$key]['tournament_info'] = $tournament[$matchInfo['tournament_id']] ?? [];
            unset($data[$key]['match_data']);
            if(isset($params['pid']))
            {
                $home = in_array($params['pid'],array_column($data[$key]['home_player_list']??[],"pid"))?1:0;
                $away = in_array($params['pid'],array_column($data[$key]['away_player_list']??[],"pid"))?1:0;
                if(($home+$away)==0)
                {
                    unset($data[$key]);
                }
                else
                {
                    $playerDetail = [];
                    if(isset($matchData['result_list']) && count($matchData['result_list'])>0)
                    {
                        foreach($matchData['result_list'] as $r_key => $result)
                        {
                            $currentKey = "";
                            if(isset($result['detail']))
                            {
                                foreach($result['detail']['result_list'] as $result_key => $value)
                                {
                                    if(in_array($value,$params['player_id']) && substr($result_key,-9)=="_playerID")
                                    {
                                        $currentKey = $result_key;
                                    }
                                }
                                if($currentKey !="")
                                {
                                    $t = explode("_",$currentKey);
                                    foreach($result['detail']['result_list'] as $result_key => $value)
                                    {
                                        if(substr($result_key,0,strlen($t['0']))==$t['0'] && (strpos($result_key,"_".$t[2]."_")>0))
                                        {
                                            $playerDetail[$r_key][$result_key] = $value;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $data[$key]['player_detail'] = ($playerDetail);
                }
            }
        }
        return $data;
    }
    public function processMatch($data, $functionList)
    {
        $intergrationService = new IntergrationService();
        //判断赛事
        if (isset($functionList['tournament']) && isset($functionList['tournament']['functionSingle'])) {

        } else {
            $f = $this->getFunction(['tournament' => []], $functionList['matchDetail']['source']);
            if (isset($f['tournament']['class'])) {
                $functionList["tournament"] = $f['tournament'];
            }
        }
        if (!isset($functionList["tournament"]["class"]) || !isset($functionList['tournament']['functionSingle'])) {
            // return $data;(没有跳过)
        }
        $modelTournamentClass = $functionList["tournament"]["class"];
        $functionTournamentSingle = $functionList["tournament"]['functionSingle'];
        //判断战队
        if (isset($functionList['totalTeamInfo']) && isset($functionList['totalTeamInfo']['functionSingle'])) {

        } else {
            $f = $this->getFunction(['totalTeamInfo' => []], $functionList['matchDetail']['source']);
            if (isset($f['totalTeamInfo']['class'])) {
                $functionList["totalTeamInfo"] = $f['totalTeamInfo'];
            }
        }
        if (!isset($functionList["totalTeamInfo"]["class"]) || !isset($functionList['totalTeamInfo']['functionSingleBySite'])) {
            return $data;
        }
        //判断队员
        if (isset($functionList['totalPlayerInfo']) && isset($functionList['totalPlayerInfo']['functionSingleBySite'])) {

        } else {
            $f = $this->getFunction(['totalPlayerInfo' => []], $functionList['matchDetail']['source']);
            if (isset($f['totalPlayerInfo']['class'])) {
                $functionList["totalPlayerInfo"] = $f['totalPlayerInfo'];
            }
        }
        if (!isset($functionList["totalPlayerInfo"]["class"]) || !isset($functionList['totalPlayerInfo']['functionSingleBySite'])) {
            return $data;
        }
        $modelClass = $functionList["totalTeamInfo"]["class"];
        $functionSingle = $functionList["totalTeamInfo"]['functionSingleBySite'];
        $teamList = [];
        $tournament = [];
        //赛事信息
        if (!isset($tournament[$data['tournament_id']])) {
            $tournamentInfo = $modelTournamentClass->$functionTournamentSingle($data['tournament_id']);
            if (isset($tournamentInfo['tournament_id'])) {
                $tournament[$data['tournament_id']] = $tournamentInfo;
            }
        }
        //战队信息
        if (!isset($teamList[$data['home_id']]))
        {
            $teamInfo = $modelClass->$functionSingle($data['home_id'],$functionList['matchDetail']['source'],"","team_id,tid,team_name,logo,description");
            if (isset($teamInfo['team_id']))
            {
                $teamList[$data['home_id']] = $teamInfo;
            }
        }
        if (!isset($teamList[$data['away_id']]))
        {
            $teamInfo = $modelClass->$functionSingle($data['away_id'],$functionList['matchDetail']['source'],"","team_id,tid,team_name,logo,description");
            if (isset($teamInfo['team_id']))
            {
                $teamList[$data['away_id']] = $teamInfo;
            }
        }
        $data['home_team_info'] = $teamList[$data['home_id']] ?? [];//战队
        $data['away_team_info'] = $teamList[$data['away_id']] ?? [];
        $data['tournament_info'] = $tournament[$data['tournament_id']] ?? [];
        if(isset($data['home_team_info']['tid']) && $data['home_team_info']['tid']>0)
        {
            $data['home_team_info'] = getFieldsFromArray($intergrationService->getTeamInfo(0,$data['home_team_info']['tid'],1,0)['data'],"tid,team_name,description,logo");
        }
        if(isset($data['away_team_info']['tid']) && $data['away_team_info']['tid']>0)
        {
            $data['away_team_info'] = getFieldsFromArray($intergrationService->getTeamInfo(0,$data['away_team_info']['tid'],1,0)['data'],"tid,team_name,description,logo");
        }
        $playerList = [];
        //处理比赛数据
        if(isset($data['match_data']))
        {
            $oPlayerModel = $functionList["totalPlayerInfo"]["class"];
            $oPlayerFunction = $functionList["totalPlayerInfo"]["functionSingleBySite"];
            $data['match_data'] = json_decode($data['match_data'],true);
            if(isset($data['match_data']['result_list']) && count($data['match_data']['result_list'])>0)
            {
                foreach($data['match_data']['result_list'] as $key => $result)
                {
                    unset($data['match_data']['result_list'][$key]['team_a_image_thumb']);
                    unset($data['match_data']['result_list'][$key]['team_b_image_thumb']);
                    //主客队和颜色的映射
                    $teamMap = ["a"=>"blue","b"=>"red"];
                    $keyMap = ["a","b","c","d","e","f","g"];
                    foreach($teamMap as $side => $color)
                    {
                        if(isset($result['detail']))
                        {
                            $data['match_data']['result_list'][$key]['dragon_list'] = $result['detail']['dragon_list']??[];
                            foreach($result['detail']['result_list'] as $key_b => $value_b)
                            {
                                foreach($keyMap as $k => $c)
                                {
                                    if (!is_array($value_b) && (substr($key_b, 0, strlen($color) + 1) == $color . "_") && strpos($key_b, "_" . $c . "_") > 0)
                                    {
                                        $new_key = str_replace([$color . "_", "_" . $c . "_"], "_", $key_b);
                                        // echo $key_b . "-" . $new_key . "-" . $value_b . "\n";
                                        $data['match_data']['result_list'][$key]['record_list_' . $side][$k][$new_key] = $value_b;
                                        unset($result['detail']['result_list'][$key_b]);
                                        unset($data['match_data']['result_list'][$key]['detail']['result_list'][$key_b]);
                                    }
                                }
                            }
                            foreach($result['detail']['result_list'] as $key_b => $value_b)
                            {
                                if(!is_array($value_b))
                                {
                                    $data['match_data']['result_list'][$key][$key_b] = $value_b;
                                }
                                unset($data['match_data']['result_list'][$key]['detail']);
                            }
                        }
                        foreach($result['record_list_'.$side] as $key_a => $player)
                        {
                            if(!isset($playerList[$player['playerID']]))
                            {

                                $playerInfo = $oPlayerModel->$oPlayerFunction($player['playerID'],$data['game'],$functionList['matchDetail']['source']);
                               // print_R($playerInfo);
                                //die();
                                if(isset($playerInfo['tid']) && $playerInfo['tid']>0)
                                {
                                //    print_R($intergrationService->getPlayerInfo(0,$playerInfo['tid'],1,0));
                                    $playerInfo = getFieldsFromArray($intergrationService->getPlayerInfo(0,$playerInfo['tid'],1,0)['data'],"pid,player_name,logo");
                                    $playerList[$player['playerID']] = $playerInfo;
                                }
                                else
                                {
                                    $playerList[$player['playerID']] = $playerInfo;
                                }
                            }
                            unset($data['match_data']['result_list'][$key]['record_list_'.$side][$key_a]['player_image_thumb']);
                            $data['match_data']['result_list'][$key]['record_list_'.$side][$key_a]['logo'] = $playerList[$player['playerID']]['logo']??"";
                            $data['match_data']['result_list'][$key]['record_list_'.$side][$key_a]['player_name'] = $playerList[$player['playerID']]['player_name']??"";
                        }
                    }
                }
            }
        }
        return $data;
    }
    public function processTournament($data, $functionList)
    {
        if (isset($functionList['roundList']) && isset($functionList['roundList']['functionSingle'])) {

        } else {
            $f = $this->getFunction(['roundList' => []], $functionList['tournament']['source']);
            if (isset($f['roundList']['class'])) {
                $functionList["roundList"] = $f['roundList'];
            }
        }
        if (isset($functionList['matchList']) && isset($functionList['matchList']['functionSingle'])) {

        } else {
            $f = $this->getFunction(['matchList' => []], $functionList['tournament']['source']);
            if (isset($f['matchList']['class'])) {
                $functionList["matchList"] = $f['matchList'];
            }
        }
        if (isset($functionList['totalTeamInfo']) && isset($functionList['totalTeamInfo']['functionSingle'])) {

        } else {
            $f = $this->getFunction(['totalTeamInfo' => []], $functionList['tournament']['source']);
            if (isset($f['totalTeamInfo']['class'])) {
                $functionList["totalTeamInfo"] = $f['totalTeamInfo'];
            }
        }
        $modelClass = $functionList["roundList"]["class"];
        $function = $functionList["roundList"]['function'];
        $matchModelClass = $functionList["matchList"]["class"];
        $matchFunction = $functionList["matchList"]['function'];
        $teamModelClass = $functionList["totalTeamInfo"]["class"];
        $teamFunction = $functionList["totalTeamInfo"]['functionSingleBySite'];
        $matchList = $matchModelClass->$matchFunction(['tournament_id'=>$data['tournament_id'],"page_size"=>1000,"fields"=>"match_id,home_id,away_id"]);
        $teamIdList = array_unique(array_merge(array_column($matchList,"home_id"),array_column($matchList,"away_id")));
        $intergrationService = new IntergrationService();
        $teamList = [];
        foreach($teamIdList as $team_id)
        {
            $teamInfo = $teamModelClass->$teamFunction($team_id,$functionList['tournament']['source'],$data['game'],"team_id,tid");
            if(isset($teamInfo['tid']) && $teamInfo['tid']>0)
            {
                $teamInfo = $intergrationService->getTeamInfo(0,$teamInfo['tid'],1,0);
                if(isset($teamInfo['data']['tid']))
                {
                    $teamList[$teamInfo['data']['tid']] = getFieldsFromArray($teamInfo['data'],"tid,logo,team_name,intergrated_id_list");
                }
            }
        }
        $data['teamList'] = array_values($teamList);
        $data['roundList'] = array_values($modelClass->$function(["tournament_id"=>$data['tournament_id']]));
        return $data;
    }
    public function processPlayerList($data, $functionList)
    {
        if (isset($functionList['playerList']) && isset($functionList['playerList']['functionSingle'])) {

        } else {
            $f = $this->getFunction(['playerList' => []], $functionList['playerList']['source']);
            if (isset($f['playerList']['class'])) {
                $functionList["playerList"] = $f['playerList'];
            }
        }
        $modelClass = $functionList["playerList"]["class"];
        $functionSingle = $functionList["playerList"]['functionSingle'];
        $teamInfo=[];
        if(!empty($data)){
            foreach ($data as $key=>&$val){
                if(!$val['player_name']){
                    unset($data[$key]);
                }
                $team_id=$val['team_id'] ?? '';
                if($team_id){
                    $teamInfo = $modelClass->$functionSingle($val['team_id']);
                }
                $val['team_info']=$teamInfo;
                if(isset($val['team_history'])){
                    $val['team_history']=json_decode($val['team_history'],true);
                }
                if(isset($val['event_history'])){
                    $val['event_history']=json_decode($val['event_history'],true);
                }
                if(isset($val['stat'])){
                    $val['stat']=json_decode($val['stat'],true);
                }
            }
        }
        if($data){
            $data=array_values($data);
        }
        return $data;
    }
    public function processLolHero($data, $functionList)
    {
        $data['skinList'] = [];
        $data['spellList'] = [];
        if (isset($functionList['lolHeroSkin']) && isset($functionList['lolHeroSkin']['function'])) {

        } else {
            $f = $this->getFunction(['lolHeroSkin' => []]);
            if (isset($f['lolHeroSkin']['class'])) {
                $functionList["lolHeroSkin"] = $f['lolHeroSkin'];
            }
        }
        $modelClass = $functionList["lolHeroSkin"]["class"];
        $function = $functionList["lolHeroSkin"]['function'];
        $teamInfo=[];
        if(!empty($data)){
            $data['skinList'] = $modelClass->$function(["hero_id"=>$data['id']]);
        }
        if (isset($functionList['lolHeroSpell']) && isset($functionList['lolHeroSpell']['function'])) {

        } else {
            $f = $this->getFunction(['lolHeroSpell' => []]);
            if (isset($f['lolHeroSpell']['class'])) {
                $functionList["lolHeroSpell"] = $f['lolHeroSpell'];
            }
        }
        $modelClass = $functionList["lolHeroSpell"]["class"];
        $function = $functionList["lolHeroSpell"]['function'];
        $teamInfo=[];
        if(!empty($data)){
            $data['spellList'] = $modelClass->$function(["hero_id"=>$data['id']]);
        }
        return $data;
    }
    public function processKplHero($data, $functionList)
    {
        if(isset($data['aka'])){
            $data['aka']=json_decode($data['aka'],true);
        }
        if(isset($data['stat'])){
            $data['stat']=json_decode($data['stat'],true);
        }
        if(isset($data['skin_list'])){
            $data['skin_list']=json_decode($data['skin_list'],true);
        }
        if(isset($data['skill_list'])){
            $data['skill_list']=json_decode($data['skill_list'],true);
        }
        if(isset($data['inscription_tips'])){
            $data['inscription_tips']=json_decode($data['inscription_tips'],true);
            if(!empty($data['inscription_tips'])){
                if (isset($functionList['kplInscription']) && isset($functionList['kplInscription']['function'])) {

                } else {
                    $f = $this->getFunction(['kplInscription' => []]);
                    if (isset($f['kplInscription']['class'])) {
                        $modelClass = $f['kplInscription']['class'] ?? '';
                        if(isset($data['inscription_tips']['sugglistIds']) && $data['inscription_tips']['sugglistIds']){
                            if(strpos($data['inscription_tips']['sugglistIds'],'|') !==false){
                                $inscriptionId=explode('|',$data['inscription_tips']['sugglistIds']);
                            }else{
                                $inscriptionId[]=$data['inscription_tips']['sugglistIds'];
                            }
                            $inscriptionInfo=$modelClass->getInscriptionByIds($inscriptionId);
                            $data['inscription_tips']['sugglistIds']=$inscriptionInfo;
                        }
                    }
                }
            }
        }
        if(isset($data['skill_tips'])){
            $data['skill_tips']=json_decode($data['skill_tips'],true);
        }
        //英雄关系
        if(isset($data['hero_tips'])){
            $data['hero_tips']=json_decode($data['hero_tips'],true);
            if(!empty($data['hero_tips'])){
                $modelClass = $functionList["kplHero"]["class"] ?? '';
                $function = $functionList["kplHero"]['function'] ?? '';

                if(isset($data['hero_tips']['mate']) && $data['hero_tips']['mate']){//最佳搭档
                   foreach ($data['hero_tips']['mate'] as &$val){
                       $heroInfo=$modelClass->getHeroInfoById($val['id']);
                       $val['hero_name']=$heroInfo['hero_name'] ?? '';
                       $val['logo']=$heroInfo['logo'] ?? '';

                   }
                }
                if(isset($data['hero_tips']['suppress']) && $data['hero_tips']['suppress']){//被压制英雄
                    foreach ($data['hero_tips']['suppress'] as &$val){
                        $heroInfo=$modelClass->getHeroInfoById($val['id']);
                        $val['hero_name']=$heroInfo['hero_name'] ?? '';
                        $val['logo']=$heroInfo['logo'] ?? '';
                    }

                }
                if(isset($data['hero_tips']['suppressed']) && $data['hero_tips']['suppressed']){//压制英雄
                    foreach ($data['hero_tips']['suppressed'] as &$val){
                        $heroInfo=$modelClass->getHeroInfoById($val['id']);
                        $val['hero_name']=$heroInfo['hero_name'] ?? '';
                        $val['logo']=$heroInfo['logo'] ?? '';
                    }
                }

            }
        }
        //英雄-关联装备
        if(isset($data['equipment_tips'])){
            $data['equipment_tips']=json_decode($data['equipment_tips'],true);
            if(!empty($data['equipment_tips'])){
                if (isset($functionList['kplEquipment']) && isset($functionList['kplEquipment']['function'])) {

                } else {
                    $f = $this->getFunction(['kplEquipment' => []]);
                    if (isset($f['kplEquipment']['class'])) {
                        $modelClass = $f['kplEquipment']['class'] ?? '';
                        foreach ($data['equipment_tips'] as &$val){
                            if($val['equipItemIds']){
                                if(strpos($val['equipItemIds'],'|') !==false){
                                    $equipItemIds=explode('|',$val['equipItemIds']);
                                }else{
                                    $equipItemIds[]=$val['equipItemIds'];
                                }
                                $equipmentInfo=$modelClass->getEquipmentByIds($equipItemIds);
                                $val['equipment_tips']=$equipmentInfo;
                            }

                        }

                    }
                }
            }

        }
        //召唤师技能
        if(isset($data['summoner_skill'])){
            $data['summoner_skill']=json_decode($data['summoner_skill'],true);
            if(!empty($data['summoner_skill'])){
                if (isset($functionList['kplSummoner']) && isset($functionList['kplSummoner']['function'])) {

                } else {
                    $f = $this->getFunction(['kplSummoner' => []]);
                    if (isset($f['kplSummoner']['class'])) {
                        $modelClass = $f['kplSummoner']['class'] ?? '';
                        if(isset($data['summoner_skill']['summonerSkillId']) && $data['summoner_skill']['summonerSkillId']){
                            if(strpos($data['summoner_skill']['summonerSkillId'],'|') !==false){
                                $summonerSkillId=explode('|',$data['summoner_skill']['summonerSkillId']);
                            }else{
                                $summonerSkillId[]=$data['summoner_skill']['summonerSkillId'];
                            }
                            $summonerInfo=$modelClass->getSkillByIds($summonerSkillId);
                            $data['summoner_skill']=$summonerInfo;
                        }
                    }
                }
            }
        }
        $kpl_hero_type=$this->kpl_hero_type;
        $data['type_name']=$kpl_hero_type[$data['type']] ?? '';
        return $data;
    }
    public function processDota2Hero($data, $functionList)
    {
        $className = 'App\Collect\\hero\\dota2\\gamedota2';
        $collectModel = new $className;
        $hero_type = $collectModel->hero_type;
        $attack_type = $collectModel->attack_type;
        $role_type = $collectModel->role_type;
        $data['roles'] = json_decode($data['roles'],true);
        foreach($data["roles"] as $key => $role)
        {
            $data["roles"][$key] = $role_type[$role];
        }
        $data['hero_type'] = $hero_type[$data['hero_type']];
        $data['attack_type'] = $attack_type[$data['attack_type']];
        $data['roles'] = json_encode($data['roles']);
        return $data;
    }
    public function processTeam($data, $functionList)
    {
        if (isset($functionList['totalTeamInfo']) && isset($functionList['totalTeamInfo']['function'])) {

        } else {
            $f = $this->getFunction(['totalTeamInfo' => []]);
            if (isset($f['totalTeamInfo']['class'])) {
                $functionList["totalTeamInfo"] = $f['totalTeamInfo'];
            }
        }
        $modelClass = $functionList["totalTeamInfo"]["class"];
        $function = $functionList["totalTeamInfo"]['function'];
        $teamInfo=[];
        if(!empty($data)){
            $data['totalTeamInfo'] = $modelClass->getTeamByName($data['team_name'],$data['game']);
        }
        if(isset($data['totalTeamInfo']['team_id']))
        {
            if (isset($functionList['playerList']) && isset($functionList['playerList']['function'])) {

            } else {
                $f = $this->getFunction(['playerList' => []]);
                if (isset($f['playerList']['class'])) {
                    $functionList["playerList"] = $f['playerList'];
                }
            }
            $modelClass = $functionList["playerList"]["class"];
            $function = $functionList["playerList"]['function'];
            if(!empty($data)){
                $data['playerList'] = $modelClass->$function(['team_id'=>$data['totalTeamInfo']['site_id']]);
            }

            if (isset($functionList['matchList']) && isset($functionList['matchList']['function'])) {

            } else {
                $f = $this->getFunction(['matchList' => []]);
                if (isset($f['matchList']['class'])) {
                    $functionList["matchList"] = $f['matchList'];
                }
            }
            $modelClass = $functionList["matchList"]["class"];
            $function = $functionList["matchList"]['function'];
            if(!empty($data)){
                $data['matchList'] = $modelClass->$function(['team_id'=>$data['team_id'],'page_size'=>10]);
            }
        }
        return $data;
    }
    public function processTotalTeam($data, $functionList)
    {
        if(isset($data['team_id']))
        {
            $f = $this->getFunction(['totalPlayerList' => []]);
            if (isset($f['totalPlayerList']['class'])) {
                $functionList["totalPlayerList"] = $f['totalPlayerList'];
            }
            $modelClass = $functionList["totalPlayerList"]["class"];
            $function = $functionList["totalPlayerList"]['function'];
            $data['playerList'] = $modelClass->$function(['team_id'=>$data['team_id'],"page_size"=>100]);
        }
        return $data;
    }
    public function processInformationList($data, $functionList)
    {
        foreach($data as $key => $value)
        {
            if(isset($value['content']))
            {
                $data[$key]['content'] = string_split(strip_tags(html_entity_decode($value['content'])),100);
            }
        }
        return $data;
    }
    public function processInformation($data, $functionList)
    {
        if(isset($data['scws_list'])) {
            $data['scws_list'] = json_decode($data['scws_list'], true);

            if (isset($functionList['scwsKeyword']) && isset($functionList['scwsKeyword']['function']))
            {}
            else
            {
                $f = $this->getFunction(['scwsKeyword' => []]);
                if (isset($f['scwsKeyword']['class']))
                {
                    $functionList["scwsKeyword"] = $f['scwsKeyword'];
                }
            }
            $modelClass = $functionList["scwsKeyword"]["class"];
            //$function = $functionList["scwsKeyword"]['functionProcess'];
            $disableKeywordList = $modelClass->getDisableList();
            foreach($data['scws_list'] as $key => $word)
            {
                if(in_array($word['keyword_id'],$disableKeywordList))
                {
                    unset($data['scws_list'][$key]);
                }
            }
            $data['scws_list'] = json_encode($data['scws_list']);
            return $data;
        }
    }
    public function processTotalPlayer($data, $functionList)
    {
        if(isset($data['player_id']))
        {
            if (isset($functionList['totalPlayerList']) && isset($functionList['totalPlayerList']['function'])) {}else {
                $f = $this->getFunction(['totalPlayerList' => []]);
                if (isset($f['totalPlayerList']['class'])) {
                    $functionList["totalPlayerList"] = $f['totalPlayerList'];
                }
            }
            if (isset($functionList['totalTeamInfo']) && isset($functionList['totalTeamInfo']['function'])) {}else{
                $f = $this->getFunction(["totalTeamInfo"=>[]]);
                if (isset($f['totalTeamInfo']['class'])) {
                    $functionList["totalTeamInfo"] = $f['totalTeamInfo'];
                }
            }
            $modelClass = $functionList["totalPlayerList"]["class"];
            $function = $functionList["totalPlayerList"]['function'];
            $teamModelClass = $functionList["totalTeamInfo"]["class"];
            $teamFunction = $functionList["totalTeamInfo"]['function'];
            $data['playerList'] = $modelClass->$function(['team_id'=>$data['team_id'],"page_size"=>100]);
            foreach($data['playerList'] as $key => $playerInfo)
            {
                if($playerInfo['player_id']==$data['player_id'])
                {
                    unset($data['playerList'][$key]);
                    break;
                }
            }
            $data['teamInfo'] = $teamModelClass->$teamFunction(['team_id'=>$data['team_id'],"fields"=>"team_id,team_name,description,logo"]);
        }
        return $data;
    }
    public function processTotalPlayerList($data, $functionList)
    {
            //print_R($data);die();
            if (isset($functionList['totalTeamInfo']) && isset($functionList['totalTeamInfo']['function'])) {}else{
                $f = $this->getFunction(["totalTeamInfo"=>[]]);
                if (isset($f['totalTeamInfo']['class'])) {
                    $functionList["totalTeamInfo"] = $f['totalTeamInfo'];
                }
            }
            $teamModelClass = $functionList["totalTeamInfo"]["class"];
            $teamFunction = $functionList["totalTeamInfo"]['function'];
            foreach($data as $key => $player)
            {
                if(isset($player['team_id']))
                {
                    $teamInfo = $teamModelClass->$teamFunction($player['team_id'],"team_name,team_id");
                    if(isset($teamInfo['team_id']))
                    {
                        $data[$key]['team_info'] = $teamInfo;
                    }
                }
            }
        return $data;
    }
    public function processScwsInformationList($data, $functionList)
    {
        if (isset($functionList['information']) && isset($functionList['information']['function'])) {

        } else {
            $f = $this->getFunction(['information' => []]);
            if (isset($f['information']['class'])) {
                $functionList["information"] = $f['information'];
            }
        }
        $modelClass = $functionList["information"]["class"];
        $function = $functionList["information"]['function'];
        foreach($data as $key => $value)
        {
            $information = $modelClass->$function($value['content_id'],["id","title","logo","create_time","site_time","content","type"]);
            if(isset($information['id']))
            {
                $information['content'] = string_split(strip_tags($information['content']),100);
                $data[$key]['content'] = $information;
            }
        }
        return $data;
    }
    public function process5118InformationList($data, $functionList)
    {
        if (isset($functionList['information']) && isset($functionList['information']['function'])) {

        } else {
            $f = $this->getFunction(['information' => []]);
            if (isset($f['information']['class'])) {
                $functionList["information"] = $f['information'];
            }
        }
        $modelClass = $functionList["information"]["class"];
        $function = $functionList["information"]['function'];
        foreach($data as $key => $value)
        {
            $information = $modelClass->$function($value['content_id'],["id","title","logo","create_time","site_time","content","type"]);
            if(isset($information['id']))
            {
                $information['content'] = string_split(strip_tags($information['content']),100);
                $data[$key]['content'] = $information;
            }
        }
        return $data;
    }
    public function processkeywordMapList($data, $functionList,$params)
    {
        if(isset($params['list']))
        {
            if(count($data)>0)
            {
                $ids = array_column($data,"content_id");
                //获取文章
                if($params['content_type'] == "information")
                {
                    $p = array_merge($params['list'],['ids'=>$ids]);
                    $functionList = $this->checkFunction($functionList,"informationList");
                    $modelClass = $functionList["informationList"]["class"];
                    $function = $functionList["informationList"]['function'];
                    $data = $modelClass->$function($p);
                }
            }
            else
            {
                $data = [];
            }
        }
        return $data;
    }
    public function processkeywordMapCount($data, $functionList,$params)
    {
        if(isset($params['list']))
        {
            if(count($data)>0)
            {
                $ids = array_column($data,"content_id");
                //获取文章
                if($params['content_type'] == "information")
                {
                    $p = array_merge($params['list'],['ids'=>$ids]);
                    $functionList = $this->checkFunction($functionList,"informationList");
                    $modelClass = $functionList["informationList"]["class"];
                    $function = $functionList["informationList"]['functionCount'];
                    $data = $modelClass->$function($p);
                }
            }
            else
            {
                $data = 0;
            }
        }
        return $data;
    }
    //检查方法列表
    public function checkFunction($functionList,$type)
    {
        if (isset($functionList[$type]) && isset($functionList[$type]['function'])) {

        } else {
            $f = $this->getFunction([$type => []]);
            if (isset($f[$type]['class'])) {
                $functionList[$type] = $f[$type];
            }
        }
        return $functionList;
    }
    public function processIntergratedTeam($data, $functionList,$params)
    {
        if(isset($data['tid']) && $data['tid']>0)
        {
            $intergrationService = (new IntergrationService());
            $data = $intergrationService->getTeamInfo(0,$data["tid"],1,$params['reset']??0)['data'];
            $f = $this->getFunction(['totalPlayerList' => []]);
            if (isset($f['totalPlayerList']['class'])) {
                $functionList["totalPlayerList"] = $f['totalPlayerList'];
            }
            $f = $this->getFunction(['matchList' => []],$params['source']??"scoregg");
            if (isset($f['matchList']['class'])) {
                $functionList["matchList"] = $f['matchList'];
            }
            $data['recentMatchList'] = [];
            $data['playerList'] = [];
            $modelClass = $functionList["totalPlayerList"]["class"];
            $function = $functionList["totalPlayerList"]['function'];
            $sourceList = config('app.intergration.player');
            if(count($data['intergrated_id_list']))
            {
                $pidList = $modelClass->$function(['team_ids'=>$data['intergrated_id_list'],"sources"=>array_column($sourceList,"source"),"fields"=>"player_id,pid","page_size"=>100]);
                $pidList = array_unique(array_column($pidList,"pid"));
                foreach($pidList as $pid)
                {
                    if($pid>0)
                    {
                        $playerInfo = $intergrationService->getPlayerInfo(0,$pid,1,$params['reset']??0);
                        if(strlen($playerInfo['data']['logo'])>=10)
                        {
                            $data['playerList'][] = getFieldsFromArray($playerInfo['data']??[],"pid,player_name,logo,position");
                        }
                    }
                }
                $modelMatchList = $functionList["matchList"]["class"];
                $functionMatchList = $functionList["matchList"]["function"];
                $functionProcessMatchList = $functionList["matchList"]["functionProcess"];
                $matchList = $modelMatchList->$functionMatchList(["team_id"=>$data['intergrated_site_id_list'],"page_size"=>4]);
                $data['recentMatchList'] = $this->$functionProcessMatchList($matchList, $functionList);
            }
            else
            {
                $data['playerList'] = [];
                $data['recentMatchList'] = [];
            }

        }
        else
        {
            $data = [];
        }
        return $data;
    }
    public function processIntergratedTeamList($data, $functionList,$params)
    {
        $intergrationService = (new IntergrationService());
        foreach($data as $key => $detailData)
        {
            if($detailData['tid']>0)
            {
                $data[$key] = getFieldsFromArray($intergrationService->getTeamInfo(0,$detailData["tid"],1,$params['reset']??0)['data'],$params['fields']??"*");
                if($data[$key]['team_name']==0)
                {
                    $data[$key] = getFieldsFromArray($intergrationService->getTeamInfo(0,$detailData["tid"],1,1)['data'],$params['fields']??"*");
                }
            }
            else
            {
                $data[$key] = [];
            }
        }
        return $data;
    }
    public function processIntergratedPlayerList($data, $functionList,$params)
    {
        $intergrationService = (new IntergrationService());
        foreach($data as $key => $detailData)
        {
            if($detailData['pid']>0)
            {
                $data[$key] = getFieldsFromArray($intergrationService->getPlayerInfo(0,$detailData["pid"],1,$params['reset']??0)['data'],$params['fields']??"*");
                if($data[$key]['player_name']==0)
                {
                    $data[$key] = getFieldsFromArray($intergrationService->getPlayerInfo(0,$detailData["pid"],1,1)['data'],$params['fields']??"*");
                }
            }
            else
            {
                $data[$key] = [];
            }
        }
        return $data;
    }
    public function processIntergratedPlayer($data, $functionList,$params)
    {
        if($data['pid']>0)
        {
            $intergrationService = (new IntergrationService());
            $data = $intergrationService->getPlayerInfo(0,$data["pid"],1,$params['reset']??0)['data'];
            $ingergratedTeam = $intergrationService->getTeamInfo($data['team_id'],0,1,$params['reset']??0)['data'];
            $f = $this->getFunction(['totalPlayerList' => []]);
            if (isset($f['totalPlayerList']['class'])) {
                $functionList["totalPlayerList"] = $f['totalPlayerList'];
            }
            $sourceList = config('app.intergration.player');
            $f = $this->getFunction(['matchList' => []],$params['source']??"scoregg");
            if (isset($f['matchList']['class'])) {
                $functionList["matchList"] = $f['matchList'];
            }
            $data['recentMatchList'] = [];
            $data['playerList'] = [];
            $data['teamInfo'] = $ingergratedTeam;
            $modelClass = $functionList["totalPlayerList"]["class"];
            $function = $functionList["totalPlayerList"]['function'];
            $pidList = $modelClass->$function(["sources"=>array_column($sourceList,"source"),'except_pid'=>$data["pid"],'team_ids'=>$ingergratedTeam['intergrated_id_list'],"fields"=>"player_id,pid","page_size"=>100]);
            $pidList = array_unique(array_column($pidList,"pid"));
            foreach($pidList as $pid)
            {
                if($pid>0)
                {
                    $data['playerList'][] = getFieldsFromArray($intergrationService->getPlayerInfo(0,$pid,1,$params['reset']??0)['data']??[],"pid,player_name,logo,position");
                }
            }
            $radarData = [];
            $radarArray = ['kill'=>"击杀",'assists'=>"助攻",'join_rate'=>"参团率",'visual_field'=>"视野",'survival'=>'生存','economy'=>'经济'];
            foreach($radarArray as $key => $name)
            {
                $radarData[$key]=["name"=>$name,"empno"=>intval(rand(40,100))];
            }
            $modelMatchList = $functionList["matchList"]["class"];
            $functionMatchList = $functionList["matchList"]["function"];
            $functionProcessMatchList = $functionList["matchList"]["functionProcess"];
            $matchList = $modelMatchList->$functionMatchList(["team_id"=>$ingergratedTeam['intergrated_site_id_list'],"page_size"=>6]);
            $data['recentMatchList'] = $this->$functionProcessMatchList($matchList, $functionList,["pid"=>$data["pid"],"player_id"=>$data['intergrated_site_id_list']]);
            $data['radarData']=$radarData;
        }
        else
        {
            $data = [];
        }
        return $data;
    }

}
