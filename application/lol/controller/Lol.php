<?php
namespace app\lol\controller;

use think\Db;

// 英雄联盟比赛竞猜
class Lol extends Base
{
    // 竞猜列表
    public function index()
    {
        $data = db('lol_game')->where('pid=0 and status<3')->select();
        $score = array();
        foreach ($data as $key => $value) {
            if($data[$key]['bo'] > 0){
                $bf = db('lol_cate')->where('game_id='.$data[$key]['id'])->select();
                foreach ($bf as $ke => $val) {
                    $score[$ke]['id'] = $bf[$ke]['id'];
                    $score[$ke]['bo_name'] = $bf[$ke]['bo_name'];
                    $score[$ke]['bo_odds'] = $bf[$ke]['bo_odds'];
                }
                $data[$key]['score'] = $score;
                $child = db('lol_game')->where('pid='.$data[$key]['id'].' and status < 3')->select();
                foreach ($child as $ke => $val) {
                    $child[$ke]['countdown'] = $child[$ke]['start_time'] - time();
                    $child[$ke]['add_time'] = date('Y-m-d H:i:s',$child[$ke]['add_time']);
                    $child[$ke]['start_time'] = date('Y-m-d H:i:s',$child[$ke]['start_time']);
                    $child[$ke]['tz'] = db('lol_cate')->where('game_id='.$child[$ke]['id'])->select();
                    // if($child[$ke]['status'] > 0){
                    //     foreach ($child[$ke]['tz'] as $k => $v) {
                    //         if($child[$ke]['tz'][$k]['status'] == 0) {
                    //             unset($child[$ke]['tz'][$k]);
                    //         }
                    //     }
                    // }
                }
                $data[$key]['child'] = $child;
            }else{
                $data[$key]['countdown'] = $data[$key]['start_time'] - time();
                $data[$key]['add_time'] = date('Y-m-d H:i:s',$data[$key]['add_time']);
                $data[$key]['start_time'] = date('Y-m-d H:i:s',$data[$key]['start_time']);
                    
                $data[$key]['tz'] = db('lol_cate')->where('game_id='.$data[$key]['id'])->select();
                // if($data[$key]['status'] > 0){
                //     foreach ($data[$key]['tz'] as $k => $v) {
                //         if($data[$key]['tz'][$k]['status'] == 0) {
                //             unset($data[$key]['tz'][$k]);
                //         }
                //     }
                // }
            }
        }
        echo json_encode(['msg'=>$data,'code'=>1,'success'=>true]);
        exit;
    }

    // 生成token
    public function token()
    {
        $token = md5(uniqid());
        // session_start();
        session('lol_token',$token);   
        echo json_encode(['msg'=>'请求成功','code'=>1,'token'=>$token,'success'=>true]);
    }

    /*
    * cid         当前投注内容的id
    * tz_odds     投注内容赔率
    * tz_result   投注内容
    * tz_money    投注金额
    * token       标识
    */
    public function add_order()
    {
        $_data = $_REQUEST;
        $data = json_decode($_data['tz_arr'],true);
        $order_money = 0;
        foreach ($data as $key => $value) {
            $data[$key]['tz_money'] = replace_specialChar($data[$key]['tz_money']);
            if(empty($data[$key]['tz_money'])){
                echo json_encode(['msg'=>'请输入正确的金额','code'=>205,'success'=>false]);
                exit; 
            }
            $order_money += $data[$key]['tz_money'];
        }
        $token = session('lol_token');
        session('lol_token',null);

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
            $game_cate = db('lol_cate')->where('id='.$data[$key]['cid'])->find();
            $lol_game = db('lol_game')->where('id='.$game_cate['game_id'])->find();
            if(USER_ID != 2){
                if($lol_game['status'] >= 2){
                    echo json_encode(['msg'=>'投注失败','code'=>206,'success'=>false]);
                    exit;
                }
                if($lol_game['status'] == 1){
                    if($game_cate['status'] == 0){
                        echo json_encode(['msg'=>'投注失败','code'=>207,'success'=>false]);
                        exit;
                    }
                }
            }
            

            if(count($data[$key]) == 3){
                $status = db('lol_cate')->where('id='.$data[$key]['cid'])->value('status');
                if($status == 1){
                    db('lol_cate')->where('id='.$data[$key]['cid'])->setInc('bo_money',$data[$key]['tz_money']);
                    $data[$key]['tz_result'] =  db('lol_cate')->where('id='.$data[$key]['cid'])->value('bo_name');
                }else{
                    echo json_encode(['msg'=>'投注失败','code'=>208,'success'=>false]);
                    exit;
                }
                
            }else{
                db('lol_cate')->where('id='.$data[$key]['cid'])->setInc('team'.$data[$key]['tz_result'].'_money',$data[$key]['tz_money']);
                $game_cate = db('lol_cate')->where('id='.$data[$key]['cid'])->find();
                $team1_odds = (($game_cate['team1_money']+$game_cate['team2_money'])/$game_cate['team1_money'])*0.95;
                $team2_odds = (($game_cate['team1_money']+$game_cate['team2_money'])/$game_cate['team2_money'])*0.95;
                $team1_odds = $team1_odds<=1.01?1.01:floor($team1_odds*100)/100;
                $team2_odds = $team2_odds<=1.01?1.01:floor($team2_odds*100)/100;
                db('lol_cate')->where('id='.$data[$key]['cid'])->update(array('team1_odds'=>$team1_odds,'team2_odds'=>$team2_odds));
                if($data[$key]['tz_result'] == 1) {
                    $data[$key]['tz_odds'] = $team1_odds;
                }else{
                    $data[$key]['tz_odds'] = $team2_odds;
                }
            }
            $data[$key]['user_id'] = $yh['id'];
            $data[$key]['add_time'] = time();   

        }
        $res = db('lol_order')->insertAll($data);
        if($res > 0) {
            /**添加账单明细**/
            $detail['yhid'] = $yh['yhid'];
            $detail['Jylx'] = 3;
            $detail['jyje'] = $order_money;
            $detail['new_money'] = $yh['balance']-$order_money;
            $detail['Jysj'] = time();
            $detail['Srhzc'] = 2;
            $detail['game_id'] = 5;
            $detail_res = db('account_details')->insert($detail,false,true);
            /**添加账单明细end**/
            foreach ($data as $key => $value) {
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
        $list = db('lol_order')->where('user_id='.USER_ID)->order('add_time desc')->select();

        foreach ($list as $key => $value) {
            $list[$key]['add_time'] = date('Y-m-d H:i:s',$list[$key]['add_time']);
            $lol_cate = db('lol_cate')->where('id='.$list[$key]['cid'])->find();
            $lol_game = db('lol_game')->where('id='.$lol_cate['game_id'])->find();
            if(strlen($list[$key]['tz_result']) == 1){
                $tz_team = $lol_game['team'.$list[$key]['tz_result']];
                $list[$key]['game_name'] = $lol_game['game_name'].'('.$lol_cate['name'].')';
                $list[$key]['tz_result'] = $tz_team;
                if(!empty($list[$key]['win_result'])){
                    $win_team = $lol_game['team'.$list[$key]['win_result']];
                    $list[$key]['win_result'] = $win_team;
                }
            }else{
                $tz_team = $lol_cate['bo_name'];
                $list[$key]['game_name'] = $lol_game['game_name'].'('.$tz_team.')';
                $list[$key]['tz_result'] = $tz_team;
                if(!empty($list[$key]['win_result'])){
                    $list[$key]['win_result'] = $tz_team;
                }
            }
            
            
        }
        echo json_encode(['msg'=>$list,'code'=>1,'success'=>true]);
        exit;
    }

}
