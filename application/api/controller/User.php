<?php

namespace app\api\controller;

use app\api\model\User as UserModel;
use think\Request;

class User extends Api
{
    //显示用户列表
    public function index()
    {
        $list = UserModel::all();
        return  $this->return_msg(200,'',$list);
    }
    //保护用户信息
    public function save(Request $request)
    {
        $request = UserModel::create($request->param());
        $this->return_msg(200,'',$request);
    }
    //根据ID获取用户信息
    public function read($id)
    {
        //根据ID查询单条记录
        $this->return_msg(200,'',UserModel::get($id));
    }
    //保存更新资源
    public function update(Request $request,$id)
    {
        //根据ID和数据更新用户信息
        $result = UserModel::update($request->param(),['id'=>$id]);
        return $this->return_msg(200,'',$result);
    }
    //根据用户ID删除记录
    public function delete($id)
    {
        //根据ID删除一条记录
        return $this ->return_msg(200,'删除成功',UserModel::destroy($id));
    }
}
