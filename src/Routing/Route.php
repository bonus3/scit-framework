<?php

namespace SCIT\Routing;

defined('SCIT_PATH') or die('Error');

abstract class Route {
    
    public static $routes_call = [];
    private static $groups = [];
    public static $request;


    public static function group($route, $callback) {
        $obj = self::extractRoute(debug_backtrace());
        $callback();
        return $obj;
    }
    
    public static function get($route, $callback) {
        return self::endpoint($route, \WP_REST_Server::READABLE, $callback);
    }
    
    public static function post($route, $callback) {
        return self::endpoint($route, \WP_REST_Server::CREATABLE, $callback);
    }
    
    public static function put($route, $callback) {
        return self::endpoint($route, \WP_REST_Server::EDITABLE, $callback);
    }
    
    public static function delete($route, $callback) {
        return self::endpoint($route, \WP_REST_Server::DELETABLE, $callback);
    }
    
    public static function endpoint($route, $method, $callback) {
        //echo memory_get_usage() / 1024;
        //echo " - ";
        $obj = self::extractRoute(debug_backtrace());
        if ($obj instanceof RouteChild) {
            $obj->setMethod($method);
            $obj->setCallback($callback);
        }
        return $obj;
    }
    
    private static function extractRoute($backtrace) {
        $endpoint = $backtrace[0]['args'][0];
        if ($group = ($backtrace[0]['function'] === 'group')) {
            $obj = new RouteGroup();
            self::setGroup($backtrace, $obj);
        } else {
            $obj = new RouteChild();
            self::$routes_call[] = $obj;
        }
        $obj->addEndpoint($endpoint);
        
        $parent = self::getGroup($backtrace);
        if ($parent) {
            $obj->setParent($parent);
        }
        return $obj;
    }
    
    private static function getGroup($backtrace) {
        $route = '';
        for ($i = count($backtrace) - 1; $i > 1; $i--){
            if ($backtrace[$i]['class'] === Route::class) {
                $route = $route . '/' . $backtrace[$i]['args'][0];
            }
        }
        return isset(self::$groups[$route]) ? self::$groups[$route] : null;
    }
    
    private static function setGroup($backtrace, &$obj) {
        $route = '';
        for ($i = count($backtrace) - 1; $i >= 0; $i--){
            if ($backtrace[$i]['class'] === Route::class) {
                $route = $route . '/' . $backtrace[$i]['args'][0];
            }
        }
        
        self::$groups[$route] = $obj;
    }
    
}