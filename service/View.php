<?php

namespace service;

class View
{
    /**
     * 模板变量
     * @var array
     */
    protected static $data = [];
    /**
     * @var array 视图配置
     */
    protected static $config = [
        //视图所在的目录名
        'view_dir_name' => 'view',
        //视图文件类型，后缀名
        'view_suffix' => 'php',
        // 模板起始路径
        'view_path'     => '',
        //目录与文件的分隔符
        'separator' => DIRECTORY_SEPARATOR,
        //视图文件根目录分隔符,如果程序中传递了该参数就说明用改参数作为模板的起始路径，否则用view_path指定
        'view_base_path'=>'@',
        // 是否去除模板文件里面的html空格与换行
        'strip_space'        => false,
    ];

    public static function fetch(string $template, array $data = [])
    {
        //判断文件是否存在
        if(!self::exists($template)) {
            //Todo 抛出异常，提示视图不存在
        }
        //处理参数
        if(!empty($data)) {
            self::$data = array_merge(self::$data,$data);
        }
        //赋值变量
        extract(self::$data);
        //包视图层
        if(pathinfo($template,PATHINFO_EXTENSION) == '') {
            //处理一下
            $template = self::template_handle($template);
        }

        include $template;
    }

    /**
     * 模板变量赋值
     * @param array $data
     * @return array
     */
    public static function assign($name,$value=null) {
        if(is_array($name)) {
            self::$data = array_merge($name,self::$data);
        } else {
            self::$data[$name] = $value;
        }

        return self::$data;
    }

    /**
     * 文件是否存在
     * @param string $template
     * @return bool
     * 判断传进来的是带后缀的还是不带后缀的，如果不带后缀要处理，
     * 比如shop@order/list 自动找到shop/view/order/list.html 或 view/shop/order/list.html
     */
    public static function exists(string $template): bool
    {
        if(pathinfo($template,PATHINFO_EXTENSION) == '') {
            //处理一下
            $template = self::template_handle($template);
        }
        return is_file($template);
    }

    /**
     * @param string $template
     * @return string
     * 对传递进来的模板参数进行处理
     * 1、对于视图层根目录的处理：
     * (1)如果携带了@参数，说明走指定的视图根目录路径
     * (2)如果没有携带参数，说明走默认的视图根目录，一般是/app/view
     * 2、对于详细的文件处理
     *   如果传入的参数带有“/”或"\",类似于于order/list，则定位到view目录下的order/list
     *   如果传入的参数没有携带“/”或"\"，类似于list，则获取当前的控制器名称（Order），
     *   将驼峰式命名替换成下划线(首字母除外)，以控制器名称为视图文件所在的目录，定位到view目录下的order/list
     *   如果template为空，则以当前的控制器名方法名为依据定位到文件
     */
    public static function template_handle(string $template): string
    {
        if (empty(self::$config['view_path'])) {
            $view = self::$config['view_dir_name'];

            if (is_dir(APP_PARH . $view)) {
                $v_path = APP_PARH . $view . DIRECTORY_SEPARATOR;
            } else {//Todo 为多应用考虑，暂时抛出异常，文件不存在
                echo "没找到";die();
            }

            self::$config['view_path'] = $v_path;
        }

        //先处理path
        if(false !== strpos($template, self::$config['view_base_path'])) {
            list($app, $template) = explode(self::$config['view_base_path'], $template);
        }
        if(isset($app)) {
            $view = self::$config['view_dir_name'];
            $viewpath = ROOT_PATH.$app.DIRECTORY_SEPARATOR.$view.DIRECTORY_SEPARATOR;
            if(is_dir($viewpath)) {
                $path = $viewpath;
            } else {
                $path = ROOT_PATH.$view.DIRECTORY_SEPARATOR.$app.DIRECTORY_SEPARATOR;
            }
        } else {
            $path = self::$config['view_path'];
        }
        //在处理文件
        if(false !== strpos($template, '/')) {
            $template = str_replace(['/'], self::$config['separator'], $template);
        } else {
            if($template == '') {//Todo 使用控制其名/方法名.html

            } else {// Todo 使用控制器名/template.html

            }
        }

        return rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.ltrim($template, DIRECTORY_SEPARATOR). '.' .ltrim(self::$config['view_suffix'], '.');
    }
}