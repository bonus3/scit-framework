<?php

namespace SCIT\Routing;

defined('SCIT_PATH') or die('Error');

class RouteGroup extends Router {
    
    private $childs = [];
    
    public function addChild(Router $child) {
        $this->childs[] = $child;
    }
    
}