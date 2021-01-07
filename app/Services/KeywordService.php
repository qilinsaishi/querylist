<?php

namespace App\Services;

use App\Models\InformationModel;
use App\Models\KeywordMapModel;
use App\Models\TeamModel as TeamModel;
use App\Models\PlayerModel as PlayerModel;

class KeywordService
{
    //爬取数据
    public function information($game = "")
    {
        $informationModel = (new InformationModel());
        $keywordMapModel = (new KeywordMapModel());
        $result = [];
        $informationList = $informationModel->getInformationList(["keywords"=>1,"fields"=>"content,id,create_time","page_size"=>200]);
        $teamKeywords = $this->teamKeywords($game,1);
        $playerKeywords = $this->playerKeywords($game,1);
        foreach($informationList as $information)
        {
            $team = [];$player = [];
            //echo strlen(strip_tags($information['content']))."\n";
            foreach($teamKeywords as $keyword => $team_id)
            {
                $count = substr_count(strip_tags($information['content']),$keyword);
                if($count>0)
                {
                    $team[$keyword] = ["id"=>$team_id,"count"=>$count] ;
                }
            }
            foreach($playerKeywords as $keyword => $player_id)
            {
                $count = substr_count(strip_tags($information['content']),$keyword);
                if($count>0)
                {
                    $player[$keyword] = ["id"=>$player_id,"count"=>$count] ;
                }
            }
            //if(count($team)>0)
            {
                $result[$information['id']]['team'] = $team;
            }
            //if(count($player)>0)
            {
                $result[$information['id']]['player'] = $player;
            }
            $informationModel->updateInformation($information['id'],['keywords'=>0,'keywords_list'=> $result[$information['id']]]);
            $keywordMapModel->saveMap($information['id'],"information", $result[$information['id']],$information['create_time']);
        }
        /*
        foreach($result as $id => $keywordsList)
        {

        }
        */
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
}
