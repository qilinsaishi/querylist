<?php

namespace App\Services\Data;
use App\Services\Data\PrivilegeService;
use App\Services\Data\RedisService;

class DataService
{
    public function getData($data)
    {
        $redisService = new RedisService();
        $privilegeService = new PrivilegeService();
        $return = [];
        $functionList = $privilegeService->getFunction($data);
        foreach($data as $name => $params)
        {
            $dataType = $params['dataType']??$name;
            if(isset($functionList[$dataType]))
            {
                $toSave = 1;
                if(isset($params['cacheWith']))
                {
                    if(isset($params['cache_time']))
                    {
                        $p = array_merge($data[$params['cacheWith']]??[],['cache_time'=>$params['cache_time'],"source"=>$params['source']??""]);
                    }
                    else
                    {
                        $p = array_merge($data[$params['cacheWith']]??[],["source"=>$params['source']??""]);
                    }
                }
                else
                {
                    $p = $params;
                }
                $dataArr = $redisService->processCache($dataType,$p);
                if(is_array($dataArr))
                {

                    //$return[$dataType] = $cache;
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
                            $p = array_merge($data[$params['cacheWith']]??[],['cache_time'=>$params['cache_time'],"source"=>$params['source']??""]);
                        }
                        else
                        {
                            $p = array_merge($data[$params['cacheWith']]??[],["source"=>$params['source']??""]);
                        }
                    }
                    else
                    {
                        $p = $params;
                    }

                    $redisService->saveCache($dataType,$p,$dataArr);
                }
                if(isset($dataType) && $dataType='informationList') {
                    $dataArr["data"] = (new ExtraProcessService())->process($dataType,$dataArr["data"]);
                }

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
            "newsdetail"=>['dataType'=>'informationList',"page_size"=>1000,"game"=>'lol',"source"=>"cpseo","fields"=>'id'],
            ],
            3=>[
            "teamdetail"=>['dataType'=>'totalTeamList',"page_size"=>1000,"game"=>'kpl',"source"=>"cpseo","fields"=>'team_id'],
            "playerdetail"=>['dataType'=>'totalPlayerList',"page_size"=>1000,"game"=>'kpl',"source"=>"cpseo","fields"=>'player_id'],
            "herodetail"=>['dataType'=>'kplHeroList','page_size'=>100,"fields"=>"hero_id"],
            "newsdetail"=>['dataType'=>'informationList',"page_size"=>1000,"game"=>'kpl',"source"=>"cpseo","fields"=>'id'],
            ],
            2=>[
                "detail"=>['dataType'=>'informationList',"page_size"=>1000,"game"=>'lol',"source"=>"cpseo","fields"=>'id'],
            ],
            4=>[
                "newsdetail"=>['dataType'=>'informationList',"page_size"=>1000,"game"=>'dota2',"type"=>"1,2,3,4,5","source"=>"gamedota2","fields"=>'id'],
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
