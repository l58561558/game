<?php
namespace app\lol\controller;

use think\Db;

// 冠军竞猜
class Champion extends Base
{
    // 竞猜列表
    public function index()
    {
        $data = db('lol_team')->select();
        echo json_encode(['msg'=>$data,'code'=>1,'success'=>true]);
        exit;
    }

    // 生成token
    public function token()
    {
        $token = md5(uniqid());
        // session_start();
        session('lol_champion_token',$token);   
        echo json_encode(['msg'=>'请求成功','code'=>1,'token'=>$token,'success'=>true]);
    }

    /*
    * tz_odds     投注内容赔率
    * tz_result   投注内容
    * tz_money    投注金额
    * token       标识
    */
    public function add_order()
    {
        $_data = $_REQUEST;

        $data = json_decode($_data['tz_result'],true);
        $order_money = 0;
        foreach ($data as $key => $value) {
            $order_money += $data[$key]['tz_money'];
        }
        $token = session('lol_champion_token');
        session('lol_champion_token',null);

        $yh = db('yh')->where('id='.USER_ID)->find();

        if(USER_ID <= 0){
            echo json_encode(['msg'=>'请登录','code'=>201,'success'=>false]);
            exit;
        }
        
        if($_data['token'] != $token){
            echo json_encode(['msg'=>'数据错误,请勿重复提交','code'=>202,'success'=>false]);
            exit; 
        }

        if($order_money <= 0){
            echo json_encode(['msg'=>'投注金额不能小于0','code'=>203,'success'=>false]);
            exit;  
        }
        if($order_money > $yh['balance']){
            echo json_encode(['msg'=>'投注金额超出可用金额','code'=>204,'success'=>false]);
            exit;  
        }
        
        foreach ($data as $key => $value) {
            $data[$key]['user_id'] = $yh['id'];
            $data[$key]['add_time'] = time();
        }
        $res = db('lol_team_order')->insertAll($data);
        if($res > 0) {
            foreach ($data as $key => $value) {
                db('lol_team')->where('id='.$data[$key]['tz_result'])->setInc('tz_money',$data[$key]['tz_money']);
                db('yh')->where('id='.$yh['id'])->setDec('balance',$data[$key]['tz_money']);
                db('yh')->where('id='.$yh['id'])->setDec('amount_money',$data[$key]['tz_money']);
            }
            echo json_encode(['msg'=>'投注成功','code'=>1,'success'=>true]);
            exit;
        }

    }

    // 订单列表
    public function order()
    {
        $list = db('lol_team_order')->where('user_id='.USER_ID)->select();

        foreach ($list as $key => $value) {
            $list[$key]['add_time'] = date('Y-m-d H:i:s',$list[$key]['add_time']);
        }
        echo json_encode(['msg'=>$list,'code'=>1,'success'=>true]);
        exit;
    }

    // 结算
    public function over()
    {   
        $win_team = db('lol_team')->where('status=1')->find();
        $list = db('lol_team_order')->select();
        foreach ($list as $key => $value) {
            if($list[$key]['tz_result'] == $win_team['id']) {
                $win_money = $list[$key]['tz_odds']*$list[$key]['tz_money'];
                db('lol_team_order')->where('id='.$list[$key]['id'])->update(array('win_money'=>$win_money,'is_win'=>1));
                db('yh')->where('id='.$list[$key]['user_id'])->setInc('balance',$win_money);
                db('yh')->where('id='.$list[$key]['user_id'])->setInc('amount_money',$win_money);
            }else{
                db('lol_team_order')->where('id='.$list[$key]['id'])->setField('is_win',2);
            }
        }

        echo json_encode(['msg'=>'结算成功','code'=>1,'success'=>true]);
        exit;
    }
}
