<?php

namespace service;

class Text
{
    protected $app;
    public function __construct(App $app)
    {
        $this->app = $app;
    }
}