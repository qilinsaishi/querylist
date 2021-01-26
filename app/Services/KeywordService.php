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

class KeywordService
{
    //爬取数据
    public function information($game = "")
    {
        $informationModel = (new InformationModel());
        $keywordMapModel = (new KeywordMapModel());
        $result = [];
        $informationList = $informationModel->getInformationList(["keywords"=>1,"fields"=>"content,id,create_time","page_size"=>300]);
        $anotherKeywords = $this->anotherKeywords(1);
        $teamKeywords = $this->teamKeywords($game,1);
        $playerKeywords = $this->playerKeywords($game,1);
        $heroKeywords = $this->heroKeywords($game,1);
        foreach($informationList as $information)
        {
            $team = [];$player = [];$hero = [];$another = [];
            //echo strlen(strip_tags($information['content']))."\n";
            foreach($anotherKeywords as $keyword => $id)
            {
                if(strlen($keyword)>=3)
                {
                    $count = substr_count(strip_tags($information['content']),$keyword);
                    if($count>0)
                    {
                        $another[$keyword] = ["id"=>$id,"count"=>$count] ;
                    }
                }
            }
            foreach($teamKeywords as $keyword => $team_id)
            {
                if(strlen($keyword)>=3)
                {
                    $count = substr_count(strip_tags($information['content']),$keyword);
                    if($count>0)
                    {
                        $team[$keyword] = ["id"=>$team_id,"count"=>$count] ;
                    }
                }
            }
            foreach($playerKeywords as $keyword => $player_id)
            {
                if(strlen($keyword)>=3)
                {
                    $count = substr_count(strip_tags($information['content']), $keyword);
                    if ($count > 0)
                    {
                        $player[$keyword] = ["id" => $player_id, "count" => $count];
                    }
                }
            }
            foreach($heroKeywords as $keyword => $hero_id)
            {
                if(strlen($keyword)>=3)
                {
                    $count = substr_count(strip_tags($information['content']), $keyword);
                    if ($count > 0)
                    {
                        $hero[$keyword] = ["id" => $hero_id, "count" => $count];
                    }
                }
            }
            $result[$information['id']]['another'] = $another;
            $result[$information['id']]['team'] = $team;
            $result[$information['id']]['player'] = $player;
            $result[$information['id']]['hero'] = $hero;
            $informationModel->updateInformation($information['id'],['keywords'=>0,'keywords_list'=> $result[$information['id']]]);
            $keywordMapModel->saveMap($information['id'],"information", $result[$information['id']],$information['create_time']);
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
            echo "cached\n";
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
            echo "cached\n";
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
            echo "cached\n";
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
            echo "cached\n";
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
        $informationModel = (new InformationModel());
        $scwsMapModel = (new ScwsMapModel());
        $scwsKeywordMapModel = (new ScwsKeywordMapModel());
        $result = [];
        $informationList = $informationModel->getInformationList(["scws"=>1,"fields"=>"content,id,type,game,create_time","page_size"=>300]);
        $sh = scws_open();
        scws_set_charset($sh, 'utf8');
        foreach($informationList as $information)
        {
            echo "start_to_process:".$information['id']."\n";
            $text = strip_tags($information['content']);
            scws_send_text($sh, $text);
            $top = scws_get_tops($sh,10);
            $keywordMap = $scwsKeywordMapModel->saveMap($top);
            foreach($top as $key => $wordInfo)
            {
                if(isset($keywordMap[$wordInfo['word']]))
                {
                    $top[$key]['keyword_id'] = $keywordMap[$wordInfo['word']];
                }
            }
            $informationModel->updateInformation($information['id'],['scws'=>0,'scws_list'=> $top]);
            $scwsMapModel->saveMap($information['id'],$information['game'],"information",$information['type'],$top,$keywordMap,$information['create_time']);
        }
    }
}
