<?php

namespace App\Collect\event\lol;

use QL\QueryList;

class chaofan
{
    protected $data_map =
        [
            'game'=>['path'=>"",'default'=>"lol"],//游戏
            'tournament_name'=>['path'=>"title",'default'=>''],//赛事名称
            'start_time'=>['path'=>"start_time",'default'=>0],//开始时间
            'end_time'=>['path'=>"end_time",'default'=>0],//开始时间
            'logo'=>['path'=>"img_url",'default'=>''],//logo
            ];
    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $res = $this->getCollectData($url);
        if (!empty($res)) {
            foreach ($res as $key=>$val){
                $cdata[$key] = [
                    'mission_id' => $arr['mission_id'],//任务id
                    'content' => json_encode($val),
                    'game' => $arr['game'],//游戏类型
                    'source_link' => $url,
                    'title' => $arr['detail']['title'] ?? '',
                    'mission_type' => $arr['mission_type'],
                    'source' => $arr['source'],
                    'status' => 1,
                    'update_time' => date("Y-m-d H:i:s")
                ];
            }
        }
        return $cdata;
    }
    public function process($arr)
    {
        //$status=0;//0:全部，1：即将开始，2：正在进行，3：已经结束
        $arr['content']['img_url'] = getImage($arr['content']['img_url']);
        $arr['content']['start_time'] = strtotime(str_replace(".","-",$arr['content']['start_time']));
        $arr['content']['end_time'] = strtotime(str_replace(".","-",$arr['content']['end_time']));
        $data = getDataFromMapping($this->data_map,$arr['content']);
        return $data;
    }

    /**
     * @param $url
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCollectData($url)
    {
        $ql = QueryList::get($url);
        $logos=$ql->find('.list-box .img-view')->attrs('style')->all();//获取图片
        $matchInfo = $ql->rules([
            'title' => ['.mask>.t2', 'text'],
            'dtime' => ['.bottom-box>.t2', 'text']
        ])->range('.list-box>li')->queryData();
        $arrData=[];
        $status=0;
        if($matchInfo) {
            foreach ($matchInfo as $key=>&$val){
                $imgUrl=str_replace(array("background-image: url('","');background-size: cover;"),'',$logos[$key]);
                $val['img_url']=$imgUrl ?? '';//图片过滤
                $arrData=explode('--',$val['dtime']);
                $curTime=date("Y.m.d");
                $val['start_time']=trim($arrData[0]) ?? '';
                $val['end_time']=trim($arrData[1]) ?? '';
                if($curTime>$val['end_time']){
                    $status=3;//已结束
                }
                if(($curTime>=$val['start_time'])&&($curTime<=$val['end_time'])){
                    $status=2;//正在进行
                }
                if($curTime<$val['start_time']){
                    $status=1;//即将开始
                }
                $val['status']=$status;
                $val['game_id']=1;//1表示lol
                $val['icon_img']='http://static.chaofan.com/static/imgs/lol-6910759caea0cc602c4824669bcc44b3.png';
                unset($val['dtime']);
            }
        }
        return $matchInfo;
    }
}
