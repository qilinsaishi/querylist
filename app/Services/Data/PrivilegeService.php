<?php

namespace App\Services\Data;

class PrivilegeService
{
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
            "matchList" => [
                'list' => [
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'cpseo'],
                    ['model' => 'App\Models\Match\#source#\matchListModel', 'source' => 'chaofan'],
                ],
                'withSource' => 1,
                'function' => "getMatchList",//获取数据方法
                'functionCount' => "getMatchCount",//获取列表方法
                'functionProcess' => "processMatchList",//格式化的处理方法
            ],
            "tournament" => [
                'list' => [
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => "cpseo"],
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => "chaofan"],
                ],
                'withSource' => 1,
                'function' => "getTournamentList",
                'functionCount' => "getTournamentCount",
                'functionSingle' => "getTournamentById",

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
                'functionProcess' => "processTotalTeam"
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
                'functionProcess' => "processTotalPlayer"
            ],
            "information" => [//资讯
                'list' => [
                    ['model' => 'App\Models\InformationModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getInformationById",
                'functionCount' => "",
                'functionSingle' => "getInformationById",
                'functionProcess' => "processInformation"
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
            "kplHero" => [//dota2英雄详情
                'list' => [
                    ['model' => 'App\Models\Hero\dota2Model', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getHeroById",
                'functionSingle' => "getHeroById",
                'functionProcess' => "processDota2Hero",
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
            //默认没找到
            $found = 0;
            //数据类型出现在优先级列表
            if (isset($priviliegeList[$dataType])) {
                //echo "found type:".$dataType."\n";
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
                                    $found = 1;
                                } else {
                                    //echo "class:".$modelName.",function:".$priviliegeList[$dataType]['function']." not found\n";
                                }
                            } else {
                                //echo "class:".$modelName.",not found\n";
                            }
                            $functionList[$dataType]['source'] = $currentSource;//$priviliegeList[$dataType]['source'];
                        }
                    }
                } //已经初始化数据来源 且 当前数据类型需要包含数据来源
                elseif ($currentSource != "" && $priviliegeList[$dataType]['withSource'] == 1) {
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
                        }
                    }
                    //如果没找到
                    if ($found == 0) {
                        //循环
                        foreach ($priviliegeList[$dataType]['list'] as $detail) {
                            $modelName = $detail['model'];
                            $modelName = str_replace("#source#", $detail['source'], $modelName);
                            $classList = $this->getClass($classList, $modelName);
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
        }//print_r($functionList);exit;
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

    public function processMatchList($data, $functionList)
    {
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
            $f = $this->getFunction(['teamList' => []], $functionList['matchList']['source']);
            if (isset($f['teamList']['class'])) {
                $functionList["teamList"] = $f['teamList'];
            }
        }
        if (!isset($functionList["teamList"]["class"]) || !isset($functionList['teamList']['functionSingle'])) {
            return $data;
        }
        $modelClass = $functionList["teamList"]["class"];
        $functionSingle = $functionList["teamList"]['functionSingle'];

        $teamList = [];
        $tournament = [];
        foreach ($data as $key => $matchInfo) {
            //赛事信息
            if (!isset($tournament[$matchInfo['tournament_id']])) {
                $tournamentInfo = $modelTournamentClass->$functionTournamentSingle($matchInfo['tournament_id']);
                if (isset($tournamentInfo['tournament_id'])) {
                    $tournament[$matchInfo['tournament_id']] = $tournamentInfo;
                }
            }
            //战队信息
            if (!isset($teamList[$matchInfo['home_id']])) {
                $teamInfo = $modelClass->$functionSingle($matchInfo['home_id']);
                if (isset($teamInfo['team_id'])) {
                    $teamList[$matchInfo['home_id']] = $teamInfo;
                }

            }
            if (!isset($teamList[$matchInfo['away_id']])) {
                $teamInfo = $modelClass->$functionSingle($matchInfo['away_id']);
                if (isset($teamInfo['team_id'])) {
                    $teamList[$matchInfo['away_id']] = $teamInfo;
                }
            }
            $data[$key]['home_team_info'] = $teamList[$matchInfo['home_id']] ?? [];//战队
            $data[$key]['away_team_info'] = $teamList[$matchInfo['away_id']] ?? [];
            $data[$key]['tournament_info'] = $tournament[$matchInfo['tournament_id']] ?? [];
        }
        return $data;
    }

    public function processPlayerList($data, $functionList)
    {
        if (isset($functionList['playerList']) && isset($functionList['playerList']['functionSingle'])) {

        } else {
            $f = $this->getFunction(['playerList' => []], $functionList['teamList']['source']);
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
}
