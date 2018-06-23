<?php

namespace SCIT\Routing;

use SCIT\Tools\BasicTools;
use SCIT\Middleware\Auth;

defined('SCIT_PATH') or die('Error');

class Router {
    
    private $parent;
    private $endpoint = '';
    private $args = [];
    
    public function setParent(RouteGroup $parent) {
        $this->parent = $parent;
    }
    
    public function addEndpoint($endpoint) {
        $this->endpoint .= '/' . $endpoint;
    }
    
    public function getEndpoint() {
        return $this->parent ? $this->parent->getEndpoint() . $this->endpoint : $this->endpoint;
    }
    
    public function getParent() {
        return $this->parent;
    }
    private function setArg($param, $index, $args) {
        if (!isset($this->args[$param])) {
            $this->args[$param] = [$index => []];
        }
        if (is_array($args)) {
            if (is_callable($args)) {
                $this->args[$param][$index][] = $args;
            } else {
                foreach ($args as $arg) {
                    if (is_callable($arg)) {
                        $this->args[$param][$index][] = $arg;
                    } else {
                        $this->validation($param, $arg);
                    }
                }
            }
        } else {
            if ($parser = $this->parse($args)) {
                $this->args[$param][$index][] = $parser;
            }
        }
    }
    
    public function validation($param, $args) {
        $this->setArg($param, 'validation', $args);
        return $this;
    }
    
    public static function parse($command) {
        if (is_callable($command)) {
            return $command;
        }
        $package = explode('.', $command);
        $last = count($package) - 1;
        $package_formated = '';
        $method = '';
        foreach ($package as $key => $part) {
            if ($key !== $last) {
                switch ($part) {
                    case 'basic':
                        $package_formated .= "\\SCIT\\Validation\\Basic";
                        break;
                    default:
                        $package_formated .= ucfirst($part) . "\\";
                        break;
                }
            } else {
                $method = $part;
            }
        }
        $method_aux = explode(':', $method);
        if (!empty($package_formated)) {
            $callable = [$package_formated, $method_aux[0]];
            $callable_aux = [$package_formated, $method];
        } else {
            $callable = $method_aux[0];
            $callable_aux = $method;
        }
        return is_callable($callable) ? $callable_aux: null;
    }
    
    public function getArgs() {
        if ($this instanceof RouteGroup) {
            return $this->args;
        }
        $args = [];
        $parent_args = $this->parent ? $this->parent->getArgs() : [];
        foreach ($this->args as $param => $arg) {
            if (!isset($args[$param])) {
                $args[$param] = [];
            }
            $arg = self::mergeParams($param, $parent_args, $arg);
            $args[$param]['required'] = $this->hasRequired($arg);
            $args[$param]['validate_callback'] = $this->parseValidation($arg);
        }
        return array_merge($parent_args, $args);
    }
    
    private function parseValidation(&$arg) {
        if (!isset($arg['validation'])) {
            return;
        }
        return function ($value, $request, $param) use ($arg) {
            $valid = true;
            foreach ($arg['validation'] as $func) {
                $call = BasicTools::extractParams($func);
                array_unshift($call['params'], $value, $request, $param);
                if (!call_user_func_array($call['method'], $call['params'])) {
                    $valid = false;
                }
            }
            return $valid;
        };
    }
    
    private static function mergeParams($param, $parent_args, $arg) {
        if(isset($parent_args[$param])) {
            foreach ($parent_args[$param] as $key => $value) {
                if (isset($arg[$key])) {
                    $arg[$key] = array_merge($value, $arg[$key]);
                }
            }
        }
        return $arg;
    }
    
    private function hasRequired($arg) {
        if (!isset($arg['validation'])) {
            return false;
        }
        foreach ($arg['validation'] as $key => $value) {
            if (is_array($value) && $value[0] === '\SCIT\Validation\Basic' && $value[1] === 'required') {
                return true;
            }
        }
        return false;
    }
    
    public function auth($type, $args = null) {
        if (!isset($this->args['permission'])) {
            $this->args['permission'] = [];
        }
        $middleware = new Auth($type, $this, $args);
        $this->args['permission'][] = $middleware->getAuth();
        return $this;
    }
    
    public function permissions() {
        if ($this instanceof RouteGroup) {
            return isset($this->args['permission']) ? $this->args['permission'] : [];
        }
        if (isset($this->args['permission']) && empty($this->args['permission'])) {
            return;
        }
        $permissions = array_merge(
            isset($this->args['permission']) ? $this->args['permission'] : [],
                $this->parent ? $this->parent->permissions() : []
        );
        return function () use ($permissions) {
            $valid = true;
            foreach ($permissions as $permission) {
                if (call_user_func([$permission, 'auth']) === false) {
                    $valid = false;
                }
            }
            return $valid;
        };
    }
    
}