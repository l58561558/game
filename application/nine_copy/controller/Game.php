<?php
namespace app\nine\controller;

class Game extends Base
{
    private $kjid = 18000001;
    private $tzid = 1000000001;
    private $game_id = 3;
    /**
     * 疯狂骰子--游戏开始
     */
    public function start(){
        db('nine_kj')->where('win_status=3')->setField('win_status',0);
        $game_code = db('game_cate')->where('game_id='.$this->game_id)->value('game_code');

        $data = array();
        // 庄家默认3000元
        // $data['banker_money'] = 3000;
        $data['add_time'] = time();
        $data['win_time'] = time()+60;
        $data['win_status'] = 1;
        $count = db('nine_kj')->count();
        $num = $this->kjid+$count;
        $kjid = $game_code.$num;
        $data['kjid'] = $kjid;
        // 开牌顺序
        // 生成40张扑克牌(分花色)
        $card = db('codex_nine')->select();
        //生出中间的牌 并在牌堆中将其剔除
        $data['deal_order'] = mt_rand(1,40);
        foreach ($card as $key => $value) {
            if($card[$key]['code_id'] == $data['deal_order']){
                unset($card[$key]);
            }
        }
        
        $where = ' and code_id !='.$data['deal_order'];
        // 先生成庄家的牌的点数
        /* 
        * 1.出豹子的概率位15% 非豹子85%
        * 2.出点数为
        *   9 / 88-100%
        *   8 / 75-87%
        *   7 / 62-74%
        *   6 / 52-61%
        *   5 / 42-51%
        *   4 / 32-41%
        *   3 / 22-31%
        *   2 / 15-21%
        *   1 / 8-14%
        *   0 / 1-7%
        */
        $game_status = db('game_cate')->where('game_id='.$this->game_id)->value('status');

        if($game_status == 1 || $game_status == 3){
            // 随机获取庄家左牌数据 并从数组中剔除
            $banker_left_key = array_rand($card);
            $banker_left_val = $card[$banker_left_key];
            unset($card[$banker_left_key]);
            $data['banker_left'] = $banker_left_val['code_id']; 
            // 随机获取庄家右牌数据 并从数组中剔除
            $banker_right_key = array_rand($card);
            $banker_right_val = $card[$banker_right_key];
            unset($card[$banker_right_key]);
            $data['banker_right'] = $banker_right_val['code_id']; 
            if($banker_left_val['code_info_id'] == $banker_right_val['code_info_id']){
                $data['banker_result'] = $banker_left_val['code_info_id'].$banker_right_val['code_info_id'];
            }else{
                $data['banker_result'] = ($banker_left_val['code_info_id']+$banker_right_val['code_info_id'])%10;
            }
        }else if($game_status == 2){
            $rand = mt_rand(1,100);
            // 生成是豹子时候的左右牌
            if($rand >= 1 && $rand <= 12){
                // 随机获取数组的键 值
                $banker_left_key = array_rand($card);
                $banker_left_val = $card[$banker_left_key];
                // 将值域表中的主键添加到左牌右牌中(方便查询花色和点数)
                $data['banker_left'] = $banker_left_val['code_id'];
                $where .= ' and code_id != '.$data['banker_left'];
                // 生成右牌
                // 获取同点数不同花色的右牌
                $banker_right_val = db('codex_nine')->where('code_info_id = '.$banker_left_val['code_info_id'].$where)->order('rand()')->find();
                $data['banker_right'] = $banker_right_val['code_id'];
                // 生成最终点数
                $data['banker_result'] = $banker_left_val['code_info_id'].$banker_right_val['code_info_id'];
                unset($card[$banker_left_key]);
                unset($card[$banker_right_val['code_id']-1]);
            }else{
                // 不是豹子的时候 先随机获取庄家点数
                $banker_rand = mt_rand(1,100);
                if($banker_rand >= 1 && $banker_rand <= 7){
                    $data['banker_result'] = 0;
                }else if($banker_rand >= 8 && $banker_rand <= 15){
                    $data['banker_result'] = 1;
                }else if($banker_rand >= 16 && $banker_rand <= 24){
                    $data['banker_result'] = 2;
                }else if($banker_rand >= 25 && $banker_rand <= 34){
                    $data['banker_result'] = 3;
                }else if($banker_rand >= 35 && $banker_rand <= 44){
                    $data['banker_result'] = 4;
                }else if($banker_rand >= 45 && $banker_rand <= 54){
                    $data['banker_result'] = 5;
                }else if($banker_rand >= 55 && $banker_rand <= 64){
                    $data['banker_result'] = 6;
                }else if($banker_rand >= 65 && $banker_rand <= 75){
                    $data['banker_result'] = 7;
                }else if($banker_rand >= 76 && $banker_rand <= 87){
                    $data['banker_result'] = 8;
                }else if($banker_rand >= 88 && $banker_rand <= 100){
                    $data['banker_result'] = 9;
                }
                
                $banker_rand = array(1,2,3,4,5,6,7,8,9);
                unset($banker_rand[$data['banker_result']-1]);
                if($data['banker_result']%2 == 0){
                    foreach ($banker_rand as $key => $value) {
                        if($value < $data['banker_result']){
                            unset($banker_rand[$key]);
                        }
                    }
                }
                $banker_left = $banker_rand[array_rand($banker_rand)];

                if($banker_left < $data['banker_result']){
                    $banker_right = $data['banker_result']-$banker_left;
                }else{
                    $banker_right = $data['banker_result']+10-$banker_left;
                }
                if($banker_left == $banker_right){
                    $data['banker_result'] = $banker_left.$banker_right;
                }

                $banker_left_val = db('codex_nine')->where('code_info_id = '.$banker_left.$where)->order('rand()')->find();
                $data['banker_left'] = $banker_left_val['code_id'];
                $where .= ' and code_id != '.$data['banker_left'];
                $banker_right_val = db('codex_nine')->where('code_info_id = '.$banker_right.$where)->order('rand()')->find();
                $data['banker_right'] = $banker_right_val['code_id'];
                $where .= ' and code_id != '.$data['banker_right'];

                unset($card[$data['banker_left']-1]);

                unset($card[$data['banker_right']-1]);
            }
        }
        
        // 随机获取闲家的牌
        // 随机获取上门左牌数据 并从数组中剔除
        $visit_left_key = array_rand($card);
        $visit_left_val = $card[$visit_left_key];
        unset($card[$visit_left_key]);
        $data['visit_left'] = $visit_left_val['code_id']; 
        // 随机获取上门右牌数据 并从数组中剔除
        $visit_right_key = array_rand($card);
        $visit_right_val = $card[$visit_right_key];
        unset($card[$visit_right_key]);
        $data['visit_right'] = $visit_right_val['code_id']; 
        if($visit_left_val['code_info_id'] == $visit_right_val['code_info_id']){
            $data['visit_result'] = $visit_left_val['code_info_id'].$visit_right_val['code_info_id'];
        }else{
            $data['visit_result'] = ($visit_left_val['code_info_id']+$visit_right_val['code_info_id'])%10;
        }
        

        // 随机获取上门左牌数据 并从数组中剔除
        $nostril_left_key = array_rand($card);
        $nostril_left_val = $card[$nostril_left_key];
        unset($card[$nostril_left_key]);
        $data['nostril_left'] = $nostril_left_val['code_id']; 
        // 随机获取上门右牌数据 并从数组中剔除
        $nostril_right_key = array_rand($card);
        $nostril_right_val = $card[$nostril_right_key];
        unset($card[$nostril_right_key]);
        $data['nostril_right'] = $nostril_right_val['code_id']; 
        if($nostril_left_val['code_info_id'] == $nostril_right_val['code_info_id']){
            $data['nostril_result'] = $nostril_left_val['code_info_id'].$nostril_right_val['code_info_id'];
        }else{
            $data['nostril_result'] = ($nostril_left_val['code_info_id']+$nostril_right_val['code_info_id'])%10;
        }

        // 随机获取上门左牌数据 并从数组中剔除
        $surname_left_key = array_rand($card);
        $surname_left_val = $card[$surname_left_key];
        unset($card[$surname_left_key]);
        $data['surname_left'] = $surname_left_val['code_id']; 
        // 随机获取上门右牌数据 并从数组中剔除
        $surname_right_key = array_rand($card);
        $surname_right_val = $card[$surname_right_key];
        unset($card[$surname_right_key]);
        $data['surname_right'] = $surname_right_val['code_id']; 
        if($surname_left_val['code_info_id'] == $surname_right_val['code_info_id']){
            $data['surname_result'] = $surname_left_val['code_info_id'].$surname_right_val['code_info_id'];
        }else{
            $data['surname_result'] = ($surname_left_val['code_info_id']+$surname_right_val['code_info_id'])%10;
        }

        // $data['banker_left'] = 1;
        // $data['banker_right'] = 5;
        // $data['banker_result'] = 3;
        // $data['surname_left'] = 4;
        // $data['surname_right'] = 8;
        // $data['surname_result'] = 3;

        $res = db('nine_kj')->insert($data);
        if($res>0){
            echo json_encode(['msg'=>'游戏开始','code'=>1,'success'=>true]);
            exit;
        }else{
            echo json_encode(['msg'=>'操作失败','code'=>0,'success'=>false]);
            exit;
        }
        // 开奖期号生成结束 , 游戏开始

    }

    public function the_card($kjid)
    {
        $card = db('codex_nine')->select();
        //生出中间的牌 并在牌堆中将其剔除
        $data = array();
        $data['deal_order'] = mt_rand(1,40);
        foreach ($card as $key => $value) {
            if($card[$key]['code_id'] == $data['deal_order']){
                unset($card[$key]);
            }
        }

        // 随机获取庄家左牌数据 并从数组中剔除
        $banker_left_key = array_rand($card);
        $banker_left_val = $card[$banker_left_key];
        unset($card[$banker_left_key]);
        $data['banker_left'] = $banker_left_val['code_id']; 
        // 随机获取上门右牌数据 并从数组中剔除
        $banker_right_key = array_rand($card);
        $banker_right_val = $card[$banker_right_key];
        unset($card[$banker_right_key]);
        $data['banker_right'] = $banker_right_val['code_id']; 
        if($banker_left_val['code_info_id'] == $banker_right_val['code_info_id']){
            $data['banker_result'] = $banker_left_val['code_info_id'].$banker_right_val['code_info_id'];
        }else{
            $data['banker_result'] = ($banker_left_val['code_info_id']+$banker_right_val['code_info_id'])%10;
        }

        // 随机获取闲家的牌
        // 随机获取上门左牌数据 并从数组中剔除
        $visit_left_key = array_rand($card);
        $visit_left_val = $card[$visit_left_key];
        unset($card[$visit_left_key]);
        $data['visit_left'] = $visit_left_val['code_id']; 
        // 随机获取上门右牌数据 并从数组中剔除
        $visit_right_key = array_rand($card);
        $visit_right_val = $card[$visit_right_key];
        unset($card[$visit_right_key]);
        $data['visit_right'] = $visit_right_val['code_id']; 
        if($visit_left_val['code_info_id'] == $visit_right_val['code_info_id']){
            $data['visit_result'] = $visit_left_val['code_info_id'].$visit_right_val['code_info_id'];
        }else{
            $data['visit_result'] = ($visit_left_val['code_info_id']+$visit_right_val['code_info_id'])%10;
        }
        

        // 随机获取上门左牌数据 并从数组中剔除
        $nostril_left_key = array_rand($card);
        $nostril_left_val = $card[$nostril_left_key];
        unset($card[$nostril_left_key]);
        $data['nostril_left'] = $nostril_left_val['code_id']; 
        // 随机获取上门右牌数据 并从数组中剔除
        $nostril_right_key = array_rand($card);
        $nostril_right_val = $card[$nostril_right_key];
        unset($card[$nostril_right_key]);
        $data['nostril_right'] = $nostril_right_val['code_id']; 
        if($nostril_left_val['code_info_id'] == $nostril_right_val['code_info_id']){
            $data['nostril_result'] = $nostril_left_val['code_info_id'].$nostril_right_val['code_info_id'];
        }else{
            $data['nostril_result'] = ($nostril_left_val['code_info_id']+$nostril_right_val['code_info_id'])%10;
        }

        // 随机获取上门左牌数据 并从数组中剔除
        $surname_left_key = array_rand($card);
        $surname_left_val = $card[$surname_left_key];
        unset($card[$surname_left_key]);
        $data['surname_left'] = $surname_left_val['code_id']; 
        // 随机获取上门右牌数据 并从数组中剔除
        $surname_right_key = array_rand($card);
        $surname_right_val = $card[$surname_right_key];
        unset($card[$surname_right_key]);
        $data['surname_right'] = $surname_right_val['code_id']; 
        if($surname_left_val['code_info_id'] == $surname_right_val['code_info_id']){
            $data['surname_result'] = $surname_left_val['code_info_id'].$surname_right_val['code_info_id'];
        }else{
            $data['surname_result'] = ($surname_left_val['code_info_id']+$surname_right_val['code_info_id'])%10;
        }

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
        $list = db('nine_kj')->field('banker_left,banker_right,visit_left,visit_right,nostril_left,nostril_right,surname_left,surname_right')->where('kjid="'.$kjid.'"')->find();
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
        $data['ye'] = db('yh')->where('id='.USER_ID)->value('balance');
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
            echo json_encode(['msg'=>'数据错误,请勿重复提交','code'=>5,'success'=>false]);
            exit; 
        }else{
            $yh = db('yh')->where('id='.USER_ID)->find();

            $kj = db('nine_kj')->where('kjid="'.$data['kjid'].'"')->find();

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
                echo json_encode(['msg'=>'投注失败,本轮已停止投注','code'=>0,'success'=>false]);
                exit;
            }

            $Kjsj = $kj['win_time'];

            if($Kjsj-$arr['Tzzfsj']<15){
                echo json_encode(['msg'=>'投注失败,本轮已停止投注','code'=>101,'success'=>false]);
                exit;             
            }else{
                $arr['Tzcgbz'] = 1;
                $res = db("order")->insert($arr);
                if($res>0){
                    db('yh')->where('id='.USER_ID)->setDec('balance',$data['order_money']);
                    db('yh')->where('id='.USER_ID)->setDec('amount_money',$data['order_money']);

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

                        $this->jackpot($tzid,$data['kjid']); // 跟新奖池数据
                        
                        /** 下注流水奖励 **/
                        reward($yhid);
                        /** 下注流水奖励 END **/

                        // 必杀模式***************
                        if(db('game_cate')->where('game_id=3')->value('status') == 3){
                            foreach ($tz_result as $key => $value) {
                                $this->kill($data['kjid'],$tz_result[$key]);
                            }
                        }
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
            $kj = db('nine_kj')
                ->field('deal_order,banker_left,banker_right,banker_result, visit_left,visit_right,visit_result, nostril_left,nostril_right,nostril_result, surname_left,surname_right,surname_result')
                ->where('kjid="'.$kjid.'"')
                ->find();
            $data = array();
            if($tz_result == 1){
                if($kj['visit_result'] > $kj['banker_result']){
                    $data['banker_left'] = $kj['visit_left'];
                    $data['banker_right'] = $kj['visit_right'];
                    $data['banker_result'] = $kj['visit_result'];
                    $data['visit_left'] = $kj['banker_left'];
                    $data['visit_right'] = $kj['banker_right'];
                    $data['visit_result'] = $kj['banker_result'];
                }
            }
            if($tz_result == 2){
                if($kj['nostril_result'] > $kj['banker_result']){
                    $data['banker_left'] = $kj['nostril_left'];
                    $data['banker_right'] = $kj['nostril_right'];
                    $data['banker_result'] = $kj['nostril_result'];
                    $data['nostril_left'] = $kj['banker_left'];
                    $data['nostril_right'] = $kj['banker_right'];
                    $data['nostril_result'] = $kj['banker_result'];
                }
            }
            if($tz_result == 3){
                if($kj['surname_result'] > $kj['banker_result']){
                    $data['banker_left'] = $kj['surname_left'];
                    $data['banker_right'] = $kj['surname_right'];
                    $data['banker_result'] = $kj['surname_result'];
                    $data['surname_left'] = $kj['banker_left'];
                    $data['surname_right'] = $kj['banker_right'];
                    $data['surname_result'] = $kj['banker_result'];
                }
            }
            $res = db('nine_kj')->where('kjid="'.$kjid.'"')->update($data);
            // $card = db('codex_nine')->select();
            // unset($card[$kj['deal_order']]);
            // if($tz_result == 1){
            //     $left = $kj['visit_left'];
            //     $right = $kj['visit_right'];
            //     $card_num = $kj['visit_result'];
            //     unset($card[$kj['nostril_left']]);
            //     unset($card[$kj['nostril_right']]);
            //     unset($card[$kj['surname_left']]);
            //     unset($card[$kj['surname_right']]);
            // }else if($tz_money == 2){
            //     $left = $kj['nostril_left'];
            //     $right = $kj['nostril_right'];
            //     $card_num = $kj['nostril_result'];
            //     unset($card[$kj['visit_left']]);
            //     unset($card[$kj['visit_right']]);
            //     unset($card[$kj['surname_left']]);
            //     unset($card[$kj['surname_right']]);
            // }else if($tz_money == 3){
            //     $left = $kj['surname_left'];
            //     $right = $kj['surname_right'];
            //     $card_num = $kj['surname_result'];
            //     unset($card[$kj['visit_left']]);
            //     unset($card[$kj['visit_right']]);
            //     unset($card[$kj['nostril_left']]);
            //     unset($card[$kj['nostril_right']]);
            // }
            // $data = array();
            // if($card_num >= $kj['banker_result']){
            //     $banker_left = 
            //     if($card_num == 1010){

            //     }else{
            //         if($card_num < 9){
            //             $banker_result = $card_num+1;
            //             $banker_left = $card[$kj['banker_left']['code_info_id']];
            //             if($banker_left < $banker_result){
            //                 $banker_right = $banker_result-$banker_left;
            //             }else{
            //                 $banker_right = $banker_result+10-$banker_left;
            //             }
            //             foreach ($card as $key => $value) {
            //                 if($card[$key]['code_info_id'] == $banker_right){
            //                     $data['banker_right'] = $card[$key]['code_id'];
            //                 }
            //             }
            //             $data['banker_result'] = $banker_result;
            //         }
            //         if($card_num > 10 && $card_num < 100){
            //             $banker_result = $card_num+11;
            //             if($banker_result == 110){
            //                 $banker_left = $banker_right = 10;
            //             }else{
            //                 $banker_left = $banker_right = substr($banker_result, 0 ,1);   
            //             }
            //             foreach ($card as $key => $value) {
            //                 if($card[$key]['code_info_id'] == $banker_left){
            //                     $data['banker_left'] = $card[$key]['code_id'];
            //                 }
            //             }
            //             unset($card[$data['banker_left']]);
            //             foreach ($card as $key => $value) {
            //                 if($card[$key]['code_info_id'] == $banker_right){
            //                     $data['banker_right'] = $card[$key]['code_id'];
            //                 }
            //             }                       
            //         }
            //     }
            // }
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
        // // 分红比例--基值
        // $ratio = db('conf_dxh')->where('key','ratio')->value('value')/2;
        // // 分红比例--幂函数
        // $power = db('conf_dxh')->where('key','power')->value('value');

        if($kj['banker_result'] > $kj['visit_result']){
            $win_result = 0;
            $win_data[] = 0;
        }else if($kj['banker_result'] == $kj['visit_result']){
            $win_result = 2;
            $win_data[] = 2;
        }else{
            $win_result = 1;
            $win_data[] = 1;
        }
        if($kj['banker_result'] > $kj['nostril_result']){
            $win_result .= 0;
            $win_data[] = 0;
        }else if($kj['banker_result'] == $kj['nostril_result']){
            $win_result .= 2;
            $win_data[] = 2;
        }else{
            $win_result .= 1;
            $win_data[] = 1;
        }
        if($kj['banker_result'] > $kj['surname_result']){
            $win_result .= 0;
            $win_data[] = 0;
        }else if($kj['banker_result'] == $kj['surname_result']){
            $win_result .= 2;
            $win_data[] = 2;
        }else{
            $win_result .= 1;
            $win_data[] = 1;
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
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setDec('banker_win_money',$order[$key]['order_info'][$k]['tz_money']);
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setInc('visit_win_money',$order[$key]['order_info'][$k]['tz_money']*2);
                        }else if($win_data[0] == 2){
                            $win_money = $order[$key]['order_info'][$k]['tz_money'];
                            db('order_info')->where('order_info_id='.$order[$key]['order_info'][$k]['order_info_id'])->update(array('win_money'=>$win_money));
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setInc('visit_win_money',$order[$key]['order_info'][$k]['tz_money']);
                        }else{
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setInc('banker_win_money',$order[$key]['order_info'][$k]['tz_money']);
                        }
                    }
                    if($order[$key]['order_info'][$k]['tz_result'] == 2){
                        if($win_data[1] == 1){
                            $win_money = $order[$key]['order_info'][$k]['tz_money']*2;
                            db('order_info')->where('order_info_id='.$order[$key]['order_info'][$k]['order_info_id'])->update(array('win_money'=>$win_money));
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setDec('banker_win_money',$order[$key]['order_info'][$k]['tz_money']);
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setInc('nostril_win_money',$order[$key]['order_info'][$k]['tz_money']*2);
                        }else if($win_data[1] == 2){
                            $win_money = $order[$key]['order_info'][$k]['tz_money'];
                            db('order_info')->where('order_info_id='.$order[$key]['order_info'][$k]['order_info_id'])->update(array('win_money'=>$win_money));
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setInc('nostril_win_money',$order[$key]['order_info'][$k]['tz_money']);
                        }else{
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setInc('banker_win_money',$order[$key]['order_info'][$k]['tz_money']);
                        }
                    }
                    if($order[$key]['order_info'][$k]['tz_result'] == 3){
                        if($win_data[2] == 1){
                            $win_money = $order[$key]['order_info'][$k]['tz_money']*2;
                            db('order_info')->where('order_info_id='.$order[$key]['order_info'][$k]['order_info_id'])->update(array('win_money'=>$win_money));
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setDec('banker_win_money',$order[$key]['order_info'][$k]['tz_money']);
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setInc('surname_win_money',$order[$key]['order_info'][$k]['tz_money']*2);
                        }else if($win_data[2] == 2){
                            $win_money = $order[$key]['order_info'][$k]['tz_money'];
                            db('order_info')->where('order_info_id='.$order[$key]['order_info'][$k]['order_info_id'])->update(array('win_money'=>$win_money));
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setInc('surname_win_money',$order[$key]['order_info'][$k]['tz_money']);
                        }else{
                            db('nine_kj')->where('kjid="'.$order[$key]['kjid'].'"')->setInc('banker_win_money',$order[$key]['order_info'][$k]['tz_money']);
                        }
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
                        /** 中奖金额分销分红 **/
                        // $id = db('yh')->where('yhid="'.$order[$key]['yhid'].'"')->value('id');
                        // fx($id,$ratio,$power,$detail[$key]['jyje']);
                        /** 中奖金额分销分红end **/
                    }else{
                        $money = array('order_money'=>$tz_sum,'win_money'=>$win_sum);
                    }
                    db('order')->where('Tzid="'.$order[$key]['Tzid'].'"')->update($money);
                }
            }            
        }
        db('nine_kj')->where('kjid="'.$kj['kjid'].'"')->update(array('win_status'=>3,'win_result'=>$win_result,'end_time'=>time())); 
        echo json_encode(['msg'=>'结算成功','data'=>$win_result,'code'=>1]);
        exit;
    }

    // 跟新本期奖池
    public function jackpot($tzid,$kjid)
    {
        // $list = db('kj')->where('kjsjzt>0 and game_id='.$this->game_id)->find();
        if(!empty($tzid)){
            $order_id = db('order')->where('kjid="'.$kjid.'" and game_id='.$this->game_id)->value('Tzid');
            $order_info = db('order_info')->field('tz_result,tz_money')->where('order_id="'.$tzid.'"')->select();

            foreach ($order_info as $key => $value) {
                $order_info[$key]['value'] = db('code_nine')->where('code_info_id = 20 and code='.$order_info[$key]['tz_result'])->value('value');
                // dump($order_info);
                // $order_info[$key]['value'] = db('code_nine')
                // ->where('code_id='.$code_dice[$key])
                // ->value('value');
                db('nine_kj')->where('kjid = "'.$kjid.'"')->setInc($order_info[$key]['value'].'_money',$order_info[$key]['tz_money']);
            }
        }
    }
}
