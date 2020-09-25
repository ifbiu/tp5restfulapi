<?php
namespace app\api\controller;

use think\Controller;
use think\Request;

class Api extends Controller {
    protected $timeout_second = 60;
    private $sign_key = 'ThinkPHP5';
    /**
     * 获取系统时间
     */
    public function getSysTime()
    {
        return $this->return_msg(200 , '' , ['time'=>time()]);
    }
    public function _initialize()
    {
        parent::_initialize();
        //获取请求对象
        $this->request = Request::instance();
        //是否开启API权限验证（方便测试）
        if (config('api_auth')){
            //验证时间戳是否超时
            $this->check_time($this->request->only(['time']));
            //验证签名是否正确
            $this->check_sign($this->request->param());
        }
    }
    public function check_sign($param)
    {
        if (!isset($param['sign']) || !$param['sign'])
        {
            $this->return_msg(400,"签名不能为空！");
        }
        if ($param['sign'] !== $this->buildSign($param))
        {
            $this->return_msg(400,"签名错误!");
        }
    }
    public function check_time($arr)
    {
        if(!isset($arr['time']) || intval($arr['time']) <= 1){
            $this->return_msg(400,'时间戳错误');
        }
        if (time() - intval($arr['time']) > $this->timeout_second){
            $this->return_msg(400,'请求错误');
        }
    }
    public function buildSign($param)
    {
        unset($param['sign']);  //sign字段不需要加入签名算法
        unset($param['time']);
        ksort($param);  //键值对的key按照升序排序
        $str = implode("",$param);  //请求参数值拼接成字符串
        $sign = md5(md5($str).$this->sign_key); //执行加密
        return $sign;
    }
    public function return_msg($code = "200",$message = "",$data = [])
    {
        header("Content-Type:application/json");    //设置返回类型
        http_response_code($code);  //设置返回头部
        $return['code'] = $code;
        $return ['message'] = $message;
        if (!empty($data))
        {
            $return['data'] = $data;
        }
        exit(json_encode($return,JSON_UNESCAPED_UNICODE));
    }




}
