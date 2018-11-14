<?php
namespace app\dial\controller;
use app\home\controller\Base; 
class Game extends Base
{
    private $kjid = 18000001;
    private $tzid = 1000000001;
    private $game_id = 4;
    /**
     * 疯狂骰子--游戏开始
     */
    public function start(){
        db('dial_kj')->where('kjsjzt=3 and game_id='.$this->game_id)->setField('kjsjzt',0);

        $game_code = db('game_cate')->where('game_id='.$this->game_id)->value('game_code');

        $data = array();

        $data['add_time'] = time();
        $data['Kjsj'] = time()+60;
        $data['kjsjzt'] = 1;

        $count = db('dial_kj')->where('game_id='.$this->game_id)->count();
        $num = $this->kjid+$count;
        $kjid = $game_code.$num;
        $data['kjid'] = $kjid;
        $data['game_id'] = $this->game_id;

        $res = db('dial_kj')->insert($data,false,true);

        echo json_encode(['msg'=>'操作成功','code'=>1,'success'=>true]);
        exit;
        // 开奖期号生成结束 , 游戏开始
    }
    public function kj()
    {
        $data = array();
        $data = db('dial_kj')->field('kjid,Kjsj')->where('kjsjzt>0 and game_id='.$this->game_id)->order('Kjsj desc')->find();
        $data['Kjdjs'] = $data['Kjsj']-time();   
  
        echo json_encode(['msg'=>$data,'code'=>1,'success'=>true]);
        exit;
    }
    /**
     * 请求历史记录
     */
    public function history()
    {
        $list = db('dial_kj')->field('kjid,Kjjg')->where('kjsjzt=0 and game_id='.$this->game_id)->limit(5)->order('add_time desc')->select();
        foreach ($list as $key => $value) {
            if(count(explode(',', $list[$key]['Kjjg'])) == 4){
                if(explode(',', $list[$key]['Kjjg'])[3] == '40001'){
                    $list[$key]['status'] = 1;
                }else if(explode(',', $list[$key]['Kjjg'])[3] == '40002'){
                    $list[$key]['status'] = 2;
                }
            }else{
                $list[$key]['status'] = 0;
            }
        }
        echo json_encode(['msg'=>$list,'code'=>1,'success'=>true]);
        exit;
    }
    /**
     * 请求余额
     */
    public function balance()
    {
        $data['ye'] = db('yh')->where('id='.USER_ID)->value('balance');
        $data['kjid'] = db('dial_kj')->where('game_id='.$this->game_id.' and kjsjzt=1')->value('kjid');
        echo json_encode(['msg'=>'请求成功','data'=>$data,'code'=>1]);
        exit; 
    }

    // 生成token
    public function token()
    {
        $token = md5(uniqid());
        session('dial_token',$token);   
        echo json_encode(['msg'=>'请求成功','code'=>1,'token'=>$token,'success'=>true]);
    }
    // 获取彩果
    public function get_result()
    {
        $data = $_REQUEST;

        $kj = db('dial_kj')->field('kjid,Kjjg,kjsjzt')->where('kjid="'.$data['kjid'].'"')->find();
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

        $_data = $_REQUEST;

        $data = json_decode($_data['tz_result'],true);
        $order_money = 0;
        foreach ($data as $key => $value) {
            $data[$key]['tz_money'] = replace_specialChar($data[$key]['tz_money']);
            if(empty($data[$key]['tz_money'])){
                echo json_encode(['msg'=>'请输入正确的金额','code'=>207,'success'=>false]);
                exit; 
            }
            $order_money += $data[$key]['tz_money'];
        }

        $token = session('dial_token');
        session('dial_token',null);

        if($_data['token'] != $token){
            echo json_encode(['msg'=>'数据错误,请勿重复提交','code'=>201,'success'=>false]);
            exit; 
        }else{
            $yh = db('yh')->where('id='.USER_ID)->find();

            $kj = db('dial_kj')->where('kjid="'.$_data['kjid'].'"')->find();

            $id = $yh['id'];

            if($order_money < 0){
                echo json_encode(['msg'=>'投注金额不能小于0','code'=>202,'success'=>false]);
                exit;  
            }
            if($order_money > $yh['balance']){
                echo json_encode(['msg'=>'投注金额超出可用金额','code'=>203,'success'=>false]);
                exit;  
            }
            if(empty($order_money)){
                echo json_encode(['msg'=>'请输入金额','code'=>204,'success'=>false]);
                exit;  
            }

            $game_code = db('game_cate')->where('game_id='.$this->game_id)->value('game_code');

            $count = db('order')->where('game_id='.$this->game_id)->count();
            $num = $this->tzid+$count;
            $tzid = $game_code.$num;

            $yhid = db('yh')->where('id='.USER_ID)->value('yhid');

            $arr['Tzid'] = $tzid;
            $arr['yhid'] = $yhid;
            $arr['kjid'] = $_data['kjid'];
            $arr['Tzzffs'] = 1;
            $arr['Tzzfsj'] = time();
            $arr['TzScbz'] = 1;
            $arr['win_result'] = 0; // 默认为未中奖
            $arr['order_money'] = $order_money;
            $arr['game_id'] = $this->game_id;

            if($kj['kjsjzt'] > 1){
                echo json_encode(['msg'=>'投注失败,本轮已停止投注','code'=>205,'success'=>false]);
                exit;
            }

            $Kjsj = $kj['Kjsj'];

            if($Kjsj-$arr['Tzzfsj']<15){
                echo json_encode(['msg'=>'投注失败,本轮已停止投注','code'=>206,'success'=>false]);
                exit;             
            }else{
                $arr['Tzcgbz'] = 1;
                $res = db("order")->insert($arr);
                if($res>0){
                    db('yh')->where('id='.USER_ID)->setDec('balance',$order_money);
                    db('yh')->where('id='.USER_ID)->setDec('amount_money',$order_money);

                    // 创建订单明细
                    foreach ($data as $key => $value) {
                        $arr1[$key]['order_id'] = $tzid;
                        $arr1[$key]['tz_result'] = $data[$key]['id'];
                        $arr1[$key]['tz_money'] = $data[$key]['tz_money'];
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
    public function over($kjid = ""){
        if(empty($kjid)){
            $kj = db('dial_kj')->where('kjsjzt = 1 and game_id='.$this->game_id)->order('add_time desc')->find();
            db('dial_kj')->where('game_id='.$this->game_id.' and kjid="'.$kj['kjid'].'"')->update(array('kjsjzt'=>2,'start_time'=>time()));
        }else{
            $kj = db('dial_kj')->where('kjsjzt = 2 and game_id='.$this->game_id.' and kjid="'.$kjid.'"')->find();
        }
        // 分红比例--基值
        $ratio = db('conf_dxh')->where('key','ratio')->value('value')/2;
        // 分红比例--幂函数
        $power = db('conf_dxh')->where('key','power')->value('value');

        $codex_dial = db('codex_dial')->field('arr_id,win_money')->select();
        foreach ($codex_dial as $key => $value) {
            $data[$codex_dial[$key]['arr_id']] = $codex_dial[$key]['win_money'];
        }

        // 将乾金猪 和 水虎 概率调到千分之一
        $mt_rand = mt_rand(1,1000);
        if($mt_rand != 1){
            unset($data[1112]);
        }
        if($mt_rand != 2 || $mt_rand != 3){
            unset($data[133]);
            unset($data[233]);
        }
        // END
        
        $game_status = db('game_cate')->where('game_id='.$this->game_id)->value('status');

        if($game_status == 1){
            /* 正常模式 */
            $win_result = array_rand($data);
        }else if($game_status == 2){
            /* 最小数模式 */
            // 获取其中最小值
            $min = min($data);
            // 判断是否有重复的彩果 如果有随机抽取一种彩果
            $array = db('codex_dial')->where('win_money='.$min)->column('arr_id');
            $rand = array_rand($array);
             
            $win_result = $array[$rand];
        }

        // 获取选中彩果的所有玩法编号
        $win_res = db('codex_dial')->field('two,six,twelve,special')->where('arr_id='.$win_result)->find();
        foreach ($win_res as $key => $value) {
            $val = 'value';
            if($value>0){
                $win_data[] = db('code_dial')
                ->where('code_info_id='.db('code_dial')->where('value="'.$key.'"')->value('code_id').' and '.$val.'='.$value)
                ->value('code_id');    
            }
        }
        // 将彩果的所有编码数组转换成字符串
        $win_code = implode(',', $win_data);

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
                    // 将 中奖彩果编码 / 中奖金额 添加到订单明细中
                    //先将 中奖彩果编码 添加到明细中
                    db('order_info')->where('order_id="'.$order[$key]['order_info'][$k]['order_id'].'"')->update(array('win_code'=>$win_code,'win_result'=>$win_code));
                    // 判断该投注是否中奖 如果中奖了 将计算出的中奖金额添加到订单明细中
                    if(array_search($order[$key]['order_info'][$k]['tz_result'], $win_data) !== false){
                        //求当前玩法的中奖金额 (投注金额*玩法赔率)
                        $win_money = $order[$key]['order_info'][$k]['tz_money']*(db('code_dial')->where('code_id='.$order[$key]['order_info'][$k]['tz_result'])->value('odds'));
                        db('order_info')->where('order_info_id='.$order[$key]['order_info'][$k]['order_info_id'])->update(array('win_money'=>$win_money));
                        //获取当前订单的所有中奖金额的和 将其添加到订单的中奖金额里 并更改中奖状态
                        $tz_sum = db('order_info')->where('order_id="'.$order[$key]['order_info'][$k]['order_id'].'"')->sum('tz_money'); 
                        $win_sum = db('order_info')->where('order_id="'.$order[$key]['order_info'][$k]['order_id'].'"')->sum('win_money'); 
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
        $order_money_sum = db('order')->where('kjid="'.$kj['kjid'].'"')->sum('order_money'); 
        $win_money_sum = db('order')->where('kjid="'.$kj['kjid'].'"')->sum('win_money'); 

        db('dial_kj')->where('kjid="'.$kj['kjid'].'"')->update(array('tz_money'=>$order_money_sum,'zjje'=>$win_money_sum,'kjsjzt'=>3,'Kjjg'=>$win_code,'edit_time'=>time())); 

        // 本期结束 清空金额数据
        foreach ($win_res as $keyy => $item) {
            $datas[$keyy.'_money'] = 0;
        }
        $datas['tz_money'] = 0;
        $datas['win_money'] = 0;
        $r = db('codex_dial')->where('1=1')->update($datas);
        echo json_encode(['msg'=>'彩果','data'=>$win_code,'code'=>1]);
        exit;
    }

    // 跟新本期奖池
    public function jackpot($tzid)
    {
        if(!empty($tzid)){
            $order_info = db('order_info')->field('tz_result,tz_money')->where('order_id="'.$tzid.'"')->select();

            $range = array();
            foreach ($order_info as $key => $value) {
                $code_dial[$key] = db('code_dial')->where('code_id='.$order_info[$key]['tz_result'])->value('code_info_id');
                $order_info[$key]['num'] = db('code_dial')->where('code_id='.$order_info[$key]['tz_result'])->value('value');
                $order_info[$key]['value'] = db('code_dial')
                ->where('code_id='.$code_dial[$key])
                ->value('value');
                $res[$key] = db('codex_dial')
                    ->where($order_info[$key]['value'].'='.$order_info[$key]['num'])
                    ->setInc($order_info[$key]['value'].'_money',$order_info[$key]['tz_money']); 
            }
            $codex_dial = db('codex_dial')->select();
            foreach ($codex_dial as $key => $value) {
                $tz_money[$key] = 
                    $codex_dial[$key]['two_money']+
                    $codex_dial[$key]['six_money']+
                    $codex_dial[$key]['twelve_money']+
                    $codex_dial[$key]['special_money'];
                $win_money[$key] = 
                    $codex_dial[$key]['two_odds']*$codex_dial[$key]['two_money']+
                    $codex_dial[$key]['six_odds']*$codex_dial[$key]['six_money']+
                    $codex_dial[$key]['twelve_odds']*$codex_dial[$key]['twelve_money']+
                    $codex_dial[$key]['special_odds']*$codex_dial[$key]['special_money'];

                $tz_money_res[$key] = db('codex_dial')->where('arr_id='.$codex_dial[$key]['arr_id'])->update(array('tz_money'=>$tz_money[$key]));
                $win_money_res[$key] = db('codex_dial')->where('arr_id='.$codex_dial[$key]['arr_id'])->update(array('win_money'=>$win_money[$key]));

            }
        }
    }
}
