<?php

namespace App\Http\Controllers;

use App\Libs\ClientServices;
use App\Models\Admin\DefaultConfig;
use App\Models\CollectResultModel;
use App\Models\TeamModel;
use QL\QueryList;

class HomeController extends Controller
{
    public function lists(){
        $id=$this->request->post('id','');echo $id.'<br/>';
        $name=$this->request->input('name','');echo $name.'<br/>';
        $data=$this->payload;

        $all=$this->request->all();

    }

    public function teamInfo(){

        //$team_id=$this->request->input('team_id','');
        $teamModel=new TeamModel();
        $params=[];
        $params['page_size']=1000;
        $params['game']='lol';
        $teamList=$teamModel->getTeamList($params);
        $data=[];
        $arr=[];
        $cdata=[];
        if(!empty($teamList)){
            foreach ($teamList as $key=>$val){
                if(!empty($val['team_name'])){
                    if(isset($data[$val['team_name']])){
                        {
                            $arr[$val['team_name']] = ($arr[$val['team_name']]??1)+1;
                        }
                    }
                    $data[$val['team_name']] = $val['team_id'];

                }

                if(!empty($val['en_name'])){
                    if(isset($val['en_name'])){
                        if(isset($data[$val['en_name']]))
                        {
                            $arr[$val['en_name']] = ($arr[$val['en_name']]??1)+1;
                        }
                    }
                    $data[$val['en_name']]=$val['team_id'];
                }

                //$data[$key]['team_id']=$val['team_id'] ?? '';
                $aka=json_decode($val['aka'],true);
                if(!empty($aka) && is_array($aka)){
                    foreach ($aka as $k=>$v){
                        if(isset($v)){
                            if(isset($data[$v]))
                            {
                                $arr[$v] = ($arr[$v]??1)+1;
                            }

                        }
                        $data[$v]=$val['team_id'];
                    }
                }

            }
        }
        ksort($data);
        $cdata=[
            'arr_count'=>count($arr),
            'datacount'=>count($data),
            'arr'=>$arr ?? [],

            'data'=> $data ?? []
        ];
        print_r($cdata);exit;

    }

    public function index()
    {
        $url='http://lol.kuai8.com/gonglue/index_1.html';

       /* $client=new ClientServices();
        $data=curl_get($url);dd($data);
        $data=$client->curlGet($url);*/

        //for($i=0;$i<=32;$i++){
           // $m=$i+1;
            //$url='http://lol.kuai8.com/gonglue/index_'.$m.'.html';
            $ql = QueryList::get($url);
            $imgs=$ql->find('.Cont .news-list li img')->attrs('data-original');//print_r($imgs);exit;
            $data=$ql->rules([
                'title' => ['.con .tit', 'text'],
                'desc' => ['.con  .txt', 'text'],
                'link' => ['.img  a', 'href'],
                'img_url' => ['.img img', 'src'],
                'dtime' => ['.con  .time', 'text']
            ])->range('.Cont .news-list li')->queryData();
           foreach ($data as $key=>$val){
                $data = [
                    "asign_to"=>1,
                    "mission_type"=>'information',//攻略
                    "mission_status"=>1,
                    "game"=>'lol',
                    "source"=>'kuai8',//
                    'title'=>'',
                    "detail"=>json_encode(
                        [
                            "url"=>$url,
                            "game"=>'lol',//英雄联盟
                            "source"=>'kuai8',//资讯
                            "title"=>$val['title'] ?? '',
                            "desc"=>$val['desc'] ?? '',
                            "img_url"=>$imgs[$key] ?? '',
                            "dtime"=>$val['dtime'] ?? '',

                        ]
                    ),
                ];
            }
        //}//exit;
        print_r($data);exit;
        foreach ($data as &$val){
            $detail_url=$val['link'];
            $detail_ql=QueryList::get($detail_url);
            $content=$detail_ql->find('.article-detail .a-detail-cont')->html();
            $author=$detail_ql->find('.article-detail .a-detail-head span:eq(0)')->text();print_r($author);exit;
            $val['author']=$author ?? '';
            $val['content']=$content ?? '';
        }dd($data);
        //$links=$ql->find('.news-list li .con .tit')->texts()->all();//分页
        dd($data);
        $data=curl_get($url);

$model=new DefaultConfig();
$a=$model->getDefaultById(3);dd($a);
        $data=$this->kplInfo();dd($data);

        //$html=iconv('gb2312','utf-8',file_get_contents('https://pvp.qq.com/web201605/herodetail/191.shtml'));

        /*$client=new ClientServices();
        $data=$client->curlGet($url);
        dd($data);*/
        //$url='http://www.2cpseo.com/teams/lol/p-1';//分页
       /* $url='http://www.2cpseo.com/teams/lol/p-1';//分页
        $ql = QueryList::get($url);
        $links=$ql->find('.hot-teams-container a')->attrs('href')->all();//分页
        dd($links);*/
      /*  $res=$this->cpseoTeam();
        dd($res);*/
        /*$url='http://www.2cpseo.com/events/lol/p-1';//分页
        $ql = QueryList::get($url);
        $links=$ql->find('.versus a')->attrs('href')->all();//分页
        dd($links);*/
        $url='http://www.2cpseo.com/event/439';
        /*$infos=$this->cpseoTeam('http://www.2cpseo.com/teams/lol/p-1');
dd($infos);*/
        $ql = QueryList::get($url);
        $logo=$ql->find('.kf_roster_dec img')->attr('src');
        $logo='http://www.2cpseo.com'.$logo;
        $wraps=$ql->find('.text_wrap:eq(0) .text_2 p')->text();
        $wraps=explode("\n",$wraps);
        if($wraps){
            foreach ($wraps as $key=>$val){
                if(strpos($val,'英雄联盟：') !==false) {
                    $title=str_replace('英雄联盟：','',$val);
                }
                if(strpos($val,'开始时间：') !==false) {
                    $startTime=str_replace('开始时间：','',$val);

                }
                if(strpos($val,'结束时间：') !==false) {
                    $endTime=str_replace('结束时间：','',$val);
                }

            }
        }

        $baseInfo=[
            'logo'=>$logo,
            'title'=>$title ?? '',
            'start_time'=>$startTime ?? '',
            'end_time'=>$endTime ?? '',
            'game_id'=>1,//game: 1表示lol
        ];

        $tapType=$ql->find('.tranding_tab .nav-tabs li')->texts()->all();
        $pkTeam=[];
        if(!empty($tapType)){
            foreach ($tapType as $key=>&$val){
                $pkTeam[$key]['type']=$val;
                $pkTeam[$key]['teamInfo'] = $ql->rules([
                    'date_2' => ['.date_2', 'text'],
                    'opponents_dec' => ['.kf_opponents_dec  h6', 'texts'],
                    'dtime' => ['.kf_opponents_gols  p', 'text']
                ])->range('#home'.($key+1).' li')->queryData();
            }
        }
        $res['baseInfo']=$baseInfo ?? [];//赛事基本
        $res['pkTeam']=$pkTeam ?? [];//pk战队
        dd($res);


        $arrData=[];
        $status=0;
        if($matchInfo) {
            foreach ($matchInfo as $key=>&$val){
                $imgUrl=str_replace(array("background-image: url('","');background-size: cover;"),'',$logos[$key]);
                $val['img_url']=$imgUrl?? '';
                $arrData=explode('--',$val['dtime']);
                $curTime=date("Y.m.d");dd($curTime);
                $val['start_time']=trim($arrData[0]) ?? '';
                $val['end_time']=trim($arrData[1]) ?? '';

            }
        }

dd($matchInfo);

        $logo=$ql->find('.lemma_pic img')->attr('src');
        $desc = $ql->find('.abstract ')->text();
        $baseInfosNames=$ql->find('.abstract_tbl tr .base-info-card-title')->texts()->all();
        $baseInfosValues=$ql->find('.abstract_tbl tr td .base-info-card-value')->texts()->all();
        $baseInfos=[];
        if($baseInfosNames){
            foreach ($baseInfosNames as $key=>$val){
                $baseInfos[$key]['name']=$val;
                $baseInfos[$key]['value']=delZzts($baseInfosValues[$key],$replace='展开');//去除特殊符号
            }
        }
dd($baseInfos);
        //皮肤
        $skinImg = $ql->find('.catalog_wrap')->htmls();
        $skiArr=explode('|',$skinImg);
        $tempSkiArr=[];
        $skinData=[];
        if($skiArr) {
           foreach ($skiArr as $key=>&$val){
               $tempSkiArr=explode('&',$val);
               $smallImg='https://game.gtimg.cn/images/yxzj/img201606/heroimg/'.$item_id.'/'.$item_id.'-smallskin-'.($key+1).'.jpg';
               $bigImg='https://game.gtimg.cn/images/yxzj/img201606/heroimg/'.$item_id.'/'.$item_id.'-bigskin-'.($key+1).'.jpg';
               $skinData[$key]['smallImg']=$smallImg;//小图
               $skinData[$key]['bigImg']=$bigImg;//大图
               $skinData[$key]['name']=$tempSkiArr[0] ?? '';//皮肤名称
           }
        }
        //评分能力
        $baseText= $ql->find('.cover-list-text')->texts()->all();
        $baseBars= $ql->find('.cover-list-bar .ibar')->attrs('style');
        $scoreInfo=[];
        if($baseText){
            foreach ($baseText as $key=>$val){
                $scoreInfo[$key]['name']=$val;
                $baseBar=str_replace(['width:','%'],'',$baseBars[$key]);
                $scoreInfo[$key]['value']=$baseBar ?? 0;
            }
        }
        //背景故事
        $heroStory= $ql->find('#hero-story .pop-bd')->html();
        //英雄介绍
        $history=$ql->find('#history .pop-bd')->html();

        //技能介绍
        $baseText= $ql->find('.cover-list-text')->texts()->all();
        $skillInfo=[];
        $skillImg=$ql->find('.skill-info  .skill-u1 li img')->attrs('src')->all();

           // $(".skill-show .show-list").eq(4).find(".skill-name b").html();
        //http://game.gtimg.cn/images/yxzj/img201606/heroimg/105/10500.png
        //game.gtimg.cn/images/yxzj/img201606/heroimg/105/10500.png
        $skillName=$ql->find('.skill-show .show-list .skill-name')->texts()->all();
        $skillDesc=$ql->find('.skill-show .show-list .skill-desc')->htmls()->all();
        //第五个技能时
        $skillNo5Name = $ql->find('.skill-show .show-list:eq(4) .skill-name')->text();
        $skillNo5Desc= $ql->find('.skill-show .show-list:eq(4) .skill-desc')->text();
        if($skillNo5Name !=''){//超过五张图片特殊处理
            $skillNo5=$ql->find('.no5')->attr('data-img');
            array_push($skillImg,$skillNo5);
            array_push($skillName,$skillNo5Name);
            array_push($skillDesc,$skillNo5Desc);
        }

        $skillBaseInfo=[];
        if($skillImg){
            foreach ($skillImg as $key=>$val){
                if($val!='###'){
                    $skillBaseInfo[$key]['killImg']='http:'.$val;//技能图片
                    if($skillName[$key]) {
                        $names=explode('冷却值',$skillName[$key]);
                        $skillBaseInfo[$key]['name']=$names[0] ??'';
                        $times=explode('消耗',$names[1]);
                        $skillBaseInfo[$key]['cooling']='冷却值'.$times[0] ??'';
                        $skillBaseInfo[$key]['consume']='消耗'.$times[1] ??'';
                    }
                    $skillBaseInfo[$key]['skillDesc']=$skillDesc[$key];
                }
            }
        }
        $skillBaseInfo=array_values($skillBaseInfo);
        //铭文搭配建议
        //铭文id,这个关联必须先执行inscription 这个铭文脚本，而且必须保证ming_id 与下面的保存一致
        $suggListIds = $ql->find('.sugg-u1')->attr('data-ming');
        $suggTips = $ql->find('.sugg-tips')->text();//铭文描述
        $suggList =[
            'sugglistIds'=>$suggListIds,
            'suggTips'=>$suggTips,
        ];


        //技能加点建议
        $suggInfo2Names = $ql->find('.sugg-info2 .sugg-name')->htmls()->all();//名称
        $suggInfo2Imgs=$ql->find('.sugg-info2 .sugg-skill img')->attrs('src')->all();//名称
        $addSkills=[];
        $tempSuggInfos=[];
        if($suggInfo2Imgs){
            foreach ($suggInfo2Imgs as $key=>$val){
                $addSkills[$key]['killImg']='http:'.$val;//技能图片;
                $tempSuggInfos=explode('</b><span>',$suggInfo2Names[$key]);
                $addSkills[$key]['name']=str_replace('<b>','',$tempSuggInfos[0]);
                foreach ($skillBaseInfo as $v){
                    if($v['killImg']=='http:'.$val) {
                        $addSkills[$key]['desc']=$v['name'];
                    }
                }
            }
        }
        //召唤师技能
        $summonerSkill=[];
        $summonerSkillName=$ql->find('.sugg-info2 .sugg-name3 b')->text();//大标题
        $summonerSkillDesc=$ql->find('.sugg-info2 .sugg-name3 span')->text();//名称
        $summonerSkillId=$ql->find('.sugg-info2 #skill3')->attr('data-skill');//关联召唤师技能id(80115|80121)
        $summonerSkill=[
            'summonerSkillName'=>$summonerSkillName ?? '',
            'summonerSkillDesc'=>$summonerSkillDesc ?? '',
            'summonerSkillId'=>$summonerSkillId ?? '',
        ];
        //英雄关系:0:最佳搭档 1:压制英雄 2:被压制英雄 (英雄原有id 需要保存一个字段)
        $heroInfoBox=[];
        $heroHdTitle = $ql->find('.hero-info-box .hero-hd li')->texts()->all();//名称
        $heroInfo=$ql->find('.hero-info-box .hero-info')->htmls('src')->all();//名称
        if($heroInfo){
            foreach ($heroInfo as $k=>$val){
                $heroInfoBox[$heroHdTitle[$k]]=QueryList::html($val)->rules(array(
                    'logo' => array('img','src'),
                    'link' => array('a','href')
                ))->range('.hero-relate-list li')->queryData();
                foreach($heroInfoBox[$heroHdTitle[$k]] as $k2=>&$v2){
                    $v2['link']='https://pvp.qq.com/web201605/herodetail/'.$v2['link'];//链接
                    $v2['logo']='http:'.$v2['logo'];//英雄图片
                    $v2['desc']=QueryList::html($val)->find('.hero-list-desc p:eq('.$k2.')')->text();
                }
            }
        }
        //出装建议
        $equipBox=[];
        $equipItemIds=$ql->find('.equip-bd .equip-list ')->attrs('data-item')->all();//关联装备id
        $equipTips=$ql->find('.equip-bd .equip-tips')->texts()->all();//描述
        if($equipItemIds){
            foreach ($equipItemIds as $key=>$val){
                $equipBox[$key]['equipItemIds']=$val;
                $equipBox[$key]['equipTips']=$equipTips[$key];
            }
        }

        $res['skinData']=$skinData ?? [];//皮肤
        $res['scoreInfo']=$scoreInfo ?? [];//评分
        $res['heroStory']=$heroStory ?? '';//背景故事
        $res['history']=$history; //英雄介绍
        $res['skillBaseInfo']=$skillBaseInfo ?? [];//技能介绍
        $res['suggList']=$suggList ?? [];//铭文搭配建议
        $res['addSkills']=$addSkills ?? [];//技能加点建议
        $res['heroInfoBox']=$heroInfoBox ?? [];//英雄关系
        $res['equipBox']=$equipBox ?? [];//出装建议
dd($res);
        return $res;
    }

    //资讯
    public function kplInfo()
    {
        $iSubType = '330';//330=>活动,329=>赛事，

        $url = 'https://apps.game.qq.com/wmp/v3.1/?p0=18&p1=searchNewsKeywordsList&page=1&pagesize=15';
        $refeerer = 'Referer: https://pvp.qq.com/web201605/searchResult.shtml';
        $pageData = curl_get($url, $refeerer);
        $cdata=$pageData['msg']['result'] ?? [];
        $data=[];
        foreach ($cdata as $key=>$val){
            $refeerer_detail ='Referer: https://pvp.qq.com/web201605/newsDetail.shtml?G_Biz='.$val['iBiz'].'&tid='.$val['iNewsId'];
            $detail_url='https://apps.game.qq.com/wmp/v3.1/public/searchNews.php?source=pvpweb_detail&p0='.$val['iBiz'].'&id='.$val['iNewsId'];
           /* $detail_data = curl_get($detail_url, $refeerer_detail);
            if($detail_data['status']==0) {
                $data[$key]=$detail_data['msg'] ?? [];
            }*/
        }

       return $data;
    }

    public function cpseoTeam($url='http://www.2cpseo.com/teams/lol/p-1'){
        $ql = QueryList::get($url);
        $links=$ql->find('.hot-teams-container a')->attrs('href')->all();
        $res=[];
        if($links){
            foreach ($links as $key=>$val){
                $ql = QueryList::get($val);
                $logo=$ql->find('.kf_roster_dec img')->attr('src');
                $aka=$ql->find('.kf_roster_dec .text span:eq(0)')->text();
                $wraps=$ql->find('.text_wrap .text_2 p')->text();
                $wraps=explode("\n",$wraps);
                foreach ($wraps as $val){
                    if(strpos($val,'地区') !==false) {
                        $area=str_replace('地区：','',$val);
                    }
                    if(strpos($val,'中文名称') !==false) {
                        $cname=str_replace('中文名称：','',$val);
                    }
                    if(strpos($val,'英文名称') !==false) {
                        $ename=str_replace('英文名称：','',$val);
                    }
                    if(strpos($val,'建队时间') !==false) {
                        $createTime=str_replace('建队时间：','',$val);
                    }
                }

                $intro=(isset($wraps[6]) && $wraps[6]) ? trim($wraps[6]):'';
                $baseInfo=[
                    'logo'=>'http://www.2cpseo.com'.$logo,
                    'aka'=>$aka,
                    'area'=>$area,
                    'cname'=>$cname,
                    'ename'=>$ename,
                    'create_time'=>$createTime,
                    'intro'=>$intro
                ];
                $teamListLink=$ql->find('.versus a')->attrs('href')->all();

                $res[$key]=[
                    'baseInfo'=>$baseInfo,
                    'teamListLink'=>$teamListLink
                ];

            }
        }

        return $res;
    }
    public function test()
    {
        $result_list = (new CollectResultModel())->getResult(100);
        print_R($result_list);

    }

}
