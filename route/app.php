<?php

use Miniroute\Route;

Route::get('', 'app\controller@Index\index');
Route::get('/hei',function(){
    echo "单身的程序员们啊，找老婆就联系我吧，哈哈哈哈哈哈";
});

 Route::dispatch();
?>