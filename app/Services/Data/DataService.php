<?php

namespace App\Services\Data;
use App\Services\Data\PrivilegeService;
use App\Services\Data\RedisService;
use App\Services\BannedWordService;

class DataService
{
    public function getData($data)
    {
        $bannedWordService = new BannedWordService();
        $redisService = new RedisService();
        $privilegeService = new PrivilegeService();
        $return = [];
        $functionList = $privilegeService->getFunction($data);
        $priviliageList = $privilegeService->getPriviliege();
        foreach($data as $name => $params)
        {
            $start = microtime(true);
            $dataType = $params['dataType']??$name;
            $view = $redisService->addViews($dataType,$params);
            if(isset($priviliageList[$dataType]) && $priviliageList[$dataType]['withSource']==1)
            {
                $fullType = $dataType.($priviliageList[$dataType]['withSource']==1?("/".$params['source']):"");
            }
            elseif(isset($priviliageList[$dataType]) && $priviliageList[$dataType]['withSource']==0)
            {
                $fullType = $dataType;
            }
            else
            {
                continue;
            }
            //echo "fullType:".$fullType."-".$name."\n";
            if(isset($functionList[$fullType]))
            {
                //echo "fullType:".$fullType."-".$name."\n";
                $toSave = 1;
                if(isset($params['cacheWith']))
                {
                    if(isset($params['cache_time']))
                    {
                        $p = array_merge($data[$params['cacheWith']]??[],['cache_time'=>$params['cache_time'],"source"=>$params['source']??"",'game'=>$params['game']??""]);
                    }
                    else
                    {
                        $p = array_merge($data[$params['cacheWith']]??[],["source"=>$params['source']??"",'game'=>$params['game']??""]);
                    }
                }
                else
                {
                    $p = $params;
                }
                $dataArr = $redisService->processCache($dataType,$p);
                //$dataArr = false;
                if(is_array($dataArr))
                {
                    $toSave = 0;
                }
                else
                {
                    $functionInfo = $functionList[$fullType];
                    $class = $functionInfo['class'];
                    $function = $functionInfo['function'];
                    $d = $class->$function($params);
                    $functionCount = $functionInfo['functionCount'];
                    $functionProcess = $functionInfo['functionProcess']??"";
                    $functionProcessCount = $functionInfo['functionProcessCount']??"";
                    if(!$functionCount || $functionCount=="")
                    {
                        $count = 0;
                    }
                    else
                    {
                        $count = $class->$functionCount($params);
                    }
                    if($functionProcessCount!="")
                    {
                        $count = $privilegeService->$functionProcessCount($d,$functionList,$params);
                    }
                    if($functionProcess!="")
                    {
                        if(isset($params['process']) && ($params['process']==0))
                        {

                        }
                        else
                        {
                            $d = $privilegeService->$functionProcess($d,$functionList,$params);
                        }
                    }
                    $dataArr = ['data'=>$d,'count'=>$count];
                }
                if($toSave==1)
                {
                    if(isset($params['cacheWith']))
                    {
                        if(isset($params['cache_time']))
                        {
                            $p = array_merge($data[$params['cacheWith']]??[],['cache_time'=>$params['cache_time'],"source"=>$params['source']??"",'game'=>$params['game']??""]);
                        }
                        else
                        {
                            $p = array_merge($data[$params['cacheWith']]??[],["source"=>$params['source']??"",'game'=>$params['game']??""]);
                        }
                    }
                    else
                    {
                        $p = $params;
                    }
                    $redisService->saveCache($dataType,$p,$dataArr);
                }
                if(isset($dataType) && $dataType=='informationList') {
                    $dataArr["data"] = (new ExtraProcessService())->process($dataType,$dataArr["data"]);
                }
                if(in_array($dataType,['information','informationList']))
                {
                    foreach($dataArr["data"] as $k_1 => $v_1)
                    {
                        if(is_array($v_1))
                        {
                            foreach($v_1 as $k_2 => $v_2)
                            {
                                if(!is_array($v_2))
                                {
                                    $dataArr["data"][$k_1][$k_2] = $bannedWordService->sensitive($v_2);
                                }
                            }
                        }
                        else
                        {
                            $dataArr["data"][$k_1] = $bannedWordService->sensitive($v_1);
                        }
                    }
                }

                $dataArr['processTime'] = microtime(true)-$start;
                $dataArr['cached'] = $toSave==1?0:1;
                $dataArr['view_key'] = $view;
                $return[$name] = $dataArr;
            }
        }
        return $return;
    }
    public function siteMap($data)
    {
        $return = [];
        $siteMapConfig = [
            1=>[
            "teamdetail"=>['dataType'=>'totalTeamList',"page_size"=>1000,"game"=>'lol',"source"=>"cpseo","fields"=>'team_id','reset'=>1],
            "playerdetail"=>['dataType'=>'totalPlayerList',"page_size"=>1000,"game"=>'lol',"source"=>"cpseo","fields"=>'player_id','reset'=>1],
            "herodetail"=>['dataType'=>'lolHeroList','page_size'=>100,"fields"=>"hero_id",'reset'=>1],
            "newsdetail"=>['dataType'=>'informationList',"page_size"=>1000,"site"=>1,"fields"=>'id','reset'=>1],
            ],
            3=>[
            "teamdetail"=>['dataType'=>'totalTeamList',"page_size"=>1000,"game"=>'kpl',"source"=>"scoregg","fields"=>'team_id','reset'=>1],
            "playerdetail"=>['dataType'=>'totalPlayerList',"page_size"=>1000,"game"=>'kpl',"source"=>"scoregg","fields"=>'player_id','reset'=>1],
            "herodetail"=>['dataType'=>'kplHeroList','page_size'=>100,"fields"=>"hero_id",'reset'=>1],
            "newsdetail"=>['dataType'=>'informationList',"page_size"=>1000,"site"=>3,"fields"=>'id','reset'=>1],
            ],
            2=>[
                "detail"=>['dataType'=>'informationList',"page_size"=>1000,"site"=>1,"fields"=>'id','reset'=>1],
            ],
            4=>[
                "newsdetail"=>['dataType'=>'informationList',"page_size"=>1000,"site"=>3,"fields"=>'id','reset'=>1],
                "videodetail"=>['dataType'=>'informationList',"page_size"=>1000,"game"=>'dota2',"type"=>"7","source"=>"gamedota2","fields"=>'id','reset'=>1],
                "tournament"=>["dataType"=>"tournamentList","game"=>'dota2',"page"=>1,"page_size"=>1000,"source"=>"gamedota2","fields"=>'tournament_id','reset'=>1],
            ],
            5=>[
                "newsdetail"=>['dataType'=>'informationList',"page_size"=>10,"site"=>5,"fields"=>'id','reset'=>1],
                "teamdetail"=>['dataType'=>'intergratedTeamList',"page_size"=>200,"game"=>['lol','kpl','dota2'],"fields"=>'tid,team_name','reset'=>1],
                "playerdetail"=>['dataType'=>'intergratedPlayerList',"page_size"=>200,"game"=>['lol','kpl','dota2'],"fields"=>'pid,player_name','reset'=>1],
                "matchdetail"=>['dataType'=>'matchList',"page_size"=>100,"game"=>['lol','kpl'],"fields"=>'match_id,game',"source"=>"scoregg","process"=>0],
                "matchdetail_2"=>['aka'=>'matchdetail','dataType'=>'matchList',"page_size"=>100,"game"=>"dota2","fields"=>'match_id,game',"source"=>"shangniu","process"=>0],
            ],
        ];
        $menu = $siteMapConfig[$data['site_id']]??[];
        foreach($menu as $type => $menu_detail)
        {
            $name = $menu_detail["aka"]??$type;
            $return[$name] = $return[$name]??[];
            $page = 1;
            $count = 1;
            while($count>0)
            {
                $menu_detail['page'] = $page;
                $menu_detail['recent'] = $data['recent']??0;
                $dataList = $this->getData([$name=>$menu_detail]);
                $count = count($dataList[$name]['data']);
                if($count>0)
                {
                    $fields = explode(",",$menu_detail['fields']);
                    $return[$name] = array_merge($return[$name],array_column($dataList[$name]['data'],$fields['0']));
                }
                $page ++;
            }
        }
        return $return;
    }

}
