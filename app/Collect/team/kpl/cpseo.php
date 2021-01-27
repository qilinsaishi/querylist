<?php

namespace App\Collect\team\kpl;

use QL\QueryList;

class cpseo
{
    protected $data_map =
        [
            "team_name"=>['path'=>"baseInfo.name",'default'=>''],
            "en_name"=>['path'=>"baseInfo.ename",'default'=>''],
            "aka"=>['path'=>"baseInfo.subname","default"=>""],
            "location"=>['path'=>"baseInfo.area","default"=>"未知"],
            "established_date"=>['path'=>"baseInfo.create_time",'default'=>"未知"],
            "coach"=>['path'=>"",'default'=>"暂无"],
            "logo"=>['path'=>"baseInfo.logo",'default'=>''],
            "description"=>['path'=>"baseInfo.intro",'default'=>"暂无"],
            "race_stat"=>['path'=>"",'default'=>[]],
            "original_source"=>['path'=>"",'default'=>"cpseo"],
            "site_id"=>['path'=>"site_id",'default'=>0],
        ];
    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $res = $this->cpseoTeam($url);
        if (!empty($res)) {
                $cdata = [
                    'mission_id' => $arr['mission_id'],//任务id
                    'content' => json_encode($res),
                    'game' => $arr['game'],//游戏类型
                    'source_link' => $url,
                    'title' => $arr['detail']['title'] ?? '',
                    'mission_type' => $arr['mission_type'],
                    'source' => $arr['source'],
                    'status' => 1,
                    'update_time' => date("Y-m-d H:i:s")
                ];
        }
        return $cdata;
    }
    public function process($arr)
    {
        /**
         * Array
        (
        [baseInfo] => Array //战队基本信息
        (
        [logo] => http://www.2cpseo.com/storage/dj/November2019/1dc10e7228f132b4bd5af7f52ddee322.jpg
        [name] => ESG //战队名称
        [game] => kpl //游戏类型
        [subname] => ESG //子名称
        [intro] => <p>ESG是来自韩国的一支<span style="">王者荣耀</span>战队.</p> //简介
        )

        [team_members] => Array  //战队队员列表
        (
        [0] => http://www.2cpseo.com/player/2319
        [1] => http://www.2cpseo.com/player/2320
        [2] => http://www.2cpseo.com/player/2321
        [3] => http://www.2cpseo.com/player/2322
        [4] => http://www.2cpseo.com/player/2323
        [5] => http://www.2cpseo.com/player/2324
        [6] => http://www.2cpseo.com/player/2325
        )

        )
         *  [information] => Array //资讯
        (
        [sTitle] => 【LPL赛事速看】第三周D7：乌迪尔男枪野区起飞！V5、WE横扫对手！
        [link] => http://www.2cpseo.com/article/djtt/9032
        [sDesc] => 乌迪尔拿了mvp，玩家沸腾了，玩家们问：谁是乌迪尔？？
        [sIMG] => /img-asset/medium/storage/articles/January2021//467311b9581531b0042ee48ac8c0733b.jpg
        [sCreated] => 2021-01-25 09:33:59
        [type] => 1761
        [content] => <p><iframe frameborder="0" scrolling="no" src="https://ygzone-v.2cpmm.com/ygzone-v/video/21/01/25/6c6bf7532eadd079fc6a6250476b6939.mp4" style="min-he
        ight:450px" width="100%"></iframe></p>
        )
         *  [history_match] => Array //赛事
        (
        [0] => Array
        (
        [date] => 2020-12-21
        [score] => 1 : 0
        [teams] => Array
        (
        [0] => FPX
        [1] => RW
        )

        [team_url] => Array
        (
        [0] => http://www.2cpseo.com/team/40
        [1] => http://www.2cpseo.com/team/41
        )

        )
         */
        
        var_dump($arr);
        $t = explode("/",$arr['source_link']);
        $arr['content']['site_id'] = intval($t[count($t)-1]??0);
        $arr['content']['baseInfo']['logo'] = getImage($arr['content']['baseInfo']['logo']);
        $data = getDataFromMapping($this->data_map,$arr['content']);
        return $data;
    }
    /**
     * 来自http://www.2cpseo.com
     * @param $url
     * @return array
     */
    public function cpseoTeam($url)
    {
        $res = [];
        $ql = QueryList::get($url);
        $logo = $ql->find('.logo-block img')->attr('src');
        $logo='http://www.2cpseo.com'.$logo;
        $name = $ql->find('.name-block .name')->text();
        $subname = $ql->find('.name-block .subname')->text();
        $intro_content=$ql->find('.intro-content-block .intro-content')->html();
        $team_members=$ql->find('.l-m-team-member:eq(0) td a')->attrs('href')->all();

        $history_match=$ql->rules([
            'date' => ['.text:eq(0)', 'text'],
            'score' => ['.score', 'text'],
            'info' => ['.info', 'html'],
            'teams' => ['.info a', 'texts'],
        ])->range('.l-m-history-match .item')->queryData();
        foreach ($history_match as &$val){
            $info=$val['info'] ?? '';
            $link=QueryList::html($info)->find('a')->attrs('href')->toArray();
            $val['team_url']=$link ?? [];
            unset($val['info']);
        }

        $article_list=$ql->rules([
            'sTitle' => ['.article-title', 'text'],
            'link' => ['a', 'href'],
            'sDesc' => ['.article-content', 'text'],
            'sIMG' => ['img', 'src'],
        ])->range('.home-article-list .article-item')->queryData(function($item){
            $item['sCreated'] = QueryList::get($item['link'])->find('.article-date')->text();
            $item['type']=1761;//资讯
            $item['content'] = QueryList::get($item['link'])->find('.article-content')->html();
            return $item;
        });
        $teamArr=explode('team/',$url);
        $team_id=end($teamArr);


        $baseInfo = [
            'logo' => $logo,
            'name' => $name ?? '',
            'team_id'=>$team_id,
            'game' => 'kpl',
            'subname' => $subname ?? '',
            'intro' => $intro_content ?? '',
            'connect_info'=>['team_id'=>$team_id]

        ];

        $res = [
            'baseInfo' => $baseInfo,
            'history_match' => $history_match,
            'team_members' => $team_members,
            'information'=>$article_list,
        ];

        return $res;
    }
    public function processMemberList($team_id,$arr)
    {
        $missionList = [];
        foreach($arr['content']['teamListLink'] as $member)
        {
            $t = explode("/",$member);
            $mission = ['mission_type'=>"player",
                'mission_status'=>0,
                'title'=>$t[count($t)-1],
                'detail'=>json_encode(['url'=>$member,
                    'name'=>$t[count($t)-1],
                    'position'=>"",
                    'logo'=>"",
                    'team_id'=>$team_id,
                    'current'=>1
                ]),
            ];
            $missionList[] = $mission;
        }
        return $missionList;
    }
}
