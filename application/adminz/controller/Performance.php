<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018.12.3
 * Time: 13:54
 */

namespace app\adminz\controller;
use think\Config;
use think\Db;

class Performance extends Base
{
    public function index()
    {
        $sql_foot = db('yh')
            ->field('pid,sum( jyje ) AS sum ')
            ->join('account_details', 'account_details.yhid = yh.yhid')
            ->where('account_details.game_id = 6 AND account_details.jylx = 3 /*交易类型=下注*/ AND yh.`status` = 0 /*非内部账号*/')
            ->group('Yqm')
            ->buildSql();
        $sql_other = db('yh')
            ->field('pid,sum( jyje ) AS sum ')
            ->join('account_details', 'account_details.yhid = yh.yhid')
            ->where('account_details.game_id <> 6 AND account_details.jylx = 3 /*交易类型=下注*/ AND yh.`status` = 0 /*非内部账号*/')
            ->group('Yqm')
            ->buildSql();
        $count = db('yh')
            ->join([$sql_foot=>'foot'], 'yh.id = foot.pid')
            ->join([$sql_other=>'not_foot'], 'yh.id = not_foot.pid ')
            ->field('yhid,foot.sum as sports_sum,not_foot.sum as other_sum')
            ->where('foot.sum IS NOT NULL OR not_foot.sum IS NOT NULL')
            ->order('yhid')
            ->count();
        $list = db('yh')
            ->join([$sql_foot=>'foot'], 'yh.id = foot.pid')
            ->join([$sql_other=>'not_foot'], 'yh.id = not_foot.pid ')
            ->field('yhid,foot.sum as sports_sum,not_foot.sum as other_sum')
            ->where('foot.sum IS NOT NULL OR not_foot.sum IS NOT NULL')
            ->order('yhid')
            ->paginate(20,$count);

        //获取分页
        $page = $list->render();

        $rate = db('performance_commission')->where('delete_time is null')->find();
        $this->assign("rate",$rate);
        $this->assign("page",$page);
        $this->assign("_list",$list);
        return view();
    }

    public function get_performance_list($result)
    {
        $where = '';
        if (!empty($result['add_time'])) $where .= ' AND account_details.jysj > ' . strtotime($result['add_time']) . ' /*（起始时间戳）*/';
        if (!empty($result['end_time'])) $where .= ' AND account_details.jysj < ' . strtotime($result['end_time']) . ' /*（终止时间戳）*/';
        if (!empty($result['user_id'])) $search_user = ' yhid = \'' . $result['user_id'] . '\' ';
        else $search_user = '1=1';

        $sql_foot = db('yh')
            ->field('pid,sum( jyje ) AS sum ')
            ->join('account_details', 'account_details.yhid = yh.yhid')
            ->where('account_details.game_id = 6 AND account_details.jylx = 3 /*交易类型=下注*/ AND yh.`status` = 0 /*非内部账号*/ ' . $where)
            ->group('Yqm')
            ->buildSql();
        $sql_other = db('yh')
            ->field('pid,sum( jyje ) AS sum ')
            ->join('account_details', 'account_details.yhid = yh.yhid')
            ->where('account_details.game_id <> 6 AND account_details.jylx = 3 /*交易类型=下注*/ AND yh.`status` = 0 /*非内部账号*/ ' . $where)
            ->group('Yqm')
            ->buildSql();
        $count = db('yh')
            ->join([$sql_foot=>'foot'], 'yh.id = foot.pid')
            ->join([$sql_other=>'not_foot'], 'yh.id = not_foot.pid ')
            ->field('yhid,foot.sum as sports_sum,not_foot.sum as other_sum')
            ->where('foot.sum IS NOT NULL OR not_foot.sum IS NOT NULL')
            ->where($search_user)
            ->order('yhid')
            ->count();
        $list = db('yh')
            ->join([$sql_foot=>'foot'], 'yh.id = foot.pid')
            ->join([$sql_other=>'not_foot'], 'yh.id = not_foot.pid ')
            ->field('yhid,foot.sum as sports_sum,not_foot.sum as other_sum')
            ->where('foot.sum IS NOT NULL OR not_foot.sum IS NOT NULL')
            ->where($search_user)
            ->order('yhid')
            ->paginate(20,$count);

        //获取分页
        $page = $list->render();
        $rate = db('performance_commission')->where('delete_time is null')->find();
        $this->assign("rate",$rate);
        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/performance_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
        return view();
    }

    public function edit_rate($is_sports, $rate)
    {
        $is_sports = (bool)$is_sports;
        $rate= (double)$rate;
        if (empty($is_sports) || $rate < 0) {
            $this->ajaxReturn(['msg'=>'比例必须大于0','data'=>'','code'=>500]);
        }
        $performance_commission = db('performance_commission')
            ->where('delete_time is null')
            ->order('id desc')
            ->find();
        if ($is_sports) {
            $data = [
                'sports_rate' => $rate,
                'edit_time' => time(),
            ];
        } else {
            $data = [
                'other_rate' => $rate,
                'edit_time' => time(),
            ];
        }
        if (empty($performance_commission)) {
            $data['edit_time'] = null;
            $data['create_time'] = time();
            if ($is_sports) {
                $data['other_rate'] = 0;
            } else {
                $data['sports_rate'] = 0;
            }
            $update = db('performance_commission')
                ->insert($data);
        } else {
            $update = db('performance_commission')
                ->where('delete_time is null')
                ->update($data);
        }
        if ($update) {
            $this->ajaxReturn(['msg'=>'成功','data'=>'','code'=>200]);
        } else {
            $this->ajaxReturn(['msg'=>'入库失败，请重试','data'=>'','code'=>500]);
        }
    }
}