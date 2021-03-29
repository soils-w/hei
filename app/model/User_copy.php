<?php

namespace app\model;
class User
{
    /**
     * 数据库配置
     * @var array
     */
    protected $config = [];

    public function __construct()
    {
        $this->config = config('database');
    }

    public function first() {
        $connect = mysqli_connect($this->config['host'], $this->config['username'], $this->config['password'], $this->config['database']) or die('连接失败'.mysqli_connect_error());
        mysqli_query($connect,"set names utf8");
        $query = mysqli_query($connect, "select user_name,mobile from sr_user limit 1,2");
        if(mysqli_num_rows($query) > 0) {
            while($row = mysqli_fetch_assoc($query)){
                $result[] = $row;
            }
            return $result;
        }

    }
}