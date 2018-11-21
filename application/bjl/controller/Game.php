<?php
namespace app\bjl\controller;
use app\home\controller\Base; 
class Game extends Base
{
    private $kjid = 18000001;
    private $tzid = 1000000001;
    private $game_id = 5;
    /**
     * 百家乐--游戏开始
     */
    public function start(){
        db('bjl_kj')->where('win_status=3')->setField('win_status',0);
        $game_code = db('game_cate')->where('game_id='.$this->game_id)->value('game_code');

        $data = array();
        $data['add_time'] = time();
        $data['win_time'] = time()+60;
        $data['win_status'] = 1;
        $count = db('bjl_kj')->count();
        $num = $this->kjid+$count;
        $kjid = $game_code.$num;
        $data['kjid'] = $kjid;
        // 开牌顺序
        // 生成52张扑克牌(分花色)
        $card = db('bjl_codex')->select();

        $game_status = db('game_cate')->where('game_id='.$this->game_id)->value('status');

        // 随机获取闲家左牌数据 并从数组中剔除
        $player_left_key = array_rand($card);
        $player_left_val = $card[$player_left_key];
        unset($card[$player_left_key]);
        $data['player_left'] = $player_left_val['code_id']; 
        $player_left_num = $player_left_val['code_info_id']>10?10:$player_left_val['code_info_id']; 
        // 随机获取闲家右牌数据 并从数组中剔除
        $player_right_key = array_rand($card);
        $player_right_val = $card[$player_right_key];
        unset($card[$player_right_key]);
        $data['player_right'] = $player_right_val['code_id'];
        $player_right_num = $player_right_val['code_info_id']>10?10:$player_right_val['code_info_id'];
        //闲家最终点数
        $player_result = ($player_left_num+$player_right_num)%10;

        if($player_result >= 0 && $player_result <= 5){
            //如果闲家左牌点数加上右牌点数大于0 并且 小于等于5 补上第三张拍
            $player_three_key = array_rand($card);
            $player_three_val = $card[$player_three_key];
            unset($card[$player_three_key]);
            $data['player_three'] = $player_three_val['code_id']; 
            $player_three_num = $player_three_val['code_info_id']>10?10:$player_three_val['code_info_id']; 
            //闲家最终点数
            $player_result = ($player_left_num+$player_right_num+$player_three_num)%10;
        }
        $data['player_result'] = $player_result;


        // 随机获取庄家左牌数据 并从数组中剔除
        $banker_left_key = array_rand($card);
        $banker_left_val = $card[$banker_left_key];
        unset($card[$banker_left_key]);
        $data['banker_left'] = $banker_left_val['code_id']; 
        $banker_left_num = $banker_left_val['code_info_id']>10?10:$banker_left_val['code_info_id']; 
        // 随机获取庄家右牌数据 并从数组中剔除
        $banker_right_key = array_rand($card);
        $banker_right_val = $card[$banker_right_key];
        unset($card[$banker_right_key]);
        $data['banker_right'] = $banker_right_val['code_id']; 
        $banker_right_num = $banker_right_val['code_info_id']>10?10:$banker_right_val['code_info_id']; 
        //庄家最终点数
        $banker_result = ($banker_left_num+$banker_right_num)%10;
        if($banker_result >= 0 && $banker_result < 7){
            if($banker_result == 3 && (!isset($player_three_num) || $player_three_num != 8)){
                $data['banker_result'] = $banker_result;
            }else if($banker_result==4 && (!isset($player_three_num) || $player_three_num!=0 || $player_three_num!=1 || $player_three_num!=8 || $player_three_num!=9)){
                $data['banker_result'] = $banker_result;
            }else if($banker_result==5 && (!isset($player_three_num) || $player_three_num!=0 || $player_three_num!=1 || $player_three_num!=2 || $player_three_num!=3 || $player_three_num!=8 || $player_three_num!=9)){
                $data['banker_result'] = $banker_result;
            }else if($banker_result==6 && (!isset($player_three_num) || $player_three_num!=6 || $player_three_num!=7)){
                $data['banker_result'] = $banker_result;
            }else{
                //如果庄家左牌点数加上右牌点数大于0 并且 小于等于2 补上第三张拍
                $banker_three_key = array_rand($card);
                $banker_three_val = $card[$banker_three_key];
                unset($card[$banker_three_key]);
                $data['banker_three'] = $banker_three_val['code_id']; 
                $banker_three_num = $banker_three_val['code_info_id']>10?10:$banker_three_val['code_info_id']; 
                //庄家最终点数
                $data['banker_result'] = ($banker_left_num+$banker_right_num+$banker_three_num)%10;
            }            
        }else{
            $data['banker_result'] = $banker_result;
        }

        if($data['banker_result'] > $data['player_result']){
            $win_result = 1;
        }else if($data['banker_result'] < $data['player_result']){
            $win_result = 2;
        }else if($data['banker_result'] == $data['player_result']){
            $win_result = 3;
        }
        if($banker_left_val['code_info_id'] == $banker_right_val['code_info_id']){
            $win_result .= ',4';
        }
        if($player_left_val['code_info_id'] == $player_right_val['code_info_id']){
            $win_result .= ',5';
        }
        $data['win_result'] = $win_result;

        $res = db('bjl_kj')->insert($data);
        if($res>0){
            echo json_encode(['msg'=>'游戏开始','code'=>1,'success'=>true]);
            exit;
        }else{
            echo json_encode(['msg'=>'操作失败','code'=>0,'success'=>false]);
            exit;
        }
        // 开奖期号生成结束 , 游戏开始

    }

    // 获取当前投注情况
    public function get_tz($kjid){
        $yhid = db('yh')->where('id='.USER_ID)->value('yhid');
        $info = db('order')
                ->field('tz_result,tz_money')
                ->alias('o')
                ->join('order_info i','o.Tzid=i.order_id')
                ->where('o.kjid="'.$kjid.'" and o.yhid="'.$yhid.'"')
                ->select();
        $data[1] = 0;
        $data[2] = 0;
        $data[3] = 0;
        $data[4] = 0;
        $data[5] = 0;
        for ($i=0; $i < count($info); $i++) { 
            if($info[$i]['tz_result'] == 1){
                $data[1] = $data[1] + $info[$i]['tz_money'];
            }else if($info[$i]['tz_result'] == 2){
                $data[2] = $data[2] + $info[$i]['tz_money'];
            }else if($info[$i]['tz_result'] == 3){
                $data[3] = $data[3] + $info[$i]['tz_money'];
            }else if($info[$i]['tz_result'] == 4){
                $data[4] = $data[4] + $info[$i]['tz_money'];
            }else if($info[$i]['tz_result'] == 5){
                $data[5] = $data[5] + $info[$i]['tz_money'];
            }
        }  
        echo json_encode(['msg'=>$data,'code'=>1,'success'=>true]);
        exit;
    }

    public function the_card($kjid)
    {
        $bjl = db('bjl_kj')->where('kjid="'.$kjid.'"')->find();
        $time = $bjl['win_time']-time();
        if($time <= 15){
            $this->error('换牌失败');
        }
        $card = db('bjl_codex')->select();
        $data = array();

        // 随机获取闲家左牌数据 并从数组中剔除
        $player_left_key = array_rand($card);
        $player_left_val = $card[$player_left_key];
        unset($card[$player_left_key]);
        $data['player_left'] = $player_left_val['code_id']; 
        $player_left_num = $player_left_val['code_info_id']>10?10:$player_left_val['code_info_id']; 
        // 随机获取闲家右牌数据 并从数组中剔除
        $player_right_key = array_rand($card);
        $player_right_val = $card[$player_right_key];
        unset($card[$player_right_key]);
        $data['player_right'] = $player_right_val['code_id'];
        $player_right_num = $player_right_val['code_info_id']>10?10:$player_right_val['code_info_id'];
        //闲家最终点数
        $player_result = ($player_left_num+$player_right_num)%10;
        $data['player_three'] = ''; 
        if($player_result >= 0 && $player_result <= 5){
            //如果闲家左牌点数加上右牌点数大于0 并且 小于等于5 补上第三张拍
            $player_three_key = array_rand($card);
            $player_three_val = $card[$player_three_key];
            unset($card[$player_three_key]);
            $data['player_three'] = $player_three_val['code_id']; 
            $player_three_num = $player_three_val['code_info_id']>10?10:$player_three_val['code_info_id']; 
            //闲家最终点数
            $player_result = ($player_left_num+$player_right_num+$player_three_num)%10;
        }else{
            $data['player_result'] = $player_result;
        }

        // 随机获取庄家左牌数据 并从数组中剔除
        $banker_left_key = array_rand($card);
        $banker_left_val = $card[$banker_left_key];
        unset($card[$banker_left_key]);
        $data['banker_left'] = $banker_left_val['code_id']; 
        $banker_left_num = $banker_left_val['code_info_id']>10?10:$banker_left_val['code_info_id']; 
        // 随机获取庄家右牌数据 并从数组中剔除
        $banker_right_key = array_rand($card);
        $banker_right_val = $card[$banker_right_key];
        unset($card[$banker_right_key]);
        $data['banker_right'] = $banker_right_val['code_id']; 
        $banker_right_num = $banker_right_val['code_info_id']>10?10:$banker_right_val['code_info_id']; 
        //庄家最终点数
        $data['banker_three'] = ''; 
        $banker_result = ($banker_left_num+$banker_right_num)%10;
        if($banker_result >= 0 && $banker_result < 7){
            if($banker_result == 3 && (!isset($player_three_num) || $player_three_num != 8)){
                $data['banker_result'] = $banker_result;
            }else if($banker_result==4 && (!isset($player_three_num) || $player_three_num!=0 || $player_three_num!=1 || $player_three_num!=8 || $player_three_num!=9)){
                $data['banker_result'] = $banker_result;
            }else if($banker_result==5 && (!isset($player_three_num) || $player_three_num!=0 || $player_three_num!=1 || $player_three_num!=2 || $player_three_num!=3 || $player_three_num!=8 || $player_three_num!=9)){
                $data['banker_result'] = $banker_result;
            }else if($banker_result==6 && (!isset($player_three_num) || $player_three_num!=6 || $player_three_num!=7)){
                $data['banker_result'] = $banker_result;
            }else{
                //如果庄家左牌点数加上右牌点数大于0 并且 小于等于2 补上第三张拍
                $banker_three_key = array_rand($card);
                $banker_three_val = $card[$banker_three_key];
                unset($card[$banker_three_key]);
                $data['banker_three'] = $banker_three_val['code_id']; 
                $banker_three_num = $banker_three_val['code_info_id']>10?10:$banker_three_val['code_info_id']; 
                //庄家最终点数
                $data['banker_result'] = ($banker_left_num+$banker_right_num+$banker_three_num)%10;
            }            
        }else{
            $data['banker_result'] = $banker_result;
        }

        if($data['banker_result'] > $data['player_result']){
            $win_result = 1;
        }else if($data['banker_result'] < $data['player_result']){
            $win_result = 2;
        }else if($data['banker_result'] == $data['player_result']){
            $win_result = 3;
        }
        if($banker_left_val['code_info_id'] == $banker_right_val['code_info_id']){
            $win_result .= ',4';
        }
        if($player_left_val['code_info_id'] == $player_right_val['code_info_id']){
            $win_result .= ',5';
        }
        $data['win_result'] = $win_result;

        $res = db('bjl_kj')->where('kjid="'.$kjid.'"')->update($data);
        if($res>0){
            $this->success('换牌成功');
        }else{
            $this->error('换牌失败');
        }

    }

    public function old_card()
    {
        $list = db('bjl_kj')
                ->field('banker_left,banker_right,banker_three,banker_result,player_left,player_right,player_three,player_result')
                ->where('win_status=0')
                ->order('add_time desc')
                ->find();
        echo json_encode(['msg'=>$list,'code'=>1,'success'=>true]);
        exit;
    }

    // 八张扑克牌
    public function card($kjid)
    {
        $list = db('bjl_kj')
                ->field('banker_left,banker_right,banker_three,banker_result,player_left,player_right,player_three,player_result')
                ->where('kjid="'.$kjid.'"')
                ->find();
        echo json_encode(['msg'=>$list,'code'=>1,'success'=>true]);
        exit;
    }

    // 开奖倒计时
    public function kj()
    {
        $data = array();
        $data = db('bjl_kj')->field('kjid,win_time')->where('win_status>0')->order('win_time desc')->find();
        $data['Kjdjs'] = $data['win_time']-time();   
  
        echo json_encode(['msg'=>$data,'code'=>1,'success'=>true]);
        exit;
    }
    /**
     * 请求历史记录
     */
    public function history()
    {
        $list = db('bjl_kj')->field('kjid,win_result')->where('win_status=0')->limit(10)->order('add_time desc')->select();
        
        foreach ($list as $key => $value) {
            // $list[$key]['win_result'] = explode(',', $list[$key]['win_result']);
            $list[$key]['win_result'] = str_replace(',', '', $list[$key]['win_result']);
        }

        echo json_encode(['msg'=>$list,'code'=>1,'success'=>true]);
        exit;
    }
    /**
     * 请求余额
     */
    public function balance()
    {
        $yh = db('yh')->where('id='.USER_ID)->find();
        $data['ye'] = $yh['balance']+$yh['no_balance'];
        $data['kjid'] = db('bjl_kj')->where('win_status=1')->value('kjid');
        echo json_encode(['msg'=>'请求成功','data'=>$data,'code'=>1]);
        exit; 
    }

    // 生成token
    public function token()
    {
        $token = md5(uniqid());
        // session_start();
        session('bjl_token',$token);   
        echo json_encode(['msg'=>'请求成功','code'=>1,'token'=>$token,'success'=>true]);
    }
    // 获取彩果
    public function get_result()
    {
        $data = $_REQUEST;

        $kj = db('bjl_kj')->field('kjid,win_result,win_status')->where('kjid="'.$data['kjid'].'"')->find();
        $kj['win_result'] = explode(',', $kj['win_result']);

        if($kj['win_status'] == 1){
            echo json_encode(['msg'=>'游戏未开奖','code'=>2,'success'=>false]);
            exit; 
        }else if($kj['win_status'] == 2){
            echo json_encode(['msg'=>'结果计算中,请稍后','code'=>0,'success'=>false]);
            exit; 
        }else{
            echo json_encode(['msg'=>$kj,'code'=>1,'success'=>true]);
            exit;
        }

    }
    /**
     * 投注
     *varchar $tz_result 投注内容
     *float $tz_money 投注总金额
     *varchar $kjid 开奖期编号
     *[type] [description]
     */
    public function touz()
    {
        // echo json_encode(['msg'=>'维护中','code'=>1001,'success'=>false]);
        // exit; 
        $data = $_REQUEST;

        $token = session('bjl_token');
        session('bjl_token',null);

        if($data['token'] != $token){
            echo json_encode(['msg'=>'数据错误,请勿重复提交','code'=>201,'success'=>false]);
            exit; 
        }else{
            $yh = db('yh')->where('id='.USER_ID)->find();

            $kj = db('bjl_kj')->where('kjid="'.$data['kjid'].'"')->find();

            $id = $yh['id'];

            if($data['tz_money'] <= 0){
                echo json_encode(['msg'=>'投注金额不能小于0','code'=>202,'success'=>false]);
                exit;  
            }
            if(empty($data['tz_result'])){
                echo json_encode(['msg'=>'请选择投注选项','code'=>207,'success'=>false]);
                exit;  
            }
            if($data['tz_money'] > $yh['balance']+$yh['no_balance']){
                echo json_encode(['msg'=>'投注金额超出可用金额','code'=>203,'success'=>false]);
                exit;  
            }
            if(empty($data['tz_money'])){
                echo json_encode(['msg'=>'请输入金额','code'=>204,'success'=>false]);
                exit;  
            }

            $game_code = db('game_cate')->where('game_id='.$this->game_id)->value('game_code');

            $count = db('order')->where('game_id='.$this->game_id)->count();
            $num = $this->tzid+$count;
            $tzid = $game_code.$num;

            $yhid = $yh['yhid'];

            $arr['Tzid'] = $tzid;
            $arr['yhid'] = $yhid;
            $arr['kjid'] = $data['kjid'];
            $arr['Tzzffs'] = 1;
            $arr['Tzzfsj'] = time();
            $arr['TzScbz'] = 1;
            $arr['win_result'] = 0; // 默认为未中奖
            $arr['order_money'] = $data['tz_money'];
            $arr['game_id'] = $this->game_id;

            if($kj['win_status'] > 1){
                echo json_encode(['msg'=>'投注失败,本轮已停止投注','code'=>205,'success'=>false]);
                exit;
            }

            $Kjsj = $kj['win_time'];

            if($Kjsj-$arr['Tzzfsj']<15){
                echo json_encode(['msg'=>'投注失败,本轮已停止投注','code'=>206,'success'=>false]);
                exit;             
            }else{
                $arr['Tzcgbz'] = 1;
                $res = db("order")->insert($arr);
                if($res>0){
                    if($data['tz_money'] >= $yh['no_balance']){
                        db('yh')->where('id='.$yh['id'])->setField('no_balance',0);
                        $residue = $data['tz_money'] - $yh['no_balance'];
                    }else{
                        db('yh')->where('id='.$yh['id'])->setDec('no_balance',$data['tz_money']);
                        $residue = 0;
                    }
                    db('yh')->where('id='.$yh['id'])->setDec('balance',$residue);
                    db('yh')->where('id='.$yh['id'])->setDec('amount_money',$data['tz_money']);
                    $balance = db('yh')->where('id='.$yh['id'])->value('balance');
                    // 创建订单明细
                    $arr1['order_id'] = $tzid;
                    $arr1['tz_result'] = $data['tz_result'];
                    $arr1['tz_money'] = $data['tz_money'];
                    $res_info = db("order_info")->insert($arr1,false,true);
                    db('bjl_code')->where('id='.$data['tz_result'])->setInc('tz_money',$data['tz_money']);
                    if($res_info>0){
                        /**添加账单明细**/
                        $detail['yhid'] = $yhid;
                        $detail['Jylx'] = 3;
                        $detail['jyje'] = $data['tz_money'];
                        $detail['new_money'] = $balance;
                        $detail['Jysj'] = time();
                        $detail['Srhzc'] = 2;
                        $detail['game_id'] = $this->game_id;
                        $detail_res = db('account_details')->insert($detail,false,true);
                        /**添加账单明细end**/
                        
                        /** 下注流水奖励 **/
                        reward($yhid);
                        /** 下注流水奖励 END **/

                        // 必杀模式***************
                        // if(db('game_cate')->where('game_id=3')->value('status') == 3){
                        //     $this->kill($data['kjid'],$data['tz_result']);
                        // }
                        // 必杀模式************

                        echo json_encode(['msg'=>'投注成功','code'=>1]);
                        exit; 
                    }       
                }
            }
        }
    }

    public function kill($kjid,$tz_result)
    {
        $money = db('game_cate')->where('game_id=3')->value('money');
        $Tzid = db('order')->where('kjid="'.$kjid.'"')->column('Tzid');
        $order_info_id_arr = array();
        foreach ($Tzid as $key => $value) {
            $order_info_id = db('order_info')->where('order_id="'.$Tzid[$key].'" and tz_result='.$tz_result)->value('order_info_id');
            if(!empty($order_info_id)){
                $order_info_id_arr[] = $order_info_id;
            }
        }
        $order_info_id = implode(',', $order_info_id_arr);
        $amount_money = db('order_info')->where('order_info_id in ('.$order_info_id.')')->sum('tz_money');
        if($amount_money >= $money){
            $kj = db('bjl_kj')
                ->field('banker_left,banker_right,banker_three,banker_result, player_left,player_right,player_three,player_result')
                ->where('kjid="'.$kjid.'"')
                ->find();
            $data = array();
            if($kj['player_result'] > $kj['banker_result']){
                $data['banker_left'] = $kj['player_left'];
                $data['banker_right'] = $kj['player_right'];
                $data['banker_three'] = $kj['player_three'];
                $data['banker_result'] = $kj['player_result'];
                $data['player_left'] = $kj['banker_left'];
                $data['player_right'] = $kj['banker_right'];
                $data['player_three'] = $kj['banker_three'];
                $data['player_result'] = $kj['banker_result'];
                $res = db('bjl_kj')->where('kjid="'.$kjid.'"')->update($data);
            }
        }
    }
    /**
     * 游戏结束 , 计算收益
     *int $kjid 开奖期编号
     */
    public function over($kjid = ""){
        // 将开奖状态改为停止投注
        if(empty($kjid)){
            $kj = db('bjl_kj')->where('win_status = 1')->order('add_time desc')->find();
            db('bjl_kj')->where('kjid="'.$kj['kjid'].'"')->update(array('win_status'=>2,'start_time'=>time()));       
        }else{
            $kj = db('bjl_kj')->where('win_status = 2 and kjid="'.$kjid.'"')->find();
        }

        db('bjl_kj')->where('kjid="'.$kj['kjid'].'"')->setField('start_time',time());

        // if($kj['banker_result'] > $kj['player_result']){
        //     $win_data[] = 1;
        //     $win_result = 1;
        // }else if($kj['banker_result'] < $kj['player_result']){
        //     $win_data[] = 2;
        //     $win_result = 2;
        // }else{
        //     $win_data[] = 3;
        //     $win_result = 3;
        // }
        // $banker_left = db('bjl_codex')->where('code_id='.$kj['banker_left'])->value('code_info_id');
        // $banker_right = db('bjl_codex')->where('code_id='.$kj['banker_right'])->value('code_info_id');
        // if($banker_left == $banker_right){
        //     $win_data[] = 4;
        //     $win_result .= ',4';
        // }
        // $player_left = db('bjl_codex')->where('code_id='.$kj['player_left'])->value('code_info_id');
        // $player_right = db('bjl_codex')->where('code_id='.$kj['player_right'])->value('code_info_id');
        // if($player_left == $player_right){
        //     $win_data[] = 5;
        //     $win_result .= ',5';
        // }
        $win_result = $kj['win_result'];
        $win_data = explode(',', $win_result);

        // 获取当期所有投注订单
        $order = db('order')->where('kjid="'.$kj['kjid'].'"')->select();
        foreach ($order as $key => $value) {
            $order[$key]['order_info'] = db('order_info')->where('order_id="'.$order[$key]['Tzid'].'"')->select();
        }

        if(!empty($order)){
            foreach ($order as $key => $value) {
                // 默认未中奖
                db('order')->where('Tzid="'.$order[$key]['Tzid'].'"')->setField('win_result',1);
                // 获取所有订单明细
                foreach ($order[$key]['order_info'] as $k => $v) {
                    // 将 中奖彩果编码 / 中奖彩果 / 中奖金额 添加到订单明细中
                        
                    //先将 中奖彩果 / 中奖彩果编码 添加到明细中
                    db('order_info')->where('order_id="'.$order[$key]['order_info'][$k]['order_id'].'"')->update(array('win_result'=>$win_result));
                    // 判断该投注是否中奖 如果中奖了 将计算出的中奖金额添加到订单明细中
                    if(in_array($order[$key]['order_info'][$k]['tz_result'], $win_data)){
                        $odds = db('bjl_code')->where('id='.$order[$key]['order_info'][$k]['tz_result'])->value('odds');
                        $win_money = $order[$key]['order_info'][$k]['tz_money']*$odds+$order[$key]['order_info'][$k]['tz_money'];
                        db('order_info')->where('order_info_id='.$order[$key]['order_info'][$k]['order_info_id'])->update(array('win_money'=>$win_money));
                        db('bjl_code')->where('id='.$order[$key]['order_info'][$k]['tz_result'])->setInc('win_money',$win_money);
                    }
                    //获取当前订单的所有中奖金额的和 将其添加到订单的中奖金额里 并更改中奖状态
                    $tz_sum = db('order_info')->where('order_id="'.$order[$key]['order_info'][$k]['order_id'].'"')->sum('tz_money'); 
                    $win_sum = db('order_info')->where('order_id="'.$order[$key]['order_info'][$k]['order_id'].'"')->sum('win_money'); 
                    if($win_sum>0){
                        //获取当前订单的所有中奖金额的和 将其添加到订单的中奖金额里 并更改中奖状态
                        $money = array('order_money'=>$tz_sum,'win_money'=>$win_sum,'win_result'=>2);
                        
                        /**添加明细**/
                        $detail[$key]['yhid'] = $order[$key]['yhid'];
                        $detail[$key]['Jylx'] = 4;
                        $detail[$key]['jyje'] = $win_money;
                        $detail[$key]['new_money'] = $win_money+db('yh')->where('yhid="'.$order[$key]['yhid'].'"')->value('balance');
                        $detail[$key]['Jysj'] = time();
                        $detail[$key]['Srhzc'] = 1;
                        $detail[$key]['game_id'] = $this->game_id;
                        $detail_res = db('account_details')->insert($detail[$key]);
                        /**添加明细end**/
                        /**计算用户收益**/
                        db('yh')->where('yhid="'.$order[$key]['yhid'].'"')->setInc('balance',$win_money);
                        db('yh')->where('yhid="'.$order[$key]['yhid'].'"')->setInc('amount_money',$win_money);
                        /**计算用户收益end**/
                    }else{
                        $money = array('order_money'=>$tz_sum,'win_money'=>$win_sum);
                    }
                    db('order')->where('Tzid="'.$order[$key]['Tzid'].'"')->update($money);
                }
            }            
        }
        db('bjl_code')->where('1=1')->update(array('tz_money'=>0,'win_money'=>0));
        db('bjl_kj')->where('kjid="'.$kj['kjid'].'"')->update(array('win_status'=>3,'end_time'=>time())); 
        echo json_encode(['msg'=>'结算成功','data'=>$win_result,'code'=>1]);
        exit;
    }
}
