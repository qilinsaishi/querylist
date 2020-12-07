<?php

namespace App\Collect\team\kpl;

use App\Services\CollectResultService;
use QL\QueryList;


class baidu_baike
{
    //数组映射
    protected $data_map =
    [
        "team_name"=>['path'=>"base_info.中文名",'default'=>''],
        "en_name"=>['path'=>"base_info.外文名",'default'=>''],
        "aka"=>['path'=>"","default"=>""],
        "location"=>['path'=>"","default"=>"未知"],
        "established_date"=>['path'=>"base_info.创办时间",'default'=>"未知"],
        "coach"=>['path'=>"base_info.现任教练",'default'=>"暂无"],
        "logo"=>['path'=>"logo",'default'=>''],
        "description"=>['path'=>"describe",'default'=>"暂无"],
        "honor_list"=>['path'=>"base_info.主要荣誉",'default'=>''],
    ];
    //爬取数据
    public function collect($arr)
    {
        $resultService = new CollectResultService();
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
    /** 分拣从页面抓取数据
     * @param string $arr   获取到的页面内容
     * @return array $data
     */
    public function process($arr)
    {

        $arr['content']['base_info'] = uniqueData($arr['content']['base_info'],"name");
        $arr['content']['base_info'] = array_combine(array_column($arr['content']['base_info'],"name"),array_column($arr['content']['base_info'],"value"));
        $pattern = "/(\[)(.*)(\])/i";
        foreach($arr['content']['base_info'] as $key => $value)
        {
            $arr['content']['base_info'][$key] = preg_replace($pattern,"",$value);
        }
        $arr['content']['describe'] = preg_replace($pattern,"",$arr['content']['describe']);
        $data = getDataFromMapping($this->data_map,$arr['content']);
        //$data['honor_list'] = preg_replace("/\s+/", "---n---",$data['honor_list']);
        $data['honor_list'] = cleanArr(explode("\n",$data['honor_list']),["","收起"]);
        return $data;
    }

    /** 从页面抓取数据
     * @param string $url   要访问的url
     * @return array $res
     */
    public function getCollectData($url='')
    {
        $res=[];
        if ($url && strlen($url)>=6) {
            $ql = QueryList::get($url);
            $title=$ql->find('.main-content  .lemmaWgt-lemmaTitle-title h1')->text();
            $res['describe'] = $ql->find('.main-content  .lemma-summary')->text();//百度百科抓取 战队简介
            $res['logo'] = $ql->find('.side-content img')->src;//战队logo
            $baseInfoNames = $ql->find('.basic-info .name')->texts();//基础信息名称
            $baseInfoValues = $ql->find('.basic-info .value')->texts();//名称对应值
            $history_title= $ql->find('.main-content  .title-text:eq(0)")')->text();
            $history_title=str_replace($title,'',$history_title);
            $list = [];
            //战队历史
            $data =  $ql->find('.main-content  .title-text:eq(0)")')->parent()->next();
            $list=$this->getList($data);
            //战队成绩
            $data =  $ql->find('.main-content  .title-text:eq(1)")')->parent()->next();
            $list1=$this->getList($data);

            $performance_title= $ql->find('.main-content  .title-text:eq(1)")')->text();
            $performance_title=str_replace($title,'',$performance_title);

            $historys=[
                'title'=>$history_title,
                'content' => $list
            ];
            $team_performance = [
                'title' => $performance_title,
                'content' => $list1
            ];

            $res['team_historys'] = $historys ?? [];//战队历史
            $res['team_performance'] = $team_performance ?? [];//战队成绩


            $baseInfos = [];
            $tmp_arr = array();
            $array = [];
            if ($baseInfoValues) {
                foreach ($baseInfoValues as $key => $val) {
                    $name = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", " ", strip_tags($baseInfoNames[$key]));
                    $name = preg_replace('# #', '', $name);
                    // if (!in_array($name, $tmp_arr)) {

                    if (strpos($val, '主要荣誉') !== false) {
                        $arrtemp = explode('主要荣誉', $val);
                        $val = $arrtemp[1] ?? '';
                        $val = trim(trim($val, '收起'));
                    }
                    $baseInfos[$key] = [
                        'name' => $name,
                        'value' => $val
                    ];
                    // }
                }
            }

            $res['base_info'] = $baseInfos;
        }
        return $res;
    }

    public function getList($data){
        $nexttext = "1";
        $list=[];
        do{
            $list[] = $data->text();
            $data = $data->next();
            $nexttext = $data->text();
        }
        while($nexttext !="");
        return  $list;
    }
}
