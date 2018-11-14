<?php
namespace app\adminz\controller;
use think\Controller;
use think\Cache;
use think\Db;

class Base extends Controller
{
	public $adminInfo;
    public function _initialize()
    {
    	$this->assign('names', array(
            'controllerName' => strtolower(CONTROLLER_NAME),
            'actionName' => strtolower(ACTION_NAME),
        ));
        
        $this->adminInfo = session(config('admin_site.session_name'));
        if(!in_array(strtolower(CONTROLLER_NAME),array('base','login'))){
            if(!$this->adminInfo){
                $this->redirect('login/index');
                exit;
            }
            
            // 提现申请数量
            $menu_list = $this->get_menu_list();
            foreach ($menu_list as $key => $value) {
                if($menu_list[$key]['controller'] == 'tixian'){
                   $count = db('tix')->where('Txzt=1')->count();
                   if($count > 0){
                        $menu_list[$key]['count'] = db('tix')->where('Txzt=1')->count();
                   }
                   
                }
            }
            // 提现申请数量END
            // dump($menu_list);die;
            //获取左侧菜单
            $this->assign('menu_list',$menu_list);
            //验证权限
            $this->auto_auth();
        }
        $this->assign('myInfo',$this->adminInfo);
        // dump($this->get_menu_list());
        $config = cache("admin_config");
        if($config==null){
            $map = array();
            // $map['group'] = 'admin';
            // $conf = db('config')->where($map)->select();
            $conf = db('config')->select();
            $config = array();
            foreach($conf as $value){
                $config[$value['key']]=$value['value'];            
            }
            cache("admin_config",$config);
        }
        // dump($config);die;
        $this->assign('site',$config);
    }

    //清除Temp缓存
    public function clear_temp($flag = false){
        Cache::clear(); 
        if(IS_AJAX && $flag){
            $this->success("缓存清除成功");
        }
        return true;
    }

    //验证权限
    public function auto_auth(){
        $controller = strtolower(CONTROLLER_NAME);
        $action = strtolower(ACTION_NAME);

        if($controller == 'index' && $action == 'index'){
            return true;
        }

        //不需要授权的方法
        $actions = array('%s_edit_field','get_%s_list');
        $acts = explode("_",$action);
        if(count($acts)>=3){
            foreach ($actions as $key => $value) {
                $action2 = sprintf($value,$acts[0]);
                $action3 = sprintf($value,$acts[1]);
                $action4 = sprintf($value,$acts[2]);
                if(($action == $action2) || ($action == $action3) || ($action == $action4))
                    return true;
            }
        }

        if($this->adminInfo['role_id'] != 1){
            if(!in_array(strtolower(CONTROLLER_NAME), array('base','login','index'))){
                $role_auth = db('role_auth')->where('role_id='.$this->adminInfo['role_id'])->column('authority_code');
                foreach ($role_auth as $key => $value) {
                    $role_auth[$key] = str_replace('_', '', $role_auth[$key]);
                }
                if(!in_array(strtolower(CONTROLLER_NAME), $role_auth)){
                    $this->error('抱歉您没有操作权限');
                }
            }
        }

        // if($this->adminInfo['role_id'] != config("admin_site.root_role")){
        //     $menu_list = cache("menu_list_".$this->adminInfo['role_id']);
        //     $authority_ids = array();
        //     foreach ($menu_list as $key => $value) {
        //         $authority_ids[] = $value['authority_id'];
        //         foreach ($value['menu_list'] as $k => $v) {
        //             $authority_ids[] = $v['authority_id'];
        //         }
        //     }
        //     //获取当前节点数据
        //     $authority_id = db("Node")->where("controller='".$controller."' and action='".$action."'")->value("authority_id");
        //     if(!in_array($authority_id, $authority_ids)){
        //         if(IS_AJAX){
        //             $this->ajaxReturn(array("info"=>'抱歉您没有操作权限','code'=>1));
        //         }
        //         return view('Tpl/auth');
        //     }
        // }
    }

    /**
     * 获取左侧导航
     * @return [type] [description]
     */
    private function get_menu_list(){
        // if($this->adminInfo['role_id']<=0)
        //     return array();
        $menu_list = cache("menu_list_".$this->adminInfo['role_id']);
        if(!$menu_list){
            //超级管理员权限
            if($this->adminInfo['role_id'] == config("admin_site.root_role")){
                $menu_list = db("Node")->where("pid=0 and is_menu=1")->order('sort_order desc')->select();
                foreach ($menu_list as $key => $value) {
                    $menu_list[$key]['menu_list'] = db("Node")->where("pid=".$value['node_id']." and is_menu=1")->order('sort_order desc')->select();

                    $menu_list2 = db("Node")->field('controller')->where("pid=".$value['node_id']." and is_menu=1")->order('sort_order desc')->group('controller')->select();
                    $controllers = array();
                    foreach ($menu_list2 as $k => $v) {
                        $controllers[] = $v['controller'];
                    }
                    $menu_list[$key]['controllers'] = $controllers;
                }
            }
            //限定权限
            else{
                $auth_list = db("RoleAuth")->field('authority_id')->where("is_enable=1 and role_id=".$this->adminInfo['role_id'])->select();
                $auth_ids = array();
                foreach ($auth_list as $key => $value) {
                    $auth_ids[] = $value['authority_id'];
                }
                $menu_list = db("Node")->where("pid=0 and is_menu=1 and authority_id in (".implode(',',$auth_ids).")")->order('sort_order desc')->select();
                foreach ($menu_list as $key => $value) {
                    $menu_list[$key]['menu_list'] = db("Node")->where("pid=".$value['node_id']." and is_menu=1 and authority_id in (".implode(',',$auth_ids).")")->order('sort_order desc')->select();

                    $menu_list2 = db("Node")->field('controller')->where("pid=".$value['node_id']." and is_menu=1 and authority_id in (".implode(',',$auth_ids).")")->order('sort_order desc')->group('controller')->select();
                    $controllers = array();
                    foreach ($menu_list2 as $k => $v) {
                        $controllers[] = $v['controller'];
                    }
                    $menu_list[$key]['controllers'] = $controllers;
                }
            }
            cache("menu_list_".$this->adminInfo['role_id'], $menu_list);
        }
        return $menu_list;
    }

    /**
     * 富编辑图片上传
     * @return [type] [description]
     */
    public function editorUpload(){
        $file = request()->file('imgFile');
        if($file){
            $fileInfo = $file->move(config('uploads_path.path').DS.'kindeditor');
            if($fileInfo){
                $info = array();
                $info['url'] = config('uploads_path.uploads').'/kindeditor/'.$fileInfo->getSaveName();
                $info['alt'] = 'alt';
                $info['message'] = '上传成功！';
                $info['info'] = '上传成功！';
                $info['error'] = 0;
                $info['status'] = 1;
                $this->ajaxReturn($info);
            }else{
                $info = array();
                $info['message'] = '上传失败！'.$file->getError();
                $info['info'] = '上传失败！'.$file->getError();
                $info['error'] = 1;
                $info['status'] = 0;
                $this->ajaxReturn($info);
            }
        }else{
            $this->error("请选择上传的图片");
        }
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
