<?php

namespace App\Services;

use App\Models\CollectResultModel as CollectModel;
use App\Models\MissionModel as MissionModel;
use App\Models\TeamModel as TeamModel;
use App\Models\PlayerModel as PlayerModel;

class MissionService
{
    //爬取数据
    public function collect($game = "", $source = "", $mission_type = '')
    {
        //获取爬取任务列表
        $mission_list = $this->getMission($game, $source, $mission_type, 100);
        $collectModel = new CollectModel();
        $missionModel = new MissionModel();
        //初始化空的类库列表
        $classList = [];
        //循环任务列表
        if (!empty($mission_list)) {
            foreach ($mission_list as $key => $mission) {
                //数据解包
                $mission['detail'] = json_decode($mission['detail'], true);
                //如果必要元素存在
                if (isset($mission['source'])) {
                    //生成类库路径
                    $className = 'App\Collect\\' . $mission['mission_type'] . '\\' . $mission['game'] . '\\' . $mission['source'];
                    //判断类库存在
                    $exist = class_exists($className);
                    //如果不存在
                    if (!$exist) {
                        echo $className . " not found\n";
                    } else {
                        //之前没有初始化过
                        if (!isset($classList[$className])) {
                            //初始化，存在列表中
                            $class = new $className;
                            $classList[$className] = $class;
                        } else {
                            //直接调用
                            $class = $classList[$className];
                        }
                        //执行爬取操作
                        $result = $class->collect($mission);
                        //如果爬取成功
                        if ($result) {
                            try {
                                //保存结果
                                $rt = $collectModel->insertCollectResult($result);
                                echo 'act:insert,id='.$rt.' lenth:'.strlen(json_encode($result));
                                //如果保存成功
                                if ($rt) {
                                    //更新任务状态，以后改成接口模式
                                    $missionModel->updateMission($mission['mission_id'], ['mission_status' => 2]);
                                } else {
                                    $return = false;
                                    return  $return;
                                }
                            } catch (\Exception $e) {
                                return $e->getMessage();
                            }
                        } else {
                            return false;
                        }
                    }
                }
                //随机等待
                $sleep = rand(10, 20);
                sleep($sleep);
                echo $sleep . "\n";
            }
        }
    }

    //爬取数据

    public function getMission($game, $source, $mission_type, $count = 3)
    {
        $asign = config('app.asign');
        $missionModel = new MissionModel();
        $mission_list = $missionModel->getMissionByMachine($asign, $count, $game, $source, $mission_type);
        return ($mission_list);
    }

    public function process($game = "kpl", $source = "", $mission_type)
    {
        //获取爬取任务列表
        $collectModel = new CollectModel();
        $missionModel = new MissionModel();
        $teamModel = new TeamModel();
        $playerModel = new PlayerModel();
        $result_list = $collectModel->getResult(100, $game, $source, $mission_type);

        //初始化空的类库列表
        $classList = [];
        //循环任务列表
        foreach ($result_list as $key => $result) {
            echo "start to process result:".$result['id']."\n";
            //数据解包
            $result['content'] = json_decode($result['content'], true);
            //如果结果数组非空
            if (is_array($result['content']) && count($result['content']) > 0) {
                //生成类库路径
                $className = 'App\Collect\\' . $result['mission_type'] . '\\' . $result['game'] . '\\' . $result['source'];
                $classList = $this->getClass($classList, $className);
                if (!isset($classList[$className])) {
                    echo $className . " not found\n";
                } else {
                    $class = $classList[$className];
                    //执行爬取操作
                    $processResult = $class->process($result);
                    if (!is_array($processResult)) {
                        echo "id:" . $result['id'] . "\n";
                        echo "mission_type:" . $result['mission_type'] . "\n";
                        echo "source:" . $result['source'] . "\n";
                        //die();
                    }
                    if ($result['mission_type'] == "team") {
                        $save = $teamModel->saveTeam($result["game"], $processResult);
                        echo "-----save:\n";
                        print_r($save);
                        echo "-----save:\n";
                        if (method_exists($class, "processMemberList")) {
                            $missionList = $class->processMemberList($save['team_id'], $result);
                            foreach ($missionList as $mission) {
                                $mission = array_merge($mission, ['title' => $mission['title'], 'game' => $result['game'], 'connect_mission_id' => $result['mission_id'], 'source' => $result['source'], 'asign_to' => 1]);
                                $insert = $missionModel->insertMission($mission);
                                echo "insertMisson4Member:" . $insert . "\n";
                            }
                            //die();
                        } else {
                            echo "no member\n";
                        }
                    } elseif ($result['mission_type'] == "player") {
                        $save = $playerModel->savePlayer($result["game"], $processResult);
                    } elseif ($result['mission_type'] == "hero") {
                        //生成类库路径
                        $modelClassName = 'App\Models\Hero\\' . $result['game'] . "Model";
                        $classList = $this->getClass($classList, $modelClassName);
                        if (isset($classList[$modelClassName])) {
                            $modelClass = $classList[$modelClassName];
                            $save = $modelClass->saveHero($processResult);
                        }
                    } elseif ($result['mission_type'] == "equipment") {
                        //生成类库路径
                        $modelClassName = 'App\Models\Equipment\\' . $result['game'] . "Model";
                        $classList = $this->getClass($classList, $modelClassName);
                        if (isset($classList[$modelClassName])) {
                            $modelClass = $classList[$modelClassName];
                            foreach ($processResult as $equipment) {
                                $save = $modelClass->saveEquipment($equipment);
                            }
                        }
                    } elseif ($result['mission_type'] == "summoner") {
                        //生成类库路径
                        $modelClassName = 'App\Models\Summoner\\' . $result['game'] . "Model";
                        $classList = $this->getClass($classList, $modelClassName);
                        if (isset($classList[$modelClassName])) {
                            $modelClass = $classList[$modelClassName];
                            foreach ($processResult as $summomer) {
                                $save = $modelClass->saveSkill($summomer);
                            }
                        }
                    } elseif ($result['mission_type'] == "inscription") {
                        //生成类库路径
                        $modelClassName = 'App\Models\Inscription\\' . $result['game'] . "Model";
                        $classList = $this->getClass($classList, $modelClassName);
                        if (isset($classList[$modelClassName])) {
                            $modelClass = $classList[$modelClassName];
                            foreach ($processResult as $inscription) {
                                $save = $modelClass->saveInscription($inscription);
                            }
                        }
                    } elseif ($result['mission_type'] == "runes") {
                        //生成类库路径
                        $modelClassName = 'App\Models\Rune\\' . $result['game'] . "Model";
                        $classList = $this->getClass($classList, $modelClassName);
                        if (isset($classList[$modelClassName])) {
                            $modelClass = $classList[$modelClassName];
                            if (isset($processResult['runeDetail'])) {
                                foreach ($processResult['rune'] as $equipment) {
                                    $save = $modelClass->saveRune($equipment);
                                }
                                $detailModelClassName = 'App\Models\Rune\\' . $result['game'] . "DetailModel";
                                $classList = $this->getClass($classList, $detailModelClassName);
                                $detailModelClass = $classList[$detailModelClassName];
                                foreach ($processResult['runeDetail'] as $equipment) {
                                    $save = $detailModelClass->saveRuneDetail($equipment);
                                }
                            } else {
                                foreach ($processResult as $rune) {
                                    $save = $modelClass->saveRune($rune);
                                }
                            }
                        }
                    }
                    elseif ($result['mission_type'] == "match") {
                         if (isset($processResult['match_list'])) {
                                $ModelClassName = 'App\Models\Match\\'.$result['source'].'\\matchListModel';
                                $classList = $this->getClass($classList, $ModelClassName);
                                $ModelClass = $classList[$ModelClassName];
                                foreach($processResult['match_list'] as $key => $value)
                                {
                                    $save = $ModelClass->saveMatch($value);
                                    echo "saveMatch:".$save."\n";
                                }
                            }
                            if (isset($processResult['team'])) {
                                $ModelClassName = 'App\Models\Match\\'.$result['source'].'\\teamModel';
                                $classList = $this->getClass($classList, $ModelClassName);
                                $ModelClass = $classList[$ModelClassName];
                                foreach($processResult['team'] as $key => $value)
                                {
                                    $saveTeam = $ModelClass->saveTeam($value);
                                    echo "saveTeam:".$saveTeam."\n";
                                }
                            }
                            if (isset($processResult['tournament'])) {
                                $ModelClassName = 'App\Models\Match\\'.$result['source'].'\\tournamentModel';
                                $classList = $this->getClass($classList, $ModelClassName);
                                $ModelClass = $classList[$ModelClassName];
                                foreach($processResult['tournament'] as $key => $value)
                                {
                                    $saveTournament = $ModelClass->saveTournament($value);
                                    echo "saveTournament:".$saveTournament."\n";
                                }
                            }
                            if (isset($processResult['event'])) {
                                $ModelClassName = 'App\Models\Match\\'.$result['source'].'\\eventModel';
                                $classList = $this->getClass($classList, $ModelClassName);
                                $ModelClass = $classList[$ModelClassName];
                                foreach($processResult['event'] as $key => $value)
                                {
                                    $saveEvent = $ModelClass->saveEvent($value);
                                    echo "saveEvent:".$saveEvent."\n";
                                }
                            }

                    }
                    elseif ($result['mission_type'] == "event")
                    {
                        $ModelClassName = 'App\Models\Match\\'.$result['source'].'\\tournamentNoIdModel';
                        $classList = $this->getClass($classList, $ModelClassName);
                        $ModelClass = $classList[$ModelClassName];

                        $tModelClassName = 'App\Models\Match\\'.$result['source'].'\\tournamentModel';
                        $classList = $this->getClass($classList, $tModelClassName);
                        $tModelClass = $classList[$tModelClassName];
                        $currentTournament = $tModelClass->getTournamentByName($processResult['tournament_name'],$processResult['game']);
                        if(!isset($currentTournament['tournament_id']))
                        {
                            $save = $ModelClass->saveTournament($processResult);
                            echo "saveEvent:".$save."\n";
                        }
                        else
                        {
                        }
                    }
                    if (is_array($save)) {
                        echo "save:" . $save['result'] . "\n";
                        if ($save['result'] > 0) {
                            $collectModel->updateStatus($result['id'], ['status' => 2]);
                        }
                    } else {
                        echo "save:" . $save . "\n";
                        if ($save > 0) {
                            $collectModel->updateStatus($result['id'], ['status' => 2]);
                        }
                    }
                }
            }
            //随机等待
            $sleep = rand(1, 2);
            //sleep($sleep);
            echo $sleep . "\n";
        }
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

    public function insertMission($data)
    {
        return (new MissionModel())->insertMission($data);
    }
}
