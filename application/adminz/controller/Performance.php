<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018.12.3
 * Time: 13:54
 */

namespace app\adminz\controller;
use think\Db;

class Performance extends Base
{
    public function index()
    {
        $startTime = isset($_GET['start_time']) ? $_GET['start_time'] : null;
        $finishTime = isset($_GET['finish_time']) ? $_GET['finish_time'] : null;


        $where = 'account_details.yhid = yh.yhid AND `account_details`.`jylx` = 3 AND `yh`.`status` = 0 AND `yh`.`yqm` > 0 ';
        if ($startTime) $where .= 'AND account_details.jysj > ' . $startTime . ' /*（起始时间戳）*/';
        if ($finishTime) $where .= 'AND account_details.jysj < ' . $finishTime . ' /*（终止时间戳）*/';

        $count = db("account_details")
            ->join('yh', 'account_details.yhid=yh.yhid')
            ->join('yh yh2', 'yh.Sfby=yh2.sjhm')
            ->where($where)
            ->group('yh.yqm')
            ->order('sum( jyje ) desc')
            ->count();
        $list = db('account_details')
            ->join('yh', 'account_details.yhid=yh.yhid')
            ->join('yh yh2', 'yh.Sfby=yh2.sjhm')
            ->field('yh.yqm,yh.Sfby,sum( jyje ) as performance,yh2.yhid')
            ->where($where)
            ->group('yh.yqm')
            ->order('sum( jyje ) desc')
            ->paginate(20,$count);

        //获取分页
        $page = $list->render();

        $this->assign("page",$page);
        $this->assign("_list",$list);
        return view();
    }

    public function get_performance_list($result)
    {
        $where = 'account_details.yhid = yh.yhid AND `account_details`.`jylx` = 3 AND `yh`.`status` = 0 AND `yh`.`yqm` > 0 ';
        if (!empty($result['add_time'])) $where .= 'AND account_details.jysj > ' . strtotime($result['add_time']) . ' /*（起始时间戳）*/';
        if (!empty($result['end_time'])) $where .= 'AND account_details.jysj < ' . strtotime($result['end_time']) . ' /*（终止时间戳）*/';

        $count = db("account_details")
            ->join('yh', 'account_details.yhid=yh.yhid')
            ->join('yh yh2', 'yh.Sfby=yh2.sjhm')
            ->where($where)
            ->group('yh.yqm')
            ->order('sum( jyje ) desc')
            ->count();
        $list = db('account_details')
            ->join('yh', 'account_details.yhid=yh.yhid')
            ->join('yh yh2', 'yh.Sfby=yh2.sjhm')
            ->field('yh.yqm,yh.Sfby,sum( jyje ) as performance,yh2.yhid')
            ->where($where)
            ->group('yh.yqm')
            ->order('sum( jyje ) desc')
            ->paginate(20,$count);

        //获取分页
        $page = $list->render();

        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/performance_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
        return view();
    }
}