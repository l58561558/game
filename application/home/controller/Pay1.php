<?php
namespace app\home\controller;

//用户登录注册控制器
class Pay extends Base
{
    public function go_pay()
    {
        $phone = db('yh')->where('id',USER_ID)->value('Sjhm');
        $phone = phone_decode($phone);
        echo json_encode(['msg'=>'请求成功','data'=>$phone,'code'=>1,'success'=>true]);
        exit; 
    }

    public function pay()
    {
    /**
     * ---------------------参数生成页-------------------------------
     * 在您自己的服务器上生成新订单，并把计算好的订单信息传给您的前端网页。
     * 注意：
     * 1.key一定要在服务端计算，不要在网页中计算。
     * 2.token只能存放在服务端，不可以以任何形式存放在网页代码中（可逆加密也不行），也不可以通过url参数方式传入网页。
     * 3.接口跑通后，如果发现收款二维码是我们官方的，请检查APP是否正在运行。为保障您收款功能正常，如果您的收款手机APP掉线超过一分钟，就会触发代收款机制，详情请看网站帮助。
     * --------------------------------------------------------------
     */
        $data = $_REQUEST;

        //从网页传入price:支付价格， istype:支付渠道：1-支付宝；2-微信支付
        $price = $data["price"];
        $istype = $data["istype"];
        //$phone = $data["phone"];
        $return_url = $data["return_url"];
        
        //$orderuid = $phone;       //此处传入您网站用户的用户名，方便在平台后台查看是谁付的款，强烈建议加上。可忽略。
        if(USER_ID == 7){
            $price = 0.01;
        }
        $yh = db('yh')->where('id="'.USER_ID.'"')->find();
		$orderuid = phone_decode($yh['Sjhm']);
        $yhid = $yh['yhid'];
        $orderid = date('YmdHis').$yh['id'];
        $arr['orderid'] = $orderid;
        $arr['yhid'] = $yh['yhid'];
        $arr['Jylx'] = 1;
        $arr['jyje'] = $price;
        $arr['new_money'] = $price+$yh['balance'];
		$arr['Srhzc'] = 1;
        $arr['Jysj'] = time();
        $arr['Zfywc'] = 2; // 1.已完成|2.未完成，Yjlx=1的情况

        $id = db("account_details")->insert($arr,false,true); // 返回新的明细ID


        //校验传入的表单，确保价格为正常价格（整数，1位小数，2位小数都可以），支付渠道只能是1或者2，orderuid长度不要超过33个中英文字。

        //此处就在您服务器生成新订单，并把创建的订单号传入到下面的orderid中。
        $goodsname = "用户充值";

        // $orderid = "1234567890";    //每次有任何参数变化，订单号就变一个吧。
        $uid = "1068440";//"此处填写平台的uid";
        $token = "f5d6764a92d3fb63e1c84970f66d1abd";//"此处填写平台的Token";
        $return_url = 'http://www.202252.com/home/pay/payreturn/';
        $notify_url = 'http://www.202252.com:8080/game/home/pay/paynotify';
        
        $key = md5($goodsname. $istype . $notify_url . $orderid . $orderuid . $price . $return_url . $token . $uid);
        //经常遇到有研发问为啥key值返回错误，大多数原因：1.参数的排列顺序不对；2.上面的参数少传了，但是这里的key值又带进去计算了，导致服务端key算出来和你的不一样。

        //$url = 'https://bell.eysj.net/pay?format=json';
        $url = 'https://bell.eysj.net/pay';
		//$url = 'https://www.baidu.com';

        $post_data['goodsname'] = $goodsname;
        $post_data['istype'] = $istype;
        $post_data['key'] = $key;
        $post_data['notify_url'] = $notify_url;
        $post_data['orderid'] = $orderid;
        $post_data['orderuid'] =$orderuid;
        $post_data['price'] = $price;
        $post_data['return_url'] = $return_url;
        $post_data['uid'] = $uid;
        
        //$res = request_post($url,$post_data);

        echo json_encode(['msg'=>'请求成功','data'=>$post_data,'url'=>$url,'code'=>1]);
        exit;
    }

    public function paynotify()
    {	
    /**
     * ---------------------通知异步回调接收页-------------------------------
     * 
     * 此页就是您之前传给pay.PayAPI.com的notify_url页的网址
     * 支付成功，平台会根据您之前传入的网址，回调此页URL，post回参数
     * 
     * --------------------------------------------------------------
     */

        $platform_trade_no = $_POST["platform_trade_no"];
        $orderid = $_POST["orderid"];
        $price = $_POST["price"];
        $realprice = $_POST["realprice"];
        $orderuid = $_POST["orderuid"];
        $key = $_POST["key"];

        //校验传入的参数是否格式正确，略

        $token = "f5d6764a92d3fb63e1c84970f66d1abd";
        
        $temps = md5($orderid . $orderuid . $platform_trade_no . $price . $realprice . $token);

        if ($temps != $key){
            return jsonError("key值不匹配");
        }else{
            //校验key成功，是自己人。执行自己的业务逻辑：加余额，订单付款成功，装备购买成功等等。
            $order = db('account_details')->where('orderid="'.$orderid.'"')->find();
            if($price != $order['jyje']){
                return jsonError("订单价格与您实际充值金额不符");
            }
            //增加余额
            if($order['Zfywc'] == 2){
                db('yh')->where('Sjhm="'.phone_encrypt($orderuid).'"')->setInc('balance',$price);
                db('yh')->where('Sjhm="'.phone_encrypt($orderuid).'"')->setInc('amount_money',$price);

                $res = db('account_details')->where('orderid="'.$orderid.'"')->setField('Zfywc',1);

                // 国庆活动奖励
                // 国庆节用户每日首冲
                // $yh = db('yh')->where('Sjhm="'.phone_encrypt($orderuid).'"')->find();
                // $start_time = strtotime(date('Y-m-d',time())); // 86400 一天
                // $end_time = $start_time+86400;
                // $sum_money = db('account_details')->where('yhid="'.$yh['yhid'].'" and Jylx=1 and Zfywc=1 and Jysj>'.$start_time.' and Jysj<'.$end_time)->sum('jyje');
                // $account_details = db('account_details')->where('yhid="'.$yh['yhid'].'" and Jylx=8 and Jysj>'.$start_time.' and Jysj<'.$end_time)->find();
                // if($sum_money >= 100 && empty($account_details)){
                //     $arr['yhid'] = $yh['yhid'];
                //     $arr['Jylx'] = 8;
                //     $arr['jyje'] = 10;
                //     $arr['new_money'] = 10+$yh['balance'];
                //     $arr['Srhzc'] = 1;
                //     $arr['Jysj'] = time();
                //     $arr['Zfywc'] = 1; // 1.已完成|2.未完成，Yjlx=1的情况
                //     $id = db("account_details")->insert($arr,false,true); // 返回新的明细ID
                //     if($id > 0){
                //         db('yh')->where('Sjhm="'.phone_encrypt($orderuid).'"')->setInc('balance',10);
                //         db('yh')->where('Sjhm="'.phone_encrypt($orderuid).'"')->setInc('amount_money',10);
                //     }
                // }
                
                // 老用户邀请新用户 新用户首冲 老用户加 10.1 元
                if($yh['is_one'] == 0){
                    $p_user = db('yh')->where('id='.$yh['pid'])->find();
                    if(!empty($yh['pid']) && $yh['pid'] > 0){
                        $ar['yhid'] = $p_user['yhid'];
                        $ar['Jylx'] = 8;
                        $ar['jyje'] = 10.1;
                        $ar['new_money'] = 10.1+$p_user['balance'];
                        $ar['Srhzc'] = 1;
                        $ar['Jysj'] = time();
                        $ar['Zfywc'] = 1; // 1.已完成|2.未完成，Yjlx=1的情况
                        $id = db("account_details")->insert($ar,false,true); // 返回新的明细ID
                        if($id > 0){
                            db('yh')->where('id='.$p_user['id'])->setInc('balance',$ar['jyje']);
                            db('yh')->where('id='.$p_user['id'])->setInc('amount_money',$ar['jyje']);
                            db('yh')->where('id='.$yh['id'])->setInc('is_one',1);
                        }
                    }
                }

                // 国庆活动奖励 END

                if($res > 0){
                    return jsonSuccess("OK");
                }else{
                    return jsonError("数据错误");
                }
            }else{
                return jsonSuccess("用户已支付");
            }

        }
    }

    public function payreturn()
    {
    /**
     * ---------------------支付成功，用户会跳转到这里-------------------------------
     * 
     * 此页就是您之前传给pay.PayAPI.com的return_url页的网址
     * 支付成功，平台会把用户跳转回这里。
     * 
     * --------------------------------------------------------------
     */

        $orderid = $_GET["orderid"];

        //此处在您数据库中查询：此笔订单号是否已经异步通知给您付款成功了。如成功了，就给他返回一个支付成功的展示。
        echo "恭喜，支付成功!，订单号：".$orderid;
    }
}
