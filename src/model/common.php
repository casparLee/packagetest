<?php
namespace  Caspar\Packagetest\model;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/28
 * Time: 17:07
 */

class common{
    public static function Is_empty($array){
        if(is_array($array)&&count($array)>0){
            return $array;
        }else{
            return [];
        }
    }

    // 公用控制器ajax返回
    public  static  function ajaxReturn($code = '1',$msg = '')
    {
        exit(json_encode(['code'=>$code,'msg'=>$msg]));
        return;
    }

}