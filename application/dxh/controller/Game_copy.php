<?php
namespace app\dxh\controller;

class Game extends Base
{
    private $kjid = 18000001;
    private $tzid = 1000000001;
    private $game_id = 1;
    /**
     * 大小和--游戏开始
     */
    public function start(){

        // 将上一期改为结束状态
        db('dxh_kj')->where('game_id=1 and kjsjzt=3')->setField('kjsjzt',0);
 
		
        $game_code = db('game_cate')->where('game_id='.$this->game_id)->value('game_code');

        $data = array();
        
        $data['add_time'] = time();
        $data['Kjsj'] = time()+60;
        $data['kjsjzt'] = 1;

        // $count = db('dxh_kj')->where('game_id='.$this->game_id)->count();
        $count = db('dxh_kj')->where('game_id='.$this->game_id)->order('add_time desc')->value('id');
        $count = !empty($count)?$count:0;

        $num = $this->kjid+$count;
        $kjid = $game_code.$num;
        $data['kjid'] = $kjid;
        $data['game_id'] = $this->game_id;
        
        $res = db('dxh_kj')->insert($data,false,true);
        // 开奖期号生成结束 , 游戏开始
    }
	
    // 生成token
    public function token()
    {
        $token = md5(uniqid());
        // session_start();
        session('dxh_token',$token);   
        echo json_encode(['msg'=>'请求成功','code'=>1,'token'=>$token,'success'=>true]);
        exit;     
    }
	
    public function kj()
    {
        $data = array();
        $data = db('dxh_kj')->field('kjid,Kjsj')->where('kjsjzt>0 and game_id='.$this->game_id)->order('Kjsj desc')->find();
        $data['Kjdjs'] = $data['Kjsj']-time();   
  
        echo json_encode(['msg'=>$data,'code'=>1,'success'=>true]);
        exit;
    }
    /**
     * 请求历史记录
     */
    public function history()
    {
        $list = db('dxh_kj')->field('kjid,Kjjg,qq_now')->where('kjsjzt=0 and game_id='.$this->game_id)->limit(15)->order('add_time desc')->select();

        foreach ($list as $key => $value) {
            $list[$key]['Kjjg'] = db('codex_dxh')->where('id='.substr($list[$key]['Kjjg'], 0, 1))->value('desc');
            $list[$key]['num'][0] = mb_substr($list[$key]['qq_now'], 0,1);
            $list[$key]['num'][1] = mb_substr($list[$key]['qq_now'], 1,1);
            $list[$key]['num'][2] = mb_substr($list[$key]['qq_now'], 2,1);
        }        
        $data['list'] = $list;
        $dxh = db('codex_dxh')->field('id,odds')->select();
        $level = db('yh')->where('id='.USER_ID)->value('level');
        if($level == 0){
            $level = 1;
        }
        foreach ($dxh as $key => $value) {
            if($dxh[$key]['id'] == 1 || $dxh[$key]['id'] == 3){
                $dxh[$key]['odds'] = round($dxh[$key]['odds']+0.05/$level,2);
            }else if($dxh[$key]['id'] == 2){
                $dxh[$key]['odds'] = round($dxh[$key]['odds']+0.6/$level,2);
            }
        }
        $data['odds'] = $dxh;
        echo json_encode(['msg'=>$data,'code'=>1,'success'=>true]);
        exit;
    }

    /**
     * 请求余额
     */
    public function balance()
    {
        $data['ye'] = db('yh')->where('id='.USER_ID)->value('balance');
        $data['kjid'] = db('dxh_kj')->where('game_id='.$this->game_id.' and kjsjzt=1')->value('kjid');
        echo json_encode(['msg'=>'请求成功','data'=>$data,'code'=>1]);
        exit; 
    }

    // 获取彩果
    public function get_result()
    {
        $data = $_REQUEST;
        $kj = db('dxh_kj')->field('kjid,Kjjg,kjsjzt,qq_now')->where('kjid="'.$data['kjid'].'"')->find();
        $kj['num'][0] = mb_substr($kj['qq_now'], 0,1);
        $kj['num'][1] = mb_substr($kj['qq_now'], 1,1);
        $kj['num'][2] = mb_substr($kj['qq_now'], 2,1);
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
        
        $token = session('dxh_token');
        session('dxh_token',null);

        if($data['token'] != $token){
            echo json_encode(['msg'=>'数据错误,请勿重复提交','code'=>5,'success'=>false]);
            exit; 
        }else{
            
            $yh = db('yh')->where('id='.USER_ID)->find();

            $kj = db('dxh_kj')->where('kjid="'.$data['kjid'].'"')->find();
            
            $id = $yh['id'];
            
            if($data['order_money'] <= 0){
                echo json_encode(['msg'=>'投注金额不能小于0','code'=>4,'success'=>false]);
                exit;  
            }
            if($data['order_money'] > $yh['balance']){
                echo json_encode(['msg'=>'投注金额超出可用金额','code'=>2,'success'=>false]);
                exit;  
            }
            if(empty($data['order_money'])){
                echo json_encode(['msg'=>'请输入金额','code'=>3,'success'=>false]);
                exit;  
            }
            
            $game_code = db('game_cate')->where('game_id='.$this->game_id)->value('game_code');

            $count = db('order')->where('game_id='.$this->game_id)->count();
            $num = $this->tzid+$count;
            $tzid = $game_code.$num;

            $yhid = db('yh')->where('id='.$id)->value('yhid');
            
            $arr['Tzid'] = $tzid;
            $arr['yhid'] = $yhid;
            $arr['kjid'] = $data['kjid'];
            $arr['Tzzffs'] = 1;
            $arr['Tzzfsj'] = time();
            $arr['TzScbz'] = 1;
            $arr['order_money'] = $data['order_money'];
            $arr['win_result'] = 0; // 默认为未中奖
            $arr['game_id'] = $this->game_id;

            if($kj['kjsjzt'] > 1){
                echo json_encode(['msg'=>'投注失败,本轮已停止投注','code'=>0,'success'=>false]);
                exit;
            }

            $Kjsj = $kj['Kjsj'];
            if($Kjsj-$arr['Tzzfsj']<40){
                echo json_encode(['msg'=>'投注失败,本轮已停止投注','code'=>0,'success'=>false]);
                exit;            
            }else{
                $arr['Tzcgbz'] = 1;
                $res = db("order")->insert($arr);
                if($res>0){
                    // 创建订单明细
                    $tz_result = explode(',', $data['tz_result']);

                    $tz_count = count($tz_result);
                    foreach ($tz_result as $key => $value) {
                        $arr1[$key]['order_id'] = $tzid;
                        $arr1[$key]['tz_result'] = $tz_result[$key];
                        $arr1[$key]['tz_money'] = $data['order_money']/$tz_count;
                    }
                    $res_info = db("order_info")->insertAll($arr1,false,true);

                    if($res_info>0){
                        // 创建成功将金额累加
                        foreach ($tz_result as $key => $value) {
                            db('codex_dxh')->where('id='.$tz_result[$key])->setInc('tz_money',$data['order_money']/$tz_count);
                            db('codex_dxh')->where('id='.$tz_result[$key])->setInc('win_money',$data['order_money']*db('codex_dxh')->where('id='.$tz_result[$key])->value('odds'));
                        }

                        /**添加账单明细**/
                        $detail['yhid'] = db('yh')->where('id='.$id)->value('yhid');
                        $detail['Jylx'] = 3;
                        $detail['jyje'] = $data['order_money'];
                        $detail['new_money'] = $yh['balance']-$data['order_money'];
                        $detail['Jysj'] = time();
                        $detail['Srhzc'] = 2;
                        $detail['game_id'] = $this->game_id;
                        $detail_res = db('account_details')->insert($detail,false,true);
                        /**添加账单明细end**/

                        /* 投注金额分销分红 */
                        $money = $data['order_money'];
                        // 分红比例--基值
                        $ratio = db('conf_dxh')->where('key','ratio')->value('value');
                        // 分红比例--幂函数
                        $power = db('conf_dxh')->where('key','power')->value('value');

                        fx($id,$ratio,$power,$money);
                        /* 投注金额分销分红end */

                        // 投注成功减去用户余额
                        db('yh')->where('id='.$id)->setDec('balance',$data['order_money']);
                        db('yh')->where('id='.$id)->setDec('amount_money',$data['order_money']);
                        $ye['balance'] = db('yh')->where('id='.$id)->value('balance');

                        /** 下注流水奖励 **/
                        reward($yhid);
                        /** 下注流水奖励 END **/

                        echo json_encode(['msg'=>'投注成功','data'=>$ye,'code'=>1,'success'=>true]);
                        exit; 
                    }else{
                        echo json_encode(['msg'=>'投注失败,本轮已停止投注','code'=>0,'success'=>false]);
                        exit;   
                    }
                }else{
                    echo json_encode(['msg'=>'投注失败,本轮已停止投注','code'=>0,'success'=>false]);
                    exit;                  
                }
            }
        }
    }

    /**
     * 游戏结束 , 计算收益
     *int $kjid 开奖期编号
     */
	public function over($kjid = "")
    {
        // 分红比例--基值
        $ratio = db('conf_dxh')->where('key','ratio')->value('value')/2;
        // 分红比例--幂函数
        $power = db('conf_dxh')->where('key','power')->value('value');

        if(empty($kjid)){
            $num = db('dxh_kj')->where('game_id=1 and kjsjzt = 1')->value('qq_now');
            $people['str'] = $num;
            $people['arr'][0] = mb_substr($num, 0,1);
            $people['arr'][1] = mb_substr($num, 1,1);
            $people['arr'][2] = mb_substr($num, 2,1);

            $kj = db('dxh_kj')->where('kjsjzt = 1 and game_id=1')->order('add_time desc')->find();
            db('dxh_kj')->where('game_id=1 and kjid="'.$kj['kjid'].'"')->update(array('kjsjzt'=>2,'start_time'=>time()));       
        }else{
            $kj = db('dxh_kj')->where('kjsjzt = 2 and game_id=1 and kjid="'.$kjid.'"')->find();
            $num = db('dxh_kj')->where('game_id=1 and kjid="'.$kjid.'"')->value('qq_now');
            $people['str'] = $num;
            $people['arr'][0] = mb_substr($num, 0,1);
            $people['arr'][1] = mb_substr($num, 1,1);
            $people['arr'][2] = mb_substr($num, 2,1);
        }

        $game_status = db('game_cate')->where('game_id=1')->value('status');

        if($game_status == 1){
            $status = false;
        }else if($game_status == 2){
            $status = true;
        }
        // 开最小数模式
        if($status || empty($num)){
            $dxh = db('codex_dxh')->where('id in (1,3)')->column('win_money');
            // 获取其中最小值
            $min = min($dxh); 
            // 判断是否有重复的彩果 如果有随机抽取一种彩果
            $array = db('codex_dxh')->where('id in (1,3) and win_money='.$min)->column('id');
            $rand = array_rand($array);
            $num = $array[$rand];
            if($num == 1){
                $one = mt_rand(3,9);
                $two = mt_rand(0,9);
                $three = $one-3;
            }else if($num == 3){
                $one = mt_rand(0,6);
                $two = mt_rand(0,9);
                $three = $one+3;
            }
            // $one = mt_rand(0,8);
            // $three = $one;
            // $two = $one+1;
            $people['str'] = $one.$two.$three;
            $people['arr'][0] = $one;
            $people['arr'][1] = $two;
            $people['arr'][2] = $three;
        }
        // END

        // 当人数开出豹子并且压豹子的金额大于10的时候 开最小数
        $baozi_money = db('codex_dxh')->where('id=4')->value('tz_money');
        if(($people['arr'][0] == $people['arr'][1] && $people['arr'][0] == $people['arr'][2] && $people['arr'][1] == $people['arr'][2]) && $baozi_money > 10){
            $dxh = db('codex_dxh')->where('id in (1,3)')->column('win_money');
            // 获取其中最小值
            $min = min($dxh); 
            $array = db('codex_dxh')->where('id in (1,3) and win_money='.$min)->column('id');
            $rand = array_rand($array);
            $num = $array[$rand];
            if($num == 1){
                $one = mt_rand(1,9);
                $two = mt_rand(0,9);
                $three = $one-1;
            }else if($num == 3){
                $one = mt_rand(0,8);
                $two = mt_rand(0,9);
                $three = $one+1;
            }
            $people['str'] = $one.$two.$three;
            $people['arr'][0] = $one;
            $people['arr'][1] = $two;
            $people['arr'][2] = $three;            
        }

        if($people['arr'][0] == $people['arr'][1] && $people['arr'][0] == $people['arr'][2] && $people['arr'][1] == $people['arr'][2]){
            $win_result = 4;
        }else if($people['arr'][0] > $people['arr'][2]){
            $win_result = 1;
        }else if($people['arr'][0] == $people['arr'][2]){
            $win_result = 2;
        }else if($people['arr'][0] < $people['arr'][2]){
            $win_result = 3;
        }

        $win_data[0] = $win_result;
        foreach ($people['arr'] as $key => $value) {
            if($people['arr'][$key] == 0){
                $win_data[$key+1] = 10;
            }else{
                $win_data[$key+1] = db('codex_dxh')->where('`key`='.$people['arr'][$key])->value('id');
            }
        }
        if($win_result == 4){
            $win_data[4] = 2;
        }

        $win_result = implode(',', array_unique($win_data));

        db('dxh_kj')->where('kjid="'.$kj['kjid'].'"')->setField('qq_now',$people['str']);

        $order = db('order')->where('kjid="'.$kj['kjid'].'"')->select();
        foreach ($order as $key => $value) {
            $order[$key]['order_info'] = db('order_info')->where('order_id="'.$order[$key]['Tzid'].'"')->select();
        }

        $detail = array();

        foreach ($order as $key => $value) {

            // 默认未中奖
            db('order')->where('Tzid="'.$order[$key]['Tzid'].'"')->setField('win_result',1);
            // 获取所有订单明细
            $order_info = db('order_info')->where('order_id="'.$order[$key]['Tzid'].'"')->select();
            foreach ($order[$key]['order_info'] as $k => $v) {
                //先将 中奖彩果编码 添加到明细中
                db('order_info')->where('order_id="'.$order[$key]['order_info'][$k]['order_id'].'"')->update(array('win_code'=>$people['str'],'win_result'=>$win_result));
                // 判断该投注是否中奖 如果中奖了 将计算出的中奖金额添加到订单明细中
                if(array_search($order[$key]['order_info'][$k]['tz_result'], $win_data) !== false){
                    //求当前玩法的中奖金额 (投注金额*玩法赔率)
                    $level = db('yh')->where('yhid="'.$order[$key]['yhid'].'"')->value('level');
                    if($level == 0){
                        $level = 1;
                    }
                    if($order[$key]['order_info'][$k]['tz_result'] == 1 || $order[$key]['order_info'][$k]['tz_result'] == 3)
                    {
                        $win_money = $order[$key]['order_info'][$k]['tz_money']*round(db('codex_dxh')->where('id='.$order[$key]['order_info'][$k]['tz_result'])->value('odds')+0.05/$level,2);
                    }
                    else if($order[$key]['order_info'][$k]['tz_result'] == 2)
                    {
                        $win_money = $order[$key]['order_info'][$k]['tz_money']*round(db('codex_dxh')->where('id='.$order[$key]['order_info'][$k]['tz_result'])->value('odds')+0.6/$level,2);
                        if($win_money > 10000){
                            $win_money = 10000;
                        }
                    }else
                    {
                        $win_money = $order[$key]['order_info'][$k]['tz_money']*(db('codex_dxh')->where('id='.$order[$key]['order_info'][$k]['tz_result'])->value('odds'));
                    } 
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
        // 将当期所有订单的投注金额和中奖金额添加到往期历史里面 并将本期状态改为 结束 kjsjzt = 0
        $order_money_sum = db('order')->where('kjid="'.$kj['kjid'].'"')->sum('order_money'); 
        $win_money_sum = db('order')->where('kjid="'.$kj['kjid'].'"')->sum('win_money'); 

        db('dxh_kj')->where('kjid="'.$kj['kjid'].'"')->update(array('tz_money'=>$order_money_sum,'zjje'=>$win_money_sum,'kjsjzt'=>3,'Kjjg'=>$win_result,'edit_time'=>time())); 

        // 本期结束 清空金额数据
        $datas['tz_money'] = 0;
        $datas['win_money'] = 0;
        db('codex_dxh')->where('1=1')->update($datas);

        echo json_encode(['msg'=>'操作成功','code'=>1,'success'=>true]);
        exit; 
    }

    public function get_qq_people()
    {
        $testurl = 'http://cgi.im.qq.com/cgi-bin/minute_city';  
        $ch = curl_init();    
        curl_setopt($ch, CURLOPT_URL, $testurl);    
        //参数为1表示传输数据，为0表示直接输出显示。  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
        //参数为0表示不带头文件，为1表示带头文件  
        curl_setopt($ch, CURLOPT_HEADER,0);  
        $output = curl_exec($ch);   
        curl_close($ch);  
        $data = json_decode($output,true);

        $num = $data['minute'][0];
        $fp = @fopen("D:/phpstudy/WWW/game/timed_task/qq_people.txt", "a+");
        fwrite($fp, "写入时间:" . date("Y-m-d H:i:s")."--");
        fclose($fp);
        $one = 0;
        $two = 0;
        $three = 0;
        if($num != false){
            for ($i=0; $i < 9; $i++) { 
                $one = $one + substr($num, $i,1);
                if($i>=3 && $i<=5){
                    $two = $two + substr($num, $i,1);
                }
                if($i>=6 && $i<=8){
                    $three = $three + substr($num, $i,1);
                }
            }
            if(strlen($one) > 1){
                $one = substr($one, 1, 1);
            }
            if(strlen($two) > 1){
                $two = substr($two, 1, 1);
            }
            if(strlen($three) > 1){
                $three = substr($three, 1, 1);
            }
            $result = $one.$two.$three;
            $fp = @fopen("D:/phpstudy/WWW/game/timed_task/qq_people.txt", "a+");
            fwrite($fp, "人数:".$num."-----结果:".$result."-----\r\n");
            fclose($fp);
            db('dxh_kj')->where('game_id='.$this->game_id.' and kjsjzt=1')->setField('qq_now',$result);
        }else{
            $fp = @fopen("D:/phpstudy/WWW/game/timed_task/qq_people.txt", "a+");
            fwrite($fp, "重新写入:".date("Y-m-d H:i:s")."-----\r\n");
            fclose($fp);
            $this->get_qq_people();
        }
    }
}
