<?php

namespace app\controller;

use app\controller\Base as CommonBase;
use app\model\User as UserModel;
use service\App;
use service\Text;

class Index extends CommonBase{

    public function index()
    {
        $userModel = new UserModel();
        $rs = $userModel->getPageList([],'user_name,mobile',1,5);
        $rs = $rs['list'];

        $this->assign(['rs' => $rs]);
        $this->fetch('index/index');
    }


}