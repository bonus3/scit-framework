<?php

namespace SCIT\Routing;

defined('SCIT_PATH') or die('Error');

class RouteChild extends Router {
    
    private $method;
    private $callback;
    
    public function setMethod($method) {
        $this->method = $method;
    }
    
    public function getMethod() {
        return $this->method;
    }
    
    public function setCallback($callback) {
        if (is_array($callback)) {
            $this->callback = [new $callback[0](), $callback[1]];
        } else {
            $this->callback = $callback;
        }
    }
    
    public function run($request) {
        $result = call_user_func($this->callback, $request);
        $data = null;
        $status = 200;
        $headers = [];
        if ($result instanceof \WP_Error) {
            $data = $result;
        } else if (isset($result['data'])) {
            $data = $result['data'];
            $status = isset($result['status']) ? $result['status'] : null;
            $headers = isset($result['headers']) ? $result['headers'] : [];
        } else {
            $data = $result;
        }
        return new \WP_REST_Response($data, $status, $headers);
    }
    
    public function getEndpointParsed() {
        $endpoint = $this->getEndpoint();
        
        $parsed = preg_replace_callback('/(\/\{[a-zA-Z0-9]+\??\})/', function ($mathes) {
            foreach($mathes as $key => $m) {
                $param = substr($m, 2);
                $param = substr($param, 0, -1);
                if (substr($param, -1) === '?') {
                    $param = substr($param, 0, -1);
                    return "(?:/(?P<". $param .">[a-zA-Z0-9\-]+))?";
                }
                return "/(?P<". $param .">[a-zA-Z0-9\-]+)";
            }
        }, $endpoint);
        
        return $parsed;
    }
    
}