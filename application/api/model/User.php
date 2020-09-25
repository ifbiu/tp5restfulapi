<?php
namespace app\api\model;

use think\Model;

class User extends Model
{
    //自动写入时间戳
    protected $autoWriteTimestamp = true;
    //数据写入时，状态字段默认为1
    protected $insert = [
        'status' => 1,
    ];
}