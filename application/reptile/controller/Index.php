<?php
namespace app\reptile\controller;

class Index extends Base
{
	public function caipiao()
	{
		$data = file_get_contents('php://input');
		$file = fopen('E:\\caipiao', 'a');
		fwrite($file, $data);
		dump($data);

	}

    public function download($pages="")
    {
    	set_time_limit(0);

    	$m = memory_get_usage();
    	$time = time();
    	header("Content-Type:text/html;charset=utf-8");
		ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; GreenBrowser)');
		ini_set('max_execution_time', '0');
		 
		$base = 'http://caipiao.163.com/order/jczq-hunhe/';
		if(empty($pages)){
			$start = '6857533.html';
		}else{
			$start = str_replace('.html', '', $pages).'.html';
		}
		
		$content_grep = '/<div id=\"content\">(.*)/';
		$next_grep = '/<a href=\"\/13_13981\/(\d+\.html)\">/';
		# $next_grep = '/next_page = \"\/html\/410377\/(\d+)\.html\"/';

		$header = '/<h1>(.*)<\/h1>/';
		 
		$next = $start;
		$file_name = date('Ymd',time()).'.txt';
		 
		while($next) {
		    echo 'getting ' . $next . PHP_EOL;
		    $result = file_get_contents("compress.zlib://".$base . $next);
			// $result = iconv("gb2312", "utf-8//IGNORE",$res);
		    preg_match_all($content_grep, $result, $match);
		 	
		 	// dump($result);die;
		    $isTitle = true;
		    $content = "";

		    foreach($match[1] as $line) {
		        $line   = str_replace("　　", '', $line);
		        $line   = str_replace("<p>", '', $line);
		        $line   = str_replace("</p>", '', $line);
		        $line   = str_replace("<br />", '', $line);
		        $line   = str_replace("&nbsp;", '', $line);
		        if($isTitle) {
		            $content = $line . PHP_EOL . PHP_EOL;
		            $isTitle = false;
		        } else {
		            $content .= '        ' . $line . PHP_EOL . PHP_EOL;
		        }
		    }

		    $file = fopen('E:\\'.$file_name, 'a');

		    // $head_match = $this->get_header($header, $result);
		    preg_match($header, $result, $head_match);
		    fwrite($file, $head_match[1]. PHP_EOL);

		    echo 'write length: ' . strlen($content) . PHP_EOL;
		    fwrite($file, $content);
		    fclose($file);
		 
		    echo '-';
		 
		    preg_match_all($next_grep, $result, $match);
		    if(!empty($match[1])){
		    	$next = $match[1][count($match[1])-1];
		    }else{
		    	$next = false;
		    	echo $head_match[1];
		    } 
		    unset($result);
		    unset($match);
		    unset($content);
		    unset($file);
		    unset($head_match);
		    $mm = memory_get_usage();
		    echo time()-$time;
		    echo '--';
		    echo $m;
		    echo $mm;
		    echo $mm-$m;
		    echo '<br>';
		    
		}
    }
	public function curl_get($url, $gzip=false){
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		if($gzip) curl_setopt($curl, CURLOPT_ENCODING, "gzip"); // 关键在这里
		$content = curl_exec($curl);
		curl_close($curl);
		return $content;
	}
	public function get_header($header, $result)
	{
		preg_match($header, $result, $head_match);
		if(empty($head_match)){
			$this->get_header($header, $result);
		}
		return $head_match;
	}

    public function save_tix()
    {
    	$tix = db('tix')->select();
    	foreach ($tix as $key => $value) {
    		$res = db('account_details')->where('Jysj='.$tix[$key]['Txsqsj'])->setField('present_status',$tix[$key]['Txzt']);
    		echo $res;
    	}
    }
}
