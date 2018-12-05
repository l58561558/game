<?php
namespace app\reptile\model;

use think\Model;

class Crawler extends Model {
    
    function __construct(){
        
    }

    public function getGameData($url='')
    {	
    	$microtime = microtime();
    	list($msec, $sec) = explode(' ', $microtime);
    	$msectime = (float)sprintf('%.0f',(floatval($msec)+floatval($sec))*1000);
    	$rand = lcg_value();
    	$url = "http://info.sporttery.cn/interface/interface_mixed.php?action=fb_list&pke=".$rand."&_=".$msectime;
    	$res = $this->getCurl($url);
    	//utf-8 转换
    	$data = iconv("GBK",'utf-8',$res);
    	$count = strpos($data,"getData();");
    	$data = substr_replace($data,"",$count);
    	//dump($data);
    	$js = $data."$.ajax({
	    		url: 'https://www.202252.com/reptile/Games/addFootball',
	    		data: JSON.stringify(data),
	    		type: 'POST',
	    		contentType: 'application/json;charset=utf-8',
	    		success: function(data) {
	    			if(data == 'success'){
                      console.log('数据添加成功');  
                    }
	    		},
	    		error: function(data) {
	    			console.log('数据传输失败');
	    		}
	    	});";
    	echo "<script type=text/javascript src='https://code.jquery.com/jquery-1.8.0.min.js'></script>";
    	echo "<script type=text/javascript>".$js."</script>";
    }

    public function getGameList($url='')
    {   
        $microtime = microtime();
        list($msec, $sec) = explode(' ', $microtime);
        $msectime = (float)sprintf('%.0f',(floatval($msec)+floatval($sec))*1000);
        $rand = lcg_value();
        $url = "http://info.sporttery.cn/interface/interface_mixed.php?action=bk_list&".$rand."&_=".$msectime;
        $res = $this->getCurl($url);
        //utf-8 转换
        $data = iconv("GBK",'utf-8',$res);
        $count = strpos($data,"getData();");
        $data = substr_replace($data,"",$count);
        //dump($data);
        $js = $data."$.ajax({
                url: 'https://www.202252.com/reptile/Games/addBasketball',
                data: JSON.stringify(data),
                type: 'POST',
                contentType: 'application/json;charset=utf-8',
                success: function(data) {
                    if(data == 'success'){
                      console.log('数据添加成功');  
                    }                    
                },
                error: function(data) {
                    console.log('数据传输失败');
                }
            });";
        echo "<script type=text/javascript src='https://code.jquery.com/jquery-1.8.0.min.js'></script>";
        echo "<script type=text/javascript>".$js."</script>";
    }

    /**
     * curl的get请求
     * @param  string $url 请求的url
     * @return mixed  $result 返回请求结果
     */
    public function getCurl($url){
		$header = 'Accept-Content-Type:application/javascript;Accept-Charset: utf-8';
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_HEADER,$header);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_HEADER,0);
        curl_setopt($curl,CURLOPT_NOBODY,0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($curl,CURLOPT_URL,$url);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}