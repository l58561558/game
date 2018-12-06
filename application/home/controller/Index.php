<?php
namespace app\home\controller;

//用户登录注册控制器
class Index extends Base
{
    public function index()
    {
        $this->redirect('https://www.202252.com/dist');
    }
    public function money($user_id = 37)
    {
        // $start = strtotime("2018-10-16");
        // $end = strtotime("2018-11-15");
        // $nine_order_money = db('account_details a')
        //             ->join('yh y','a.yhid=yh.yhid')
        //             ->where('y.yqm='.$yqm.' and jylx=3 and y.status=0 and a.game_id!=6 and a.Jysj>'.$start.' and a.Jysj<'.$end)
        //             ->sum('a.jyje');
        // $nba_fb_order_money = db('account_details a')
        //             ->join('yh y','a.yhid=yh.yhid')
        //             ->where('y.yqm='.$yqm.' and jylx=3 and y.status=0 and a.game_id=6 and a.Jysj>'.$start.' and a.Jysj<'.$end)
        //             ->sum('a.jyje');

        // echo '日期: 2018-10-16~~2018-11-15<br>';
        // echo '总投注:'.$nine_order_money."<br>";  
        // echo '篮球和足球总投注:'.$nba_fb_order_money;          
        $where = ' and status=0 and id != 173';
    	$yh1 = db('yh')->where('pid='.$user_id.$where)->column('id');
    	$data[] = $yh1;
    	for ($i=0; $i < count($yh1); $i++) { 
    		$yh2 = db('yh')->where('pid='.$yh1[$i].$where)->column('id');
    		$data[] = $yh2;
    		if(!empty($yh2[$i])){
    			for ($j=0; $j < count($yh2[$i]); $j++) { 
    				$yh3 = db('yh')->where('pid='.$yh2[$j].$where)->column('id');
    				$data[] = $yh3;
    				if(!empty($yh3[$j])){
		    			for ($k=0; $k < count($yh3[$j]); $k++) { 
		    				$yh4 = db('yh')->where('pid='.$yh3[$k].$where)->column('id');
		    				$data[] = $yh4;
		    				if(!empty($yh4[$k])){
				    			for ($l=0; $l < count($yh4[$k]); $l++) { 
				    				$yh5 = db('yh')->where('pid='.$yh4[$k].$where)->column('id');
				    				$data[] = $yh5;
				    				if(!empty($yh5[$l])){
						    			for ($z=0; $z < count($yh5[$l]); $z++) { 
						    				$yh6 = db('yh')->where('pid='.$yh5[$l].$where)->column('id');
						    				$data[] = $yh6;
						    				if(!empty($yh6[$y])){
								    			for ($j=0; $j < count($yh6[$y]); $j++) { 
								    				$yh7 = db('yh')->where('pid='.$yh6[$y].$where)->column('id');
								    				$data[] = $yh7;
								    			}
								    		}
						    			}
						    		}
				    			}
				    		}
		    			}
		    		}
    			}
    		}
    	}
    	for ($i=0; $i < count($data); $i++) { 
    		if(!empty($data[$i])){
    			for ($j=0; $j < count($data[$i]); $j++) { 
    				if(!empty($data[$i][$j])){
    					$id_data[] = $data[$i][$j];
    				}
    			}	
    		}
    	}

    	if(!empty($id_data)){
	    	$id_data_str = implode(',', $id_data);
			$yhid = db('yh')->where('id in ('.$id_data_str.')')->column('yhid');

			$start = strtotime("2018-10-16");
			$end = strtotime("2018-11-15");
			$order_money = 0;
			foreach ($yhid as $key => $value) {
				$order_money += db('account_details')->where('jylx=3 and yhid="'.$value.'" and Jysj>'.$start.' and Jysj<'.$end)->sum('jyje');
			}
			echo '日期: 2018-10-16~~2018-11-15<br>';
	    	echo '除篮球和足球总投注:'.$order_money."<br>";
	    	$nba_order_money = db('nba_order')->where('user_id in ('.$id_data_str.') and add_time>'.$start.' and add_time<'.$end)->sum('order_money');
	    	$fb_order_money = db('fb_order')->where('user_id in ('.$id_data_str.') and add_time>'.$start.' and add_time<'.$end)->sum('order_money');
	    	$nba_fb = $nba_order_money+$fb_order_money;
	    	echo '篮球和足球总投注:'.$nba_fb;	
    	}else{
    		echo '没有数据';
    	}

    	
    }

    public function get_data($data, $id=0){
        $list = array();
        foreach($data as $v) {
            if($v['pid'] == $id) {
            	array_push($list, $v);
                $this->get_data($data, $v['id']);
            }
        }
        return $list;     
    }
}
