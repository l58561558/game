<?php
namespace app\home\controller;

//个人中心控制器
class User extends Base
{
    // 实名认证
    /*参数
    * username 用户名
    * Sfzh 身份证号
    * Yhkh 银行卡号
    */

    // public function __construct()
    // {
    //     // 设置不用登录的控制器
    //     $arr = array();

    //     $controller = strtolower(request()->controller());

    //     $user_id = cookie('user_id');
        
    //     if(!in_array($controller, $arr) && $user_id==null){
    //         echo json_encode(['msg'=>'请登录','code'=>0,'success'=>false]);
    //         exit;
    //     }
    // }


    public $Txqqid = 100000000;

    // 填写认证信息
    public function set_renzheng(){
        $data = $_REQUEST;

        $yhid = db('yh')->where('id='.USER_ID)->value('yhid');

        $yhk = db('yhk')->where('yhid="'.$yhid.'"')->find();

        // if(!empty($yhk)){
        //     $arr['username'] = name_substr_cut($data['username']);
        //     $arr['bank_no'] = bank_no_substr_cut($data['bank_no']);
        //     $arr['id_card'] = id_card_substr_cut($data['id_card']);

        //     echo json_encode(['msg'=>$arr,'code'=>1,'success'=>true]);
        //     exit;
        // }

        if(empty($data)){
            echo json_encode(['msg'=>'请输入信息','code'=>0,'success'=>false]);
            exit;
        }

        // $len = strlen($data['id_card']);

        // if($len != 18){
        //     echo json_encode(['msg'=>'身份证位数错误','code'=>0,'success'=>false]);
        //     exit;
        // }
        $bank_len = strlen($data['bank_no']);

        if($bank_len < 16 || $bank_len >19){
            echo json_encode(['msg'=>'银行卡位数错误','code'=>0,'success'=>false]);
            exit;
        }
        $yhid = db('yh')->where('id='.USER_ID)->value('yhid');
        $arr['yhid'] = $yhid;
        $arr['Yhkid'] = $yhid.'01';
        $arr['username'] = $data['username'];
        $arr['Yhkh'] = $data['bank_no'];
        // $arr['Sfzh'] = $data['id_card'];
        $arr['zcsj'] = time();

        if(empty($yhk)){
            $res = db('yhk')->insert($arr,false,true);
        }else{
            $res = db('yhk')->where('yhid="'.$yhid.'"')->update($arr);
        }

        if($res>0){
            echo json_encode(['msg'=>'认证成功','code'=>1,'success'=>true]);
            exit;
        }else{
            echo json_encode(['msg'=>'认证失败','code'=>0,'success'=>false]);
            exit;
        }
    }

    // 获取当前用户认证信息
    public function get_renzheng()
    {
        $yhid = db('yh')->where('id='.USER_ID)->value('yhid');

        $list = db('yhk')->where('yhid="'.$yhid.'"')->find();

        $arr = array();

        if(empty($list['username']) || empty($list['Yhkh'])){
            echo json_encode(['msg'=>'请填写信息','code'=>0,'success'=>false]);
            exit;           
        }else{
            // $arr['username'] = name_substr_cut($list['username']);
            // $arr['bank_no'] = bank_no_substr_cut($list['Yhkh']);
            // $arr['id_card'] = id_card_substr_cut($list['Sfzh']);  
            $arr['username'] = $list['username'];
            $arr['bank_no'] = $list['Yhkh'];
            $arr['id_card'] = $list['Sfzh'];           
        }

        echo json_encode(['msg'=>$arr,'code'=>1,'success'=>true]);
        exit;
    }

    // 修改银行卡认证信息
    public function save_renzheng(){
        $data = $_REQUEST;
		
        // $len = strlen($data['id_card']);

        // if($len != 18){
        //     echo json_encode(['msg'=>'身份证位数错误','code'=>0,'success'=>false]);
        //     exit;
        // }
        $bank_len = strlen($data['bank_no']);

        if($bank_len < 16 || $bank_len >19){
            echo json_encode(['msg'=>'银行卡位数错误','code'=>0,'success'=>false]);
            exit;
        }		
        $arr['username'] = $data['username'];
        $arr['Yhkh'] = $data['bank_no'];
        // $arr['Sfzh'] = $data['id_card'];
        $arr['Xgsj'] = time();

        $yhid = db('yh')->where('id='.USER_ID)->value('yhid');

        $res = db('yhk')->where('yhid="'.$yhid.'"')->update($arr);

        if($res>0){
            echo json_encode(['msg'=>'修改成功','code'=>1,'success'=>true]);
            exit;
        }else{
            echo json_encode(['msg'=>'修改失败','code'=>0,'success'=>false]);
            exit;
        }
    }

    // 个人中心--钱包
    public function purse(){
        $yh = db('yh')
                ->field('yhid,no_balance,balance,freezing_amount,amount_money')
                ->where('id='.USER_ID)
                ->find();
        // if(empty($yh['mbquestion1']) || empty($yh['mbquestion2']) || empty($yh['mbanswer1']) || empty($yh['mbanswer2'])){
        //     echo json_encode(['msg'=>'请填写密保问题','code'=>0,'success'=>false]);
        //     exit;
        // }else{
            $list['yh']['yhid'] = $yh['yhid'];
            $list['yh']['no_balance'] = $yh['no_balance'];
            $list['yh']['balance'] = $yh['balance']+$yh['no_balance'];
            $list['yh']['usable'] = $yh['balance']; // 可用金额
            $list['yh']['freezing_amount'] = $yh['freezing_amount'];
            $list['yh']['amount_money'] = $yh['amount_money'];
        // }

        $list['yhk']['username'] = db('yhk')->where('yhid="'.$list['yh']['yhid'].'"')->value('username');
        $list['yhk']['Yhkh'] = db('yhk')->where('yhid="'.$list['yh']['yhid'].'"')->value('Yhkh');
        echo json_encode(['msg'=>$list,'code'=>1,'success'=>true]);
        exit;

    }

	public function is_tixian()
    {
        $yh = db('yh')->where('id',USER_ID)->find();

        $yhid = $yh['yhid'];

        $yhk = db('yhk')->where('yhid="'.$yhid.'"')->find();

        if(empty($yhk)){
            echo json_encode(['msg'=>'该用户未实名认证','code'=>0,'success'=>false]);
            exit;
        }else{
            echo json_encode(['msg'=>'可以提现','code'=>1,'success'=>true]);
            exit;
        }
    } 
	
    // 个人中心--提现
    public function tixian(){
        // echo json_encode(['msg'=>'提现系统维护中','code'=>0,'success'=>true]);
        // exit;
        $data = $_REQUEST;

        $yh = db('yh')->where('id',USER_ID)->find();

        $yhid = $yh['yhid'];

        $yhk = db('yhk')->where('yhid="'.$yhid.'"')->find();

        if($yh['balance'] - $data['money'] < 0){
            echo json_encode(['msg'=>'申请失败,余额不足','code'=>4,'success'=>false]);
            exit;
        }
        if($data['money'] < 0){
            echo json_encode(['msg'=>'申请失败,投注金额必须大于0','code'=>2,'success'=>false]);
            exit;
        }
        if(empty($yhk)){
            echo json_encode(['msg'=>'该用户未实名认证','code'=>0,'success'=>false]);
            exit;
        }

        // 如果充值之后投注金额不超过充值金额的百分之40 不允许提现
        // $tz_money = 0;
        // $last_pay_money = $yh['last_pay_money'];
        // $order_money = db('order')->where('yhid="'.$yhid.'" and Tzzfsj>'.$yh['last_pay_time'])->sum('order_money');
        // $fb_order_money = db('fb_order')->where('user_id='.$yh['id'].' and add_time > '.$yh['last_pay_time'])->sum('order_money');
        // $lol_order_money = db('lol_order')->where('user_id='.$yh['id'].' and add_time > '.$yh['last_pay_time'])->sum('tz_money');
        // $nba_order_money = db('nba_order')->where('user_id='.$yh['id'].' and add_time > '.$yh['last_pay_time'])->sum('order_money');
        // $tz_momey = $order_money+$fb_order_money+$lol_order_money+$nba_order_money;
        // if($last_pay_money*0.2 > $tz_momey){
        //     echo json_encode(['msg'=>'为防止恶意套现洗钱行为,提现需投注20%钻石','code'=>0,'success'=>false]);
        //     exit;
        // }

        db('yh')->where('yhid="'.$yhid.'"')->setInc('freezing_amount',$data['money']);
        db('yh')->where('yhid="'.$yhid.'"')->setDec('balance',$data['money']);
        // db('yh')->where('yhid="'.$yhid.'"')->setDec('amount_money',$data['money']);

        $count = db('tix')->count();
        // $Txqqid = $this->Txqqid+$count;

        $arr['yhid'] = $yhid;
        $arr['Yhkid'] = $yhk['Yhkid'];
        // $arr['Txqqid'] = $Txqqid;
        $arr['Txtxje'] = $data['money'];
        $arr['Txsqsj'] = time();
        $arr['Txzt'] = 1;
        $arr['Txdjje'] = $data['money'];

        $res = db('tix')->insert($arr,false,true);

        if($res>0){
            $Txqqid = $this->Txqqid+$res;
            db('tix')->where('id='.$res)->setField('Txqqid',$Txqqid);
            // 添加明细列表
            $detail['yhid'] = $yhid;
            $detail['Jylx'] = 2;
            $detail['Srhzc'] = 2;
            $detail['jyje'] = $data['money'];
            $detail['new_money'] = $yh['balance']-$data['money'];
            $detail['Txqqid'] = $Txqqid;
            $detail['Jysj'] = time();
            $detail['present_status'] = 1;
            $detail_res = db('account_details')->insert($detail,false,true);

            echo json_encode(['msg'=>'申请成功,请等待管理员审核','code'=>1,'success'=>true]);
            exit;            
        }else{
            echo json_encode(['msg'=>'申请失败','code'=>0,'success'=>false]);
            exit;
        }

    }


    // 取消提现
    public function del_tixian()
    {
        // echo json_encode(['msg'=>'提现系统维护中','code'=>0,'success'=>true]);
        // exit;
        $data = $_REQUEST;

        $tx = db('tix')->where('Txqqid="'.$data['Txqqid'].'"')->find();

        if($tx['Txzt'] > 1){
            echo json_encode(['msg'=>'取消失败，管理员已操作','code'=>0,'success'=>true]);
            exit; 
        }
        $tix = db('tix')->where('Txqqid="'.$data['Txqqid'].'"')->setField('Txzt',2);
        $account_details = db('account_details')->where('Txqqid="'.$data['Txqqid'].'"')->setField('present_status',2);

        if($tix > 0 && $account_details > 0){
            db('yh')->where('id='.USER_ID)->setDec('freezing_amount',$tx['Txdjje']);
            db('yh')->where('id='.USER_ID)->setInc('balance',$tx['Txdjje']);
            // db('yh')->where('id='.USER_ID)->setInc('amount_money',$money);
            echo json_encode(['msg'=>'取消成功','code'=>1,'success'=>true]);
            exit; 
        }else{
            echo json_encode(['msg'=>'操作失败','code'=>0,'success'=>false]);
            exit; 
        }
    }


    // 个人中心--分享收益
    /*
    *   交易类型，1.充值|2.提现|3.投注|4.中奖金额|5.分销收益
    */
    public function share(){
        $arr = array();

        $yhid = db('yh')->where('id='.USER_ID)->value('yhid');

        $list = db('account_details')
                ->alias('a')
                ->join('yh b','a.fx_yhid=b.id','LEFT')
                ->field('a.jyje,a.Jysj,b.Sjhm')
                ->where('a.yhid="'.$yhid.'" and a.Jylx=5')
                ->order('Jysj desc')
                ->select();

        foreach ($list as $key => $value) {
            $list[$key]['Jysj'] = date('Y-m-d H:i',$value['Jysj']);
            $list[$key]['Sjhm'] = substr(phone_decode($list[$key]['Sjhm']), -4, 4);
        }

        $arr['data'] = $list;

        $amount_money = db('account_details')->where('yhid="'.$yhid.'" and Jylx=5')->sum('jyje');

        $arr['amount_money'] = $amount_money;

        echo json_encode(['msg'=>$arr,'code'=>1,'success'=>true]);
        exit;
    }

    // 个人中心--分享收益--下级用户
    public function child_yh()
    {
        $child_yh = db('yh')->field('id,Sjhm,zcsj')->where('pid='.USER_ID)->select();

        foreach ($child_yh as $key => $value) {
            $child_yh[$key]['money'] = db('account_details')->where('fx_yhid='.$child_yh[$key]['id'])->sum('jyje');
            $child_yh[$key]['zcsj'] = date('Y-m-d',$value['zcsj']);
            $child_yh[$key]['Sjhm'] = phone_decode($child_yh[$key]['Sjhm']);
        }

        echo json_encode(['msg'=>$child_yh,'code'=>1,'success'=>true]);
        exit;
    }


    // 个人中心--账号明细
    // 交易类型，1.充值|2.提现|3.投注|4.中奖金额|5.分销收益

    public function account_details(){
        $yhid = db('yh')->where('id='.USER_ID)->value('yhid');
        // $yhid = 'YH00123456';

        $list = db('account_details')
                ->field('id,Txqqid,Jylx,jyje,Jysj,Srhzc,present_status')
                ->where('yhid="'.$yhid.'"')
                ->order('Jysj desc')
                ->select();
		
        $arr = array();
		
        foreach ($list as $key => $value) {
            $arr[$key]['trade_type'] = db('code_info')->where('code_info_id=17 and dxhzy='.$list[$key]['Jylx'])->value('code_name');
			if($list[$key]['Srhzc'] == 1){
				$arr[$key]['trade_money'] = '+'.$list[$key]['jyje'];
			}else if($list[$key]['Srhzc'] == 2){
				$arr[$key]['trade_money'] = '-'.$list[$key]['jyje'];
			}
            $arr[$key]['trade_time'] = date('Y-m-d H:i',$list[$key]['Jysj']);
            $arr[$key]['sign'] = $list[$key]['Srhzc'];
            $arr[$key]['present_status'] = $list[$key]['present_status'];
            $arr[$key]['Txqqid'] = $list[$key]['Txqqid'];
        }

        echo json_encode(['msg'=>$arr,'code'=>1,'success'=>true]);
        exit;

    }

    // 个人中心--中奖纪录

    public function win_record(){
        $yhid = db('yh')->where('id='.USER_ID)->value('yhid');

        $list = db('order')
                ->field('Tzid,kjid,order_money,win_money,win_result,Tzzfsj,game_id')
                ->where('yhid="'.$yhid.'" and win_result=2 and Tzcgbz=1 and TzScbz=1')
                ->order('Tzzfsj desc')
                ->select();

        foreach ($list as $key => $value) {
            $list[$key]['data'] = db('order_info')->where('order_id="'.$list[$key]['Tzid'].'"')->select();
        }

        // dump($list);die;
        $arr = array();

        foreach ($list as $key => $value) {
            $arr[$key]['order_id'] = $list[$key]['Tzid']; // 订单号
            $arr[$key]['issue'] = $list[$key]['kjid']; // 期号
            $arr[$key]['order_money'] = $list[$key]['order_money']; // 投注金额
            $arr[$key]['order_time'] = date('Y-m-d H:i',$list[$key]['Tzzfsj']); // 投注支付时间(下单时间)
            $arr[$key]['win_money'] = $list[$key]['win_money']; // 中奖金额
            $arr[$key]['win_result'] = db('code_info')->where('code_info_id=23 and dxhzy='.$list[$key]['win_result'])->value('code_name');; // 中奖结果:0.未开奖|1.没中奖|2.中奖
            $arr[$key]['game_name'] = db('game_cate')->where('game_id='.$list[$key]['game_id'])->value('game_name'); // 游戏名称
            $arr[$key]['data'] = $list[$key]['data'];
            if(!empty($list[$key]['data'])){
                for ($i=0; $i < count($list[$key]['data']); $i++) {
                    if($list[$key]['game_id']==1){
                        $arr[$key]['data'][$i]['win_code'] = explode(',', $arr[$key]['data'][$i]['win_code']);
                        foreach ($arr[$key]['data'][$i]['win_code'] as $k => $v) {
                            $arr[$key]['data'][$i]['win_code'][$k] = db('codex_dxh')->where('id='.$arr[$key]['data'][$i]['win_code'][$k])->value('desc');
                        }
                        $arr[$key]['data'][$i]['win_code'] = implode(',', $arr[$key]['data'][$i]['win_code']);
                        // $arr[$key]['data'][$i]['win_result'] = db('codex_dxh')->where('id='.substr($list[$key]['data'][$i]['win_result'], 0, 1))->value('desc');
                    }
                    if($arr[$key]['data'][$i]['win_money'] > 0){
                        $arr[$key]['data'][$i]['win_status'] = 1;
                    }else{
                        $arr[$key]['data'][$i]['win_status'] = 2;
                    }
                }
            }
        }

        echo json_encode(['msg'=>$arr,'code'=>1,'success'=>true]);
        exit;
    }

    // 个人中心--我的订单
    public function order(){
        $yhid = db('yh')->where('id='.USER_ID)->value('yhid');

        $list = db('order')
                ->field('Tzid,kjid,order_money,Tzzfsj,win_money,win_result,game_id')
                ->where('yhid="'.$yhid.'" and Tzcgbz=1 and TzScbz=1')
                ->order('Tzzfsj desc')
                ->select(); 

        foreach ($list as $key => $value) {
            $list[$key]['data'] = db('order_info')->where('order_id="'.$list[$key]['Tzid'].'"')->select();
        }   

        $arr = array();

        foreach ($list as $key => $value) {
            if(!empty($list[$key]['data'])){
                $arr[$key]['order_id'] = $list[$key]['Tzid']; // 订单号
                $arr[$key]['issue'] = $list[$key]['kjid']; // 期号
                $arr[$key]['order_money'] = $list[$key]['order_money']; // 投注金额
                $arr[$key]['order_time'] = date('Y-m-d H:i',$list[$key]['Tzzfsj']); // 投注支付时间(下单时间)
                $arr[$key]['win_money'] = $list[$key]['win_money']; // 中奖金额
                $arr[$key]['win_result'] = db('code_info')->where('code_info_id=23 and dxhzy='.$list[$key]['win_result'])->value('code_name'); // 中奖结果:0.未开奖|1.没中奖|2.中奖
                $arr[$key]['game_name'] = db('game_cate')->where('game_id='.$list[$key]['game_id'])->value('game_name'); // 游戏名称
                $arr[$key]['data'] = $list[$key]['data'];
                for ($i=0; $i < count($arr[$key]['data']); $i++) {
                    if(!empty($arr[$key]['data'][$i]['win_result'])){
                        if($list[$key]['game_id']==1){
                            $arr[$key]['data'][$i]['win_code'] = explode(',', $arr[$key]['data'][$i]['win_code']);
                            foreach ($arr[$key]['data'][$i]['win_code'] as $k => $v) {
                                $arr[$key]['data'][$i]['win_code'][$k] = db('codex_dxh')->where('id='.$arr[$key]['data'][$i]['win_code'][$k])->value('desc');
                            }
                            $arr[$key]['data'][$i]['win_code'] = implode(',', $arr[$key]['data'][$i]['win_code']);
                            // $arr[$key]['data'][$i]['win_result'] = db('codex_dxh')->where('id='.$arr[$key]['data'][$i]['win_result'])->value('desc');
                        }
                        if($arr[$key]['data'][$i]['win_money'] > 0){
                            $arr[$key]['data'][$i]['win_status'] = 1;
                        }else{
                            $arr[$key]['data'][$i]['win_status'] = 2;
                        }
                    }else{
                        if($list[$key]['game_id']==1){
                            $arr[$key]['data'][$i]['win_result'] = '未开奖';
                            $arr[$key]['win_result'] = '未开奖';
                        }
                    }
                }
            }
        }

        echo json_encode(['msg'=>$arr,'code'=>1,'success'=>true]);
        exit;  
    }

    // 个人中心--邀请好友
    /**
     * @param  string $url      [路径]
     * @param  string $yhid     [用户编号后六位]
     * @return json 
    */

    public function invite_friend(){
        $user_id = USER_ID;
        $yhid = db('yh')->where('id='.$user_id)->value('yhid');
        $id = mb_substr($yhid, 4);
        // $url = 'http://'.$_SERVER['HTTP_HOST'].url('login/reg').'?yqm='.$id;
        $url = 'https://www.202252.com/#/regist'.'?yqm='.$id;
        $arr['yqm'] = $id;
        $arr['url'] = $url;

        echo json_encode(['msg'=>$arr,'code'=>1,'success'=>true]);
        exit;
    }
    
    // 中奖通知
    public function inform()
    {
        $dat = $_REQUEST;
        
        $data = db('order')
        ->alias('o')
        ->join('yh y','o.yhid = y.yhid','LEFT')
        ->field('y.Sjhm,o.win_money,o.Tzzfsj')
        ->where('o.kjid="'.$dat['kjid'].'" and o.win_result=2')
        ->order('o.Tzzfsj desc')
        // ->limit(10)
        ->select();
        
        foreach ($data as $key => $value) {
            $data[$key]['Sjhm'] = phone_substr_cut(phone_decode($data[$key]['Sjhm']));
        }
        echo json_encode(['msg'=>$data,'code'=>1,'success'=>true]);
        exit;
    }

    // radio 比例数值
    // 获取抽取下级用户分成
    public function get_radio()
    {
        $data['radio'] = db('yh')->where('id='.USER_ID)->value('radio');

        echo json_encode(['msg'=>$data,'code'=>1,'success'=>true]);
        exit;
    }
    // radio 比例数值
    // 设置抽取下级用户分成
    public function set_radio()
    {
        $data = $_REQUEST;
        if($data['radio'] < 0 || $data['radio'] > 5){
            echo json_encode(['msg'=>'请输入正确的数值','code'=>0,'success'=>false]);
            exit;
        }
        $radio = $data['radio']/100;
        db('yh')->where('id='.USER_ID)->setField('base',$data['radio']);
        $res = $this->save_radio(USER_ID);
        if($res === TRUE){
            echo json_encode(['msg'=>'修改成功','code'=>1,'success'=>true]);
            exit;
        }else{
            echo json_encode(['msg'=>'修改失败','code'=>0,'success'=>false]);
            exit;
        }
    }

    public function save_radio($id)
    {
        $user = db('yh')->where('id='.$id)->find();
        $child_user = db('yh')->where('pid='.$id)->find();
        if(!empty($child_user)){
            $radio = $user['radio']*(1-$user['base']);
            db('yh')->where('id='.$child_user['id'])->setField('radio',$radio);
            $this->save_radio($child_user['id']);
        }

        return TRUE;
    }
}
