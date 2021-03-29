<?php
// +----------------------------------------------------------------------
// | HEI 助手函数
// +----------------------------------------------------------------------
// | Author: wanglq <1763020198@qq.com>
// +----------------------------------------------------------------------
// | Date: 2021-03-18
// +----------------------------------------------------------------------

/**
 * 配置文件助手函数
 */
if(!function_exists('config')) {
    /**
     * 获取配置文件下的数据
     * @param string $name
     * @return array|mixed|string
     */
    function config(string $name) {

        $configClass = new Config(ROOT_PATH.'config'.DIRECTORY_SEPARATOR, 'php');

        $configClass->load($name);

        return $configClass->get();
    }
}
