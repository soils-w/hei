<?php
// +----------------------------------------------------------------------
// | HEI
// +----------------------------------------------------------------------
// | Author: wanglq <1763020198@qq.com>
// +----------------------------------------------------------------------
// | Date: 2021-03-18
// +----------------------------------------------------------------------

namespace service;

class Config
{
    /**
     * 配置文件目录
     * @var string
     */
    protected $path;

    /**
     * 配置文件后缀
     * @var string
     */
    protected $ext;

    /**
     * 配置参数
     * @var array
     */
    protected $config = [];

    /**
     * Config constructor.
     * @param string $path
     * @param string $ext
     */
    public function __construct(string $path = '', string $ext = 'php')
    {
        $this->path = $path;
        $this->ext = $ext;
    }

    /**
     * 加载配置文件
     * @param string $file
     * @param string|null $name
     * @return array
     */
    public function load(string $file, string $name = null): array
    {
        if(is_file($file)) {
            $filename = $file;
        } elseif (is_file($this->path . $file . '.' . $this->ext)) {
            $filename = $this->path . $file . '.' . $this->ext;
        }

        if(isset($filename)) {
            return $this->parse($filename, $name);
        }
        return $this->config;
    }

    /**
     * 解析配置文件
     * @param $filename
     * @param string|null $name
     * @return array
     */
    public function parse($filename, string $name = null) :array
    {
        //判断是什么类型的文件，引用进来
        $type = pathinfo($filename, PATHINFO_EXTENSION);
        switch ($type) {
            case 'php':
                $config = include $filename;
                break;
            case 'json':
                $config = json_decode(file_get_contents($filename), true);
                break;
            case 'ini':
                $config = parse_ini_file($filename, true, INI_SCANNER_TYPED) ?: [];
                break;
        }
        return is_array($config) ? $this->set($config, $name) : [];
    }

    /**
     * 设置配置参数 name为数组则为批量设置
     * @access public
     * @param  array  $config 配置参数
     * @param  string $name 配置名
     * @return array
     */
    public function set(array $config, string $name = null): array
    {
        if (!empty($name)) {
            if (isset($this->config[$name])) {
                $result = array_merge($this->config[$name], $config);
            } else {
                $result = $config;
            }

            $this->config[$name] = $result;
        } else {
            $result = $this->config = array_merge($this->config, array_change_key_case($config));
        }
        return $result;
    }

    /**
     * 获取配置参数
     * @param string $name
     * @return array|mixed|string
     */
    public function get(string $name = '')
    {
        //没有传递name时，返回所有的配置内容
        if(!$name) {
            return $this -> config;
        }

        if(false === strpos($name, '.')) {//只有一层目录
            return $this->pull($name);
        }

        return $this->deeppull($name);

    }

    /**
     * 给到的name有“.”，获取多层目录下面的某一个name值
     * @param string $name
     * @return string
     */
    public function deeppull(string $name) {
        $nameArr = explode('.', $name);

        $config = $this->config;

        foreach ($nameArr as $val) {
            if(isset($config[$val])) {
                $config = $config[$val];
            } else {
                return '';
            }
        }
        return $config;
    }

    /**
     * 给到的name没有"."，获取一层目录
     * @param string $name
     * @return mixed|string
     */
    public function pull(string $name)
    {
        $name = strtolower($name);

        return $this->config[$name]??'';//php7 新特性
    }

    public function has(string $name):array
    {
        return !is_null($this->get($name));
    }

}