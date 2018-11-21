<?php
namespace app\reptile\controller;

class Index extends Base
{
	public function caipiao()
	{
		$data = file_get_contents('php://input');

		if(!empty($data)){
			$data = json_decode($data);
			
			$weekday = array('周日','周一','周二','周三','周四','周五','周六');
			foreach ($data as $key => $value) {
				$fb_game_data = $data[$key];
				$fb_game['game_no']                  = $fb_game_data->gameNo;
				$fb_game['game_name']                = $fb_game_data->gameName;
				$fb_game['end_time']                 = ($fb_game_data->deadLine)/1000;
				$fb_game['home_team']                = $fb_game_data->hostTeam;
				$fb_game['road_team']                = $fb_game_data->guestTeam;
				$fb_game['let_score']                = $fb_game_data->concede;
				$fb_game['add_time']                 = time();
				if((($fb_game_data->deadLine)/1000) >= (strtotime(date('Y-m-d', ($fb_game_data->deadLine)/1000))+43200)){
					$fb_game['week']                 = $weekday[date('w', ($fb_game_data->deadLine)/1000)];
				}else{
					$fb_game['week']                 = $weekday[date('w', (strtotime(date('Y-m-d', ($fb_game_data->deadLine)/1000))-43200))];
				}
				
				$game_id = db('fb_game')->insertGetId($fb_game);

				$fb_game_cate['home_win']            = $fb_game_data->hostWin;
				$fb_game_cate['home_eq']             = $fb_game_data->draw;
				$fb_game_cate['home_lose']           = $fb_game_data->guestWin;
				$fb_game_cate['let_score_home_win']  = $fb_game_data->rfhostWin;
				$fb_game_cate['let_score_home_eq']   = $fb_game_data->rfdraw;
				$fb_game_cate['let_score_home_lose'] = $fb_game_data->rfguestWin;
				$fb_game_cate['one_zero']            = $fb_game_data->one_zero;
				$fb_game_cate['two_zero']            = $fb_game_data->two_zero;
				$fb_game_cate['two_one']             = $fb_game_data->two_one;
				$fb_game_cate['three_zero']          = $fb_game_data->three_zero;
				$fb_game_cate['three_one']           = $fb_game_data->three_one;
				$fb_game_cate['three_two']           = $fb_game_data->three_two;
				$fb_game_cate['four_zero']           = $fb_game_data->four_zero;
				$fb_game_cate['four_one']            = $fb_game_data->four_one;
				$fb_game_cate['four_two']            = $fb_game_data->four_two;
				$fb_game_cate['five_zero']           = $fb_game_data->five_zero;
				$fb_game_cate['five_one']            = $fb_game_data->five_one;
				$fb_game_cate['five_two']            = $fb_game_data->five_two;
				$fb_game_cate['win_other']           = $fb_game_data->win_another;				
				$fb_game_cate['zero_zero']           = $fb_game_data->zero_zero;
				$fb_game_cate['one_one']             = $fb_game_data->one_one;
				$fb_game_cate['two_two']             = $fb_game_data->two_two;
				$fb_game_cate['three_three']         = $fb_game_data->three_three;				
				$fb_game_cate['eq_other']            = $fb_game_data->draw_another;				
				$fb_game_cate['zero_one']            = $fb_game_data->zero_one;
				$fb_game_cate['zero_two']            = $fb_game_data->zero_two;
				$fb_game_cate['one_two']             = $fb_game_data->one_two;
				$fb_game_cate['zero_three']          = $fb_game_data->zero_three;
				$fb_game_cate['one_three']           = $fb_game_data->one_three;
				$fb_game_cate['two_three']           = $fb_game_data->two_three;
				$fb_game_cate['zero_four']           = $fb_game_data->zero_four;
				$fb_game_cate['one_four']            = $fb_game_data->one_four;
				$fb_game_cate['two_four']            = $fb_game_data->two_four;
				$fb_game_cate['zero_five']           = $fb_game_data->zero_five;
				$fb_game_cate['one_five']            = $fb_game_data->one_five;
				$fb_game_cate['two_five']            = $fb_game_data->two_five;				
				$fb_game_cate['lose_other']          = $fb_game_data->lost_another;			
				$fb_game_cate['total_zero']          = $fb_game_data->Zero;
				$fb_game_cate['total_one']           = $fb_game_data->One;
				$fb_game_cate['total_two']           = $fb_game_data->Two;
				$fb_game_cate['total_three']         = $fb_game_data->three;
				$fb_game_cate['total_four']          = $fb_game_data->four;
				$fb_game_cate['total_five']          = $fb_game_data->five;
				$fb_game_cate['total_six']           = $fb_game_data->six;
				$fb_game_cate['total_seven_gt']      = $fb_game_data->other;			
				$fb_game_cate['win_win']             = $fb_game_data->win_win;
				$fb_game_cate['win_eq']              = $fb_game_data->win_draw;
				$fb_game_cate['win_lose']            = $fb_game_data->win_lost;
				$fb_game_cate['eq_win']              = $fb_game_data->draw_win;
				$fb_game_cate['eq_eq']               = $fb_game_data->draw_draw;
				$fb_game_cate['eq_lose']             = $fb_game_data->draw_lost;
				$fb_game_cate['lose_win']            = $fb_game_data->lost_win;
				$fb_game_cate['lose_eq']             = $fb_game_data->lost_draw;
				$fb_game_cate['lose_lose']           = $fb_game_data->lost_lost;

				$array = array('home_win','home_eq','home_lose','let_score_home_win','let_score_home_eq','let_score_home_lose');
				foreach ($fb_game_cate as $k => $v) {
					$fb_game_cate_data['game_id'] = $game_id;
					$fb_game_cate_data['cate_name'] = db('fb_code')->where('code="'.$k.'"')->value('code_name');
					$fb_game_cate_data['cate_code'] = $k;
					$fb_game_cate_data['cate_odds'] = $v;$v==0?0:(in_array($k, $array)?floor($v*1.085*100)/100:$v);
					$fb_game_cate_data['status'] = 1;
					$fb_game_cate_data['is_win'] = 0;
					$fb_game_cate_res = db('fb_game_cate')->insertGetId($fb_game_cate_data);
				}
			}
			// dump($fb_game_cate_res);
			die;
			// dump($fb_game_cate);die;
			$fb_game_res = db('fb_game')->insertAll($fb_game);
			$fb_game_cate_res = db('fb_game_cate')->insertAll($fb_game_cate);
			if($fb_game_cate_res > 0){
				echo json_encode(['msg'=>'添加成功','data'=>$msg,'code'=>1,'success'=>true]);
            	exit;
			}else{
				echo json_encode(['msg'=>'添加失败','code'=>200,'success'=>false]);
            	exit;
			}
		}else{
			echo json_encode(['msg'=>'请填写数据','code'=>201,'success'=>false]);
            exit;
		}

	}
    public function weekday($time) 
    { 
        if(is_numeric($time)) 
        { 
            $weekday = array('周日','周一','周二','周三','周四','周五','周六');
            return $weekday[date('w', $time)]; 
        } 
        return false; 
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
