<?php

namespace Api\Container;

use DI\Container;

abstract class AbstractContainer
{
    protected $diContainer;
    
    public function __construct(Container $diContainer)
    {
        $this->diContainer = $diContainer;
    }
    
    abstract public function initialize();
}
