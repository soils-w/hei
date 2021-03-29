<?php

namespace service;

class App extends Container
{
    protected $bind = [];

    public function __construct()
    {
        self::setInstance($this);
        $this->instance('app',$this);
        $this->instance('\service\Container',$this);
    }
}