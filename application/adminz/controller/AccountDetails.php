<?php
namespace app\adminz\controller;

class AccountDetails extends Base
{
    /**
     * 列表页面
     * @return [type] [description]
     */
    public function index($id=''){
        $map = "";
        $yhid = '';
        if(!empty($id)){
            $map = 'id='.$id;
            $yhid = db('yh')->where($map)->value('yhid');
        }
        
        $this->assign('id',$yhid);

        return view();
    }


    /**
     * 编辑记录
     * @param  integer $id [description]
     * @return [type]          [description]
     */
    public function edit($id = 0){
        if(IS_POST){

            $data = $_REQUEST;

            //删除非数据库字段
            unset($data['__cfduid']);
            unset($data['PHPSESSID']);
            unset($data['Hm_lvt_24b7d5cc1b26f24f256b6869b069278e']);
            unset($data['cf_use_ob']);
            unset($data['Hm_lpvt_24b7d5cc1b26f24f256b6869b069278e']);

            $flag = db("account_details")->update($data);
            if($flag || $flag === 0){
                $this->success("保存成功");
            }
            $this->error("保存失败");
        }
        
        $data = db('account_details')->where("id=".$id)->find();
        $this->assign("data",$data);
        return view();
    }

    /**
     * 获取数据
     * @param  integer $position_id [description]
     * @return [type]               [description]
     */
    public function get_account_details_list(){
        $map = '1=1';
        $where = '';

        $data = $_REQUEST;

        if(!empty($data['Sjhm'])){
            $yhid = db('yh')->where('Sjhm like "%'.phone_encrypt($data['Sjhm']).'%"')->value('yhid');
            $map .= ' and yhid = "'.$yhid.'"';
            $where .= ' and yhid = "'.$yhid.'"';
        }
        if($data['cate']>0){
            $map .= ' and Jylx='.$data['cate'];
        }
        if($data['pay_cate']>0){
            $map .= ' and Zfywc='.$data['pay_cate'];
        }
        if($data['tx_cate']>0){
            $map .= ' and present_status='.$data['tx_cate'];
        }
        if(!empty($data['add_time'])){
            $map .= ' and Jysj>='.strtotime($data['add_time']);
            $where .= ' and Jysj>='.strtotime($data['add_time']);
        }
        if(!empty($data['end_time'])){
            $map .= ' and Jysj<='.strtotime($data['end_time']);
            $where .= ' and Jysj<='.strtotime($data['end_time']);
        }

        if(!empty($data['end_time'])){
            $map .= ' and Jysj<='.strtotime($data['end_time']);
            $where .= ' and Jysj<='.strtotime($data['end_time']);
        }

        if(!empty($data['yhid'])){
            $statistics['child_tz_money'] = 0;
            $id = db('yh')->where('yhid="'.$data['yhid'].'"')->value('id');
            $id_arr = db('yh')->where('pid='.$id)->column('yhid');
            foreach ($id_arr as $key => $value) {
                $statistics['child_tz_money'] += db('account_details')->where('yhid = "'.$value.'" and Jylx=3'.$where)->sum('jyje');
            }

            $map .= ' and yhid like "%'.$data['yhid'].'%"';
            $where .= ' and yhid like "%'.$data['yhid'].'%"';

            $this->assign("id",$data['yhid']);
        }

        if(!empty($data['id'])){
            $statistics['child_tz_money'] = 0;
            $id = db('yh')->where('yhid="'.$data['id'].'"')->value('id');
            $id_arr = db('yh')->where('pid='.$id)->column('yhid');
            foreach ($id_arr as $key => $value) {
                $statistics['child_tz_money'] += db('account_details')->where('yhid = "'.$value.'" and Jylx=3'.$where)->sum('jyje');
            }

            $map .= ' and yhid like "%'.$data['id'].'%"';
            $where .= ' and yhid like "%'.$data['id'].'%"';
            
            $this->assign("id",$data['id']);
        }
        
        $user_id_arr = db('yh')->where('status=1')->column('yhid');
        foreach ($user_id_arr as $key => $value) {
            $where .= ' and yhid != "'.$user_id_arr[$key].'"';
            $map .= ' and yhid != "'.$user_id_arr[$key].'"';
        }
        
        $statistics['cz_money'] = db('account_details')->where('Jylx=1 and Zfywc=1'.$where)->sum('jyje');
        $statistics['tx_money'] = db('account_details')->where('Jylx=2 and present_status=3'.$where)->sum('jyje');
        $statistics['tz_money'] = db('account_details')->where('Jylx=3'.$where)->sum('jyje');
        $statistics['win_money'] = db('account_details')->where('Jylx=4'.$where)->sum('jyje');
        $statistics['fx_money'] = db('account_details')->where('Jylx=5'.$where)->sum('jyje');
        $statistics['tzjl_money'] = db('account_details')->where('Jylx=6'.$where)->sum('jyje');

        $count = db("account_details")->where($map)->order('id desc')->count();
        $list = db("account_details")->where($map)->order('id desc')->paginate(20,$count);

        //获取分页
        $page = $list->render();
        //遍历数据
        $list->each(function($item,$key){
            if($item['Jylx'] == 1 || $item['Jylx'] == 7){
                $item['zf_status'] = db('code_info')->where('code_info_id=19 and dxhzy='.$item['Zfywc'])->value('code_name');
            }
            $item['status'] = $item['Jylx'];
            $item['Jylx'] = db('code_info')->where('code_info_id=17 and dxhzy='.$item['Jylx'])->value('code_name');
            $item['p_status'] = db('code_info')->where('code_info_id=32 and dxhzy='.$item['present_status'])->value('code_name');
            $item['phone'] = phone_decode(db('yh')->where('yhid="'.$item['yhid'].'"')->value('Sjhm'));

            return $item;
        });
        // dump($list);die;
        $this->assign("page",$page);
        $this->assign("_list",$list);
        $this->assign("statistics",$statistics);
        $html = $this->fetch("tpl/account_details_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    /**
     * 编辑指定字段
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function account_details_edit_field($id = 0){
        //模块化更新
        // $flag = model('article')->allowField(true)->save($_REQUEST,['article_id'=>$id]);
        $data = $_REQUEST;
        //删除非数据库字段
        unset($data['id']);
        $data['id'] = $id;
        $flag = db("account_details")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }

    /**
     * 删除数据
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function account_details_delete($id = 0){
        $map = array();
        $map['id'] = $id;
        $flag = db('account_details')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }
}
