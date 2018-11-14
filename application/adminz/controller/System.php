<?php
namespace app\adminz\controller;

class System extends Base
{
    /**
     * 列表页面
     * @return [type] [description]
     */
    public function index(){
    	
        return view();
    }

    public function site(){
       if(IS_POST){
            $request = $_REQUEST;

            $keys = array_keys($request);
            foreach ($keys as $key) {
                $map = array();
                $map['key'] = $key;
                $data = array();
                $data['value'] = $request[$key];
                db("Config")->where($map)->update($data);
            }

            $file = request()->file('logo_img');
            $data = array();
            $map = array();
            $map['key'] = 'logo';
            if($file){
                $info = $file->move(config('uploads_path.path').DS.'logo');
                if($info){
                    $data['value'] = $info->getSaveName();
                    db('Config')->where($map)->update($data);
                }else{
                    $this->error($file->getError());
                }
            }

            cache('web_config',null);
            $this->success("保存成功"); 
        }
        $map = array();
        $map['group'] = array('in',array('logo','seo','copyright','technologt','record'));
        $conf = db("Config")->field('key,value')->where($map)->select();
        $config=array();
        foreach($conf as $value){
            $config[$value['key']]=$value['value'];            
        }
        // dump($conf);
        $this->assign('config',$config);
        
        return view();
    }

    public function admin_site(){
       if(IS_POST){
            $title =  input('title');

            $data = array();
            $map = array();
            $map['key'] = 'admin_title';
            $data['value'] = $title;
            $save = db("Config")->where($map)->update($data);
            if($save || $save === 0)
                $this->success('保存成功');
            $this->error('保存失败');
        }
        $map = array();
        $map['group'] = 'admin';
        $conf = db("Config")->field('key,value')->where($map)->select();
        $config = array();
        foreach($conf as $value){
            $config[$value['key']]=$value['value'];            
        }
        $this->assign('config',$config);
        return view();
    }

    // 赔率设置
    public function conf(){
        if(IS_POST){
            $request = $_REQUEST;

            $keys = array_keys($request);
            foreach ($keys as $key) {
                $map = array();
                $map['key'] = $key;
                $data = array();
                $data['value'] = $request[$key];
                $save = db("conf_dxh")->where($map)->update($data);
            }
            if($save || $save === 0)
                $this->success('保存成功');
            $this->error('保存失败');
        }
        $map = array();
        $map['group'] = 'odds';
        $odds = db("conf_dxh")->field('key,value')->select();
        $data = array();
        foreach($odds as $value){
            $data[$value['key']]=$value['value'];            
        }
        $this->assign('data',$data);
        return view();        
    }
}
