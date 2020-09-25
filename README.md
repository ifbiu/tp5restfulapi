

详细见[毫小秋的博客-ThinkPHP5实现RESTfulAPI](https://ifbiu.com/article38/)

# 使用方法

首先配置httpd-vhosts-conf文件和本地hosts

我的本地文件目录在F:/www/demo/tp5restfulapi

```
<VirtualHost *:80>
  ServerName tp5restfulapi.com
  DocumentRoot "F:/www/demo/tp5restfulapi/public"
 <Directory "F:/www/demo/tp5restfulapi/public">
  </Directory>
</VirtualHost>
```

## 1、增加路由解析

在application\route.php追加

```php
Route::resource('users','api/User');   //注册一个资源路由，对应restful各个方法
```

## 2、构建数据表

创建数据库

```mysql
CREATE DATABASE tp5restfulapi;
```

创建user表

```mysql
use tp5restfulapi;
CREATE TABLE `user`  (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT '主键id，自增长',
  `mobile` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '手机号',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户姓名',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户邮箱',
  `status` tinyint(4) NOT NULL COMMENT '用户状态',
  `create_time` int(10) NOT NULL COMMENT '用户创建时间',
  `update_time` int(10) NOT NULL COMMENT '用户更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET=utf8;
```

![](https://candide.oss-cn-beijing.aliyuncs.com/ifbiu/20200925165831.png)

配置好database.php数据库信息

![](https://candide.oss-cn-beijing.aliyuncs.com/ifbiu/1601024563302-20200925170229.png)

## 3、构建验证器

在application\api\controller下新建Api.php

```php
<?php

namespace app\api\controller;

use think\Controller;
use think\Request;

class Api extends Controller
{

   // 执行加密key
   private $sign_key = 'thinkphp5';

   // 请求对象
   protected $request;

   // 默认接口的有效期为60秒
   protected $timeout_second = 60;


   // 获取系统时间
   public function getSysTime()
   {
      return $this->return_msg(200, '', ['time' => time()]);
   }

   // 初始化方法
   public function _initialize()
   {
      parent::_initialize();
      // 实例化请求对象
      $this->request = Request::instance();
      // 是否开启API权限验证（方便测试）
      if (config('api_auth')) {
         // 验证时间戳是否超时
         $this->check_time($this->request->only(['time']));
         // 验证签名是否正确
         $this->check_sign($this->request->param());
      }
   }


   // 验证请求签名是否正确

   public function check_sign($param)
   {
      if (!isset($param['sign']) || !$param['sign']) {
         $this->return_msg(400, '签名不能为空!');
      }
      if ($param['sign'] !== $this->buildSign($param)) {
         $this->return_msg(400, '签名错误!');
      }
   }


   // 检测接口是否超时
   public function check_time($arr)
   {
      if (!isset($arr['time']) || intval($arr['time']) <= 1) {
         $this->return_msg(400, '时间戳错误!');
      }
      if (time() - intval($arr['time']) > $this->timeout_second) {
         $this->return_msg(400, '请求超时!');
      }
   }


   // 构建请求签名
   public function buildSign($param)
   {
      unset($param['sign']);                  // sign字段不需要加入签名算法
      unset($param['time']);
      ksort($param);                          // 键值对的key按照升序排序
      $str = implode('', $param);         // 请求参数值拼接成字符串
      $sign = md5(md5($str) . $this->sign_key); // 执行加密
      return $sign;
   }

   // 数据返回
   public function return_msg($code = '200', $message = '', $data = [])
   {
      header('Content-Type: application/json');   // 设置返回类型
      http_response_code($code);                      // 设置返回头部
      $return['code'] = $code;
      $return['message'] = $message;
      if (!empty($data)) {
         $return['data'] = $data;
      }
      exit(json_encode($return, JSON_UNESCAPED_UNICODE));
   }
}

```

## 4、构建模型

在application\api\model下新建User.php

```php
<?php
namespace app\api\model;

use think\Model;

// 用户表自定义模型
class User extends Model
{
   // 自动写入时间戳
   protected $autoWriteTimestamp = true;

   // 数据写入的时候，状态字段默认为1
   protected $insert             = [
      'status' => 1,
   ];
}
```

## 5、构建控制器

在application\api\controller下新建User.php

```php
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
```

## 6、测试接口

打开Postman测试接口

### 1. 增

+ POST请求，需要填写请求参数

![](https://candide.oss-cn-beijing.aliyuncs.com/ifbiu/1601025843472-20200925172344.png)

### 2. 查

+ GET请求，直接访问，无需请求参数

  + 获取所有信息

  ![](https://candide.oss-cn-beijing.aliyuncs.com/ifbiu/1601026186680-20200925172935.png)

  + 获取单个信息

  ![](https://candide.oss-cn-beijing.aliyuncs.com/ifbiu/1601026388336-20200925173302.png)

### 3. 改

+ PUT请求，无需请求参数

![](https://candide.oss-cn-beijing.aliyuncs.com/ifbiu/1601026935977-20200925174203.png)

### 4. 删

+ DELETE请求，无需请求参数

![](https://candide.oss-cn-beijing.aliyuncs.com/ifbiu/1601027162591-20200925174554.png)