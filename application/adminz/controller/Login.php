<?php
namespace app\adminz\controller;
class Login extends Base
{
    public function index(){
        if(!empty($_COOKIE['remember'])){
            $this->assign('remember',$_COOKIE['remember']);
        }else{
            $this->assign('remember','');
        }
        return view('index');
    }


    /**
     * 登录
     * @param  string $username [description]
     * @param  string $password [description]
     * @return [type]           [description]
     */
    public function login($username = '', $password = '',$yzm = '',$checkbox=''){
        // dump($_COOKIE);die;

        $captcha = new \think\captcha\Captcha();
    	//如果已经登录过，重新进入后台入口时，自动跳转到index/index
        if(session(config('admin_site.session_name'))) {
            $this->redirect('index/index');
        }
        if($username && $password){
            $login_name = $username;
            $login_pass = $password;
         
            $map = array();
            $map['login_name'] = $login_name;
            $admin = db('Admin')->field('id,login_name,login_pass,nickname,status,role_id')->where($map)->find();
            if($admin){
                if(md5($login_pass)!=$admin['login_pass']) exit(json_encode(array('status'=>0,'info'=>'密码错误')));
                // if(!captcha_check($yzm)){
                //     exit(json_encode(array('status'=>0,'info'=>'验证码错误')));
                // };
                if($admin['status']==0) exit(json_encode(array('status'=>0,'info'=>'账号被冻结不能登录')));
                //添加上次登录时间和更新登录次数
                $data=array();
                $data['last_login_time']=time();
                $map=array();
                $map['login_name'] = $login_name;
                $data['last_ip'] = $_SERVER['REMOTE_ADDR'];
                $loginCount = db("Admin")->where($map)->setInc('login_count');
                $time = db("Admin")->where($map)->update($data);
                if($checkbox=='on'){
                    setcookie("remember",$login_name, time()+315360000);
                }else{
                    setcookie("remember",'', time() - 3600);
                } 
                session(config('admin_site.session_name'),$admin);
                exit(json_encode(array('status'=>1,'info'=>'登录成功')));
            }else{
            	exit(json_encode(array('status'=>0,'info'=>'用户名不存在')));
            }
        }else{
            $this->display();
           
        }
    }
    /**
     * 后台退出登录
     * @return [type] [description]
     */
    public function logout(){
        unset($_SESSION[config('admin_site.session_name')]);
        unset($_SESSION);
        session_destroy();
        //以上步骤彻底销毁session
        $this->success('退出成功！',url('login/login'));
    }
}
