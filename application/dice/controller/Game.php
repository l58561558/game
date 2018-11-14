<?php
namespace app\dice\controller;

class Game extends Base
{
    private $kjid = 18000001;
    private $tzid = 1000000001;
    private $game_id = 2;
    /**
     * 疯狂骰子--游戏开始
     */
    public function start(){
        // 清空金额数据
        $datas['sum_numbers_money'] = 0;
        $datas['is_leopard_money'] = 0;
        $datas['dx_money'] = 0;
        $datas['sum_money'] = 0;
        $datas['num1_left_money'] = 0;
        $datas['num1_right_money'] = 0;
        $datas['l_dice_money'] = 0;
        $datas['r_dice_money'] = 0;
        $datas['two_dice_money'] = 0;
        $datas['tz_money'] = 0;
        $datas['win_money'] = 0;
        $r = db('codex_dice')->where('1=1')->update($datas);

        db('dice_kj')->where('game_id=2 and kjsjzt=3')->setField('kjsjzt',0);

        $game_code = db('game_cate')->where('game_id='.$this->game_id)->value('game_code');

        $data = array();

        $data['add_time'] = time();
        $data['Kjsj'] = time()+60;
        $data['kjsjzt'] = 1;

        $count = db('dice_kj')->where('game_id='.$this->game_id)->count();
        $num = $this->kjid+$count;
        $kjid = $game_code.$num;
        $data['kjid'] = $kjid;
        $data['game_id'] = $this->game_id;

        $res = db('dice_kj')->insert($data,false,true);
        // 开奖期号生成结束 , 游戏开始

        // 开始时给豹子的中奖金额加十块钱
        // $baozi = '11,22,33,44,55,66';

        // db('codex_dice')->where('arr_id in ('.$baozi.')')->update(array('tz_money'=>0.9));
        // db('codex_dice')->where('arr_id in ('.$baozi.')')->update(array('win_money'=>0.9*5.8));
        // db('codex_dice')->where('arr_id in ('.$baozi.') and is_leopard=1')->update(array('is_leopard_money'=>0.9));

        // cookie('sz_kjid',$kjid);

    }
    public function kj()
    {
        $data = array();
        $data = db('dice_kj')->field('kjid,Kjsj')->where('kjsjzt>0 and game_id='.$this->game_id)->order('Kjsj desc')->find();
        $data['Kjdjs'] = $data['Kjsj']-time();   
  
        echo json_encode(['msg'=>$data,'code'=>1,'success'=>true]);
        exit;
    }
    /**
     * 请求历史记录
     */
    public function history()
    {
        $list = db('dice_kj')->field('kjid,Kjjg')->where('kjsjzt=0 and game_id='.$this->game_id)->limit(10)->order('add_time desc')->select();
        $data['list'] = $list;
        $dice = db('code_dice')->where('code_info_id>0')->select();
        $user = db('yh')->where('id='.USER_ID)->find();
        foreach ($dice as $key => $value) {
            
            if($dice[$key]['code_id'] == 20001){
                $dice[$key] = $dice[55];
            }
            if($dice[$key]['code_id'] == 20002){
                $dice[$key] = $dice[56];
            }
            $dice[$key]['odds'] = round($dice[$key]['odds']*$user['radio'],2);
        }
        unset($dice[55]);
        unset($dice[56]);
        $data['odds'] = $dice;
        echo json_encode(['msg'=>$data,'code'=>1,'success'=>true]);
        exit;
    }
    /**
     * 请求余额
     */
    public function balance()
    {
        $data['ye'] = db('yh')->where('id='.USER_ID)->value('balance');
        $data['kjid'] = db('dice_kj')->where('game_id='.$this->game_id.' and kjsjzt=1')->value('kjid');
        echo json_encode(['msg'=>'请求成功','data'=>$data,'code'=>1]);
        exit; 
    }

    // 生成token
    public function token()
    {
        $token = md5(uniqid());
        // session_start();
        session('dice_token',$token);   
        echo json_encode(['msg'=>'请求成功','code'=>1,'token'=>$token,'success'=>true]);
    }
    // 获取彩果
    public function get_result()
    {
        $data = $_REQUEST;

        // db('kj')->where('kjsjzt>0 and game_id=1')->setField('kjsjzt',0);
        //db('kj')->where('game_id=1 and kjid="'.$data['kjid'].'"')->setField('kjsjzt',0);

        $kj = db('dice_kj')->field('kjid,Kjjg,kjsjzt')->where('kjid="'.$data['kjid'].'"')->find();
        if($kj['kjsjzt'] == 1){
            echo json_encode(['msg'=>'游戏未开奖','code'=>2,'success'=>false]);
            exit; 
        }else if($kj['kjsjzt'] == 2){
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
     *float $order_money 投注总金额
     *varchar $kjid 开奖期编号
     *[type] [description]
     */
    public function touz()
    {
        // echo json_encode(['msg'=>'维护中','code'=>1001,'success'=>false]);
        // exit; 
        $data = $_REQUEST;

        $token = session('dice_token');
        session('dice_token',null);

        if($data['token'] != $token){
            echo json_encode(['msg'=>'数据错误,请勿重复提交','code'=>5,'success'=>false]);
            exit; 
        }else{
            $data['tz_result'] = $tz_result = json_decode($data['tz_result'],TRUE);
            $order_money = 0; // 投注总金额
            foreach ($tz_result as $key => $value) {
                $tz_result[$key]['money'] = replace_specialChar($tz_result[$key]['money']);
                if($tz_result[$key]['money'] <= 0){
                    echo json_encode(['msg'=>'投注金额不能小于0','code'=>4,'success'=>false]);
                    exit;  
                }
                $order_money += $tz_result[$key]['money'];

            }

            $yh = db('yh')->where('id='.USER_ID)->find();

            $kj = db('dice_kj')->where('kjid="'.$data['kjid'].'"')->find();

            $id = $yh['id'];

            if($order_money < 0){
                echo json_encode(['msg'=>'投注金额不能小于0','code'=>4,'success'=>false]);
                exit;  
            }
            if($order_money > $yh['balance']){
                echo json_encode(['msg'=>'投注金额超出可用金额','code'=>2,'success'=>false]);
                exit;  
            }
            if(empty($order_money)){
                echo json_encode(['msg'=>'请输入金额','code'=>3,'success'=>false]);
                exit;  
            }

            $game_code = db('game_cate')->where('game_id='.$this->game_id)->value('game_code');

            $count = db('order')->where('game_id='.$this->game_id)->count();
            $num = $this->tzid+$count;
            $tzid = $game_code.$num;

            $yhid = db('yh')->where('id='.USER_ID)->value('yhid');

            $arr['Tzid'] = $tzid;
            $arr['yhid'] = $yhid;
            $arr['kjid'] = $data['kjid'];
            $arr['Tzzffs'] = 1;
            $arr['Tzzfsj'] = time();
            $arr['TzScbz'] = 1;
            $arr['win_result'] = 0; // 默认为未中奖
            $arr['order_money'] = $order_money;
            $arr['game_id'] = $this->game_id;

            if($kj['kjsjzt'] > 1){
                echo json_encode(['msg'=>'投注失败,网络超时','code'=>0,'success'=>false]);
                exit;
            }

            $Kjsj = $kj['Kjsj'];

            if($Kjsj-$arr['Tzzfsj']<15){
                echo json_encode(['msg'=>'投注失败,本轮已停止投注','code'=>101,'success'=>false]);
                exit;             
            }else{
                $arr['Tzcgbz'] = 1;
                $res = db("order")->insert($arr);
                if($res>0){
                    db('yh')->where('id='.USER_ID)->setDec('balance',$order_money);
                    db('yh')->where('id='.USER_ID)->setDec('amount_money',$order_money);

                    // 创建订单明细
                    foreach ($tz_result as $key => $value) {
                        $arr1[$key]['order_id'] = $tzid;
                        $arr1[$key]['tz_result'] = $tz_result[$key]['num'];
                        $arr1[$key]['tz_money'] = $tz_result[$key]['money'];
                    }

                    $res_info = db("order_info")->insertAll($arr1,false,true);
                    if($res_info>0){
                        /**添加账单明细**/
                        $detail['yhid'] = $yhid;
                        $detail['Jylx'] = 3;
                        $detail['jyje'] = $order_money;
                        $detail['new_money'] = $yh['balance']-$order_money;
                        $detail['Jysj'] = time();
                        $detail['Srhzc'] = 2;
                        $detail['game_id'] = $this->game_id;
                        $detail_res = db('account_details')->insert($detail,false,true);
                        /**添加账单明细end**/

                        /* 投注金额分销分红 */
                        $money = $order_money;
                        // 分红比例--基值
                        $ratio = db('conf_dxh')->where('key','ratio')->value('value');
                        // 分红比例--幂函数
                        $power = db('conf_dxh')->where('key','power')->value('value');
                        
                        fx($id,$ratio,$power,$money);
                        /* 投注金额分销分红end */

                        $this->jackpot($tzid); // 跟新奖池数据

                        /** 下注流水奖励 **/
                        reward($yhid);
                        /** 下注流水奖励 END **/

                        echo json_encode(['msg'=>'投注成功','code'=>1]);
                        exit; 
                    }       
                }
            }
        }
    }


    /**
     * 游戏结束 , 计算收益
     *int $kjid 开奖期编号
     */
    public function over(){
        // 将开奖状态改为停止投注
        // $kjid = cookie('sz_kjid');
        // db('kj')->where('kjid="'.$kjid.'" and game_id='.$this->game_id)->setField('kjsjzt',2);
        db('dice_kj')->where('kjsjzt = 1 and game_id='.$this->game_id)->setField('kjsjzt',2);
        // 获取当期开奖信息
        // $kj = db('kj')->where('kjid="'.$kjid.'" and game_id='.$this->game_id)->find();
        $kj = db('dice_kj')->where('kjsjzt = 2 and game_id='.$this->game_id)->select();

        for ($i=0; $i < count($kj); $i++) { 

            db('dice_kj')->where('game_id='.$this->game_id.' and kjid="'.$kj[$i]['kjid'].'"')->setField('start_time',time());
            // 分红比例--基值
            $ratio = db('conf_dxh')->where('key','ratio')->value('value')/2;
            // 分红比例--幂函数
            $power = db('conf_dxh')->where('key','power')->value('value');

            // 获取所有的骰子点数和预计中奖金额
            $codex_dice = db('codex_dice')->field('arr_id,win_money')->select();
            foreach ($codex_dice as $key => $value) {
                $data[$codex_dice[$key]['arr_id']] = $codex_dice[$key]['win_money'];
            }
            $game_status = db('game_cate')->where('game_id='.$this->game_id)->value('status');

            if($game_status == 1){
                /* 正常模式 */
                $left = mt_rand(1,6);
                $right = mt_rand(1,6);
                $win_result = $left.$right;
            }else if($game_status == 2){
                /* 最小数模式 */
                // 获取其中最小值
                $min = min($data); 
                // 判断是否有重复的彩果 如果有随机抽取一种彩果
                $array = db('codex_dice')->where('win_money='.$min)->column('arr_id');
                $rand = array_rand($array);
                $win_result = $array[$rand];
                 
                $win_result = $array[$rand];
            }

            // 获取选中彩果的所有玩法编号
            $win_res = db('codex_dice')->field('sum_numbers,is_leopard,dx,sum,num1_left,num1_right,l_dice,r_dice,two_dice')->where('arr_id='.$win_result)->find();

            $arr = array('2','3','4','10','11','12');

            foreach ($win_res as $key => $value) {
                $val = 'value';
                if($key=='sum' || $key=='two_dice'){
                    $val = 'code_name';
                    if($key == 'sum'){
                        if(in_array($value, $arr)){
                            $val = 'value';
                            $value = '6';
                        }
                    }
                }
                
                $win_data[] = db('code_dice')
                ->where('code_info_id='.db('code_dice')->where('value="'.$key.'"')->value('code_id').' and '.$val.'='.$value)
                ->value('code_id');
            }

            $win_arr = array(50001,50002,50003,50004,50005,50006);
            foreach ($win_data as $k => $v) {
                if(in_array($win_data[$k], $win_arr)){
                    $win_data[$k] = str_replace(5000,4000,$win_data[$k]);
                }
            }
            // 判断如果 和的单双数玩法编号为空 则给他 ’其他‘ 玩法 编码为30006
            // foreach ($win_data as $k => $v) {
            //     if(in_array($win_res['sum'], $arr)){
            //         $win_data[$k] = 30006;
            //     }
            // }
            // foreach ($win_data as $k => $v) {
            //     if($v == NULL){
            //         $v = 30006;
            //     }
            // }
            // dump($win_data);die;
            // 将彩果的所有编码数组转换成字符串
            $win_code = implode(',', $win_data);
            // 获取当期所有投注订单
            $order = db('order')->where('kjid="'.$kj[$i]['kjid'].'"')->select();
            if(!empty($order)){
                foreach ($order as $key => $value) {
                    // 默认未中奖
                    db('order')->where('Tzid="'.$order[$key]['Tzid'].'"')->setField('win_result',1);
                    // 获取所有订单明细
                    $order_info = db('order_info')->where('order_id="'.$order[$key]['Tzid'].'"')->select();
                    foreach ($order_info as $k => $v) {
                        // 将 中奖彩果编码 / 中奖彩果 / 中奖金额 添加到订单明细中
                            
                            //先将 中奖彩果 / 中奖彩果编码 添加到明细中
                        db('order_info')->where('order_id="'.$order_info[$k]['order_id'].'"')->update(array('win_code'=>$win_code,'win_result'=>$win_result));
                            // 判断该投注是否中奖 如果中奖了 将计算出的中奖金额添加到订单明细中
                        

                        if(array_search($order_info[$k]['tz_result'], $win_data) !== false){
                            $user = db('yh')->where('yhid="'.$order[$key]['yhid'].'"')->find();
                            //求当前玩法的中奖金额 (投注金额*玩法赔率)
                            $win_money = $order_info[$k]['tz_money']*(round(db('code_dice')->where('code_id='.$order_info[$k]['tz_result'])->value('odds')*$user['radio'],2));
                            db('order_info')->where('order_info_id='.$order_info[$k]['order_info_id'])->update(array('win_money'=>$win_money));
                            //获取当前订单的所有中奖金额的和 将其添加到订单的中奖金额里 并更改中奖状态
                            $tz_sum = db('order_info')->where('order_id="'.$order_info[$k]['order_id'].'"')->sum('tz_money'); 
                            $win_sum = db('order_info')->where('order_id="'.$order_info[$k]['order_id'].'"')->sum('win_money'); 
                            db('order')->where('Tzid="'.$order[$key]['Tzid'].'"')->update(array('order_money'=>$tz_sum,'win_money'=>$win_sum,'win_result'=>2));
                            
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
                            /** 中奖金额分销分红 **/
                            // $id = db('yh')->where('yhid="'.$order[$key]['yhid'].'"')->value('id');
                            // fx($id,$ratio,$power,$detail[$key]['jyje']);
                            /** 中奖金额分销分红end **/
                        }
                    }
                }            
            }


            // 将当期所有订单的投注金额和中奖金额添加到往期历史里面 并将本期状态改为 结束 kjsjzt = 3
            $order_money_sum = db('order')->where('kjid="'.$kj[$i]['kjid'].'"')->sum('order_money'); 
            $win_money_sum = db('order')->where('kjid="'.$kj[$i]['kjid'].'"')->sum('win_money'); 
            // $str1 = substr($win_result,1,1);
            // $str2 = substr($win_result,-1,1);
            // $str = $str1.','.$str2;
            db('dice_kj')->where('kjid="'.$kj[$i]['kjid'].'"')->update(array('tz_money'=>$order_money_sum,'zjje'=>$win_money_sum,'kjsjzt'=>3,'Kjjg'=>$win_result,'edit_time'=>time())); 
        }
        // 本期结束 清空金额数据
        foreach ($win_res as $keyy => $item) {
            $datas[$keyy.'_money'] = 0;
        }
        $datas['tz_money'] = 0;
        $datas['win_money'] = 0;
        $r = db('codex_dice')->where('1=1')->update($datas);

 
        // echo json_encode(['msg'=>'彩果','data'=>$win_result,'code'=>1]);
        // exit;
    }

    // 跟新本期奖池
    public function jackpot($tzid)
    {
        // $list = db('kj')->where('kjsjzt>0 and game_id='.$this->game_id)->find();
        if(!empty($tzid)){
            // $order_id = db('order')->where('kjid="'.$list['kjid'].'" and game_id='.$this->game_id)->value('Tzid');
            $order_info = db('order_info')->field('tz_result,tz_money')->where('order_id="'.$tzid.'"')->select();

            $range = array();
            foreach ($order_info as $key => $value) {
                $code_dice[$key] = db('code_dice')->where('code_id='.$order_info[$key]['tz_result'])->value('code_info_id');
                if($code_dice[$key] == 30 || $code_dice[$key] == 80){
                    $order_info[$key]['num'] = db('code_dice')->where('code_id='.$order_info[$key]['tz_result'])->value('code_name');
                    if($order_info[$key]['num'] == '其他'){
                        $order_info[$key]['num'] = 0;
                    }
                }else{
                    $order_info[$key]['num'] = db('code_dice')->where('code_id='.$order_info[$key]['tz_result'])->value('value');
                }
                // dump($order_info);
                $order_info[$key]['value'] = db('code_dice')
                ->where('code_id='.$code_dice[$key])
                ->value('value');
                $res[$key] = db('codex_dice')
                    ->where($order_info[$key]['value'].'='.$order_info[$key]['num'])
                    ->setInc($order_info[$key]['value'].'_money',$order_info[$key]['tz_money']);
            }

            $codex_dice = db('codex_dice')->select();
            // dump($codex_dice);

            foreach ($codex_dice as $key => $value) {
                $tz_money[$key] = 
                    $codex_dice[$key]['sum_numbers_money']+
                    $codex_dice[$key]['is_leopard_money']+
                    $codex_dice[$key]['dx_money']+
                    $codex_dice[$key]['sum_money']+
                    $codex_dice[$key]['num1_left_money']+
                    // $codex_dice[$key]['num1_right_money']+
                    $codex_dice[$key]['l_dice_money']+
                    $codex_dice[$key]['r_dice_money']+
                    $codex_dice[$key]['two_dice_money'];
                $win_money[$key] = 
                    $codex_dice[$key]['sum_numbers_odds']*$codex_dice[$key]['sum_numbers_money']+
                    $codex_dice[$key]['is_leopard_odds']*$codex_dice[$key]['is_leopard_money']+
                    $codex_dice[$key]['dx_odds']*$codex_dice[$key]['dx_money']+
                    $codex_dice[$key]['sum_odds']*$codex_dice[$key]['sum_money']+
                    $codex_dice[$key]['num1_left_odds']*$codex_dice[$key]['num1_left_money']+
                    // $codex_dice[$key]['num1_right_odds']*$codex_dice[$key]['num1_right_money']+
                    $codex_dice[$key]['l_dice_odds']*$codex_dice[$key]['l_dice_money']+
                    $codex_dice[$key]['r_dice_odds']*$codex_dice[$key]['r_dice_money']+
                    $codex_dice[$key]['two_dice_odds']*$codex_dice[$key]['two_dice_money'];

                $tz_money_res[$key] = db('codex_dice')->where('arr_id='.$codex_dice[$key]['arr_id'])->update(array('tz_money'=>$tz_money[$key]));
                $win_money_res[$key] = db('codex_dice')->where('arr_id='.$codex_dice[$key]['arr_id'])->update(array('win_money'=>$win_money[$key]));

            }
            // dump($win_money);
            // dump($tz_money);die;
        }
    }
}
