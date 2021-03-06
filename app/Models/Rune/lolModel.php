<?php

namespace App\Models\Rune;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class lolModel extends Model
{
    protected $table = "lol_rune_info";
    protected $primaryKey = "rune_id";
    public $timestamps = false;
    protected $connection = "query_list";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $attributes = [
        "aka" => []
    ];
    protected $toJson = [
        "aka", "slots", "bonuses"
    ];
    protected $toAppend = [
        "aka"
    ];

    public function getRuneList($params)
    {
        $rune_list =$this->select("*");
        //游戏类型

        $pageSizge = $params['page_size']??3;
        $page = $params['page']??1;
        $rune_list = $rune_list
            ->limit($pageSizge)
            ->offset(($page - 1) * $pageSizge)
            ->orderBy("rune_id")
            ->get()->toArray();
        if (!empty($rune_list)) {
            foreach ($rune_list as &$val) {
                $val = $this->getRuneById($val['rune_id']);
            }
        }
        return $rune_list;
    }

    public function getRuneByName($rune_name)
    {
        $rune_info = $this->select("*")
            ->where("rune_name", $rune_name)
            ->get()->first();
        if (isset($rune_info->rune_id)) {
            $rune_info = $rune_info->toArray();
        } else {
            $rune_info = [];
        }
        return $rune_info;
    }

    public function insertRune($data)
    {
        foreach ($this->attributes as $key => $value) {
            if (!isset($data[$key])) {
                $data[$key] = $value;
            }

        }
        foreach ($this->toJson as $key) {
            if (isset($data[$key])) {
                $data[$key] = json_encode($data[$key]);
            }
        }
        $currentTime = date("Y-m-d H:i:s");
        if (!isset($data['create_time'])) {
            $data['create_time'] = $currentTime;
        }
        if (!isset($data['update_time'])) {
            $data['update_time'] = $currentTime;
        }
        return $this->insertGetId($data);
    }

    public function updateRune($rune_id = 0, $data = [])
    {
        $currentTime = date("Y-m-d H:i:s");
        if (!isset($data['update_time'])) {
            $data['update_time'] = $currentTime;
        }
        return $this->where('rune_id', $rune_id)->update($data);
    }

    public function saveRune($data)
    {
        $data['rune_name'] = preg_replace("/\s+/", "", $data['rune_name']);
        $data['rune_name'] = trim($data['rune_name']);
        $data['aka'] = ($data['aka'] == "") ? [] : [$data['aka']];
        $currentRune = $this->getRuneByName($data['rune_name']);
        if (!isset($currentRune['rune_id'])) {
            echo "toInsertRune:" . "\n";
            return $this->insertRune($data);
        } else {
            echo "toUpdateRune:" . $currentRune['rune_id'] . "\n";
            //校验原有数据
            foreach ($data as $key => $value) {
                if (in_array($key, $this->toAppend)) {
                    $t = json_decode($currentRune[$key], true);
                    foreach ($value as $k => $v) {
                        if (!in_array($v, $t)) {
                            $t[] = $v;
                        }
                    }
                    $data[$key] = $t;
                }
                if (in_array($key, $this->toJson)) {
                    $value = json_encode($value);
                }
                if (isset($currentRune[$key]) && ($currentRune[$key] == $value)) {
                    //echo $currentRune[$key]."-".$value."\n";
                    echo $key . ":passed\n";
                    unset($data[$key]);
                } else {
                    echo $key . ":difference:\n";
                }
            }
            if (count($data)) {
                return $this->updateRune($currentRune['rune_id'], $data);
            } else {
                return true;
            }
        }
    }

    public function getRuneCount($params = [])
    {
        $rune_count = $this;
        return $rune_count->count();
    }

    public function getRuneById($rune_id)
    {
        $redis_key = 'lol_runs_' . $rune_id;
        try {
            if (Redis::exists($redis_key)) {
                $rune_info = Redis::get($redis_key);
                $rune_info = json_decode($rune_info, true);
            } else {
                $rune_info = $this->select("*")
                    ->where("rune_id", $rune_id)
                    ->get()->first();
                if (isset($rune_info->rune_id)) {
                    $rune_info = $rune_info->toArray();
                } else {
                    $rune_info = [];
                }
                if (isset($rune_info['aka']) && $rune_info['aka']) {
                    $rune_info['aka'] = json_decode($rune_info['aka'], true);
                    $rune_info['aka'] = $rune_info['aka'] ?? [];
                }
                if (isset($rune_info['bonuses']) && $rune_info['bonuses']) {
                    $rune_info['bonuses'] = json_decode($rune_info['bonuses'], true);
                    $rune_info['bonuses'] = $rune_info['bonuses'] ?? [];
                }
                $run_detail = [];
                if (isset($rune_info['slots']) && $rune_info['slots']) {
                    $rune_info['slots'] = json_decode($rune_info['slots'], true);
                    $rune_info['slots'] = $rune_info['slots'] ?? [];
                    if (!empty($rune_info['slots'])) {
                        foreach ($rune_info['slots'] as $key => &$val) {
                            $runes = $val['runes'] ?? [];
                            if (!empty($runes)) {
                                $run_detail_model = new lolDetailModel();
                                $run_detail = $run_detail_model->getRuneListByIds($runes);
                            }
                            $val['runs_detail'] = $run_detail ?? [];

                        }
                    }
                }
                Redis::set($redis_key, json_encode($rune_info), 120);
            }

        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        return $rune_info;
    }
}
