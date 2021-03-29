<?php

namespace app\controller;

use service\View;

class Base {
    /**
     * @name 析构函数
     */
    public function __construct()
    {

    }

    public function assign($name, $value = null)
    {
        return View::assign($name,$value);

    }

    public function fetch($template, $data=[])
    {
        return View::fetch($template, $data);
    }
}