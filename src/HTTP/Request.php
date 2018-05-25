<?php

namespace SCIT\HTTP;

use Request\Headers;

defined('SCIT_PATH') or die('Error');

class Request {
    
    public static $instance;
    public $headers;
    
    public function __construct() {
        $this->headers = new Headers();
        $this->headers->setHeader('Access-Control-Allow-Origin', '*');
        $this->headers->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
    }


    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
}