<?php

namespace App\Collect\team\lol;

use App\Models\CollectUrlModel;
use App\Services\CollectResultService;
use App\Services\MissionService as oMission;
use QL\QueryList;

class wanplus
{
    protected $data_map =
        [
            "team_name"=>['path'=>"title",'default'=>""],
            "en_name"=>['path'=>"",'default'=>""],
            "aka"=>['path'=>"aka","default"=>"暂无"],
            "location"=>['path'=>"country","default"=>"未知"],
            "create_date"=>['path'=>"",'default'=>"未知"],
            "coach"=>['path'=>"",'default'=>"未知"],
            "logo"=>['path'=>"logo",'default'=>""],
            "describe"=>['path'=>"",'default'=>"暂无"],
            "raceStat"=>['path'=>"raceStat",'default'=>"暂无"]
        ];
    public function collect($arr)
    {

        $url = $arr['detail']['url'] ?? '';
        $res = $this->getCollectData($url);
        $cdata = [];
        if (!empty($res)) {
            $cdata = [
                'mission_id' => $arr['mission_id'],
                'content' => json_encode($res),
                'game' => $arr['game'],
                'source_link'=>$url,
                'title'=>$arr['detail']['title'] ?? '',
                'mission_type'=>$arr['mission_type'],
                'source'=>$arr['source'],
                'status' => 1,
                'update_time'=>date("Y-m-d H:i:s")

            ];
            //处理战队采集数据

            return $cdata;
        }

    }
    public function process($arr)
    {
        $className = 'App\Libs\CollectLib';
        $lib = new $className;
        //处理胜平负
        $t = explode("/",$arr['content']['military_exploits']??'');
        $arr['content']['raceStat'] = ["win"=>intval($t[0]??0),"draw"=>intval($t[1]??0),"lose"=>intval($t[2]??0)];
        $data = $lib->getDataFromMapping($this->data_map,$arr['content']);
        print_R($data);
    }

    /**
     * @param string $url
     * @return array $res
     */
    public function getCollectData($url='')
    {
        $res=[];
        if ($url && strlen($url)>=6) {
            $ql = QueryList::get($url);
            $res['logo'] = $ql->find('#sharePic')->src;//战队logo
            $res['logo'] =str_replace('_mid','',$res['logo'] );
            $infos= $ql->find('#shareTitle')->text();
            $infos=trim($infos,'【');
            $infos=trim($infos,'】');
            $arr=explode('，',$infos);
            if($arr){
                $res['title']=$arr[0]??'';//战队名称
                $res['aka']=trim($arr[1])??'';//别名
                if($res['aka']){
                    $res['aka']=str_replace('别名:','',$res['aka']);
                }
                $res['country']=trim($arr[2])??'';//国家
                if($res['country']){
                    $res['country']=str_replace('别名:','',$res['country']);
                }
            }
            //战绩
            $res['military_exploits']=$ql->find('.team_tbb dt:eq(0)')->text();
            $res['military_exploits']= preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", " ", strip_tags( $res['military_exploits']));
            //现役队员
            $cur_imgs = $ql->find('.team_box  ul:eq(0) img')->attrs('src')->all();//队员图片
            $cur_position= $ql->find('.team_box  ul:eq(0) li>a strong')->texts()->all();//队员名称
            $cur_name = $ql->find('.team_box  ul:eq(0) li>a span')->texts()->all();//队员名称
            if($cur_name){
                foreach ($cur_name as $key=>$val){
                    $position=$cur_position[$key]??'';
                    if($position){
                        $position=str_replace('位置:','',$cur_position[$key]);
                    }
                    $res['cur_team_members'][$key]=[
                        'name'=>$val,//队员名称
                        'main_img'=>(isset($cur_imgs[$key]) && $cur_imgs[$key]) ? str_replace('_mid','',$cur_imgs[$key]) :'',//队员主图
                        'position'=>$position,//位置
                    ];
                }
            }
            //历史队员
            $old_imgs = $ql->find('.team_box  ul:eq(1) img')->attrs('src')->all();//队员图片
            $old_position= $ql->find('.team_box  ul:eq(1) li>a strong')->texts()->all();//队员名称
            $old_name = $ql->find('.team_box  ul:eq(1) li>a span')->texts()->all();//队员名称
            if($old_name){
                foreach ($old_name as $key=>$val){
                    $position=$old_position[$key]??'';

                    $res['old_team_members'][$key]=[
                        'name'=>$val,//队员名称
                        'main_img'=>(isset($old_imgs[$key]) && $old_imgs[$key]) ? str_replace('_mid','',$old_imgs[$key]) :'',//队员主图,
                        //'position'=>$position,//位置
                    ];
                }
            }
        }
        return $res;
    }
}
