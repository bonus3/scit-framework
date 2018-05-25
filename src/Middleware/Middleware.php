<?php

namespace SCIT\Middleware;

defined('SCIT_PATH') or die('Error');

abstract class Middleware {
    
    public static $list = [];
    public static $instance;
    
    public function __construct() {
        self::$list[get_class($this)] = $this;
    }
    
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
}