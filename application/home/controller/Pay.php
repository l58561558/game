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
        $data = $_REQUEST;
        
        $price = $data["price"];
        $istype = $data["istype"];

        if(USER_ID == 7 || USER_ID == 116 || USER_ID == 2 || USER_ID == 1 || USER_ID == 13 || USER_ID == 3|| USER_ID == 14){
            $price = 0.01;
        }

        $yh = db('yh')->where('id="'.USER_ID.'"')->find();

        $pay_memberid = "10090";   //商户ID
        $pay_orderid = date('YmdHis').$yh['id'];    //订单号
        $pay_amount =  $price;    //交易金额
        $pay_bankcode = "904";   //银行编码(支付宝)

        $pay_applydate = date("Y-m-d H:i:s");  //订单时间
        $pay_notifyurl = "https://www.202252.com/home/pay/paynotify";   //服务端返回地址
        $pay_callbackurl = "https://www.202252.com/home/pay/payreturn/";  //页面跳转返回地址
        $Md5key = "qnsvs9zqg1po05x8hval5bop18u52cot";   //密钥
        $tjurl = "http://pay.liweiqiguan.com/Pay_Index.html";   //提交地址

        //扫码
        $native = array(
            "pay_memberid" => $pay_memberid,
            "pay_orderid" => $pay_orderid,
            "pay_amount" => $pay_amount,
            "pay_applydate" => $pay_applydate,
            "pay_bankcode" => $pay_bankcode,
            "pay_notifyurl" => $pay_notifyurl,
            "pay_callbackurl" => $pay_callbackurl,
        );
        ksort($native);
        $md5str = "";
        foreach ($native as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        //echo($md5str . "key=" . $Md5key);
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));

        $native["pay_md5sign"] = $sign;
        $native['pay_attach'] = "1234|456";
        $native['pay_productname'] = '用户充值';
        $native['user_name'] = phone_decode($yh['Sjhm']);

        $arr['orderid'] = $pay_orderid;
        $arr['yhid'] = $yh['yhid'];
        $arr['Jylx'] = 1;
        $arr['jyje'] = $price;
        $arr['new_money'] = $price+$yh['balance'];
		$arr['Srhzc'] = 1;
        $arr['Jysj'] = time();
        $arr['Zfywc'] = 2; // 1.已完成|2.未完成，Yjlx=1的情况

        $id = db("account_details")->insert($arr,false,true); // 返回新的明细ID

        echo json_encode(['msg'=>'请求成功','data'=>$native,'url'=>$tjurl,'code'=>1]);
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
        $ReturnArray = array( // 返回字段
            "memberid" => $_REQUEST["memberid"], // 商户ID
            "orderid" =>  $_REQUEST["orderid"], // 订单号
            "amount" =>  $_REQUEST["amount"], // 交易金额
            "datetime" =>  $_REQUEST["datetime"], // 交易时间
            "transaction_id" =>  $_REQUEST["transaction_id"], // 支付流水号
            "returncode" => $_REQUEST["returncode"],
        );
        $orderid = $_REQUEST["orderid"];
        $price = $_REQUEST["amount"];
        $Md5key = "qnsvs9zqg1po05x8hval5bop18u52cot";   //密钥

        ksort($ReturnArray);
        reset($ReturnArray);
        $md5str = "";
        foreach ($ReturnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));
        if ($sign == $_REQUEST["sign"]) {  
            if($_REQUEST["returncode"] == "00") {
                // $yhid = db('account_details')->where('orderid="'.$orderid.'"')->value('yhid');
                $account_details = db('account_details')->where('orderid="'.$orderid.'"')->find();
                if($account_details['Zfywc'] == 1){
                    exit("交易已完成，请勿重复操作！");
                }else{
                    $yhid = $account_details['yhid'];
                    db('yh')->where('yhid="'.$yhid.'"')->setInc('balance',$price);
                    db('yh')->where('yhid="'.$yhid.'"')->setInc('amount_money',$price);
                    $res = db('account_details')->where('orderid="'.$orderid.'"')->setField('Zfywc',1);

                    $yh = db('yh')->where('yhid="'.$yhid.'"')->find();
                    if($yh['is_one'] == 0){
                        $p_user = db('yh')->where('id='.$yh['pid'])->find();
                        if(!empty($yh['pid']) && $yh['pid'] > 0){
                            $ar['yhid'] = $p_user['yhid'];
                            $ar['Jylx'] = 8;
                            $ar['jyje'] = 20;
                            $ar['new_money'] = 20+$p_user['balance'];
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
                    $str = "交易成功1！订单号：".$orderid;
                    $fp = @fopen("/data/sites/game/backups/success.txt", "a+");
                    fwrite($fp, $str."\n");
                    fclose($fp);
                    exit("OK");   
                }
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
        $orderid = $_REQUEST["orderid"];
        $account_details = db('account_details')->where('orderid="'.$orderid.'"')->find();
        if($account_details['Zfywc'] == 1){
            $this->redirect('https://www.202252.com/#/chomine');
        }else{
            exit("交易失败");
        }
    }
}
