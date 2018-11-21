<?php
namespace app\nine\controller;
use app\nine\model\Card;
use app\home\controller\Base; 
class Game extends Base
{
    private $kjid = 18000001;
    private $tzid = 1000000001;
    private $game_id = 3;
    /**
     * 疯狂骰子--游戏开始
     */
    public function start(){
        set_time_limit(0);
        // sleep(10);
        db('nine_kj')->where('win_status=3')->setField('win_status',0);
        $game_code = db('game_cate')->where('game_id='.$this->game_id)->value('game_code');

        $nine_kamisho_1 = db('nine_kamisho')->where('kamisho_status=1')->order('add_time asc')->select();
        // 如果$nine_kamisho_2是空的 证明没有续庄的
        $nine_kamisho_2 = db('nine_kamisho')->where('kamisho_status=2')->order('add_time asc')->find();
        // 如果游戏开始没有续庄的 按添加时间顺讯顺位上庄
        
        if(empty($nine_kamisho_2)){
            if(!empty($nine_kamisho_1)){
                $kamisho['money'] = $nine_kamisho_1[0]['money'];
                $kamisho['order_id'] = $nine_kamisho_1[0]['order_id'];
                db('nine_kamisho')->where('id='.$nine_kamisho_1[0]['id'])->setField('kamisho_status',2);
            }else{
                $kamisho['user_id'] = 0;
                $kamisho['add_time'] = time();
                $kamisho['kamisho_num'] = 1;
                $kamisho['user_status'] = 0;
                $kamisho['kamisho_status'] = 1;
                for ($i=0; $i < mt_rand(3,5); $i++) { 
                    $kamisho['money'] = mt_rand(2,5)*1000;
                    db('nine_kamisho')->insert($kamisho);
                }
                $nine_kamisho = db('nine_kamisho')->where('kamisho_status=1')->order('add_time asc')->find();
                db('nine_kamisho')->where('id='.$nine_kamisho['id'])->setField('kamisho_status',2);
            }
        }else{
            if($nine_kamisho_2['is_along'] == 1){
                $kamisho['money'] = $nine_kamisho_2['money'];
                $kamisho['order_id'] = $nine_kamisho_2['order_id'];
            }else{
                db('nine_kamisho')->where('id='.$nine_kamisho_2['id'])->setField('kamisho_status',0);
                if(!empty($nine_kamisho_1)){
                    $kamisho['money'] = $nine_kamisho_1[0]['money'];
                    $kamisho['order_id'] = $nine_kamisho_1[0]['order_id'];
                    db('nine_kamisho')->where('id='.$nine_kamisho_1[0]['id'])->setField('kamisho_status',2);
                }else{
                    $kamisho['user_id'] = 0;
                    $kamisho['add_time'] = time();
                    $kamisho['kamisho_num'] = 1;
                    $kamisho['user_status'] = 0;
                    $kamisho['kamisho_status'] = 1;
                    for ($i=0; $i < mt_rand(3,5); $i++) { 
                        $kamisho['money'] = mt_rand(2,5)*1000;
                        db('nine_kamisho')->insert($kamisho);
                    }
                    $nine_kamisho = db('nine_kamisho')->where('kamisho_status=1')->order('add_time asc')->find();
                    db('nine_kamisho')->where('id='.$nine_kamisho['id'])->setField('kamisho_status',2);
                }   
            }
        }
        
        /*  END  */

        $card = new Card();
        $data = $card->get_card();
        $data['banker_money'] = $kamisho['money'];
        $data['banker_win_money'] = $kamisho['money'];
        $data['add_time'] = time();
        $data['win_time'] = time()+60;
        $data['win_status'] = 1;
        $count = db('nine_kj')->count();
        $num = $this->kjid+$count;
        $kjid = $game_code.$num;
        $data['kjid'] = $kjid;

        $res = db('nine_kj')->insert($data);
        if(isset($kamisho['order_id'])){
            db('order')->where('Tzid="'.$kamisho['order_id'].'"')->setField('kjid',$kjid);
        }
        // sleep(5);
        
        $array = array('visit_money','nostril_money','surname_money');
        for ($i=0; $i < 40; $i++) { 
            $random = $array[array_rand($array)];
            $data = db('nine_kj')->field('banker_money,visit_money,nostril_money,surname_money')->where('kjid="'.$kjid.'"')->find();
            $data['prev_money'] = $data['banker_money']-($data['visit_money']+$data['nostril_money']+$data['surname_money']);
            if($i == 39){
                $data['visit_money'] = $data['nostril_money'] = $data['surname_money'] = (int)$data['prev_money']/3;
            }else{
                // $data['visit_money'] = round($data['banker_money']*int((mt_rand(20,30)/3/1000)));
                // $data['nostril_money'] = round($data['banker_money']*int((mt_rand(20,30)/3/1000)));
                // $data['surname_money'] = round($data['banker_money']*int((mt_rand(20,30)/3/1000)));
                $random_money = round($data['banker_money']*(mt_rand(2,3)/100));    
            }
            
            db('nine_kj')->where('kjid="'.$kjid.'"')->setInc($random,$random_money);

            // db('nine_kj')->where('kjid="'.$kjid.'"')->setInc('visit_money',$data['visit_money']);
            // db('nine_kj')->where('kjid="'.$kjid.'"')->setInc('nostril_money',$data['nostril_money']);
            // db('nine_kj')->where('kjid="'.$kjid.'"')->setInc('surname_money',$data['surname_money']);
            $next_data = db('nine_kj')->field('banker_money,visit_money,nostril_money,surname_money')->where('kjid="'.$kjid.'"')->find();
            $data['money'] = round($next_data['banker_money']-($next_data['visit_money']+$next_data['nostril_money']+$next_data['surname_money']));
            sleep(1);
            // dump($data);
        }
        // if($res>0){
        //     echo json_encode(['msg'=>'游戏开始','code'=>1,'success'=>true]);
        //     exit;
        // }else{
        //     echo json_encode(['msg'=>'操作失败','code'=>0,'success'=>false]);
        //     exit;
        // }
        // 开奖期号生成结束 , 游戏开始

    }

    // 获取可投注金额
    public function get_tz_money($kjid)
    {
        $data = db('nine_kj')->field('banker_money,visit_money,nostril_money,surname_money')->where('kjid="'.$kjid.'"')->find();
        $data['money'] = round($data['banker_money']-($data['visit_money']+$data['nostril_money']+$data['surname_money']));
        echo json_encode(['msg'=>'请求成功','data'=>$data,'code'=>1,'success'=>true]);
        exit;
    }

    // 我要上庄
    public function kamisho()
    {
        $_data = $_REQUEST;
        $yh = db('yh')->where('id='.USER_ID)->find();
        if($_data['money'] < 1000){
            echo json_encode(['msg'=>'上庄金额不能小于1000','code'=>201,'success'=>false]);
            exit;  
        }
        if($_data['money'] > $yh['balance']){
            echo json_encode(['msg'=>'上庄金额超出可用金额','code'=>202,'success'=>false]);
            exit;  
        }
        if(empty($_data['money'])){
            echo json_encode(['msg'=>'请输入金额','code'=>203,'success'=>false]);
            exit;  
        }
        $data['user_id'] = USER_ID;
        $data['add_time'] = time();
        $data['kamisho_num'] = 1;
        $data['user_status'] = 1;
        $data['kamisho_status'] = 1;
        $data['money'] = $_data['money'];
        $nine_kamisho_id = db('nine_kamisho')->insertGetId($data);

        if($nine_kamisho_id > 0){
            $yhid = $yh['yhid'];
            $game_code = db('game_cate')->where('game_id='.$this->game_id)->value('game_code');

            $count = db('order')->where('game_id='.$this->game_id)->count();
            $num = $this->tzid+$count;
            $tzid = $game_code.$num;

            $arr['Tzid'] = $tzid;
            $arr['yhid'] = $yhid;
            // $arr['kjid'] = $data['kjid'];
            $arr['Tzzffs'] = 1;
            $arr['Tzzfsj'] = time();
            $arr['TzScbz'] = 1;
            $arr['win_result'] = 0; // 默认为未中奖
            $arr['order_money'] = $data['money'];
            $arr['game_id'] = $this->game_id;
            $arr['Tzcgbz'] = 1;
            $res = db("order")->insert($arr);

            // 创建订单明细
            $tz_result = 0;
            $arr1['order_id'] = $tzid;
            $arr1['tz_result'] = $tz_result;
            $arr1['tz_money'] = $data['money'];

            $res_info = db("order_info")->insert($arr1,false,true);

            db('nine_kamisho')->where('id='.$nine_kamisho_id)->setField('order_id',$tzid);

            if($res_info>0){
                /**添加账单明细**/
                $detail['yhid'] = $yhid;
                $detail['Jylx'] = 3;
                $detail['jyje'] = $data['money'];
                $detail['new_money'] = db('yh')->where('id='.USER_ID)->value('balance')-$data['money'];
                $detail['Jysj'] = time();
                $detail['Srhzc'] = 1;
                $detail['game_id'] = $this->game_id;
                $detail_res = db('account_details')->insert($detail,false,true);
                /**添加账单明细end**/
                
                /** 下注流水奖励 **/
                reward($yhid);
                /** 下注流水奖励 END **/

                db('yh')->where('id='.USER_ID)->setDec('balance',$data['money']);
                db('yh')->where('id='.USER_ID)->setDec('amount_money',$data['money']);

                echo json_encode(['msg'=>'上庄成功','code'=>1,'success'=>true]);
                exit;
            }    
        }
    }

    // 继续上庄 
    public function keep_kamisho()
    {
        $_data = $_REQUEST;
        $yh = db('yh')->where('id='.USER_ID)->find();
        $nine_kamisho = db('nine_kamisho')->where('kamisho_status=2 and user_id='.USER_ID)->find();
        if(empty($nine_kamisho)){
            echo json_encode(['msg'=>'操作超时','code'=>204,'success'=>false]);
            exit; 
        }
        if($_data['money'] < 1000){
            echo json_encode(['msg'=>'上庄金额不能小于1000','code'=>201,'success'=>false]);
            exit;  
        }
        if($_data['money'] > $yh['balance']){
            echo json_encode(['msg'=>'上庄金额超出可用金额','code'=>202,'success'=>false]);
            exit;  
        }
        if(empty($_data['money'])){
            echo json_encode(['msg'=>'请输入金额','code'=>203,'success'=>false]);
            exit;  
        }
        $data['edit_time'] = time();
        $data['money'] = $_data['money'];
        $data['is_along'] = 1;
        $data['kamisho_num'] = $nine_kamisho['kamisho_num']+1;
        $data['kamisho_status'] = 2;

        db('nine_kamisho')->where('id='.$nine_kamisho['id'])->update($data);

        $game_code = db('game_cate')->where('game_id='.$this->game_id)->value('game_code');
        $count = db('order')->where('game_id='.$this->game_id)->count();
        $num = $this->tzid+$count;
        $tzid = $game_code.$num;
        $yhid = $yh['yhid'];

        $arr['Tzid'] = $tzid;
        $arr['yhid'] = $yhid;
        $arr['Tzzffs'] = 1;
        $arr['Tzzfsj'] = time();
        $arr['TzScbz'] = 1;
        $arr['win_result'] = 0; // 默认为未中奖
        $arr['order_money'] = $data['money'];
        $arr['game_id'] = $this->game_id;
        $arr['Tzcgbz'] = 1;
        $res = db("order")->insert($arr);

        // 创建订单明细
        $tz_result = 0;
        $arr1['order_id'] = $tzid;
        $arr1['tz_result'] = $tz_result;
        $arr1['tz_money'] = $data['money'];

        $res_info = db("order_info")->insert($arr1,false,true);

        db('nine_kamisho')->where('kamisho_status=2 and user_id='.USER_ID)->setField('order_id',$tzid);

        if($res_info>0){
            /**添加账单明细**/
            $detail['yhid'] = $yhid;
            $detail['Jylx'] = 3;
            $detail['jyje'] = $data['money'];
            $detail['new_money'] = db('yh')->where('id='.USER_ID)->value('balance')-$data['money'];
            $detail['Jysj'] = time();
            $detail['Srhzc'] = 1;
            $detail['game_id'] = $this->game_id;
            $detail_res = db('account_details')->insert($detail,false,true);
            /**添加账单明细end**/
            
            /** 下注流水奖励 **/
            reward($yhid);
            /** 下注流水奖励 END **/

            db('yh')->where('id='.USER_ID)->setDec('balance',$data['money']);
            db('yh')->where('id='.USER_ID)->setDec('amount_money',$data['money']);

            echo json_encode(['msg'=>'操作成功','code'=>1,'success'=>true]);
            exit;
        }    
    }

    // 是否是庄家
    public function is_kamisho()
    {
        $data = db('nine_kamisho')->where('kamisho_status=2 and user_id='.USER_ID)->find();
        if(empty($data)){
            echo json_encode(['msg'=>'不是','data'=>0,'code'=>1,'success'=>true]);
            exit;
        }else{
            echo json_encode(['msg'=>'是','data'=>1,'code'=>1,'success'=>true]);
            exit;
        }
    }

    // 获取当前排名
    public function ranking()
    {
        $nine_kamisho = db('nine_kamisho')->where('kamisho_status=1')->order('add_time asc')->column('user_id');
        $ranking = 0;
        foreach ($nine_kamisho as $key => $value) {
            if($value == USER_ID){
                $ranking = $key+1;
            }
        }
        $data['count'] = count($nine_kamisho);
        $data['ranking'] = $ranking;
        echo json_encode(['msg'=>'请求成功','data'=>$data,'code'=>1,'success'=>true]);
        exit;
    }

    //  取消上庄
    public function off_kamisho()
    {
        $nine_kamisho = db('nine_kamisho')->where('kamisho_status=1 and user_id='.USER_ID)->find();
        $res = db('nine_kamisho')->where('id='.$nine_kamisho['id'])->setField('kamisho_status',0);
        if($res > 0){
            db('yh')->where('id='.$nine_kamisho['user_id'])->setInc('balance',$nine_kamisho['money']);
            db('yh')->where('id='.$nine_kamisho['user_id'])->setInc('amount_money',$nine_kamisho['money']);
            echo json_encode(['msg'=>'请求成功','code'=>1,'success'=>true]);
            exit;    
        }else{
            echo json_encode(['msg'=>'请求失败','code'=>0,'success'=>true]);
            exit; 
        }
    }

    //  下庄
    public function down_kamisho()
    {
        $res = db('nine_kamisho')->where('kamisho_status=2 and user_id='.USER_ID)->setField('kamisho_status',0);
        if($res > 0){
            echo json_encode(['msg'=>'请求成功','code'=>1,'success'=>true]);
            exit;    
        }else{
            echo json_encode(['msg'=>'请求失败','code'=>0,'success'=>true]);
            exit; 
        }
    }

    // 换牌
    public function the_card($kjid)
    {
        $card = new Card();
        $data = $card->get_card();

        $res = db('nine_kj')->where('kjid="'.$kjid.'"')->update($data);
        if($res>0){
            $this->success('换牌成功');
        }else{
            $this->error('换牌失败');
        }
    }

    // 发牌顺序
    public function deal_order($kjid)
    {
        $data['deal_order'] = db('nine_kj')->where('kjid="'.$kjid.'"')->value('deal_order');
        echo json_encode(['msg'=>$data,'code'=>1,'success'=>true]);
        exit;
    }

    // 上一期九张牌
    public function old_card()
    {
        $list = db('nine_kj')
        ->field('deal_order,banker_left,banker_right,visit_left,visit_right,nostril_left,nostril_right,surname_left,surname_right')
        ->where('win_status=0')
        ->order('add_time desc')
        ->find();
        $banker_left = db('codex_nine')->where('code_id='.$list['banker_left'])->value('code_info_id');
        $banker_right = db('codex_nine')->where('code_id='.$list['banker_right'])->value('code_info_id');
        $visit_left = db('codex_nine')->where('code_id='.$list['visit_left'])->value('code_info_id');
        $visit_right = db('codex_nine')->where('code_id='.$list['visit_right'])->value('code_info_id');
        $nostril_left = db('codex_nine')->where('code_id='.$list['nostril_left'])->value('code_info_id');
        $nostril_right = db('codex_nine')->where('code_id='.$list['nostril_right'])->value('code_info_id');
        $surname_left = db('codex_nine')->where('code_id='.$list['surname_left'])->value('code_info_id');
        $surname_right = db('codex_nine')->where('code_id='.$list['surname_right'])->value('code_info_id');
        $num['banker_num'] = (($banker_left + $banker_right)%10).'点';
        $num['visit_num'] = (($visit_left + $visit_right)%10).'点';
        $num['nostril_num'] = (($nostril_left + $nostril_right)%10).'点';
        $num['surname_num'] = (($surname_left + $surname_right)%10).'点';
        if($banker_left == $banker_right){
            $num['banker_num'] = '豹子';
        }
        if($visit_left == $visit_right){
            $num['visit_num'] = '豹子';
        }
        if($nostril_left == $nostril_right){
            $num['nostril_num'] = '豹子';
        }
        if($surname_left == $surname_right){
            $num['surname_num'] = '豹子';
        }
        $data['deal_order'] = $list['deal_order'];
        unset($list['deal_order']);
        $data['card'] = $list;
        $data['num'] = $num;

        echo json_encode(['msg'=>$data,'code'=>1,'success'=>true]);
        exit;

    }
    // 八张扑克牌
    public function card($kjid)
    {
        $list = db('nine_kj')->field('banker_money,banker_left,banker_right,visit_left,visit_right,nostril_left,nostril_right,surname_left,surname_right')->where('kjid="'.$kjid.'"')->find();
        $banker_left = db('codex_nine')->where('code_id='.$list['banker_left'])->value('code_info_id');
        $banker_right = db('codex_nine')->where('code_id='.$list['banker_right'])->value('code_info_id');
        $visit_left = db('codex_nine')->where('code_id='.$list['visit_left'])->value('code_info_id');
        $visit_right = db('codex_nine')->where('code_id='.$list['visit_right'])->value('code_info_id');
        $nostril_left = db('codex_nine')->where('code_id='.$list['nostril_left'])->value('code_info_id');
        $nostril_right = db('codex_nine')->where('code_id='.$list['nostril_right'])->value('code_info_id');
        $surname_left = db('codex_nine')->where('code_id='.$list['surname_left'])->value('code_info_id');
        $surname_right = db('codex_nine')->where('code_id='.$list['surname_right'])->value('code_info_id');
        $num['banker_num'] = (($banker_left + $banker_right)%10).'点';
        $num['visit_num'] = (($visit_left + $visit_right)%10).'点';
        $num['nostril_num'] = (($nostril_left + $nostril_right)%10).'点';
        $num['surname_num'] = (($surname_left + $surname_right)%10).'点';
        if($banker_left == $banker_right){
            $num['banker_num'] = '豹子';
        }
        if($visit_left == $visit_right){
            $num['visit_num'] = '豹子';
        }
        if($nostril_left == $nostril_right){
            $num['nostril_num'] = '豹子';
        }
        if($surname_left == $surname_right){
            $num['surname_num'] = '豹子';
        }
        echo json_encode(['msg'=>$list,'num'=>$num,'code'=>1,'success'=>true]);
        exit;
    }

    public function kj()
    {
        $data = array();
        $data = db('nine_kj')->field('kjid,win_time')->where('win_status>0')->order('win_time desc')->find();
        $data['Kjdjs'] = $data['win_time']-time();   
  
        echo json_encode(['msg'=>$data,'code'=>1,'success'=>true]);
        exit;
    }
    /**
     * 请求历史记录
     */
    public function history()
    {
        $list = db('nine_kj')->field('kjid,win_result')->where('win_status=0')->limit(5)->order('add_time desc')->select();
        foreach ($list as $key => $value) {
            $list[$key]['win_res'][0] = substr($list[$key]['win_result'], 0, 1);
            $list[$key]['win_res'][1] = substr($list[$key]['win_result'], 1, 1);
            $list[$key]['win_res'][2] = substr($list[$key]['win_result'], 2, 1);
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
        $data['kjid'] = db('nine_kj')->where('win_status=1')->value('kjid');
        echo json_encode(['msg'=>'请求成功','data'=>$data,'code'=>1]);
        exit; 
    }

    // 生成token
    public function token()
    {
        $token = md5(uniqid());
        // session_start();
        session('nine_token',$token);   
        echo json_encode(['msg'=>'请求成功','code'=>1,'token'=>$token,'success'=>true]);
    }
    // 获取彩果
    public function get_result()
    {
        $data = $_REQUEST;

        // db('kj')->where('kjsjzt>0 and game_id=1')->setField('kjsjzt',0);
        //db('kj')->where('game_id=1 and kjid="'.$data['kjid'].'"')->setField('kjsjzt',0);

        $kj = db('nine_kj')->field('kjid,win_result,win_status')->where('kjid="'.$data['kjid'].'"')->find();
        $kj['win_res'][0] = substr($kj['win_result'], 0, 1);
        $kj['win_res'][1] = substr($kj['win_result'], 1, 1);
        $kj['win_res'][2] = substr($kj['win_result'], 2, 1);
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
     *float $order_money 投注总金额
     *varchar $kjid 开奖期编号
     *[type] [description]
     */
    public function touz()
    {
        // echo json_encode(['msg'=>'维护中','code'=>1001,'success'=>false]);
        // exit; 
        $data = $_REQUEST;

        $token = session('nine_token');
        session('nine_token',null);

        if($data['token'] != $token){
            echo json_encode(['msg'=>'数据错误,请勿重复提交','code'=>201,'success'=>false]);
            exit; 
        }else{
            $yh = db('yh')->where('id='.USER_ID)->find();

            $kj = db('nine_kj')->where('kjid="'.$data['kjid'].'"')->find();

            $id = $yh['id'];

            if($data['order_money'] <= 0){
                echo json_encode(['msg'=>'投注金额不能小于1000','code'=>202,'success'=>false]);
                exit;  
            }
            if($data['order_money'] > $yh['balance']+$yh['no_balance']){
                echo json_encode(['msg'=>'投注金额超出可用金额','code'=>203,'success'=>false]);
                exit;  
            }
            if(empty($data['order_money'])){
                echo json_encode(['msg'=>'请输入金额','code'=>204,'success'=>false]);
                exit;  
            }
            $kamisho_money = $kj['banker_money']-($kj['visit_money']+$kj['nostril_money']+$kj['surname_money']);
            if($kamisho_money < 0){
                echo json_encode(['msg'=>'超出可投注金额','code'=>205,'success'=>false]);
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
            $arr['order_money'] = $data['order_money'];
            $arr['game_id'] = $this->game_id;

            if($kj['win_status'] > 1){
                echo json_encode(['msg'=>'投注失败,本轮已停止投注','code'=>206,'success'=>false]);
                exit;
            }

            $Kjsj = $kj['win_time'];

            if($Kjsj-$arr['Tzzfsj']<15){
                echo json_encode(['msg'=>'投注失败,本轮已停止投注','code'=>207,'success'=>false]);
                exit;             
            }else{
                $arr['Tzcgbz'] = 1;
                $res = db("order")->insert($arr);
                if($res>0){
                    if($order_money >= $yh['no_balance']){
                        db('yh')->where('id='.$yh['id'])->setField('no_balance',0);
                        $residue = $order_money - $yh['no_balance'];
                    }else{
                        db('yh')->where('id='.$yh['id'])->setDec('no_balance',$order_money);
                        $residue = 0;
                    }
                    db('yh')->where('id='.$yh['id'])->setDec('balance',$residue);
                    db('yh')->where('id='.$yh['id'])->setDec('amount_money',$order_money);
                    $balance = db('yh')->where('id='.$yh['id'])->value('balance');

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
                        /**添加账单明细**/
                        $detail['yhid'] = $yhid;
                        $detail['Jylx'] = 3;
                        $detail['jyje'] = $data['order_money'];
                        $detail['new_money'] = $balance;
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

                        $this->jackpot($tzid,$data['kjid']); // 跟新奖池数据
                        
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

    // 必杀模式
    public function kill($kjid)
    {
        $game_cate = db('game_cate')->where('game_id=3')->find();
        $money = $game_cate['money'];
        $kj = db('nine_kj')
                ->field('deal_order,banker_left,banker_right,banker_result, visit_left,visit_right,visit_result, nostril_left,nostril_right,nostril_result, surname_left,surname_right,surname_result')
                ->where('kjid="'.$kjid.'"')
                ->find();
        $data = array();
        $Tzid = db('order')->where('kjid="'.$kjid.'"')->column('Tzid');
        if(!empty($Tzid)){
            if($game_cate['status'] == 2){
                $where = ' and tz_result=0';
            }else if($game_cate['status'] == 3){
                $where = ' and tz_result>0';
            }
            $order_info_id_arr = array();
            foreach ($Tzid as $key => $value) {
                $order_info_id = db('order_info')->where('order_id="'.$Tzid[$key].'"'.$where)->value('order_info_id');
                if(!empty($order_info_id)){
                    $order_info_id_arr[] = $order_info_id;
                }
            }
            if(!empty($order_info_id_arr)){
                if(count($order_info_id_arr) > 1){
                    $order_info_id_arr_id = implode(',', $order_info_id_arr);
                }else{
                    $order_info_id_arr_id = $order_info_id_arr[0];
                }
                $amount_money = db('order_info')->where('order_info_id in ('.$order_info_id_arr_id.')')->sum('tz_money');
                if($amount_money >= $money){
                    $arr = array('banker_result'=>$kj['banker_result'],'visit_result'=>$kj['visit_result'],'nostril_result'=>$kj['nostril_result'],'surname_result'=>$kj['surname_result']);
                    if($game_cate['status'] == 2){//庄家必死模式
                        $nine_kamisho = db('nine_kamisho')->where('kamisho_status=2')->order('add_time asc')->find();
                        if($nine_kamisho['user_id'] != 0){
                            $k = array_search(min($arr), $arr);
                            if($k != 'banker_result'){
                                unset($arr['banker_result']);
                                $key = array_search(min($arr), $arr);
                            }
                        }
                    }else if($game_cate['status'] == 3){//庄家必杀模式
                        $k = array_search(max($arr), $arr);
                        if($k != 'banker_result'){
                            unset($arr['banker_result']);
                            $key = array_search(max($arr), $arr);
                        }
                    }
                    if(isset($k) && $k != 'banker_result'){
                        $result = explode('_', $key)[0];
                        $left = $result.'_left';
                        $right = $result.'_right';
                        $result = $result.'_result';
                        $data['banker_left'] = $kj[$left];
                        $data['banker_right'] = $kj[$right];
                        $data['banker_result'] = $kj[$result];
                        $data[$left] = $kj['banker_left'];
                        $data[$right] = $kj['banker_right'];
                        $data[$result] = $kj['banker_result'];
                        $res = db('nine_kj')->where('kjid="'.$kjid.'"')->update($data);
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
        
        // 将开奖状态改为停止投注
        if(empty($kjid)){
            $kj = db('nine_kj')->where('win_status = 1')->order('add_time desc')->find();
            db('nine_kj')->where('kjid="'.$kj['kjid'].'"')->update(array('win_status'=>2,'start_time'=>time()));       
        }else{
            $kj = db('nine_kj')->where('win_status = 2 and kjid="'.$kjid.'"')->find();
        }

        db('nine_kj')->where('kjid="'.$kj['kjid'].'"')->setField('start_time',time());

        $game_cate = db('game_cate')->where('game_id=3')->find();

        //必杀模式
        if($game_cate['status'] > 1){
            $this->kill($kj['kjid']);
        }
        //必杀模式



        $kj = db('nine_kj')->where('win_status = 2 and kjid="'.$kj['kjid'].'"')->find();
        
        if($kj['banker_result'] > $kj['visit_result']){
            $win_result = 0;
            $win_data[] = 0;
            db('nine_kj')->where('kjid="'.$kj['kjid'].'"')->setInc('banker_win_money',$kj['visit_money']);
        }else if($kj['banker_result'] == $kj['visit_result']){
            $win_result = 2;
            $win_data[] = 2;
        }else{
            $win_result = 1;
            $win_data[] = 1;
            db('nine_kj')->where('kjid="'.$kj['kjid'].'"')->setDec('banker_win_money',$kj['visit_money']);
        }
        if($kj['banker_result'] > $kj['nostril_result']){
            $win_result .= 0;
            $win_data[] = 0;
            db('nine_kj')->where('kjid="'.$kj['kjid'].'"')->setInc('banker_win_money',$kj['nostril_money']);
        }else if($kj['banker_result'] == $kj['nostril_result']){
            $win_result .= 2;
            $win_data[] = 2;
        }else{
            $win_result .= 1;
            $win_data[] = 1;
            db('nine_kj')->where('kjid="'.$kj['kjid'].'"')->setDec('banker_win_money',$kj['nostril_money']);
        }
        if($kj['banker_result'] > $kj['surname_result']){
            $win_result .= 0;
            $win_data[] = 0;
            db('nine_kj')->where('kjid="'.$kj['kjid'].'"')->setInc('banker_win_money',$kj['surname_money']);
        }else if($kj['banker_result'] == $kj['surname_result']){
            $win_result .= 2;
            $win_data[] = 2;
        }else{
            $win_result .= 1;
            $win_data[] = 1;
            db('nine_kj')->where('kjid="'.$kj['kjid'].'"')->setDec('banker_win_money',$kj['surname_money']);
        }

        // 获取当期所有投注订单
        $order = db('order')->where('kjid="'.$kj['kjid'].'"')->select();

        if(!empty($order)){
            foreach ($order as $key => $value) {
                $order[$key]['order_info'] = db('order_info')->where('order_id="'.$order[$key]['Tzid'].'"')->select();
            }
            foreach ($order as $key => $value) {
                // 默认未中奖
                db('order')->where('Tzid="'.$order[$key]['Tzid'].'"')->setField('win_result',1);
                // 获取所有订单明细
                foreach ($order[$key]['order_info'] as $k => $v) {
                    // 将 中奖彩果编码 / 中奖彩果 / 中奖金额 添加到订单明细中
                        //先将 中奖彩果 / 中奖彩果编码 添加到明细中
                    db('order_info')->where('order_id="'.$order[$key]['order_info'][$k]['order_id'].'"')->update(array('win_result'=>$win_result));
                        // 判断该投注是否中奖 如果中奖了 将计算出的中奖金额添加到订单明细中
                        // 0: 闲家输 1: 闲家赢
                    if($order[$key]['order_info'][$k]['tz_result'] == 1){
                        if($win_data[0] == 1){
                            $win_money = $order[$key]['order_info'][$k]['tz_money']*2;
                            db('order_info')->where('order_info_id='.$order[$key]['order_info'][$k]['order_info_id'])->update(array('win_money'=>$win_money));
                            // db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setDec('banker_win_money',$order[$key]['order_info'][$k]['tz_money']);
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setInc('visit_win_money',$order[$key]['order_info'][$k]['tz_money']*2);
                        }else if($win_data[0] == 2){
                            $win_money = $order[$key]['order_info'][$k]['tz_money'];
                            db('order_info')->where('order_info_id='.$order[$key]['order_info'][$k]['order_info_id'])->update(array('win_money'=>$win_money));
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setInc('visit_win_money',$order[$key]['order_info'][$k]['tz_money']);
                        }
                    }
                    if($order[$key]['order_info'][$k]['tz_result'] == 2){
                        if($win_data[1] == 1){
                            $win_money = $order[$key]['order_info'][$k]['tz_money']*2;
                            db('order_info')->where('order_info_id='.$order[$key]['order_info'][$k]['order_info_id'])->update(array('win_money'=>$win_money));
                            // db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setDec('banker_win_money',$order[$key]['order_info'][$k]['tz_money']);
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setInc('nostril_win_money',$order[$key]['order_info'][$k]['tz_money']*2);
                        }else if($win_data[1] == 2){
                            $win_money = $order[$key]['order_info'][$k]['tz_money'];
                            db('order_info')->where('order_info_id='.$order[$key]['order_info'][$k]['order_info_id'])->update(array('win_money'=>$win_money));
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setInc('nostril_win_money',$order[$key]['order_info'][$k]['tz_money']);
                        }
                    }
                    if($order[$key]['order_info'][$k]['tz_result'] == 3){
                        if($win_data[2] == 1){
                            $win_money = $order[$key]['order_info'][$k]['tz_money']*2;
                            db('order_info')->where('order_info_id='.$order[$key]['order_info'][$k]['order_info_id'])->update(array('win_money'=>$win_money));
                            // db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setDec('banker_win_money',$order[$key]['order_info'][$k]['tz_money']);
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setInc('surname_win_money',$order[$key]['order_info'][$k]['tz_money']*2);
                        }else if($win_data[2] == 2){
                            $win_money = $order[$key]['order_info'][$k]['tz_money'];
                            db('order_info')->where('order_info_id='.$order[$key]['order_info'][$k]['order_info_id'])->update(array('win_money'=>$win_money));
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setInc('surname_win_money',$order[$key]['order_info'][$k]['tz_money']);
                        }
                    }
                    //获取当前订单的所有中奖金额的和 将其添加到订单的中奖金额里 并更改中奖状态
                    $tz_sum = db('order_info')->where('order_id="'.$order[$key]['order_info'][$k]['order_id'].'"')->sum('tz_money'); 
                    $win_sum = db('order_info')->where('order_id="'.$order[$key]['order_info'][$k]['order_id'].'"')->sum('win_money'); 
                    if($win_sum>0 && $order[$key]['order_info'][$k]['tz_result'] != 0){
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
                    }else{
                        $money = array('order_money'=>$tz_sum,'win_money'=>$win_sum);
                    }
                    db('order')->where('Tzid="'.$order[$key]['Tzid'].'"')->update($money);
                }
            }

            $banker_win_money = db('nine_kj')->where('kjid="'.$kj['kjid'].'"')->value('banker_win_money');
            foreach ($order as $key => $value) {
                $banker_order = db('order_info')->where('tz_result = 0 and order_id="'.$order[$key]['Tzid'].'"')->find();
                if(!empty($banker_order)){
                    if($banker_win_money > 0){
                        $order_info_arr['win_result'] = $win_result;
                        $order_info_arr['win_money'] = $banker_win_money;
                        db('order_info')->where('order_id="'.$banker_order['order_id'].'"')->update($order_info_arr);

                        $order_arr['win_result'] = 2;
                        $order_arr['win_money'] = $banker_win_money;
                        db('order')->where('Tzid="'.$banker_order['order_id'].'"')->update($order_arr);
                        /**添加明细**/
                        $yhid = db('order')->where('Tzid="'.$banker_order['order_id'].'"')->value('yhid');
                        $detail['yhid'] = $yhid;
                        $detail['Jylx'] = 4;
                        $detail['jyje'] = $banker_win_money;
                        $balance = db('yh')->where('yhid="'.$yhid.'"')->value('balance');
                        $detail['new_money'] = $banker_win_money+$balance;
                        $detail['Jysj'] = time();
                        $detail['Srhzc'] = 1;
                        $detail['game_id'] = $this->game_id;
                        $detail_res = db('account_details')->insert($detail);
                        /**添加明细end**/
                        /**计算用户收益**/
                        db('yh')->where('yhid="'.$yhid.'"')->setInc('balance',$banker_win_money);
                        db('yh')->where('yhid="'.$yhid.'"')->setInc('amount_money',$banker_win_money);
                    }else{
                        $order_info_arr['win_result'] = $win_result;
                        $order_info_arr['win_money'] = 0;
                        db('order_info')->where('order_id="'.$banker_order['order_id'].'"')->update($order_info_arr);
                        $order_arr['win_result'] = 1;
                        $order_arr['win_money'] = 0;
                        db('order')->where('Tzid="'.$banker_order['order_id'].'"')->update($order_arr);
                    }   
                }
            }            
        }
        // 结算开始自动下庄
        db('nine_kamisho')->where('kamisho_status=2')->setField('kamisho_status',0);

        db('nine_kj')->where('kjid="'.$kj['kjid'].'"')->update(array('win_status'=>3,'win_result'=>$win_result,'end_time'=>time())); 
        echo json_encode(['msg'=>'结算成功','data'=>$win_result,'code'=>1]);
        exit;
    }

    // 跟新本期奖池
    public function jackpot($tzid,$kjid)
    {
        if(!empty($tzid)){
            $order_id = db('order')->where('kjid="'.$kjid.'" and game_id='.$this->game_id)->value('Tzid');
            $order_info = db('order_info')->field('tz_result,tz_money')->where('order_id="'.$tzid.'"')->select();
            foreach ($order_info as $key => $value) {
                $order_info[$key]['value'] = db('code_nine')->where('code_info_id = 20 and code='.$order_info[$key]['tz_result'])->value('value');
                db('nine_kj')->where('kjid = "'.$kjid.'"')->setInc($order_info[$key]['value'].'_money',$order_info[$key]['tz_money']);
            }
        }
    }
}
