<?php

namespace App\Services;

use App\Models\InformationModel;
use App\Models\KeywordMapModel;
use App\Models\ScwsMapModel;
use App\Models\ScwsKeywordMapModel;
use App\Models\TeamModel as TeamModel;
use App\Models\PlayerModel as PlayerModel;
use App\Models\KeywordsModel as KeywordsModel;
use App\Models\Hero\lolModel as lolHeroModel;
use App\Models\Hero\kplModel as kplHeroModel;
use App\Models\Hero\dota2Model as dota2HeroModel;
use App\Services\Data\RedisService;


class KeywordService
{
    public $expect_keywords = [
        "nbsp","lt","gt","span","quot"
    ];
    //爬取数据
    public function information($game = "")
    {
        if(!in_array($game,['lol','kpl','dota2']))
        {
            return true;
        }
        //$redisService = new RedisService();
        $informationModel = (new InformationModel());
        $result = [];
        $informationList = $informationModel->getInformationList(["game"=>$game,"keywords"=>1,"fields"=>"id","page_size"=>1000]);
        $informationList = array_column($informationList,"id");
        foreach($informationList as $id)
        {
            $this->processKeyword($id,$informationModel);
        }
    }

    public function teamKeywords($game,$force = 0)
    {
        $return = [];
        $redis = app("redis.connection");
        $redis_key = "team_keywords_" . $game;
        $cache = $redis->get($redis_key);
        $cache = json_decode($cache,true);
        if(is_array($cache) && count($cache)>0 && $force == 0)
        {
            //echo "cached\n";
            $return = $cache;
        }
        else
        {
            $teamKeywords = (new TeamModel())->getAllKeywords($game);
            if(count($teamKeywords)>0)
            {
                $return = $teamKeywords;
                $redis->set($redis_key, json_encode($teamKeywords));
                $redis->expire($redis_key, 3600);
            }
        }
        return $return;
    }
    public function playerKeywords($game,$force = 0)
    {
        $return = [];
        $redis = app("redis.connection");
        $redis_key = "player_keywords_" . $game;
        $cache = $redis->get($redis_key);
        $cache = json_decode($cache,true);
        if(is_array($cache) && count($cache)>0 && $force ==0)
        {
            //echo "cached\n";
            $return = $cache;
        }
        else
        {
            $playerKeywords = (new PlayerModel())->getAllKeywords($game);
            if(count($playerKeywords)>0)
            {
                $return = $playerKeywords;
                $redis->set($redis_key, json_encode($playerKeywords));
                $redis->expire($redis_key, 3600);
            }
        }
        return $return;
    }
    public function heroKeywords($game,$force = 0)
    {
        $return = [];
        $redis = app("redis.connection");
        $redis_key = "hero_keywords_" . $game;
        $cache = $redis->get($redis_key);
        $cache = json_decode($cache,true);
        if(is_array($cache) && count($cache)>0 && $force ==0)
        {
            //echo "cached\n";
            $return = $cache;
        }
        else
        {
            if($game=="lol")
            {
                $heroKeywords = (new lolHeroModel())->getAllKeywords($game);
            }
            elseif($game=="kpl")
            {
                $heroKeywords = (new kplHeroModel())->getAllKeywords($game);
            }
            elseif($game=="dota2")
            {
                $heroKeywords = (new dota2HeroModel())->getAllKeywords($game);
            }
            if(count($heroKeywords)>0)
            {
                $return = $heroKeywords;
                $redis->set($redis_key, json_encode($heroKeywords));
                $redis->expire($redis_key, 3600);
            }
        }
        return $return;
    }
    public function anotherKeywords($force = 0)
    {
        $return = [];
        $redis = app("redis.connection");
        $redis_key = "another_keywords";
        $cache = $redis->get($redis_key);
        $cache = json_decode($cache,true);
        if(is_array($cache) && count($cache)>0 && $force ==0)
        {
            //echo "cached\n";
            $return = $cache;
        }
        else
        {
            $keywords = (new KeywordsModel())->getAllKeywords();
            if(count($keywords)>0)
            {
                $return = $keywords;
                $redis->set($redis_key, json_encode($keywords));
                $redis->expire($redis_key, 3600);
            }
        }
        return $return;
    }
    //爬取数据
    public function tfIdf($game = "")
    {
//        $redisService = new RedisService();
        $informationModel = (new InformationModel());
 //       $scwsMapModel = (new ScwsMapModel());
 //       $scwsKeywordMapModel = (new ScwsKeywordMapModel());
        $result = [];
        $informationList = $informationModel->getInformationList(["game"=>$game,"scws"=>1,"fields"=>"id","page_size"=>1000]);
        $informationList = array_column($informationList,"id");

        foreach($informationList as $content_id)
        {
            $this->processScws($content_id,$informationModel);
        }
    }
    public function processKeyword($content_id,$informationModel)
    {
        echo "start to process:".$content_id."\n";
        $redisService = new RedisService();
        //$informationModel = (new InformationModel());
        $keywordMapModel = (new KeywordMapModel());
        $information = $informationModel->getInformationById($content_id,["content","game","id","create_time"]);
        $anotherKeywords = $this->anotherKeywords(0);
        $teamKeywords = $this->teamKeywords($information["game"],0);
        $playerKeywords = $this->playerKeywords($information["game"],0);
        $heroKeywords = $this->heroKeywords($information["game"],0);
        $team = [];$player = [];$hero = [];$another = [];
        //echo strlen(strip_tags($information['content']))."\n";
        foreach($anotherKeywords as $keyword => $id)
        {
            if(!in_array($keyword,$this->expect_keywords) && strlen($keyword)>=3 && strlen($keyword)<=20)
            {
                $count = substr_count(strip_tags($information['content']),$keyword);
                if($count >= 1)
                {
                    $another[$keyword] = ["id"=>$id,"count"=>$count] ;
                }
            }
        }
        foreach($teamKeywords as $keyword => $team_id)
        {
            if(!in_array($keyword,$this->expect_keywords) && strlen($keyword)>=3 && strlen($keyword)<=20)
            {
                $count = substr_count(strip_tags($information['content']),$keyword);
                if($count >= 1)
                {
                    $team[$keyword] = ["id"=>$team_id,"count"=>$count] ;
                }
            }
            $team = sort_split_array($team,"count",5);
        }
        foreach($playerKeywords as $keyword => $player_id)
        {
            if(!in_array($keyword,$this->expect_keywords) && strlen($keyword)>=3 && strlen($keyword)<=20)
            {
                $count = substr_count(strip_tags($information['content']), $keyword);
                if ($count >= 1)
                {
                    $player[$keyword] = ["id" => $player_id, "count" => $count];
                }
            }
            $player = sort_split_array($player,"count",5);
        }
        foreach($heroKeywords as $keyword => $hero_id)
        {
            if(!in_array($keyword,$this->expect_keywords) && strlen($keyword)>=3 && strlen($keyword)<=20)
            {
                $count = substr_count(strip_tags($information['content']), $keyword);
                if ($count >= 1)
                {
                    $hero[$keyword] = ["id" => $hero_id, "count" => $count];
                }
            }
            $hero = sort_split_array($hero,"count",5);
        }
        $result[$information['id']]['another'] = $another;
        $result[$information['id']]['team'] = $team;
        $result[$information['id']]['player'] = $player;
        $result[$information['id']]['hero'] = $hero;

        $informationModel->updateInformation($information['id'],['keywords'=>0,'keywords_list'=> $result[$information['id']]]);
        $keywordMapModel->saveMap($information['id'],$information["game"],"information", $result[$information['id']],$information['create_time']);
        $data = $redisService->refreshCache("information",[strval($information['id'])]);
        echo "end to process:".$content_id."\n";
    }
    public function processScws($content_id,$informationModel)
    {
        $redisService = new RedisService();
        $scwsMapModel = new ScwsMapModel();
        $scwsKeywordMapModel = new ScwsKeywordMapModel();
        $sh = scws_open();
        scws_set_charset($sh, 'utf8');
        $information = $informationModel->getInformationById($content_id,["content","type","game","id","create_time"]);
        echo "start_to_process:".$information['id']."\n";
        $replace_arr = [
            '&gt;'=>'>','&rt;'=>'<','&amp;'=>'&','&quot;'=>''
        ];
        $content = (strip_tags(html_entity_decode($information['content'])));
        foreach($replace_arr as $k => $v)
        {
            $content = str_replace($k,$v,$content);
        }
        $text = strip_tags($content);
        scws_send_text($sh, $text);
        $top = scws_get_tops($sh,10);
        foreach($top as $key => $word)
        {
            if(in_array($word['word'],$this->expect_keywords))
            {
                unset($top[$key]);
            }
        }
        $keywordMap = $scwsKeywordMapModel->saveMap($top);
        foreach($top as $key => $wordInfo)
        {
            if(isset($keywordMap[$wordInfo['word']]))
            {
                $top[$key]['keyword_id'] = $keywordMap[$wordInfo['word']];
            }
        }
        $informationModel->updateInformation($information['id'],['scws'=>0,'scws_list'=> $top]);
        print_R($top);
        echo "count:".count($top)."\n";
        $scwsMapModel->saveMap($information['id'],$information['game'],"information",$information['type'],$top,$keywordMap,$information['create_time']);
        $data = $redisService->refreshCache("information",[strval($information['id'])]);
    }
}
