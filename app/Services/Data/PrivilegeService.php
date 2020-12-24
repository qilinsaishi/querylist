<?php

namespace App\Services\Data;

class PrivilegeService
{
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
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => "chaofan"],
                    ['model' => 'App\Models\Match\#source#\tournamentModel', 'source' => "cpseo"],
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
            "defaultConfig" => [//通用配置
                'list' => [
                    ['model' => 'App\Models\Admin\DefaultConfig', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getDefaultConfigList",
                'functionCount' => "getDefaultCount",
                'functionSingle' => "getDefaultConfigByKey",
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
            "lolRuneList" => [//lol召唤师列表
                'list' => [
                    ['model' => 'App\Models\Rune\lolModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getRuneList",
                'functionCount' => "getRuneCount",
                'functionProcess' => "processRuneList",//格式化的处理方法
                'functionSingle' => "getRuneById",
            ],
            "lolRune" => [//lol召唤师详情
                'list' => [
                    ['model' => 'App\Models\Rune\lolModel', 'source' => ''],
                ],
                'withSource' => 0,
                'function' => "getRuneById",
                'functionProcess' => "processRuneList",//格式化的处理方法
                'functionSingle' => "getRuneById",
            ],



        ];
        return $privilegeList;
    }

    public function getFunction($data, $currentSource = "")
    {
        $currentSource = "";
        //获取各个数据类型对应的类库优先级列表以及获取方法
        $priviliegeList = $this->getPriviliege();
        $classList = [];
        $functionList = [];
        foreach ($data as $dataType => $params) {
            //echo "found type:".$dataType."\n";
            // echo "currentSource:".$currentSource."\n";
            $found = 0;
            if (isset($priviliegeList[$dataType])) {
                if ($currentSource == "" && $priviliegeList[$dataType]['withSource'] == 1) {
                    foreach ($priviliegeList[$dataType]['list'] as $detail) {
                        $modelName = $detail['model'];
                        $currentSource = $currentSource == "" ? $detail['source'] : $currentSource;
                        $modelName = str_replace("#source#", $detail['source'], $modelName);
                        $classList = $this->getClass($classList, $modelName);
                        if (!isset($functionList[$dataType])) {
                            if (isset($classList[$modelName])) {
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
                } elseif ($currentSource != "" && $priviliegeList[$dataType]['withSource'] == 1) {
                    $functionList[$dataType]['source'] = $currentSource;//$priviliegeList[$dataType]['source'];
                    $list = array_combine(array_column($priviliegeList[$dataType]['list'], "source"), array_column($priviliegeList[$dataType]['list'], "model"));
                    if (isset($list[$currentSource])) {
                        $modelName = $list[$currentSource];
                        $modelName = str_replace("#source#", $currentSource, $modelName);
                        $classList = $this->getClass($classList, $modelName);
                        if (method_exists($classList[$modelName] ?? [], $priviliegeList[$dataType]['function'])) {
                            $functionList[$dataType] = ["className" => $modelName, "class" => $classList[$modelName], "function" => $priviliegeList[$dataType]['function']];
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
                        }
                    }
                    if ($found == 0) {
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
                    $functionList[$dataType]['source'] = $currentSource;//$priviliegeList[$dataType]['source'];
                }elseif($currentSource == "" && $priviliegeList[$dataType]['withSource'] == 0){
                    foreach ($priviliegeList[$dataType]['list'] as $detail) {
                        $modelName = $detail['model'];
                        $currentSource = $currentSource == "" ? $detail['source'] : $currentSource;
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
                                    $found = 1;
                                } else {
                                    echo "class:".$modelName.",function:".$priviliegeList[$dataType]['function']." not found\n";
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

    public function processRuneList($data, $functionList)
    {

        //判断战队
        /*if (isset($functionList['teamList']) && isset($functionList['teamList']['functionSingle'])) {

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
        }*/
        return $data;
    }
}
