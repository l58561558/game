<?php
namespace app\dial\controller;
use think\Request; 
use think\Controller;
use think\Cookie;
use think\Session;

class Base extends Controller
{
    public function _initialize()
    {
        session::start();
        $user_id = session('user_id');
        defined('USER_ID') or define('USER_ID', $user_id);
    } 



    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void
     */
    protected function ajaxReturn($data) {
        // 返回JSON数据格式到客户端 包含状态信息
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }    
    

}
