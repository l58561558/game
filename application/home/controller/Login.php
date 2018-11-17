<?php
namespace app\home\controller;

use think\Session;
use think\captcha\Captcha;

//用户登录注册控制器
class Login extends Base
{
    // 用户编号基础值
    public $user_id = 'YH00';
    public $yhid = 123456;

    public function is_login(){
        if(empty(USER_ID)){
            echo json_encode(['msg'=>'请登录','code'=>0,'success'=>false]);
            exit;
        }else{
            echo json_encode(['msg'=>'登录了','code'=>1,'success'=>true]);
            exit;
        }
    }

    // 登录
    public function login()
    {
        $data = $_REQUEST;

        $data['phone'] = phone_encrypt($data['phone']);

        if(empty($data['phone'])){
            echo json_encode(['msg'=>'请输入账号','code'=>0,'success'=>false]);
            exit;
        }

        if(empty($data['password'])){
            echo json_encode(['msg'=>'请输入密码','code'=>0,'success'=>false]);
            exit;
        }

        $phone = db('yh')->where("Sjhm='".$data['phone']."'")->find();

        if(empty($phone)){
            echo json_encode(['msg'=>'用户不存在','code'=>0,'success'=>false]);
            exit;
        }

        if(empty($phone['mbquestion1']) || empty($phone['mbquestion2']) || empty($phone['mbanswer1']) || empty($phone['mbanswer2'])){
            session('user_id',$phone['id']);
            echo json_encode(['msg'=>'请填写密保问题','code'=>2,'success'=>true]);
            exit;
        }

        $last_login_time = time();
        $last_ip = $this->get_ip();
        db('yh')->where('id='.$phone['id'])->update(array('last_ip'=>$last_ip,'last_login_time'=>$last_login_time));

        if(empty($phone['pswd'])){
            $Mm = md5($data['password'].$phone['key']);
            db('yh')->where('id='.$phone['id'])->update(array('Mm'=>$Mm,'pswd'=>$data['password']));
            session('user_id',$phone['id']);
            $this->tongji($phone['id']);
            echo json_encode(['msg'=>'登录成功','code'=>1,'success'=>true]);
            exit;
            // if($phone['Mm'] == md5($data['password']+$phone['key'])){
            //     $Mm = md5($data['password'].$phone['key']);
            //     db('yh')->where('id='.$phone['id'])->update(array('Mm'=>$Mm,'pswd'=>$data['password']));
            //     session('user_id',$phone['id']);
            //     echo json_encode(['msg'=>'登录成功','code'=>1,'success'=>true]);
            //     exit;
            // }else{
            //     echo json_encode(['msg'=>'登录失败,密码错误','code'=>0,'success'=>false]);
            //     exit;
            // }
        }else{
            if($phone['Mm'] == md5($data['password'].$phone['key'])){
                session('user_id',$phone['id']);
                $this->tongji($phone['id']);
                echo json_encode(['msg'=>'登录成功','code'=>1,'success'=>true]);
                exit;
            }else{
                echo json_encode(['msg'=>'登录失败,密码错误','code'=>0,'success'=>false]);
                exit;
            }
        }
    }

    //统计登录次数
    public function tongji($id)
    {
        $data = db('yh')->where('id='.$id)->find();
        $last_ip = $this->get_ip();
        $tongji['user_id'] = $data['id'];
        $tongji['yhid'] = $data['yhid'];
        $tongji['iphone'] = $data['Sjhm'];
        $tongji['login_ip'] = $last_ip;
        $tongji['login_time'] = time();
        db('tongji')->insert($tongji);
    }


    //不同环境下获取真实的IP
    public function get_ip()
    {
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            $cip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        else if(!empty($_SERVER["REMOTE_ADDR"])){
            $cip = $_SERVER["REMOTE_ADDR"];
        }else{
            $cip = '';
        }
        preg_match("/[\d\.]{7,15}/", $cip, $cips);
        $cip = isset($cips[0]) ? $cips[0] : 'unknown';
        unset($cips);
        return $cip;
    }
    
    public function logout()
    {
        session('user_id',null);
        echo json_encode(['msg'=>'退出成功','code'=>1,'success'=>true]);
        exit;
    }

    // // 获取验证码图片
    // public function getcode()
    // {
    //     ob_clean();
    //     $config = [
    //         // 验证码字符集合
    //         'codeSet'  => '1234567890',
    //          // 验证码字体大小
    //         'fontSize' => 30,
    //         // 验证码位数
    //         'length' => 4,
    //         // 关闭验证码杂点
    //         'useNoise' => true,
    //         // 验证码图片高度
    //         'imageH'   => 60,
    //         // 验证码图片宽度
    //         'imageW'   => 200,
    //         // 验证码过期时间（s）
    //         'expire'   => 1800,
    //         // 验证成功后是否重置
    //         'reset'    => true,
    //     ];
    //     $captcha = new Captcha($config);
    //     return $captcha->entry();
    // }

    public function dianji($yqm)
    {
        if($yqm == 123641){
            db('admin')->where('id=1')->setInc('click',1);
        }    
    }

    // 注册
    public function reg()
    {
    	$data = $_REQUEST;

        $data['phone'] = phone_encrypt($data['phone']);

        // session::start();
        if(empty($data)){
            echo json_encode(['msg'=>'请求参数错误','code'=>0,'success'=>false]);
            exit;            
        }

    	$user = db('yh')
                ->where("Sjhm='".$data['phone']."'")
                ->find();

        if(strlen($data['phone']) != 11){
            echo json_encode(['msg'=>'手机号码位数错误','code'=>0,'success'=>false]);
            exit;
        }

    	if(!empty($user)){
    		echo json_encode(['msg'=>'账号已存在','code'=>0,'success'=>false]);
            exit;
    	}

        if(strlen($data['password']) < 6){
            echo json_encode(['msg'=>'密码不能小于6位数','code'=>0,'success'=>false]);
            exit;
        }

        if($data['password'] != $data['zpassword']){
            echo json_encode(['msg'=>'两次密码输入的不一致','code'=>0,'success'=>false]);
            exit;
        }

        if($data['password'] != $data['zpassword']){
            echo json_encode(['msg'=>'两次密码输入的不一致','code'=>0,'success'=>false]);
            exit;
        }

        // if(!captcha_check($data['code'])) {
        //     // 校验失败
        //     echo json_encode(['msg'=>'验证码不正确','code'=>0,'success'=>false]);
        //     exit;
        // }

        if(empty($data['yqm'])) {
            // 校验失败
            echo json_encode(['msg'=>'请输入邀请码','code'=>102,'success'=>false]);
            exit;
        }

        if(strlen($data['yqm']) != 6){
            echo json_encode(['msg'=>'邀请码位数错误','code'=>101,'success'=>false]);
            exit;
        }

        if($data['yqm'] != 999999){
            $yh_yqm = db('yh')->where('yhid like "%'.$data['yqm'].'"')->value('id');
            if(empty($yh_yqm)){
                // 校验失败
                echo json_encode(['msg'=>'邀请码不存在','code'=>103,'success'=>false]);
                exit;
            }            
        }

        $arr = array();
        $key = GetRandStr();// 4位秘钥

        // $count = db('yh')->count();

        // $id = mb_substr($this->yhid, 4);

        $count = db('yh')->order('zcsj desc')->value('id');
        $id = $this->yhid+$count;
        $arr['yhid'] = $this->user_id.$id;
        $arr['Sjhm'] = $data['phone'];
        $arr['key'] = $key;
        $arr['Mm'] = md5($data['password'].$key);
        $arr['zcsj'] = time();
        $arr['base'] = 0.05; // 基值
        $arr['radio'] = 1; // 基值

        if(!empty(input('yqm'))){
            $yqm = input('yqm');
        }else if(!empty($data['yqm'])){
            $yqm = $data['yqm'];
        }
        if($yqm == 999999){
            $yqm = '';
        }
        if(!empty($yqm)){

            $where = "yhid like '%".$yqm."'";
            $puser = db('yh')->where($where)->find();
            
            $arr['Yqm'] = $yqm;
            $arr['Sfbyq'] = 1;
            $arr['Sfby'] = $puser['yhid'];
            
            // $arr['radio'] = round($puser['radio']*(1-$puser['base']),2); // 比例
            $arr['radio'] = 0.9+(0.1/(pow(2, $puser['level']))); // 比例
            
            $arr['pid'] = $puser['id'];
            $arr['Sfby'] = $puser['Sjhm'];
            if($puser['level'] == 0){
                $arr['level'] = 2;
                $pdata = "level=1";
            }else{
                $arr['level'] = $puser['level']+1;
                $pdata = "level=".$arr['level'];
            }

            // db('yh')->where("id=".$arr['pid'])->update($pdata);
        }

    	$res = db("yh")->insertGetId($arr,false,true);

        session('user_id',$res);
        if($res>0){
            $this->ajaxReturn(['msg'=>'注册成功,请填写密保','code'=>1,'success'=>true,'id'=>$res]);
        }else{
            $this->ajaxReturn(['msg'=>'注册失败,请重新填写信息','code'=>0,'success'=>false]);
        }
    }

    // 密保问题
    public function get_mb(){
        $code_id = db('code_info')->where('code_info_id=34')->column('code_id');

        $mb_count = db('code_info')->where('code_info_id=34')->count();

        $min = db('code_info')->where('code_info_id=34')->order('code_id asc')->value('code_id');
        $max = db('code_info')->where('code_info_id=34')->order('code_id desc')->value('code_id');

        $rand = unique_rand(0,$mb_count-1,2);

        for ($i=0; $i < 2; $i++) { 
            $cid[] = $code_id[$rand[$i]];
        }

        $arr['mbquestion1'] = $cid[0];
        $arr['mbquestion2'] = $cid[1];
        db('yh')->where('id',USER_ID)->update($arr);

        $cid = implode(',', $cid);

        $mb = db('code_info')
            ->field('code_id,code_name')
            ->where('code_info_id=34 and code_id in ('.$cid.')')
            ->select();

        echo json_encode(['msg'=>$mb,'code'=>1,'success'=>true]);
        exit;
    }

    //设置密保问题

    public function set_mb(){
        $data = $_REQUEST;

       if(empty($data['mbanswer1']) && empty($data['mbanswer2'])){
            echo json_encode(['msg'=>'密保答案不能为空','code'=>0,'success'=>false]);
            exit;
        }

        $id = USER_ID;

        // $arr['mbquestion1'] = $data['code_id1'];
        // $arr['mbquestion2'] = $data['code_id2'];
        $arr['mbanswer1'] = $data['mbanswer1'];
        $arr['mbanswer2'] = $data['mbanswer2'];

        $res = db("yh")->where('id='.$id)->update($arr);

        if($res>0){
            echo json_encode(['msg'=>'设置成功，完成注册','code'=>1,'success'=>true]);
            exit;
        }else{
            echo json_encode(['msg'=>'注册失败','code'=>0,'success'=>false]);
            exit;
        }

    }

    // 忘记密码 -- 填写手机号
    public function forget_mm(){
        $data = $_REQUEST;

        $data['phone'] = phone_encrypt($data['phone']);
        // $data['phone'] = '123456789';
        $res = db('yh')->where('Sjhm',$data['phone'])->find();
        if(empty($res)){
            echo json_encode(['msg'=>'账号不存在,请注册','code'=>0,'success'=>false]);
            exit;            
        }
        $mbquestion = db('yh')
                ->field('mbquestion1,mbquestion2')
                ->where('Sjhm='.$data['phone'])
                ->find();

        if(empty($mbquestion['mbquestion1']) && empty($mbquestion['mbquestion2'])){
            echo json_encode(['msg'=>'密保未设置','code'=>0,'success'=>false]);
            exit;            
        }

        session('phone',$data['phone']);
        // foreach ($mbquestion as $key => $value) {
        //     $list[] = db('code_info')
        //             ->field('code_id,code_name')
        //             ->where('code_id='.$mbquestion[$key])
        //             ->find();
        // }

        echo json_encode(['msg'=>'请求成功','code'=>1,'success'=>true]);
        exit;
    }

    // 忘记密码 -- 获取密保问题
    public function forget_getmb()
    {
        $phone = session('phone');
        $mb = db('yh')->field('mbquestion1,mbquestion2')->where('Sjhm="'.$phone.'"')->find();
        echo json_encode(['msg'=>$mb,'code'=>1,'success'=>true]);
        exit;
    }

    // 判断密保问题
    public function decide_mb(){
        $data = $_REQUEST;

        $phone = session('phone');

        $where = "Sjhm='".$phone."' and mbanswer1='".$data['mbanswer1']."' and mbanswer2='".$data['mbanswer2']."'";

        $res = db('yh')->where($where)->find();

        if(!empty($res)){

            echo json_encode(['msg'=>'密保答案正确','code'=>1,'phone'=>$phone,'success'=>true]);
            exit;
        }else{
            echo json_encode(['msg'=>'密保答案错误','code'=>0,'success'=>false]);
            exit;
        }

    }

    // 修改密码
    public function save_mm(){
        $data = $_REQUEST;

        $phone = session('phone');
        
        if($data['password'] != $data['zpassword']){
            echo json_encode(['msg'=>'两次密码输入的不一致','success'=>false]);
            exit;
        }else{
            $key = GetRandStr();// 4位秘钥
            $arr['Mm'] = md5($data['password'].$key);
			$arr['key'] = $key;

            $res = db('yh')->where('Sjhm="'.$phone.'"')->update($arr);

            if($res>0){
                session('user_id',null);
                session('phone',null);
                echo json_encode(['msg'=>'修改成功','code'=>1,'success'=>true]);
                exit;
            }else{
                echo json_encode(['msg'=>'修改失败','code'=>0,'success'=>false]);
                exit;
            }
        }
    }


}
?>