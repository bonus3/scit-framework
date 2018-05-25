<?php

namespace SCIT\Middleware;

use SCIT\Auth as Athentication;

defined('SCIT_PATH') or die('Error');

class Auth extends Middleware {
    
    private $auth;
    
    public function __construct($type, $routeObject = null) {
        parent::__construct();
        switch ($type) {
            case 'basic':
                $this->auth = new Athentication\Basic($routeObject);
                break;
            default:
                if (class_exists($type)) {
                    $this->auth = new $type();
                }
                break;
        }
        
        if (!isset(class_implements($this->auth)[Athentication\IAuth::class])) {
            throw new \Exception('Invalid Auth class. Not is IAuth instance.');
        }
    }
    
    public function getAuth() {
        return $this->auth;
    }
    
}