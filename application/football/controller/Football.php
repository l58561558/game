<?php
namespace app\football\controller;
use app\football\model\Group;
use think\Db;
use app\home\controller\Base; 

// fb比赛竞猜
class Football extends Base
{
    // 竞猜列表
    public function index()
    {
        $data = db('fb_game')->where('end_time>='.time())->order('end_time')->select();

        if(!empty($data)){
            foreach ($data as $key => $value) {
                $id = $data[$key]['id'];
                $data[$key]['date'] = date('Y-m-d',$data[$key]['add_time']);
                $data[$key]['end_time'] = date('Y-m-d H:i:s',$data[$key]['end_time']);

                $data[$key]['tz'] = db('fb_game_cate')->where('game_id='.$id)->limit(6)->select();
                $data[$key]['tz_result'][] = $data[$key]['tz'];
                $data[$key]['tz_result'][] = db('fb_game_cate')->where('game_id='.$id)->limit(6,31)->select();
                $data[$key]['tz_result'][] = db('fb_game_cate')->where('game_id='.$id)->limit(37,8)->select();
                $data[$key]['tz_result'][] = db('fb_game_cate')->where('game_id='.$id)->limit(45,9)->select();

                // $code = db("fb_code")->where('code_pid=0')->select();
                // foreach ($code as $k => $v) {
                //     $tz_data[] = db("fb_code")->where('code_pid='.$code[$k]['id'])->select();
                // }
                // if(isset($tz_data[1])){
                //     $tz_data[0] = array_merge($tz_data[0],$tz_data[1]);
                //     unset($tz_data[1]);
                // }

                // $fb_code = array_values($tz_data);

                // for ($i=0; $i < count($fb_code); $i++) {
                //     for ($j=0; $j < count($fb_code[$i]); $j++) {

                //         if(isset($fb_code[$i][$j]['code'])){
                //             $fb_cate[$i][$j] = db("fb_game_cate")->where('game_id='.$id.' and cate_code="'.$fb_code[$i][$j]['code'].'"')->find();
                //         }else{
                //             $fb_cate[$i][$j] = $fb_code[$i][$j];
                //         }
                //     }
                // }
                // $data[$key]['tz'] = $fb_cate[0];
                // $data[$key]['tz_result'] = $fb_cate;
                // unset($tz_data);
            }
            $count = count($data);
            $game_data = array();
            for ($i=0; $i < $count; $i++) {
                if(isset($data[$i]) && empty($game_data[$i])){
                    $game_data[$i][] = $data[$i];
                }
                for ($j=$i+1; $j < $count; $j++) {
                    if(isset($data[$i])){
                        if(isset($data[$j])){
                            if($data[$i]['date'] == $data[$j]['date']){
                                $game_data[$i][] = $data[$j];
                                unset($data[$j]);
                            }else{
                                continue;
                            }
                        }
                    }else{
                        continue;
                    }
                }
            }
            $game_data = array_merge($game_data);
            echo json_encode(['msg'=>$game_data,'code'=>1,'success'=>true]);
            exit;
        }else{
            echo json_encode(['msg'=>'暂无比赛','code'=>201,'success'=>false]);
            exit;
        }
        
    }

    // 生成token
    public function token()
    {
        $token = md5(uniqid());
        // session_start();
        session('fb_token',$token);   
        echo json_encode(['msg'=>'请求成功','code'=>1,'token'=>$token,'success'=>true]);
    }

    /*
    * multiple  (string)    倍数
    * chuan     (string)    串法
    * token     (string)    标识
    * tz        (array多维数组)     投注内容
    [
        'game_id' => 1, (游戏场次ID)
        'tz_result' => ['1','2','3','4'], (投注选项ID | 数组)

    ],
    [
        'game_id' => 2, (游戏场次ID)
        'tz_result' => ['5','6','7'], (投注选项ID | 数组)
    ],
    [
        'game_id' => 3, (游戏场次ID)
        'tz_result' => ['8','9','10'], (投注选项ID | 数组)
    ],
    .       .       .       .       .       .
    .       .       .       .       .       .
    .       .       .       .       .       .
    */
    public function add_order()
    {
        $data = $_REQUEST;
        if(empty($data['chuan'])){
            echo json_encode(['msg'=>'请选择串法','code'=>203,'success'=>false]);
            exit;
        }

        $chaun_data = explode(',', $data['chuan']);
        $tz_data = json_decode($data['tz'],true);

        $token = session('fb_token');
        session('fb_token',null);

        $yh = db('yh')->where('id='.USER_ID)->find();

        if(USER_ID <= 0){
            echo json_encode(['msg'=>'请登录','code'=>201,'success'=>false]);
            exit;
        }
        if($data['token'] != $token){
            echo json_encode(['msg'=>'数据错误,请勿重复提交','code'=>202,'success'=>false]);
            exit; 
        }
              

        foreach ($tz_data as $ke => $val) {
            $fb_game = db('fb_game')->where('id='.$tz_data[$ke]['game_id'])->find();
            if($fb_game['end_time'] <= time()){
                echo json_encode(['msg'=>'投注失败','code'=>206,'success'=>false]);
                exit;
            }
        }

        $count = count($tz_data);
        $touz = array();
        for ($i=0; $i < $count; $i++) { 
            if(isset($tz_data[$i]) && empty($touz[$i])){
                $touz[$i][] = $tz_data[$i]['tz_result'];
                $tz[$i]['game_id'] = $tz_data[$i]['game_id'];
                $tz[$i]['tz_result'][] = $tz_data[$i]['tz_result'];
            }
            for ($j=$i+1; $j < $count; $j++) { 
                if(isset($tz_data[$i])){
                    if(isset($tz_data[$j])){
                        if($tz_data[$i]['game_id'] == $tz_data[$j]['game_id']){ 
                            $touz[$i][] = $tz_data[$j]['tz_result']; 
                            $tz[$i]['game_id'] = $tz_data[$j]['game_id'];
                            $tz[$i]['tz_result'][] = $tz_data[$j]['tz_result'];
                            unset($tz_data[$j]);
                        }else{
                            continue;
                        }    
                    }   
                }else{
                    continue;
                }
            }
        }

        $touz = array_merge($touz);
        $tz = array_merge($tz);

        $Group = new Group();
        foreach ($chaun_data as $key => $value) {
            $group[] = $Group->order_group($chaun_data[$key],$touz,1);  
        }
        foreach ($group as $key => $value) {
            foreach ($group[$key] as $k => $v) {
                $group_data[] = $group[$key][$k];
            }
        }
        $tz_num = count($group_data);

        $order_money = $tz_num*$data['multiple']*2;

        if($order_money > $yh['balance']+$yh['no_balance']){
            echo json_encode(['msg'=>'投注金额超出可用金额','code'=>204,'success'=>false]);
            exit;  
        } 

        $order['user_id'] = $yh['id'];
        $order['add_time'] = time();
        $order['multiple'] = $data['multiple'];
        $order['chuan'] = $data['chuan'];
        $order['is_win'] = 0;
        $order_id = db('fb_order')->insert($order,false,true);
        foreach ($tz as $key => $value) {
            $order_info[$key]['order_id'] = $order_id;
            $order_info[$key]['game_id'] = $tz[$key]['game_id'];
            $order_info[$key]['tz_result'] = is_array($tz[$key]['tz_result'])?implode(',', $tz[$key]['tz_result']):$tz[$key]['tz_result'];
            $order_info[$key]['game_status'] = 0;
            $order_info[$key]['add_time'] = time();
        }
        $fb_order_info = db('fb_order_info')->insertAll($order_info);

        $order_group = array();

        foreach ($group_data as $key => $value) {
            $order_group[$key]['order_id'] = $order_id;
            $order_group[$key]['group_res'] = $group_data[$key];
            $order_group[$key]['status'] = 0;
        }
        $fb_order_group = db('fb_order_group')->insertAll($order_group);

        

        $order_no = date('YmdHis',time()).'FB'.$order_id;
        $res = db('fb_order')->where('order_id='.$order_id)->update(array('tz_num'=>$tz_num,'order_money'=>$order_money,'order_no'=>$order_no));

        if($res) {
            if($order_money >= $yh['no_balance']){
                db('yh')->where('id='.$yh['id'])->setField('no_balance',0);
                $residue = $order_money - $yh['no_balance'];
            }else{
                db('yh')->where('id='.$yh['id'])->setDec('no_balance',$order_money);
                $residue = 0;
            }
            db('yh')->where('id='.$yh['id'])->setDec('balance',$residue);
            db('yh')->where('id='.$yh['id'])->setDec('amount_money',$order_money);
            $balance = db('yh')->where('id='.$yh['id'])->value('balance');
            /**添加账单明细**/
            $detail['yhid'] = $yh['yhid'];
            $detail['Jylx'] = 3;
            $detail['jyje'] = $order_money;
            $detail['new_money'] = $balance;
            $detail['Jysj'] = time();
            $detail['Srhzc'] = 2;
            $detail['game_id'] = 6;
            $detail_res = db('account_details')->insert($detail,false,true);
            /**添加账单明细end**/
            // db('yh')->where('id='.$yh['id'])->setDec('balance',$order_money);
            // db('yh')->where('id='.$yh['id'])->setDec('amount_money',$order_money); 

            echo json_encode(['msg'=>'投注成功','code'=>1,'success'=>true]);
            exit;
        }else{
            echo json_encode(['msg'=>'投注失败','code'=>205,'success'=>true]);
            exit;
        }

    }
    /*
    * multiple  (string)    倍数
    * chuan     (string)    串法
    * tz        (array多维数组)     投注内容
    [
        'game_id' => 1, (游戏场次ID)
        'tz_result' => ['1','2','3','4'], (投注选项ID | 数组)

    ],
    [
        'game_id' => 2, (游戏场次ID)
        'tz_result' => ['5','6','7'], (投注选项ID | 数组)
    ],
    [
        'game_id' => 3, (游戏场次ID)
        'tz_result' => ['8','9','10'], (投注选项ID | 数组)
    ],
    .       .       .       .       .       .
    .       .       .       .       .       .
    .       .       .       .       .       .
    */
    // 获取注数
    public function get_tz()
    {
        $data = $_REQUEST;

        $chaun_data = explode(',', $data['chuan']);
        if(empty($chaun_data)){
            $chaun_data = 1;
        }
        $tz_data = json_decode($data['tz'],true);

        if(empty($tz_data)){
            echo json_encode(['msg'=>'投注失败,请选择投注选项','code'=>201,'success'=>false]);
            exit;
        }
        foreach ($tz_data as $ke => $val) {
            $fb_game = db('fb_game')->where('id='.$tz_data[$ke]['game_id'])->find();
            if($fb_game['end_time'] <= time()){
                echo json_encode(['msg'=>'投注失败,请选择投注选项','code'=>202,'success'=>false]);
                exit;
            }
        }
        if(empty($data['chuan'])){
            echo json_encode(['msg'=>'请选择串法','code'=>203,'success'=>false]);
            exit;
        }

        $count = count($tz_data);

        $touz = array();
        for ($i=0; $i < $count; $i++) { 
            if(isset($tz_data[$i]) && empty($touz[$i])){
                $touz[$i][] = $tz_data[$i]['tz_result'];
                $tz[$i]['game_id'] = $tz_data[$i]['game_id'];
                $tz[$i]['tz_result'][] = $tz_data[$i]['tz_result'];
            }
            for ($j=$i+1; $j < $count; $j++) { 
                if(isset($tz_data[$i])){  
                    if(isset($tz_data[$j])){
                        if($tz_data[$i]['game_id'] == $tz_data[$j]['game_id']){           
                            $touz[$i][] = $tz_data[$j]['tz_result']; 
                            $tz[$i]['game_id'] = $tz_data[$j]['game_id'];
                            $tz[$i]['tz_result'][] = $tz_data[$j]['tz_result'];
                            unset($tz_data[$j]);
                        }else{
                            continue;
                        }    
                    }
                }else{
                    continue;
                }
            }
        }

        $touz = array_merge($touz);
        $tz = array_merge($tz);

        $Group = new Group();
        $group_count = 0;
        foreach ($chaun_data as $key => $value) {
            $group_count += $Group->order_group($chaun_data[$key],$touz,2);  
        }
        
        // $msg['tz_num'] = $group_count*$data['multiple'];
        $msg['tz_num'] = $group_count;
        $msg['order_money'] = $group_count*$data['multiple']*2;
        echo json_encode(['msg'=>$msg,'code'=>1,'success'=>true]);
        exit;
    }


    // 订单列表
    public function order()
    {
        $list = db('fb_order')->where('user_id='.USER_ID)->order('add_time desc')->select();
        foreach ($list as $key => $value) {
            $list[$key]['game_name'] = '競彩足球';
            $list[$key]['add_time'] = date('Y-m-d',$list[$key]['add_time']);
        }

        echo json_encode(['msg'=>$list,'code'=>1,'success'=>true]);
        exit;
    }

    // 订单详情
    public function order_info($order_id=1)
    {
        $order = db('fb_order')->where('order_id='.$order_id)->order('add_time desc')->find();
        $data['order_money'] = $order['order_money'];
        $data['win_money'] = $order['win_money'];
        $data['order_no'] = $order['order_no'];
        $data['add_time'] = date('Y-m-d H:i:s' ,$order['add_time']);
        $order_info = db('fb_order_info')->where('order_id='.$order_id)->select();
        $data['game_num'] = count($order_info);
        if(strpos($order['chuan'], ',') === false){
            $data['chuan'] = db('fb_chuan')->where('chuan_id='.$order['chuan'])->value('chuan_name');;
        }else{
            $chuan = explode(',', $order['chuan']);
            foreach ($chuan as $key => $value) {
                $chuann[] = db('fb_chuan')->where('chuan_id='.$chuan[$key])->value('chuan_name');
            }
            $data['chuan'] = implode(',', $chuann);
        }
        
        $data['multiple'] = $order['multiple'];
        $data['is_win'] = $order['is_win'];

        $arr = array('let_score_home_win','let_score_home_eq','let_score_home_lose');

        foreach ($order_info as $key => $value) {
            $game[$key] = db('fb_game')->where('id='.$order_info[$key]['game_id'])->find();
            $data['order_info'][$key]['week'] = $game[$key]['week'];
            $data['order_info'][$key]['game_no'] = $game[$key]['game_no'];
            $data['order_info'][$key]['down_score'] = $game[$key]['down_score'];
            if(!empty($game[$key]['down_score'])){
                $data['order_info'][$key]['home_score'] = explode(':', $game[$key]['down_score'])[0];
                $data['order_info'][$key]['road_score'] = explode(':', $game[$key]['down_score'])[1];
            }
            if(is_numeric($game[$key]['home_team']) && is_numeric($game[$key]['road_team'])){
                $data['order_info'][$key]['home_team'] = db('fb_team')->where('team_id='.$game[$key]['home_team'])->value('team_name');
                $data['order_info'][$key]['road_team'] = db('fb_team')->where('team_id='.$game[$key]['road_team'])->value('team_name');
            }else{
                $data['order_info'][$key]['home_team'] = $game[$key]['home_team'];
                $data['order_info'][$key]['road_team'] = $game[$key]['road_team'];
            }
            $data['order_info'][$key]['win_result'] = [];

            // 投注
            if(strpos($order_info[$key]['tz_result'] , ',') === false){
                $ngc = db('fb_game_cate')->where('cate_id='.$order_info[$key]['tz_result'])->find();
                $ngc['attr'] = '';
                if($ngc['cate_code'] == 'let_score_home_win' || $ngc['cate_code'] == 'let_score_home_eq' || $ngc['cate_code'] == 'let_score_home_lose'){
                    $ngc['attr'] = db('fb_game')->where('id='.$ngc['game_id'])->value('let_score');
                }
                $data['order_info'][$key]['tz_result'][0] = $ngc;
            }else{
                if(!empty($order_info[$key]['tz_result'])){
                    $tz_result = explode(',', $order_info[$key]['tz_result']);
                    foreach ($tz_result as $ke => $val) {
                        $ngc = db('fb_game_cate')->where('cate_id='.$tz_result[$ke])->find();
                        $ngc['attr'] = '';
                        if($ngc['cate_code'] == 'let_score_home_win' || $ngc['cate_code'] == 'let_score_home_eq' || $ngc['cate_code'] == 'let_score_home_lose'){
                            $ngc['attr'] = db('fb_game')->where('id='.$ngc['game_id'])->value('let_score');
                        }
                        $tz_result[$ke] = $ngc;
                    }
                    $data['order_info'][$key]['tz_result'] = $tz_result;
                }
            } 

            // 彩果
            if(!empty($order_info[$key]['win_result'])){
                if(strpos($order_info[$key]['win_result'] , ',') === false){
                    $ngc = db('fb_game_cate')->where('cate_id='.$order_info[$key]['win_result'])->find();
                    $ngc['attr'] = '';
                    if($ngc['cate_code'] == 'let_score_home_win' || $ngc['cate_code'] == 'let_score_home_eq' || $ngc['cate_code'] == 'let_score_home_lose'){
                        $ngc['attr'] = db('fb_game')->where('id='.$ngc['game_id'])->value('let_score');
                    }
                    $data['order_info'][$key]['win_result'][0] = $ngc;
                }else{
                    $win_result = explode(',', $order_info[$key]['win_result']);
                    foreach ($win_result as $ke => $val) {
                        $ngc = db('fb_game_cate')->where('cate_id='.$win_result[$ke])->find();
                        $ngc['attr'] = '';
                        if($ngc['cate_code'] == 'let_score_home_win' || $ngc['cate_code'] == 'let_score_home_eq' || $ngc['cate_code'] == 'let_score_home_lose'){
                            $ngc['attr'] = db('fb_game')->where('id='.$ngc['game_id'])->value('let_score');
                        }
                        $win_result[$ke] = $ngc;
                    }
                    $data['order_info'][$key]['win_result'] = $win_result;                      
                }  
            } 
            // if(!empty($order_info[$key]['win_game_result'])){
            //     $win_game_result = explode(',', $order_info[$key]['win_game_result']);
            //     foreach ($win_game_result as $ke => $val) {
            //         $win_game_result[$ke] = db('fb_game_cate')->field('cate_name,cate_odds,is_win')->where('cate_id='.$win_game_result[$ke])->find();
            //     }
            //     $data['order_info'][$key]['win_result'] = $win_game_result;  
            // }
        }
        echo json_encode(['msg'=>$data,'code'=>1,'success'=>true]);
        exit;
    }

}
