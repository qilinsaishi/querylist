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
        foreach($data as $name => $params)
        {
            $start = microtime(true);
            $dataType = $params['dataType']??$name;
            $view = $redisService->addViews($dataType,$params);
            if(isset($functionList[$dataType]))
            {
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
                if(is_array($dataArr))
                {
                    $toSave = 0;
                }
                else
                {
                    $functionInfo = $functionList[$dataType];
                    $class = $functionInfo['class'];
                    $function = $functionInfo['function'];
                    //$params = $data[$dataType];
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
                        $d = $privilegeService->$functionProcess($d,$functionList,$params);
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
            "teamdetail"=>['dataType'=>'totalTeamList',"page_size"=>1000,"game"=>'lol',"source"=>"cpseo","fields"=>'team_id'],
            "playerdetail"=>['dataType'=>'totalPlayerList',"page_size"=>1000,"game"=>'lol',"source"=>"cpseo","fields"=>'player_id'],
            "herodetail"=>['dataType'=>'lolHeroList','page_size'=>100,"fields"=>"hero_id"],
            "newsdetail"=>['dataType'=>'informationList',"page_size"=>1000,"site"=>1,"fields"=>'id'],
            ],
            3=>[
            "teamdetail"=>['dataType'=>'totalTeamList',"page_size"=>1000,"game"=>'kpl',"source"=>"scoregg","fields"=>'team_id'],
            "playerdetail"=>['dataType'=>'totalPlayerList',"page_size"=>1000,"game"=>'kpl',"source"=>"scoregg","fields"=>'player_id'],
            "herodetail"=>['dataType'=>'kplHeroList','page_size'=>100,"fields"=>"hero_id"],
            "newsdetail"=>['dataType'=>'informationList',"page_size"=>1000,"site"=>3,"fields"=>'id'],
            ],
            2=>[
                "detail"=>['dataType'=>'informationList',"page_size"=>1000,"site"=>3,"fields"=>'id'],
            ],
            4=>[
                "newsdetail"=>['dataType'=>'informationList',"page_size"=>1000,"site"=>3,"fields"=>'id'],
                "videodetail"=>['dataType'=>'informationList',"page_size"=>1000,"game"=>'dota2',"type"=>"7","source"=>"gamedota2","fields"=>'id'],
                "tournament"=>["dataType"=>"tournamentList","game"=>'dota2',"page"=>1,"page_size"=>1000,"source"=>"gamedota2","fields"=>'tournament_id'],
            ],
        ];
        $menu = $siteMapConfig[$data['site_id']]??[];
        foreach($menu as $type => $menu_detail)
        {
            $return[$type] = [];
            $page = 1;
            $count = 1;
            while($count>0)
            {
                $menu_detail['page'] = $page;
                $menu_detail['recent'] = $data['recent']??0;
                $dataList = $this->getData([$type=>$menu_detail]);
                $count = count($dataList[$type]['data']);
                if($count>0)
                {
                    $return[$type] = array_merge($return[$type],array_column($dataList[$type]['data'],$menu_detail['fields']));
                }
                $page ++;
            }
        }
        return $return;
    }

}
