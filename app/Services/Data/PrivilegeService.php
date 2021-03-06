<?php

namespace App\Services\Data;

use App\Collect\hero\dota2\gamedota2;
use App\Models\PlayerModel;
use function AlibabaCloud\Client\json;

class PrivilegeService
{
    //public $id_map = ["teamList"=>["dota2"=>""]];
    public $kpl_hero_type = [
        1 => '战士',
        2 => '法师',
        3 => '坦克',
        4 => '刺客',
        5 => '射手',
        6 => '辅助',
        10 => '限免',
        11 => '新手'
    ];

    //获取各个数据类型对应的类库优先级列表以及获取方法
    public function getPriviliege()
    {
        $privilegeList = [
            "intergratedTeam" =>
                [
                    'list' => [
                        ['model' => 'App\Models\Team\TotalTeamModel', 'source' => ''],
                    ],
                    'withSource' => 0,
                    'function' => "getTeamById",
                    'functionSingle' => "getTeamById",
                    'functionProcess' => "processIntergratedTeam",
                ],
            "intergratedTeamList" =>
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
            "intergratedPlayer" =>
                [
                    'list' => [
                        ['model' => 'App\Models\Player\TotalPlayerModel', 'source' => ''],
                    ],
                    'withSource' => 0,
                    'function' => "getPlayerById",
                    'functionSingle' => "getPlayerById",
                    'functionProcess' => "processIntergratedPlayer",
                ],
            "intergratedPlayerList" =>
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
            "intergratedPlayerListByPlayer" =>
                [
                    'list' => [
                        ['model' => 'App\Models\PlayerModel', 'source' => ''],
                    ],
                    'withSource' => 0,
                    'function' => "getPlayerList",
                    'functionCount' => "getPlayerCount",
                    'functionSingle' => "getPlayerById",
                    'functionProcess' => "processIntergratedPlayerListByPlayer",
                ],
            "intergratedTeamListByTeam" =>
                [
                    'list' => [
                        ['model' => 'App\Models\TeamModel', 'source' => ''],
                    ],
                    'withSource' => 0,
                    'function' => "getTeamList",
                    'functionCount' => "getTeamCount",
                    'functionSingle' => "getTeamById",
                    'functionProcess' => "processIntergratedTeamListByTeam",
                ],
            "matchList" => [
                'list' => [
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'cpseo'],
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'chaofan'],
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'gamedota2'],
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'scoregg'],
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'wca'],
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'shangniu'],
                ],
                'withSource' => 1,
                'function' => "getMatchList",//获取数据方法
                'functionCount' => "getMatchCount",//获取列表方法
                'functionProcess' => "processMatchList",//格式化的处理方法
            ],
            "matchDetail" => [
                'list' => [
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'scoregg'],
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'wca'],
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'shangniu'],
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
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => 'wca'],
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => 'shangniu'],
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
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => 'wca'],
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => 'shangniu'],
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
            "gameConfig" => [//游戏配置
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
                'functionCount' => "getMapCount",
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
            "baiduInformaitonList" => [//由baidu分词索引生成的中文分词
                'list' => [
                    ['model' => 'App\Models\BaiduKeywordMapModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getList",
                'functionCount' => "getCount",
                'functionProcess' => "processBaiduInformationList",
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
                'functionProcess' => "processDota2Hero",
            ],
            "activityList" => [//活动
                'list' => [
                    ['model' => 'App\Models\Admin\ActivityList', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getActivityList",
                'functionCount' => "getActivityCount",
                'functionSingle' => "getActivityById",
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
            //print_R(array_column($functionList,"source"));
            $dataType = $params['dataType'] ?? $name;
            //echo "try to find type:".$dataType."\n";
            //echo "currentSource:".$currentSource."\n";
            $sourceFound = 0;
            if (isset($priviliegeList[$dataType]["withSource"]) && $priviliegeList[$dataType]["withSource"] == 1 && isset($params['source'])) {
                $availableSource = array_column($priviliegeList[$dataType]['list'], "source");
                if (in_array($params['source'], $availableSource)) {
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
                        if ($sourceFound == 1 && ($params['source'] != $detail['source'])) {
                            continue;
                        }
                        //数据类型和来源的双重结构
                        $fullDataType = $dataType.'/'.$params['source'];
                        //echo "full:".$fullDataType."\n";

                        //如果之前没初始化过
                        if (!isset($functionList[$fullDataType])) {
                            //如果类库初始化成功
                            if (isset($classList[$modelName])) {
                                //检查基础function方法存在
                                if (method_exists($classList[$modelName], $priviliegeList[$dataType]['function'])) {
                                    //echo "class:".$modelName.",function:".$priviliegeList[$dataType]['function']." found\n";

                                    $functionList[$fullDataType] = ["className" => $modelName, "class" => $classList[$modelName], "function" => $priviliegeList[$dataType]['function']];
                                    if (isset($priviliegeList[$dataType]['functionCount']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionCount'])) {
                                        $functionList[$fullDataType]['functionCount'] = $priviliegeList[$dataType]['functionCount'];
                                    } else {
                                        $functionList[$fullDataType]['functionCount'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionSingle']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionSingle'])) {
                                        $functionList[$fullDataType]['functionSingle'] = $priviliegeList[$dataType]['functionSingle'];
                                    } else {
                                        $functionList[$fullDataType]['functionSingle'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionUpdate']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionUpdate'])) {
                                        $functionList[$fullDataType]['functionUpdate'] = $priviliegeList[$dataType]['functionUpdate'];
                                    } else {
                                        $functionList[$fullDataType]['functionUpdate'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionProcess'])) {
                                        $functionList[$fullDataType]['functionProcess'] = $priviliegeList[$dataType]['functionProcess'];
                                    } else {
                                        $functionList[$fullDataType]['functionProcess'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionProcessCount'])) {
                                        $functionList[$fullDataType]['functionProcessCount'] = $priviliegeList[$dataType]['functionProcessCount'];
                                    } else {
                                        $functionList[$fullDataType]['functionProcessCount'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionSingleBySite'])) {
                                        $functionList[$fullDataType]['functionSingleBySite'] = $priviliegeList[$dataType]['functionSingleBySite'];
                                    } else {
                                        $functionList[$fullDataType]['functionSingleBySite'] = "";
                                    }
                                    $found = 1;
                                } else {
                                    //echo "class:".$modelName.",function:".$priviliegeList[$dataType]['function']." not found\n";
                                }
                            } else {
                                //echo "class:".$modelName.",not found\n";
                            }
                            $functionList[$fullDataType]['source'] = ($sourceFound == 1 ? $params['source'] : $currentSource);//$priviliegeList[$dataType]['source'];
                        }
                    }
                } //已经初始化数据来源 且 当前数据类型需要包含数据来源
                elseif ($currentSource != "" && $priviliegeList[$dataType]['withSource'] == 1) {
                    if ($sourceFound == 1) {
                        $currentSource = $params['source'];
                    }

                    //获取当前数据了行的类库列表
                    $list = array_combine(array_column($priviliegeList[$dataType]['list'], "source"), array_column($priviliegeList[$dataType]['list'], "model"));
                    //如果包含已经被初始化的数据来源
                    if (isset($list[$currentSource])) {
                        $modelName = $list[$currentSource];
                        $modelName = str_replace("#source#", $currentSource, $modelName);
                        //初始化
                        $classList = $this->getClass($classList, $modelName);
                        if ($sourceFound == 1 && ($params['source'] != $currentSource)) {
                            continue;
                        }
                        //数据类型和来源的双重结构
                        $fullDataType = $dataType.'/'.$params['source'];
                        //调用已有的数据类型
                        $functionList[$fullDataType]['source'] = ($params['source']);
                        //echo "full:".$fullDataType."\n";
                        //检查方法存在
                        if (method_exists($classList[$modelName] ?? [], $priviliegeList[$dataType]['function'])) {
                            $functionList[$fullDataType] = ["className" => $modelName, "class" => $classList[$modelName], "function" => $priviliegeList[$dataType]['function']];
                            //标记为找到
                            $found = 1;
                            if (isset($priviliegeList[$dataType]['functionCount']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionCount'])) {
                                $functionList[$fullDataType]['functionCount'] = $priviliegeList[$dataType]['functionCount'];
                            } else {
                                $functionList[$fullDataType]['functionCount'] = "";
                            }
                            if (isset($priviliegeList[$dataType]['functionSingle']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionSingle'])) {
                                $functionList[$fullDataType]['functionSingle'] = $priviliegeList[$dataType]['functionSingle'];
                            } else {
                                $functionList[$fullDataType]['functionSingle'] = "";
                            }
                            if (isset($priviliegeList[$dataType]['functionUpdate']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionUpdate'])) {
                                $functionList[$fullDataType]['functionUpdate'] = $priviliegeList[$dataType]['functionUpdate'];
                            } else {
                                $functionList[$fullDataType]['functionUpdate'] = "";
                            }
                            if (isset($priviliegeList[$dataType]['functionProcess'])) {
                                $functionList[$fullDataType]['functionProcess'] = $priviliegeList[$dataType]['functionProcess'];
                            } else {
                                $functionList[$fullDataType]['functionProcess'] = "";
                            }
                            if (isset($priviliegeList[$dataType]['functionProcessCount'])) {
                                $functionList[$fullDataType]['functionProcessCount'] = $priviliegeList[$dataType]['functionProcessCount'];
                            } else {
                                $functionList[$fullDataType]['functionProcessCount'] = "";
                            }
                            if (isset($priviliegeList[$dataType]['functionSingleBySite'])) {
                                $functionList[$fullDataType]['functionSingleBySite'] = $priviliegeList[$dataType]['functionSingleBySite'];
                            } else {
                                $functionList[$fullDataType]['functionSingleBySite'] = "";
                            }
                        }
                    }
                    //如果没找到
                    if ($found == 0) {
                        //循环
                        foreach ($priviliegeList[$dataType]['list'] as $detail) {
                            $modelName = $detail['model'];
                            $modelName = str_replace("#source#", $detail['source'], $modelName);
                            $classList = $this->getFunction($classList, $modelName);
                            if ($sourceFound == 1 && ($params['source'] != $detail['source'])) {
                                continue;
                            }
                            if (!isset($functionList[$fullDataType])) {
                                if (isset($classList[$modelName])) {
                                    if (method_exists($classList[$modelName], $priviliegeList[$dataType]['function'])) {
                                        //echo "class:".$modelName.",function:".$priviliegeList[$dataType]['function']." found\n";
                                        $functionList[$fullDataType] = ["className" => $modelName, "class" => $classList[$modelName], "function" => $priviliegeList[$dataType]['function']];
                                        if (method_exists($classList[$modelName], $priviliegeList[$dataType]['functionCount'])) {
                                            $functionList[$fullDataType]['functionCount'] = $priviliegeList[$dataType]['functionCount'];
                                        } else {
                                            $functionList[$fullDataType]['functionCount'] = "";
                                        }
                                        if (isset($priviliegeList[$dataType]['functionSingle']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionSingle'])) {
                                            $functionList[$fullDataType]['functionSingle'] = $priviliegeList[$dataType]['functionSingle'];
                                        } else {
                                            $functionList[$fullDataType]['functionSingle'] = "";
                                        }
                                        if (isset($priviliegeList[$dataType]['functionUpdate']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionUpdate'])) {
                                            $functionList[$fullDataType]['functionUpdate'] = $priviliegeList[$dataType]['functionUpdate'];
                                        } else {
                                            $functionList[$fullDataType]['functionUpdate'] = "";
                                        }
                                        if (isset($priviliegeList[$dataType]['functionProcess'])) {
                                            $functionList[$fullDataType]['functionProcess'] = $priviliegeList[$dataType]['functionProcess'];
                                        } else {
                                            $functionList[$fullDataType]['functionProcess'] = "";
                                        }
                                        if (isset($priviliegeList[$dataType]['functionProcessCount'])) {
                                            $functionList[$fullDataType]['functionProcessCount'] = $priviliegeList[$dataType]['functionProcessCount'];
                                        } else {
                                            $functionList[$fullDataType]['functionProcessCount'] = "";
                                        }
                                        if (isset($priviliegeList[$dataType]['functionSingleBySite'])) {
                                            $functionList[$fullDataType]['functionSingleBySite'] = $priviliegeList[$dataType]['functionSingleBySite'];
                                        } else {
                                            $functionList[$fullDataType]['functionSingleBySite'] = "";
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
                    $functionList[$fullDataType]['source'] = ($params['source']??$currentSource);
                } elseif ($priviliegeList[$dataType]['withSource'] == 0) {
                    //数据类型和来源的双重结构
                    $fullDataType = $dataType;
                    //echo "full:".$fullDataType."\n";

                    foreach ($priviliegeList[$dataType]['list'] as $detail) {
                        $modelName = $detail['model'];
                        //$currentSource = $currentSource == "" ? $detail['source'] : $currentSource;
                        $classList = $this->getClass($classList, $modelName);
                        if ($sourceFound == 1 && ($params['source'] != $detail['source'])) {
                            continue;
                        }
                        if (!isset($functionList[$fullDataType])) {
                            if (isset($classList[$modelName])) {
                                if (method_exists($classList[$modelName], $priviliegeList[$dataType]['function'])) {
                                    //echo "class:".$modelName.",function:".$priviliegeList[$dataType]['function']." found\n";exit;
                                    $functionList[$fullDataType] = ["className" => $modelName, "class" => $classList[$modelName], "function" => $priviliegeList[$dataType]['function']];
                                    if (isset($priviliegeList[$dataType]['functionCount']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionCount'])) {
                                        $functionList[$fullDataType]['functionCount'] = $priviliegeList[$dataType]['functionCount'];
                                    } else {
                                        $functionList[$fullDataType]['functionCount'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionSingle']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionSingle'])) {
                                        $functionList[$fullDataType]['functionSingle'] = $priviliegeList[$dataType]['functionSingle'];
                                    } else {
                                        $functionList[$fullDataType]['functionSingle'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionUpdate']) && method_exists($classList[$modelName], $priviliegeList[$dataType]['functionUpdate'])) {
                                        $functionList[$fullDataType]['functionUpdate'] = $priviliegeList[$dataType]['functionUpdate'];
                                    } else {
                                        $functionList[$fullDataType]['functionUpdate'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionProcess'])) {
                                        $functionList[$fullDataType]['functionProcess'] = $priviliegeList[$dataType]['functionProcess'];
                                    } else {
                                        $functionList[$fullDataType]['functionProcess'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionProcessCount'])) {
                                        $functionList[$fullDataType]['functionProcessCount'] = $priviliegeList[$dataType]['functionProcessCount'];
                                    } else {
                                        $functionList[$fullDataType]['functionProcessCount'] = "";
                                    }
                                    if (isset($priviliegeList[$dataType]['functionSingleBySite'])) {
                                        $functionList[$fullDataType]['functionSingleBySite'] = $priviliegeList[$dataType]['functionSingleBySite'];
                                    } else {
                                        $functionList[$fullDataType]['functionSingleBySite'] = "";
                                    }
                                    $found = 1;
                                } else {
                                    //echo "class:" . $modelName . ",function:" . $priviliegeList[$dataType]['function'] . " not found\n";
                                }
                            } else {
                                //echo "class:".$modelName.",not found\n";
                            }
                            $functionList[$fullDataType]['source'] = "";//$priviliegeList[$dataType]['source'];
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

    public function processMatchList($data, $functionList, $params = [])
    {
        $intergrationService = new IntergrationService();
        if (isset($params['source']) &&  ($params['source'] == 'wca' || $params['source'] == 'shangniu')) {
            $functionList = $this->checkFunction($functionList,"tournament",$params['source']);
            $modelTournamentClass = $functionList["tournament"."/".$params['source']]["class"];
            $functionTournamentSingle = $functionList["tournament"."/".$params['source']]['functionSingle'];

            $functionList = $this->checkFunction($functionList,"totalTeamList");
            $modelTeamClass = $functionList["totalTeamList"]["class"];
            $functionTeamSingle = $functionList["totalTeamList"]['functionSingleBySite'];
            $tournament = [];
            $teamList = [];
            $playerList = [];
            $teamMap = ["a" => "home", "b" => "away"];
            if (count($data)>0) {
                foreach ($data as $matchKey => $matchInfo) {

                    //赛事详情
                    if (isset($matchInfo['tournament_id'])) {
                        $tournamentInfo = $modelTournamentClass->$functionTournamentSingle($matchInfo['tournament_id']);
                        if (isset($tournamentInfo['tournament_id'])) {
                            $tournament[$matchInfo['tournament_id']] = $tournamentInfo;
                        }
                    }
                    $data[$matchKey]['tournament_info'] = $tournament[$matchInfo['tournament_id']] ?? [];
                    //=====================================战队信息=====================================
                    if (isset($matchInfo['home_id']) && $matchInfo['home_id']>0) {
                        $teamInfo = $modelTeamClass->$functionTeamSingle($matchInfo['home_id'], $functionList["matchList"."/".$params['source']]['source'], $matchInfo['game']);
                        if (isset($teamInfo['team_id'])) {
                            if (isset($teamInfo['tid']) && $teamInfo['tid'] > 0) {
                                $teamInfo = getFieldsFromArray($intergrationService->getTeamInfo(0, $teamInfo['tid'], 1, 0)['data'], "tid,team_name,logo,intergrated_id_list");
                            }
                            $teamList[$matchInfo['home_id']] = $teamInfo;
                        }
                    }
                    if (isset($matchInfo['away_id']) && $matchInfo['away_id']>0) {
                        $teamInfo = $modelTeamClass->$functionTeamSingle($matchInfo['away_id'], $functionList["matchList"."/".$params['source']]['source'], $matchInfo['game']);
                        if (isset($teamInfo['team_id'])) {
                            if (isset($teamInfo['tid']) && $teamInfo['tid'] > 0) {
                                $teamInfo = getFieldsFromArray($intergrationService->getTeamInfo(0, $teamInfo['tid'], 1, 0)['data'], "tid,team_name,logo,intergrated_id_list");
                            }
                            $teamList[$matchInfo['away_id']] = $teamInfo;
                        }

                    }

                    $data[$matchKey]['home_team_info'] = $teamList[$matchInfo['home_id']] ?? [];//战队
                    $data[$matchKey]['away_team_info'] = $teamList[$matchInfo['away_id']] ?? [];
                    //=====================================战队信息=====================================
                    //=====================================队员关联比赛信息=====================================
                    $matchData = json_decode($matchInfo['match_data'], true);
                    if(isset($matchData['matchData']) && $matchData['matchData']>0){
                        array_multisort(array_column($matchData['matchData'],"boxNum"),SORT_ASC,$matchData['matchData']);
                        foreach ($matchData['matchData'] as $matchDataKey=>$matchDataInfo){
                            $data[$matchKey]['game_count']=$matchDataInfo['boxNum'];
                            //=========================================主队信息
                            if(isset($matchDataInfo['homeTeam']['playerList']) && count($matchDataInfo['homeTeam']['playerList'])>0) {
                                foreach ($matchDataInfo['homeTeam']['playerList'] as $playerKey=>$homePlayerInfo){
                                    if(isset($homePlayerInfo['playerId']) && isset($params['player_id'][0]) && $homePlayerInfo['playerId']== $params['player_id'][0]){
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['boxNum']=$matchDataInfo['boxNum'];
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['matchId']=$matchDataInfo['matchId'];
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['homeName']=$matchDataInfo['homeTeam']['teamName']??'';
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['homeId']=$matchDataInfo['homeTeam']['teamId']??'';
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['homeScore']=$matchDataInfo['homeTeam']['score']??0;
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['homeLogo']=$matchDataInfo['homeTeam']['teamLogo']??'';
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['homePlayerId']=$homePlayerInfo['playerId']??'';
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['homePlayerName']=$homePlayerInfo['playerName']??'';
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['homePlayerLogo']=$homePlayerInfo['playerLogo']??'';
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['HeroName']=$homePlayerInfo['heroName']??'';
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['HeroLogo']=$homePlayerInfo['heroLogo']??'';
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['equipmentList']=$homePlayerInfo['equipmentList']??'';
                                        if($matchDataInfo['homeTeam']['score']>=$matchDataInfo['awayTeam']['score']){
                                            $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['win_teamID']=$matchDataInfo['homeTeam']['teamId'];
                                        }else{
                                            $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['win_teamID']=$matchDataInfo['awayTeam']['teamId'];
                                        }
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['killCount']=$homePlayerInfo['playerStat']['killCount']?? 0;
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['dieCount']=$homePlayerInfo['playerStat']['dieCount']?? 0;
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['assistsCount']=$homePlayerInfo['playerStat']['assistsCount']?? 0;
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['awayName']=$matchDataInfo['awayTeam']['teamName']??'';
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['awayId']=$matchDataInfo['awayTeam']['teamId']??'';
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['awayScore']=$matchDataInfo['awayTeam']['score']??0;
                                        $playerList[$matchDataInfo['boxNum'].$homePlayerInfo['playerId']]['awayLogo']=$matchDataInfo['awayTeam']['teamLogo']??'';

                                    }

                                }

                            }
                            //==================================主队================================================
                            //==================================客队================================================
                            if(isset($matchDataInfo['awayTeam']['playerList']) && count($matchDataInfo['awayTeam']['playerList'])>0) {
                                foreach ($matchDataInfo['awayTeam']['playerList'] as $playerKey=>$awayPlayerInfo){
                                    if(isset($awayPlayerInfo['playerId']) && isset($params['player_id'][0]) && $awayPlayerInfo['playerId']== $params['player_id'][0]){
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['boxNum']=$matchDataInfo['boxNum'];
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['matchId']=$matchDataInfo['matchId'];
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['awayName']=$matchDataInfo['awayTeam']['teamName']??'';
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['awayId']=$matchDataInfo['awayTeam']['teamId']??'';
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['awayScore']=$matchDataInfo['awayTeam']['score']??'';
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['awayLogo']=$matchDataInfo['awayTeam']['teamLogo']??'';
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['awayPlayerId']=$awayPlayerInfo['playerId']??'';
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['awayPlayerName']=$awayPlayerInfo['playerName']??'';
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['awayPlayerLogo']=$awayPlayerInfo['playerLogo']??'';
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['HeroName']=$awayPlayerInfo['heroName']??'';
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['HeroLogo']=$awayPlayerInfo['heroLogo']??'';
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['equipmentList']=$awayPlayerInfo['equipmentList']??'';
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['killCount']=$awayPlayerInfo['playerStat']['killCount']?? 0;
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['dieCount']=$awayPlayerInfo['playerStat']['dieCount']?? 0;
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['assistsCount']=$awayPlayerInfo['playerStat']['assistsCount']?? 0;
                                        if($matchDataInfo['homeTeam']['score']>=$matchDataInfo['awayTeam']['score']){
                                            $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['win_teamID']=$matchDataInfo['homeTeam']['teamId'];
                                        }else{
                                            $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['win_teamID']=$matchDataInfo['awayTeam']['teamId'];
                                        }
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['homeName']=$matchDataInfo['homeTeam']['teamName']??'';
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['homeId']=$matchDataInfo['homeTeam']['teamId']??'';
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['homeScore']=$matchDataInfo['homeTeam']['score']??'';
                                        $playerList[$matchDataInfo['boxNum'].$awayPlayerInfo['playerId']]['homeLogo']=$matchDataInfo['homeTeam']['teamLogo']??'';

                                    }

                                }

                            //=====================================客队=================================

                            }


                        }
                    }
                    $data[$matchKey]['player_detail'] = (count($playerList)>0)?array_values($playerList):[];
                    //=====================================队员信息=====================================
                    unset($data[$matchKey]['match_data']);

                }
            }

            return $data;
        } else {
            $functionList = $this->checkFunction($functionList,"tournament",$params['source']);
            $modelTournamentClass = $functionList["tournament"."/".$params['source']]["class"];
            $functionTournamentSingle = $functionList["tournament"."/".$params['source']]['functionSingle'];
            $functionList = $this->checkFunction($functionList,"totalTeamList");
            $modelClass = $functionList["totalTeamList"]["class"];
            $functionSingle = $functionList["totalTeamList"]['functionSingleBySite'];
            $functionList = $this->checkFunction($functionList,"totalPlayerInfo",$params['source']);
            $functionList = $this->checkFunction($functionList,"totalPlayerList",$params['source']);
            $playerModelClass = $functionList["totalPlayerInfo"]["class"];
            $functionPlayerSingle = $functionList["totalPlayerInfo"]['functionSingleBySite'];

            $teamList = [];
            $playerList = [];
            $tournament = [];
            $teamMap = ["a" => "home", "b" => "away"];
            foreach ($data as $key => $matchInfo) {
                //赛事信息
                if (!isset($tournament[$matchInfo['tournament_id']])) {
                    $tournamentInfo = $modelTournamentClass->$functionTournamentSingle($matchInfo['tournament_id']);
                    if (isset($tournamentInfo['tournament_id'])) {
                        $tournament[$matchInfo['tournament_id']] = $tournamentInfo;
                    }
                }
                foreach ($teamMap as $side => $color) {
                    $data[$key][$color . '_player_id_list'] = [];
                }
                //战队信息
                if (!isset($teamList[$matchInfo['home_id']])) {
                    $teamInfo = $modelClass->$functionSingle($matchInfo['home_id'], $functionList["matchList"."/".$params['source']]['source'], $matchInfo['game']);
                    if (isset($teamInfo['team_id'])) {
                        if (isset($teamInfo['tid']) && $teamInfo['tid'] > 0) {
                            $teamInfo = getFieldsFromArray($intergrationService->getTeamInfo(0, $teamInfo['tid'], 1, 0)['data'], "tid,team_name,logo,intergrated_id_list");
                        }
                        $teamList[$matchInfo['home_id']] = $teamInfo;
                    }
                }
                if (!isset($teamList[$matchInfo['away_id']])) {
                    $teamInfo = $modelClass->$functionSingle($matchInfo['away_id'], $functionList["matchList"."/".$params['source']]['source'], $matchInfo['game']);
                    if (isset($teamInfo['team_id'])) {
                        if (isset($teamInfo['tid']) && $teamInfo['tid'] > 0) {
                            $teamInfo = getFieldsFromArray($intergrationService->getTeamInfo(0, $teamInfo['tid'], 1, 0)['data'], "tid,team_name,logo,intergrated_id_list");
                        }
                        $teamList[$matchInfo['away_id']] = $teamInfo;
                    }
                }
                $matchData = json_decode($matchInfo['match_data'], true);
                $playerIdList = [];
                if (is_array($matchData['result_list'])) {
                    foreach ($teamMap as $side => $color) {
                        $data[$key][$color . '_player_list'] = [];
                        foreach ($matchData['result_list'] as $round => $round_info) {
                            if (isset($round_info['record_list_' . $side])) {
                                foreach ($round_info['record_list_' . $side] as $player) {
                                    $data[$key][$color . '_player_list'][$player['playerID']] = ["player_name" => $player['player_nickname'], "logo" => $player["player_image_thumb"], "pid" => 0];
                                }
                            }
                        }
                    }
                    $data[$key]['home_team_info'] = $teamList[$matchInfo['home_id']] ?? [];//战队
                    $data[$key]['away_team_info'] = $teamList[$matchInfo['away_id']] ?? [];
                    foreach ($teamMap as $side => $color) {
                        $data[$key][$color . '_player_id_list'] = array_keys($data[$key][$color . '_player_list']);
                        foreach ($data[$key][$color . '_player_list'] as $player_id => $playerInfo) {
                            if (!isset($playerList[$player_id])) {
                                $playerInfo = $playerModelClass->$functionPlayerSingle($player_id, $matchInfo['game'], $functionList["matchList"."/".$params['source']]['source']);
                                if (isset($playerInfo['player_id'])) {
                                    if (isset($playerInfo['pid']) && $playerInfo['pid'] > 0) {
                                        $playerInfo = getFieldsFromArray($intergrationService->getPlayerInfo(0, $playerInfo['pid'], 1, 0)['data'], "pid,player_name,logo");
                                    }
                                    $playerList[$player_id] = $playerInfo;
                                }
                            }
                            if (isset($playerList[$player_id])) {
                                $data[$key][$color . '_player_list'][$player_id] = $playerList[$player_id];
                            }
                        }
                        //如果没有对阵的队员
                        if (count($data[$key][$color . '_player_list'] ?? []) == 0) {
                            continue;
                            $teamId = $data[$key][$color . '_team_info']['intergrated_id_list'] ?? $data[$key][$color . '_id'];
                            $functionPlayerList = $functionList["totalPlayerList"]["function"];
                            $teamPlayerList = $functionList["totalPlayerList"]["class"]->$functionPlayerList(['team_ids' => $teamId, "pageSize" => 999]);
                            foreach ($teamPlayerList as $player) {
                                if ($player['pid'] > 0) {
                                    $data[$key][$color . '_player_id_list'][] = $player['player_id'];
                                    $data[$key][$color . '_player_list'][] = getFieldsFromArray($intergrationService->getPlayerInfo(0, $player['pid'], 1, 0)['data'], "pid,player_name,logo");
                                    $countPlayer = count($data[$key][$color . '_player_list']);
                                    if ($countPlayer > 5) {
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
                if (isset($params['pid'])) {
                    $home = in_array($params['pid'], array_column($data[$key]['home_player_list'] ?? [], "pid")) ? 1 : 0;
                    $away = in_array($params['pid'], array_column($data[$key]['away_player_list'] ?? [], "pid")) ? 1 : 0;
                    if (($home + $away) == 0) {
                        unset($data[$key]);
                    } else {
                        $playerDetail = [];
                        if (isset($matchData['result_list']) && count($matchData['result_list']) > 0) {
                            foreach ($matchData['result_list'] as $r_key => $result) {
                                $playerDetail[$r_key]['win_teamID'] = $result['win_teamID'];
                                $currentKey = "";
                                if (isset($result['detail'])) {
                                    foreach ($result['detail']['result_list'] as $result_key => $value) {
                                        if (in_array($value, $params['player_id']) && substr($result_key, -9) == "_playerID") {
                                            $currentKey = $result_key;
                                        }
                                    }
                                    if ($currentKey != "") {
                                        $t = explode("_", $currentKey);
                                        foreach ($result['detail']['result_list'] as $result_key => $value) {
                                            if (substr($result_key, 0, strlen($t['0'])) == $t['0'] && (strpos($result_key, "_" . $t[2] . "_") > 0)) {
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
    }
    public function processMatch($data, $functionList , $params)
    {
        $intergrationService = new IntergrationService();
        $functionList = $this->checkFunction($functionList,"tournament",$params['source']);
        $functionList = $this->checkFunction($functionList,"totalTeamInfo");
        $functionList = $this->checkFunction($functionList,"totalPlayerInfo");
        $functionList = $this->checkFunction($functionList,"totalPlayerList");
        $modelTournamentClass = $functionList["tournament"."/".$params['source']]["class"];
        $functionTournamentSingle = $functionList["tournament"."/".$params['source']]['functionSingle'];
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
        if (!isset($teamList[$data['home_id']])) {
            $teamInfo = $modelClass->$functionSingle($data['home_id'],$params['source'], "", "team_id,tid,team_name,logo,description");
            if (isset($teamInfo['team_id'])) {
                $teamList[$data['home_id']] = $teamInfo;
            }
        }
        if (!isset($teamList[$data['away_id']])) {
            $teamInfo = $modelClass->$functionSingle($data['away_id'],$params['source'], "", "team_id,tid,team_name,logo,description");
            if (isset($teamInfo['team_id'])) {
                $teamList[$data['away_id']] = $teamInfo;
            }
        }
        $data['home_team_info'] = $teamList[$data['home_id']] ?? [];//战队
        $data['away_team_info'] = $teamList[$data['away_id']] ?? [];
        $data['tournament_info'] = $tournament[$data['tournament_id']] ?? [];

        $oPlayerListModel = $functionList["totalPlayerList"]["class"];
        $oplayerFunction = $functionList["totalPlayerList"]['function'];
        $sourceList = config('app.intergration.player');
        if (isset($data['home_team_info']['tid']) && $data['home_team_info']['tid'] > 0) {
            $data['home_team_info'] = getFieldsFromArray($intergrationService->getTeamInfo(0, $data['home_team_info']['tid'], 1, 0)['data'], "tid,team_name,en_name,game,description,logo,intergrated_id_list");
            $home_description='';
            if(strip_tags($data['home_team_info']['description'])=='暂无') {
                if(strpos($data['home_team_info']['team_name'],'战队')===false){
                    $home_description.=$data['home_team_info']['team_name'].'战队，';
                }
                if(strpos($data['home_team_info']['team_name'],'俱乐部')===false){
                    if($data['home_team_info']['en_name']!=''){
                        $home_description.='全称'.$data['home_team_info']['en_name'].'电子竞技俱乐部，';
                    }else{
                        $home_description.='全称'.$data['home_team_info']['team_name'].'电子竞技俱乐部，';
                    }

                }
                if($data['home_team_info']['game']=='lol'){
                    $home_description.="英雄联盟职业电竞俱乐部，旗下成员包括";
                }elseif($data['home_team_info']['game']=='kpl'){
                    $home_description.="王者荣耀职业电竞俱乐部，旗下成员包括";
                }elseif($data['home_team_info']['game']=='dota2'){
                    $home_description.="DOTA2职业电竞俱乐部，旗下成员包括";
                }
                if (count($data['home_team_info']['intergrated_id_list'])) {
                    $pidList = $oPlayerListModel->$oplayerFunction(['team_ids' => $data['home_team_info']['intergrated_id_list'], "sources" => array_column($sourceList, "source"), "fields" => "player_id,pid", "page_size" => 6]);
                    $pidList = array_unique(array_column($pidList, "pid"));
                    $pidListCount=count($pidList);
                    foreach ($pidList as $pid) {
                        if ($pid > 0) {
                            $aPlayerInfo = $intergrationService->getPlayerInfo(0, $pid, 1, $params['reset'] ?? 0);
                            if(isset($aPlayerInfo['data']['player_name']) && $aPlayerInfo['data']['player_name']!='') {
                                $home_description.=$aPlayerInfo['data']['player_name'].'，';

                            }


                        }
                    }

                }
                $home_description=trim($home_description,'，');
                if($pidListCount>=5){
                    $home_description=$home_description.' 等';
                }
                $data['home_team_info']['description']=$home_description;

            }
        }
        if (isset($data['away_team_info']['tid']) && $data['away_team_info']['tid'] > 0) {
            $data['away_team_info'] = getFieldsFromArray($intergrationService->getTeamInfo(0, $data['away_team_info']['tid'], 1, 0)['data'], "tid,team_name,en_name,game,description,logo,intergrated_id_list");
            $away_description='';
            if(strip_tags($data['away_team_info']['description'])=='暂无') {
                if(strpos($data['away_team_info']['team_name'],'战队')===false){
                    $away_description.=$data['away_team_info']['team_name'].'战队，';
                }
                if(strpos($data['away_team_info']['team_name'],'俱乐部')===false){
                    if($data['away_team_info']['en_name']!=''){
                        $away_description.='全称'.$data['away_team_info']['en_name'].'电子竞技俱乐部，';
                    }else{
                        $away_description.='全称'.$data['away_team_info']['team_name'].'电子竞技俱乐部，';
                    }

                }
                if($data['away_team_info']['game']=='lol'){
                    $away_description.="英雄联盟职业电竞俱乐部，旗下成员包括";
                }elseif($data['away_team_info']['game']=='kpl'){
                    $away_description.="王者荣耀职业电竞俱乐部，旗下成员包括";
                }elseif($data['away_team_info']['game']=='dota2'){
                    $away_description.="DOTA2职业电竞俱乐部，旗下成员包括";
                }
                if (count($data['away_team_info']['intergrated_id_list'])) {
                    $pidList = $oPlayerListModel->$oplayerFunction(['team_ids' => $data['away_team_info']['intergrated_id_list'], "sources" => array_column($sourceList, "source"), "fields" => "player_id,pid", "page_size" => 6]);
                    $pidList = array_unique(array_column($pidList, "pid"));
                    $pidListCount=count($pidList);
                    foreach ($pidList as $pid) {
                        if ($pid > 0) {
                            $aPlayerInfo = $intergrationService->getPlayerInfo(0, $pid, 1, $params['reset'] ?? 0);
                            if(isset($aPlayerInfo['data']['player_name']) && $aPlayerInfo['data']['player_name']!='') {
                                $away_description.=$aPlayerInfo['data']['player_name'].'，';

                            }


                        }
                    }

                }
                $away_description=trim($away_description,'，');
                if($pidListCount>=5){
                    $away_description=$away_description.' 等';
                }
                $data['away_team_info']['description']=$away_description;

            }
        }
        $playerList = [];

        //处理比赛数据
        if (isset($data['match_data'])) {
            $oPlayerModel = $functionList["totalPlayerInfo"]["class"];
            $oPlayerFunction = $functionList["totalPlayerInfo"]["functionSingleBySite"];
            $data['match_data'] = json_decode($data['match_data'], true);
            if($params['source']=='shangniu'){
                //处理尚牛的数据
                if(isset($data['match_data']['matchData']) && $data['match_data']['matchData']>0) {
                    foreach ($data['match_data']['matchData'] as $matchKey=>$matchDataInfo){
                        //homeTeam下面的pick
                       if(isset($matchDataInfo['homeTeam']['pick']) && count($matchDataInfo['homeTeam']['pick'])>0){
                            foreach ($matchDataInfo['homeTeam']['pick'] as $homePickKey=>&$homePickInfo){
                                if(strpos($homePickInfo['logo'],'esports-cdn.namitiyu.com') !==false){
                                    $homePickInfo['logo']='';
                                }

                            }
                        }
                        //awayTeam下面的pick
                        if(isset($matchDataInfo['awayTeam']['pick']) && count($matchDataInfo['awayTeam']['pick'])>0){
                            foreach ($matchDataInfo['awayTeam']['pick'] as $awayPickKey=>&$awayPickInfo){
                                if(strpos($awayPickInfo['logo'],'esports-cdn.namitiyu.com') !==false){
                                    $awayPickInfo['logo']='';
                                }

                            }
                        }
                        //homeTeam下面的ban
                        if(isset($matchDataInfo['homeTeam']['ban']) && count($matchDataInfo['homeTeam']['ban'])>0){
                            foreach ($matchDataInfo['homeTeam']['ban'] as $homeBanKey=>&$homeBanInfo){
                                if(strpos($homeBanInfo['logo'],'esports-cdn.namitiyu.com') !==false){
                                    $homeBanInfo['logo']='';
                                }

                            }
                        }
                        //awayTeam下面的ban
                        if(isset($matchDataInfo['awayTeam']['ban']) && count($matchDataInfo['awayTeam']['ban'])>0){
                            foreach ($matchDataInfo['awayTeam']['ban'] as $awayBanKey=>&$awayBanInfo){
                                if(strpos($awayBanInfo['logo'],'esports-cdn.namitiyu.com') !==false){
                                    $awayBanInfo['logo']='';
                                }

                            }
                        }

                        //homeTeam playerList
                        if(isset($matchDataInfo['homeTeam']['playerList']) && count($matchDataInfo['homeTeam']['playerList'])>0){
                            foreach ($matchDataInfo['homeTeam']['playerList'] as $homePlayerKey=>&$homePlayerInfo){
                                $playerInfo = $oPlayerModel->$oPlayerFunction($homePlayerInfo['playerId'], $data['game'],$params['source']);
                                $homePlayerInfo['playerName']=$playerInfo['player_name'] ??$homePlayerInfo['playerName'];
                                $homePlayerInfo['playerLogo']=$playerInfo['logo'] ?? '';
                                if(strpos($homePlayerInfo['heroLogo'],'esports-cdn.namitiyu.com') !==false){
                                    $homePlayerInfo['heroLogo']='';
                                }
                                if(is_array($homePlayerInfo['equipmentList']) && $homePlayerInfo['equipmentList']){
                                    foreach ($homePlayerInfo['equipmentList'] as &$equipmentInfo){//装备
                                        if(strpos($equipmentInfo['logo'],'esports-cdn.namitiyu.com') !==false){
                                            $equipmentInfo['logo']='';
                                        }

                                    }

                                }

                            }
                        }
                        //awayTeam playerList
                        if(isset($matchDataInfo['awayTeam']['playerList']) && count($matchDataInfo['awayTeam']['playerList'])>0){
                            foreach ($matchDataInfo['awayTeam']['playerList'] as $awayPlayerKey=>&$awayPlayerInfo){
                                $playerInfo = $oPlayerModel->$oPlayerFunction($awayPlayerInfo['playerId'], $data['game'],$params['source']);
                                $awayPlayerInfo['playerName']=$playerInfo['player_name'] ??$awayPlayerInfo['playerName'];
                                $awayPlayerInfo['playerLogo']=$playerInfo['logo'] ?? '';
                                if(strpos($awayPlayerInfo['heroLogo'],'esports-cdn.namitiyu.com') !==false){
                                    $awayPlayerInfo['heroLogo']='';
                                }
                                if(is_array($awayPlayerInfo['equipmentList']) && $awayPlayerInfo['equipmentList']){
                                    foreach ($awayPlayerInfo['equipmentList'] as &$equipmentInfo){//装备
                                        if(strpos($equipmentInfo['logo'],'esports-cdn.namitiyu.com') !==false){
                                            $equipmentInfo['logo']='';
                                        }

                                    }

                                }

                            }
                        }
                        $data['match_data']['matchData'][$matchKey]['homeTeam']['playerList']=$matchDataInfo['homeTeam']['playerList'];
                        $data['match_data']['matchData'][$matchKey]['awayTeam']['playerList']=$matchDataInfo['awayTeam']['playerList'];
                        $data['match_data']['matchData'][$matchKey]['homeTeam']['pick']=$matchDataInfo['homeTeam']['pick'];
                        $data['match_data']['matchData'][$matchKey]['awayTeam']['pick']=$matchDataInfo['awayTeam']['pick'];
                        $data['match_data']['matchData'][$matchKey]['homeTeam']['ban']=$matchDataInfo['homeTeam']['ban'];
                        $data['match_data']['matchData'][$matchKey]['awayTeam']['ban']=$matchDataInfo['awayTeam']['ban'];

                    }

                }

            }
            if (isset($data['match_data']['result_list']) && count($data['match_data']['result_list']) > 0) {
                foreach ($data['match_data']['result_list'] as $key => $result) {
                    unset($data['match_data']['result_list'][$key]['team_a_image_thumb']);
                    unset($data['match_data']['result_list'][$key]['team_b_image_thumb']);
                    //主客队和颜色的映射
                    $teamMap = ["a" => "blue", "b" => "red"];
                    $keyMap = ["a", "b", "c", "d", "e", "f", "g"];
                    foreach ($teamMap as $side => $color) {
                        if (isset($result['detail'])) {
                            $data['match_data']['result_list'][$key]['dragon_list'] = $result['detail']['dragon_list'] ?? [];
                            foreach ($result['detail']['result_list'] as $key_b => $value_b) {
                                foreach ($keyMap as $k => $c) {
                                    if (!is_array($value_b) && (substr($key_b, 0, strlen($color) + 1) == $color . "_") && strpos($key_b, "_" . $c . "_") > 0) {
                                        $new_key = str_replace([$color . "_", "_" . $c . "_"], "_", $key_b);
                                        // echo $key_b . "-" . $new_key . "-" . $value_b . "\n";
                                        $data['match_data']['result_list'][$key]['record_list_' . $side][$k][$new_key] = $value_b;
                                        unset($result['detail']['result_list'][$key_b]);
                                        unset($data['match_data']['result_list'][$key]['detail']['result_list'][$key_b]);
                                    }
                                }
                            }
                            foreach ($result['detail']['result_list'] as $key_b => $value_b) {
                                if (!is_array($value_b)) {
                                    $data['match_data']['result_list'][$key][$key_b] = $value_b;
                                }
                                unset($data['match_data']['result_list'][$key]['detail']);
                            }
                        }
                        if (isset($result['record_list_' . $side])) {
                            foreach ($result['record_list_' . $side] as $key_a => $player) {
                                if (!isset($playerList[$player['playerID']])) {
                                    $playerInfo = $oPlayerModel->$oPlayerFunction($player['playerID'], $data['game'],$params['source']);
                                    if (isset($playerInfo['pid']) && $playerInfo['pid'] > 0) {
                                        $playerInfo = getFieldsFromArray($intergrationService->getPlayerInfo(0, $playerInfo['pid'], 1, 0)['data'], "pid,player_name,logo");
                                        $playerList[$player['playerID']] = $playerInfo;
                                    } else {
                                        $playerList[$player['playerID']] = $playerInfo;
                                    }
                                }
                                unset($data['match_data']['result_list'][$key]['record_list_' . $side][$key_a]['player_image_thumb']);
                                $data['match_data']['result_list'][$key]['record_list_' . $side][$key_a]['pid'] = $playerList[$player['playerID']]['pid'] ?? 0;
                                $data['match_data']['result_list'][$key]['record_list_' . $side][$key_a]['logo'] = $playerList[$player['playerID']]['logo'] ?? "";
                                $data['match_data']['result_list'][$key]['record_list_' . $side][$key_a]['player_name'] = $playerList[$player['playerID']]['player_name'] ?? "";
                            }
                        }

                    }
                }
            }
        }
        return $data;
    }

    public function processTournament($data, $functionList, $params = [])
    {
        $intergrationService = new IntergrationService();
        if ($data['game'] == 'dota2') {
            if (isset($functionList["matchList"."/".$params['source']]) && isset($functionList["matchList"."/".$params['source']]['functionSingle'])) {

            } else {
                $functionList = $this->checkFunction($functionList,"matchList",$params['source']);
            }
            $data['teamList'] = [];
            $matchModelClass = $functionList["matchList"."/".$params['source']]["class"];
            $matchFunction = $functionList["matchList"."/".$params['source']]['function'];
            $matchList = $matchModelClass->$matchFunction([
                'tournament_id' => $data['tournament_id'],
                "page_size" => 1000,
                "fields" => "match_id,home_id,away_id,game,game_bo,tournament_id,home_name,away_name,home_score,away_score,home_logo,away_logo"
            ]);

            $data['recentMatchList'] =  $matchList;
            $functionList = $this->checkFunction($functionList,"totalTeamInfo");
            $teamModelClass = $functionList["totalTeamInfo"]["class"];
            $teamFunction = $functionList["totalTeamInfo"]['functionSingleBySite'];
            $teamList = [];
            foreach ($matchList as $matchInfo)
            {
                if(isset($matchInfo['home_id']) && $matchInfo['home_id']>0 ){
                    $teamInfo = $teamModelClass->$teamFunction($matchInfo['home_id'], $functionList['tournament'."/".$params['source']]['source'], $data['game'], "team_id,tid,logo");
                    if (isset($teamInfo['team_id'])) {
                        if (isset($teamInfo['tid']) && $teamInfo['tid'] > 0) {
                            $teamInfo = getFieldsFromArray($intergrationService->getTeamInfo(0, $teamInfo['tid'], 1, 0)['data'], "tid,team_name,logo,intergrated_id_list");
                            $teamList[$matchInfo['home_id']] = $teamInfo;
                        }

                    }
                }else{
                    $teamList[$matchInfo['home_name']] = ['team_name'=>$matchInfo['home_name'] ,'logo'=> $matchInfo['home_logo'],'tid'=> 0];
                }

                if(isset($matchInfo['away_id']) && $matchInfo['away_id']>0 ){
                    $teamInfo = $teamModelClass->$teamFunction($matchInfo['away_id'], $functionList['tournament'."/".$params['source']]['source'], $data['game'], "team_id,team_name,tid,logo");
                    if (isset($teamInfo['team_id'])) {
                        if (isset($teamInfo['tid']) && $teamInfo['tid'] > 0) {
                            $teamInfo = getFieldsFromArray($intergrationService->getTeamInfo(0, $teamInfo['tid'], 1, 0)['data'], "tid,team_name,logo,intergrated_id_list");
                            $teamList[$matchInfo['away_id']] = $teamInfo;
                        }

                    }
                }else{
                    $teamList[$matchInfo['away_name']] = ['team_name'=>$matchInfo['away_name'] ,'logo'=> $matchInfo['away_logo'],'tid'=> 0];
                }


            }

            $data['teamList'] = $teamList;
            return $data;
        } else {
            $functionList = $this->checkFunction($functionList,"roundList",$params['source']);
            $functionList = $this->checkFunction($functionList,"matchList",$params['source']);
            $functionList = $this->checkFunction($functionList,"totalTeamInfo");
            $modelClass = $functionList["roundList"."/".$params['source']]["class"];
            $function = $functionList["roundList"."/".$params['source']]['function'];
            $matchModelClass = $functionList["matchList"."/".$params['source']]["class"];
            $matchFunction = $functionList["matchList"."/".$params['source']]['function'];
            $teamModelClass = $functionList["totalTeamInfo"]["class"];
            $teamFunction = $functionList["totalTeamInfo"]['functionSingleBySite'];
            $matchList = $matchModelClass->$matchFunction(['tournament_id' => $data['tournament_id'], "page_size" => 1000, "fields" => "match_id,home_id,away_id"]);
            $teamIdList = array_unique(array_merge(array_column($matchList, "home_id"), array_column($matchList, "away_id")));

            $teamList = [];
            foreach ($teamIdList as $team_id) {
                $teamInfo = $teamModelClass->$teamFunction($team_id, $functionList['tournament'."/".$params['source']]['source'], $data['game'], "team_id,tid");
                if (isset($teamInfo['tid']) && $teamInfo['tid'] > 0) {
                    $teamInfo = $intergrationService->getTeamInfo(0, $teamInfo['tid'], 1, 0);
                    if (isset($teamInfo['data']['tid'])) {
                        $teamList[$teamInfo['data']['tid']] = getFieldsFromArray($teamInfo['data'], "tid,logo,team_name,intergrated_id_list");
                    }
                }
            }
            $data['teamList'] = array_values($teamList);
            $data['roundList'] = array_values($modelClass->$function(["tournament_id" => $data['tournament_id']]));
            return $data;
        }
    }

    public function processPlayerList($data, $functionList)
    {
        $functionList = $this->checkFunction($functionList,"playerList");
        $modelClass = $functionList["playerList"]["class"];
        $functionSingle = $functionList["playerList"]['functionSingle'];
        $teamInfo = [];
        if (!empty($data)) {
            foreach ($data as $key => &$val) {
                if (!$val['player_name']) {
                    unset($data[$key]);
                }
                $team_id = $val['team_id'] ?? '';
                if ($team_id) {
                    $teamInfo = $modelClass->$functionSingle($val['team_id']);
                }
                $val['team_info'] = $teamInfo;
                if (isset($val['team_history'])) {
                    $val['team_history'] = json_decode($val['team_history'], true);
                }
                if (isset($val['event_history'])) {
                    $val['event_history'] = json_decode($val['event_history'], true);
                }
                if (isset($val['stat'])) {
                    $val['stat'] = json_decode($val['stat'], true);
                }
            }
        }
        if ($data) {
            $data = array_values($data);
        }
        return $data;
    }

    public function processLolHero($data, $functionList)
    {
        $data['skinList'] = [];
        $data['spellList'] = [];
        $functionList = $this->checkFunction($functionList,"lolHeroSkin");

        $modelClass = $functionList["lolHeroSkin"]["class"];
        $function = $functionList["lolHeroSkin"]['function'];
        $teamInfo = [];
        if (!empty($data)) {
            $data['skinList'] = $modelClass->$function(["hero_id" => $data['id']]);
        }
        $functionList = $this->checkFunction($functionList,"lolHeroSpell");
        $modelClass = $functionList["lolHeroSpell"]["class"];
        $function = $functionList["lolHeroSpell"]['function'];
        $teamInfo = [];
        if (!empty($data)) {
            $data['spellList'] = $modelClass->$function(["hero_id" => $data['id']]);
        }
        return $data;
    }

    public function processKplHero($data, $functionList)
    {
        if (isset($data['aka'])) {
            $data['aka'] = json_decode($data['aka'], true);
        }
        if (isset($data['stat'])) {
            $data['stat'] = json_decode($data['stat'], true);
        }
        if (isset($data['skin_list'])) {
            $data['skin_list'] = json_decode($data['skin_list'], true);
        }
        if (isset($data['skill_list'])) {
            $data['skill_list'] = json_decode($data['skill_list'], true);
        }
        if (isset($data['inscription_tips'])) {
            $data['inscription_tips'] = json_decode($data['inscription_tips'], true);
            if (!empty($data['inscription_tips'])) {
                $functionList = $this->checkFunction($functionList,"kplInscription");
                    $modelClass = $functionList['kplInscription']['class'] ?? '';
                    if (isset($data['inscription_tips']['sugglistIds']) && $data['inscription_tips']['sugglistIds']) {
                        if (strpos($data['inscription_tips']['sugglistIds'], '|') !== false) {
                            $inscriptionId = explode('|', $data['inscription_tips']['sugglistIds']);
                        } else {
                            $inscriptionId[] = $data['inscription_tips']['sugglistIds'];
                        }
                        $inscriptionInfo = $modelClass->getInscriptionByIds($inscriptionId);
                        $data['inscription_tips']['sugglistIds'] = $inscriptionInfo;
                    }


            }
        }
        if (isset($data['skill_tips'])) {
            $data['skill_tips'] = json_decode($data['skill_tips'], true);
        }
        //英雄关系
        if (isset($data['hero_tips'])) {
            $data['hero_tips'] = json_decode($data['hero_tips'], true);
            if (!empty($data['hero_tips'])) {
                $modelClass = $functionList["kplHero"]["class"] ?? '';
                $function = $functionList["kplHero"]['function'] ?? '';

                if (isset($data['hero_tips']['mate']) && $data['hero_tips']['mate']) {//最佳搭档
                    foreach ($data['hero_tips']['mate'] as &$val) {
                        $heroInfo = $modelClass->getHeroInfoById($val['id']);
                        $val['hero_name'] = $heroInfo['hero_name'] ?? '';
                        $val['logo'] = $heroInfo['logo'] ?? '';

                    }
                }
                if (isset($data['hero_tips']['suppress']) && $data['hero_tips']['suppress']) {//被压制英雄
                    foreach ($data['hero_tips']['suppress'] as &$val) {
                        $heroInfo = $modelClass->getHeroInfoById($val['id']);
                        $val['hero_name'] = $heroInfo['hero_name'] ?? '';
                        $val['logo'] = $heroInfo['logo'] ?? '';
                    }

                }
                if (isset($data['hero_tips']['suppressed']) && $data['hero_tips']['suppressed']) {//压制英雄
                    foreach ($data['hero_tips']['suppressed'] as &$val) {
                        $heroInfo = $modelClass->getHeroInfoById($val['id']);
                        $val['hero_name'] = $heroInfo['hero_name'] ?? '';
                        $val['logo'] = $heroInfo['logo'] ?? '';
                    }
                }

            }
        }
        //英雄-关联装备
        if (isset($data['equipment_tips'])) {
            $data['equipment_tips'] = json_decode($data['equipment_tips'], true);
            if (!empty($data['equipment_tips'])) {
                $functionList = $this->checkFunction($functionList,"kplEquipment");
                    $modelClass = $functionList['kplEquipment']['class'] ?? '';
                    foreach ($data['equipment_tips'] as &$val) {
                        if ($val['equipItemIds']) {
                            if (strpos($val['equipItemIds'], '|') !== false) {
                                $equipItemIds = explode('|', $val['equipItemIds']);
                            } else {
                                $equipItemIds[] = $val['equipItemIds'];
                            }
                            $equipmentInfo = $modelClass->getEquipmentByIds($equipItemIds);
                            $val['equipment_tips'] = $equipmentInfo;
                        }

                    }



            }

        }
        //召唤师技能
        if (isset($data['summoner_skill'])) {
            $data['summoner_skill'] = json_decode($data['summoner_skill'], true);
            if (!empty($data['summoner_skill'])) {
                $functionList = $this->checkFunction($functionList,"kplSummoner");
                $modelClass = $functionList['kplSummoner']['class'] ?? '';
                if (isset($data['summoner_skill']['summonerSkillId']) && $data['summoner_skill']['summonerSkillId']) {
                    if (strpos($data['summoner_skill']['summonerSkillId'], '|') !== false) {
                        $summonerSkillId = explode('|', $data['summoner_skill']['summonerSkillId']);
                    } else {
                        $summonerSkillId[] = $data['summoner_skill']['summonerSkillId'];
                    }
                    $summonerInfo = $modelClass->getSkillByIds($summonerSkillId);
                    $data['summoner_skill'] = $summonerInfo;
                }
            }
        }
        $kpl_hero_type = $this->kpl_hero_type;
        $data['type_name'] = $kpl_hero_type[$data['type']] ?? '';
        return $data;
    }

    public function processDota2Hero($data, $functionList)
    {
        $className = 'App\Collect\\hero\\dota2\\gamedota2';
        $collectModel = new $className;
        $hero_type = $collectModel->hero_type;
        $attack_type = $collectModel->attack_type;
        $role_type = $collectModel->role_type;
        $data['roles'] = json_decode($data['roles'], true);
        foreach ($data["roles"] as $key => $role) {
            $data["roles"][$key] = $role_type[$role];
        }
        $data['hero_type'] = $hero_type[$data['hero_type']];
        $data['attack_type'] = $attack_type[$data['attack_type']];
        $data['roles'] = json_encode($data['roles']);
        return $data;
    }
    public function processTotalTeam($data, $functionList)
    {
        if (isset($data['team_id'])) {
            $functionList = $this->checkFunction($functionList,"totalPlayerList");
            $modelClass = $functionList["totalPlayerList"]["class"];
            $function = $functionList["totalPlayerList"]['function'];
            $data['playerList'] = $modelClass->$function(['team_id' => $data['team_id'], "page_size" => 100]);
        }
        return $data;
    }

    public function processInformationList($data, $functionList)
    {
        foreach ($data as $key => $value) {
            if (isset($value['content'])) {
                $data[$key]['content'] = string_split(strip_tags(html_entity_decode($value['content'])), 100);
            }
        }
        return $data;
    }

    public function processInformation($data, $functionList)
    {
        if (isset($data['scws_list'])) {
            $data['scws_list'] = json_decode($data['scws_list'], true);
            $functionList = $this->checkFunction($functionList,"scwsKeyword");
            $modelClass = $functionList["scwsKeyword"]["class"];
            $disableKeywordList = $modelClass->getDisableList();
            foreach ($data['scws_list'] as $key => $word) {
                if (in_array($word['keyword_id'], $disableKeywordList)) {
                    unset($data['scws_list'][$key]);
                }
            }
            $data['scws_list'] = json_encode($data['scws_list']);
            return $data;
        }
    }

    public function processTotalPlayer($data, $functionList)
    {
        if (isset($data['player_id'])) {
            $functionList = $this->checkFunction($functionList,"totalTeamInfo");
            $functionList = $this->checkFunction($functionList,"totalPlayerList");
            $modelClass = $functionList["totalPlayerList"]["class"];
            $function = $functionList["totalPlayerList"]['function'];
            $teamModelClass = $functionList["totalTeamInfo"]["class"];
            $teamFunction = $functionList["totalTeamInfo"]['function'];
            $data['playerList'] = $modelClass->$function(['team_id' => $data['team_id'], "page_size" => 100]);
            foreach ($data['playerList'] as $key => $playerInfo) {
                if ($playerInfo['player_id'] == $data['player_id']) {
                    unset($data['playerList'][$key]);
                    break;
                }
            }
            $data['teamInfo'] = $teamModelClass->$teamFunction(['team_id' => $data['team_id'], "fields" => "team_id,team_name,description,logo"]);
        }
        return $data;
    }

    public function processTotalPlayerList($data, $functionList)
    {
        $functionList = $this->checkFunction($functionList,"totalTeamInfo");
        $teamModelClass = $functionList["totalTeamInfo"]["class"];
        $teamFunction = $functionList["totalTeamInfo"]['function'];
        foreach ($data as $key => $player) {
            if (isset($player['team_id'])) {
                $teamInfo = $teamModelClass->$teamFunction($player['team_id'], "team_name,team_id");
                if (isset($teamInfo['team_id'])) {
                    $data[$key]['team_info'] = $teamInfo;
                }
            }
        }
        return $data;
    }

    public function processScwsInformationList($data, $functionList, $params)
    {
        $functionList = $this->checkFunction($functionList,"informationList");
        $modelClass = $functionList["informationList"]["class"];
        $function = $functionList["informationList"]['function'];
        $data = $modelClass->$function(['ids' => array_column($data, "content_id"), "site" => $params['site'] ?? 0, "page" => $params['page'] ?? 1, "page_size" => $params['page_size'] ?? 10, "fields" => $params['fields'] ?? "id,title,logo,create_time,site_time,content,type"]);
        foreach ($data as $key => $info) {
            $data[$key]['content'] = string_split(strip_tags($info['content']), 100);
        }
        return $data;
    }

    public function process5118InformationList($data, $functionList)
    {
        $functionList = $this->checkFunction($functionList,"information");
        $modelClass = $functionList["information"]["class"];
        $function = $functionList["information"]['function'];
        foreach ($data as $key => $value) {
            $information = $modelClass->$function($value['content_id'], ["id", "title", "logo", "create_time", "site_time", "content", "type"]);
            if (isset($information['id'])) {
                $information['content'] = string_split(strip_tags(html_entity_decode($information['content'])), 100);
                $data[$key] = $information;
            }
        }
        return $data;
    }
    public function processBaiduInformationList($data, $functionList)
    {
        $functionList = $this->checkFunction($functionList,"information");
        $modelClass = $functionList["information"]["class"];
        $function = $functionList["information"]['function'];
        foreach ($data as $key => $value) {
            $information = $modelClass->$function($value['content_id'], ["id", "title", "logo", "create_time", "site_time", "content", "type"]);
            if (isset($information['id'])) {
                $information['content'] = string_split(strip_tags(html_entity_decode($information['content'])), 100);
                $data[$key] = $information;
            }
        }
        return $data;
    }

    public function processkeywordMapList($data, $functionList, $params)
    {
        if (isset($params['list'])) {
            if (count($data) > 0) {
                $ids = array_column($data, "content_id");
                //获取文章
                if ($params['content_type'] == "information") {
                    $p = array_merge($params['list'], ['ids' => $ids, "site" => $params['site'] ?? 0]);
                    $functionList = $this->checkFunction($functionList, "informationList");
                    $modelClass = $functionList["informationList"]["class"];
                    $function = $functionList["informationList"]['function'];
                    $data = $modelClass->$function($p);
                }
            } else {
                $data = [];
            }
        }
        return $data;
    }

    public function processkeywordMapCount($data, $functionList, $params)
    {
        if (isset($params['list'])) {
            if (count($data) > 0) {
                $ids = array_column($data, "content_id");
                //获取文章
                if ($params['content_type'] == "information") {
                    $p = array_merge($params['list'], ['ids' => $ids]);
                    $functionList = $this->checkFunction($functionList, "informationList");
                    $modelClass = $functionList["informationList"]["class"];
                    $function = $functionList["informationList"]['functionCount'];
                    $data = $modelClass->$function($p);
                }
            } else {
                $data = 0;
            }
        }
        return $data;
    }

    //检查方法列表
    public function checkFunction($functionList, $dataType,$source="")
    {
        $priviliageList = $this->getPriviliege();
        if(isset($priviliageList[$dataType]) && $priviliageList[$dataType]['withSource']==1)
        {
            $fullDataType = $dataType.'/'.$source;
        }
        elseif(isset($priviliageList[$dataType]) && $priviliageList[$dataType]['withSource']==0)
        {
            $fullDataType = $dataType;
        }
        if (isset($functionList[$fullDataType]) && isset($functionList[$fullDataType]['function'])) {
        } else {
            $f = $this->getFunction([$dataType => ["source"=>$source]],$source);
            if (isset($f[$fullDataType]['class'])) {
                $functionList[$fullDataType] = $f[$fullDataType];
            }
        }
        return $functionList;
    }

    public function processIntergratedTeam($data, $functionList, $params)
    {
        if (isset($data['tid']) && $data['tid'] > 0) {
            $intergrationService = (new IntergrationService());
            $data = $intergrationService->getTeamInfo(0, $data["tid"], 1, $params['reset'] ?? 0)['data'];
            $functionList = $this->checkFunction($functionList,"totalPlayerList");
            $functionList = $this->checkFunction($functionList,"matchList",$data['original_source']);
            $data['recentMatchList'] = [];
            $data['playerList'] = [];
            $modelClass = $functionList["totalPlayerList"]["class"];
            $function = $functionList["totalPlayerList"]['function'];
            $sourceList = config('app.intergration.player');
            if (count($data['intergrated_id_list'])) {
                $pidList = $modelClass->$function(['team_ids' => $data['intergrated_id_list'], "sources" => array_column($sourceList, "source"), "fields" => "player_id,pid", "page_size" => 100]);
                $pidList = array_unique(array_column($pidList, "pid"));
                foreach ($pidList as $pid) {
                    if ($pid > 0) {
                        $playerInfo = $intergrationService->getPlayerInfo(0, $pid, 1, $params['reset'] ?? 0);
                        if (strlen($playerInfo['data']['logo']) >= 10) {
                            $data['playerList'][] = getFieldsFromArray($playerInfo['data'] ?? [], "pid,player_name,logo,position");
                        }
                    }
                }
                $modelMatchList = $functionList["matchList"."/".$data['original_source']]["class"];
                $functionMatchList = $functionList["matchList"."/".$data['original_source']]["function"];
                $functionProcessMatchList = $functionList["matchList"."/".$data['original_source']]["functionProcess"];
                $matchList = $modelMatchList->$functionMatchList(["team_id" => $data['intergrated_site_id_list'][$data['original_source']] ?? [0],"start"=>1, "page_size" => 4]);
                $data['recentMatchList'] = $this->$functionProcessMatchList($matchList, $functionList,['source'=>$data['original_source']]);
            } else {
                $data['playerList'] = [];
                $data['recentMatchList'] = [];
            }

        } else {
            $data = [];
        }
        return $data;
    }

    public function processIntergratedTeamList($data, $functionList, $params)
    {
        $intergrationService = (new IntergrationService());
        foreach ($data as $key => $detailData) {
            if ($detailData['tid'] > 0) {
                $data[$key] = getFieldsFromArray($intergrationService->getTeamInfo(0, $detailData["tid"], 1, $params['reset'] ?? 0)['data'], $params['fields'] ?? "*");
                if ($data[$key]['team_name'] == 0) {
                    $data[$key] = getFieldsFromArray($intergrationService->getTeamInfo(0, $detailData["tid"], 1, 1)['data'], $params['fields'] ?? "*");
                }
            } else {
                $data[$key] = [];
            }
        }
        if (isset($params['tid']) && is_array($params['tid']) && count($params['tid']) > 0) {
            $tidList = array_flip(array_column($data, "tid"));
            $teamList = [];
            foreach ($params['tid'] as $tid) {
                if ($tid > 0 && isset($tidList[$tid])) {
                    $teamList[] = $data[$tidList[$tid]];
                }
            }
            if (count($teamList) > 0) {
                $data = $teamList;
            }
        }
        return $data;
    }

    public function processIntergratedPlayerList($data, $functionList, $params)
    {
        $intergrationService = (new IntergrationService());
        foreach ($data as $key => $detailData) {
            if ($detailData['pid'] > 0) {
                $data[$key] = getFieldsFromArray($intergrationService->getPlayerInfo(0, $detailData["pid"], 1, $params['reset'] ?? 0)['data'], $params['fields'] ?? "*");
                if ($data[$key]['player_name'] == 0) {
                    $data[$key] = getFieldsFromArray($intergrationService->getPlayerInfo(0, $detailData["pid"], 1, 1)['data'], $params['fields'] ?? "*");
                }
            } else {
                $data[$key] = [];
            }
        }
        return $data;
    }
    public function processIntergratedPlayerListByPlayer($data, $functionList, $params)
    {
        $intergrationService = (new IntergrationService());
        $data = array_combine(array_column($data,"player_id"),array_values($data));
        foreach ($data as $key => $detailData) {
            if ($detailData['pid'] > 0) {
                $data[$key] = getFieldsFromArray($intergrationService->getPlayerInfo(0, $detailData["pid"], 1, $params['reset'] ?? 0)['data'], $params['fields'] ?? "*");
                if ($data[$key]['player_name'] == 0) {
                    $data[$key] = getFieldsFromArray($intergrationService->getPlayerInfo(0, $detailData["pid"], 1, 1)['data'], $params['fields'] ?? "*");
                }
            } else {
                unset($data[$key]);
            }
        }
        return $data;
    }
    public function processIntergratedTeamListByTeam($data, $functionList, $params)
    {
        $intergrationService = (new IntergrationService());
        $data = array_combine(array_column($data,"team_id"),array_values($data));
        foreach ($data as $key => $detailData) {
            if ($detailData['tid'] > 0) {
                $data[$key] = getFieldsFromArray($intergrationService->getTeamInfo(0, $detailData["tid"], 1, $params['reset'] ?? 0)['data'], $params['fields'] ?? "*");
                if ($data[$key]['team_name'] == 0) {
                    $data[$key] = getFieldsFromArray($intergrationService->getTeamInfo(0, $detailData["tid"], 1, 1)['data'], $params['fields'] ?? "*");
                }
            } else {
                unset($data[$key]);
            }
        }
        return $data;
    }

    public function processIntergratedPlayer($data, $functionList, $params)
    {
        if ($data['pid'] > 0) {
            $intergrationService = (new IntergrationService());
            $data = $intergrationService->getPlayerInfo(0, $data["pid"], 1, $params['reset'] ?? 0)['data'];
            $ingergratedTeam = $intergrationService->getTeamInfo($data['team_id'], 0, 1, $params['reset'] ?? 0)['data'];
            $functionList = $this->checkFunction($functionList,"totalPlayerList");
            $sourceList = config('app.intergration.player');
            //echo $data['original_source'];echo "\n";echo  $data['game'];exit;
            $functionList = $this->checkFunction($functionList,"matchList",$data['original_source']);
            $data['recentMatchList'] = [];
            $data['playerList'] = [];
            $data['teamInfo'] = $ingergratedTeam;
            $modelClass = $functionList["totalPlayerList"]["class"];
            $function = $functionList["totalPlayerList"]['function'];
            $pidList = $modelClass->$function(["sources" => array_column($sourceList, "source"), 'except_pid' => $data["pid"], 'team_ids' => $ingergratedTeam['intergrated_id_list'], "fields" => "player_id,pid", "page_size" => 100]);
            $pidList = array_unique(array_column($pidList, "pid"));
            foreach ($pidList as $pid) {
                if ($pid > 0) {
                    $data['playerList'][] = getFieldsFromArray($intergrationService->getPlayerInfo(0, $pid, 1, $params['reset'] ?? 0)['data'] ?? [], "pid,player_name,logo,position");
                }
            }
            $radarData = [];
            $radarArray = ['kill' => "击杀", 'assists' => "助攻", 'join_rate' => "参团率", 'visual_field' => "视野", 'survival' => '生存', 'economy' => '经济'];
            foreach ($radarArray as $key => $name) {
                $radarData[$key] = ["name" => $name, "empno" => intval(rand(40, 100))];
            }

            $modelMatchList = $functionList["matchList"."/".$data['original_source']]["class"];
            $functionMatchList = $functionList["matchList"."/".$data['original_source']]["function"];
            $functionProcessMatchList = $functionList["matchList"."/".$data['original_source']]["functionProcess"];
            $matchList = $modelMatchList->$functionMatchList(["team_id" => $ingergratedTeam['intergrated_site_id_list'][$data['original_source']] ?? [0],"start"=>1, "page_size" => 10]);
            $data['recentMatchList'] = $this->$functionProcessMatchList($matchList, $functionList, ["source"=>$data['original_source'],"pid" => $data["pid"], "player_id" => $data['intergrated_site_id_list'][$data['original_source']] ?? [0]]);
            $data['radarData'] = $radarData;
        } else {
            $data = [];
        }
        return $data;
    }
}
