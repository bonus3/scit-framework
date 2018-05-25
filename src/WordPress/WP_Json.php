<?php

namespace SCIT\WordPress;
use SCIT\Routing\Route;

defined('SCIT_PATH') or die('Error');

class WP_Json {
    private static $namespace = 'app';
    
    public static function register_routes($routes) {
        foreach ($routes as $route) {
            $config = [
                'methods' => $route->getMethod(),
                'callback' => [$route, 'run'],
                'args' => $route->getArgs(),
                'permission_callback' => $route->permissions()
            ];
            register_rest_route(
                apply_filters('bonus_route_namespace', self::$namespace),
                $route->getEndpointParsed(),
                $config
            );
        }
    }
    
    public static function changeBaseApi() {
        return 'api';
    }
}