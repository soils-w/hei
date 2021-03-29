<?php
// +----------------------------------------------------------------------
// | HEI
// +----------------------------------------------------------------------
// | Author: wanglq <1763020198@qq.com>
// +----------------------------------------------------------------------
//开启错误
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

//定义根目录
define('ROOT_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR);
//定义app目录
define('APP_PARH',ROOT_PATH.'app'.DIRECTORY_SEPARATOR);
//自动加载
require __DIR__ . '/../vendor/autoload.php';
//$a = new \service\App();
//$b = \service\Container::getInstance();
//dump($b);
//dump($a);die();
//数据库连接
require '../config/database.php';
// 路由配置
require '../route/app.php';


