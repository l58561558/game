<?php
namespace app\home\controller;

use think\cache\driver\Redis;

class Oredis extends Base
{
    public function index()
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);
        $arr = array('c','c++','php','java','go','python');
        // $redis->set("myqueue",$arr);
        foreach ($arr as $key => $value) {
            $redis->lPush("myqueue",$value);
        }

    }

    public function dindex()
    {
    	$redis = new Redis();
        $arr = array('c','c++','php','java','go','python');
        // $redis->set("myqueue",$arr);
        $data = $redis->get('myqueue');
        dump($data);
    }

    public function clear()
    {
        $redis = new Redis();
        $redis->clear('myqueue');
    }

}
