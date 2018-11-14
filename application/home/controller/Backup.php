<?php
namespace app\home\controller;

use think\Db;

/*
*每天00:00将数据备份并清空表
*明细表--开奖表--订单表--订单明细表
*
*/
class Backup extends Base
{
    public function backup()
    {
        Vendor('PHPExcel/PHPExcelApi');

        $PHPExcel = new \PHPExcelApi();

        $dxh_kj = db('dxh_kj')->select();
        if(!empty($dxh_kj)){
            foreach ($dxh_kj[0] as $key => $value) {
                $dxh_kj_key[] = $key;
            }
            
            $time = date('YmdHis',time());
            $PHPExcel->setFileName('dxh_kj('.$time.')');
            $PHPExcel->setHeader($dxh_kj_key);
            $PHPExcel->exportData($dxh_kj);
        }

        $dice_kj = db('dice_kj')->select();
        if(!empty($dice_kj)){
            foreach ($dice_kj[0] as $key => $value) {
                $dice_kj_key[] = $key;
            }
            
            $time = date('YmdHis',time());
            $PHPExcel->setFileName('dice_kj('.$time.')');
            $PHPExcel->setHeader($dice_kj_key);
            $PHPExcel->exportData($dice_kj);
        }

        $dial_kj = db('dial_kj')->select();
        if(!empty($dial_kj)){
            foreach ($dial_kj[0] as $key => $value) {
                $dial_kj_key[] = $key;
            }
            
            $time = date('YmdHis',time());
            $PHPExcel->setFileName('dial_kj('.$time.')');
            $PHPExcel->setHeader($dial_kj_key);
            $PHPExcel->exportData($dial_kj);
        }


        $nine_kj = db('nine_kj')->select();
        if(!empty($nine_kj)){
            foreach ($nine_kj[0] as $key => $value) {
                $nine_kj_key[] = $key;
            }
            
            $time = date('YmdHis',time());
            $PHPExcel->setFileName('nine_kj('.$time.')');
            $PHPExcel->setHeader($nine_kj_key);
            $PHPExcel->exportData($nine_kj);
        }


        $order = db('order')->select();
        if(!empty($order)){
            foreach ($order[0] as $key => $value) {
                $order_key[] = $key;
            }
            
            $time = date('YmdHis',time());
            $PHPExcel->setFileName('order('.$time.')');
            $PHPExcel->setHeader($order_key);
            $PHPExcel->exportData($order);
        }


        $order_info = db('order_info')->select();
        if(!empty($order_info)){
            foreach ($order_info[0] as $key => $value) {
                $order_info_key[] = $key;
            }
            
            $time = date('YmdHis',time());
            $PHPExcel->setFileName('order_info('.$time.')');
            $PHPExcel->setHeader($order_info_key);
            $PHPExcel->exportData($order_info);
        }

        // $account_details = db('account_details')->select();
     //    if(!empty($account_details)){
     //     foreach ($account_details[0] as $key => $value) {
     //         $account_details_key[] = $key;
     //     }
            
     //     $time = date('YmdHis',time());
     //     $PHPExcel->setFileName('account_details('.$time.')');
     //     $PHPExcel->setHeader($account_details_key);
     //     $PHPExcel->exportData($account_details);
     //    }

        // 清空表
        // Db::execute('TRUNCATE table `dxh_kj`');
        // Db::execute('TRUNCATE table `dice_kj`');
        // Db::execute('TRUNCATE table `dial_kj`');
        // Db::execute('TRUNCATE table `nine_kj`');
        // Db::execute('TRUNCATE table `order`');
        // Db::execute('TRUNCATE table `order_info`');
        // Db::execute('TRUNCATE table `account_details`');
        echo json_encode(['msg'=>'备份成功','code'=>1,'success'=>true]);
        exit;
    }
}
