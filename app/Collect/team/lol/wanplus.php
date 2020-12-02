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
            "team_name"=>['path'=>"title",'default'=>''],
            "en_name"=>['path'=>"",'default'=>''],
            "aka"=>['path'=>"aka","default"=>""],
            "location"=>['path'=>"country","default"=>"未知"],
            "established_date"=>['path'=>"",'default'=>"未知"],
            "coach"=>['path'=>"",'default'=>"暂无"],
            "logo"=>['path'=>"logo",'default'=>''],
            "description"=>['path'=>"",'default'=>"暂无"],
            "race_stat"=>['path'=>"raceStat",'default'=>[]],
        ];
    public function collect($arr)
    {
        $return = [];
        $url = $arr['detail']['url'] ?? '';
        $res = $this->getCollectData($url);
        $cdata = [];
        if (!empty($res))
        {
            //处理战队采集数据
            $cdata = [
                'mission_id' => $arr['mission_id'],
                'content' => json_encode($res),
                'game' => $arr['game'],
                'source_link'=>$url,
                'title'=>$arr['detail']['title'] ?? '',
                'mission_type'=>$arr['mission_type'],
                'source'=>$arr['source'],
                'status' => 1,
            ];
            //处理战队采集数据
            $return = $cdata;
        }
        return $return;

    }
    public function process($arr)
    {


        //处理胜平负
        $t = explode("/",$arr['content']['military_exploits']??'');
        $arr['content']['raceStat'] = ["win"=>intval($t[0]??0),"draw"=>intval($t[1]??0),"lose"=>intval($t[2]??0)];
        $data = getDataFromMapping($this->data_map,$arr['content']);
        return $data;
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
            $infos=$ql->find('.f15')->texts()->all();//胜/平/负(历史总战绩)
            $country=$aka=$title='';
            if(!empty($infos)){
                foreach ($infos as $val){
                    if(strpos($val,'名称')!==false) {
                        $title=str_replace('名称：','',$val);
                    }
                    if(strpos($val,'别名')!==false) {
                        $aka=str_replace('别名：','',$val);
                    }
                    if(strpos($val,'地区')!==false) {
                        $country=str_replace('地区：','',$val);
                    }

                }
            }
            $res['country']=$country;
            $res['aka']=$aka;
            $res['title']=$title;
            //战绩
            $res['military_exploits']=$ql->find('.team_tbb dt:eq(0)')->text();//胜/平/负(历史总战绩)
            $res['military_exploits']= preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", " ", strip_tags( $res['military_exploits']));
            //现役队员
            $cur_imgs = $ql->find('.team_box  ul:eq(0) img')->attrs('src')->all();//队员图片
            $cur_position= $ql->find('.team_box  ul:eq(0) li>a strong')->texts()->all();//队员名称
            $cur_name = $ql->find('.team_box  ul:eq(0) li>a span')->texts()->all();//队员名称
            $cur_link = $ql->find('.team_box  ul:eq(0) li>a ')->attrs('href')->all();//队员名称
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
                        'link_url'=>'https://www.wanplus.com/'.$cur_link[$key] ??''
                    ];
                }
            }
            //历史队员
            $old_imgs = $ql->find('.team_box  ul:eq(1) img')->attrs('src')->all();//队员图片
            $old_position= $ql->find('.team_box  ul:eq(1) li>a strong')->texts()->all();//队员名称
            $old_name = $ql->find('.team_box  ul:eq(1) li>a span')->texts()->all();//队员名称
            $old_link = $ql->find('.team_box  ul:eq(1) li>a ')->attrs('href')->all();//队员名称
            if($old_name){
                foreach ($old_name as $key=>$val){
                    $position=$old_position[$key]??'';

                    $res['old_team_members'][$key]=[
                        'name'=>$val,//队员名称
                        'main_img'=>(isset($old_imgs[$key]) && $old_imgs[$key]) ? str_replace('_mid','',$old_imgs[$key]) :'',//队员主图,
                        'link_url'=>'https://www.wanplus.com'.$old_link[$key] ?? ''
                        //'position'=>$position,//位置
                    ];
                }
            }

        }
        return $res;
    }
}
