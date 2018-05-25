<?php

namespace SCIT\WordPress;
use SCIT\Routing\Route;

defined('SCIT_PATH') or die('Error');

class WordPress {
    
    private $request;
    private $userApp;
    private $appDirectory;
    
    public function __construct($namespace_base = '', $app_directory = '') {
        if (!empty($namespace_base) && !empty($app_directory)) {
            global $bonus_autoload;
            $bonus_autoload->addPsr4($namespace_base, $app_directory);
            $this->userApp = $namespace_base;
            $this->appDirectory = $app_directory;
        }
        $this->includeFiles();
        add_action('rest_api_init', [$this, 'routes']);
        add_filter('rest_url_prefix', [WP_Json::class, 'changeBaseApi']);
        add_filter('rest_pre_serve_request', [$this, 'setRequestObject'], 10, 4);
    }
    
    public function routes() {
        WP_Json::register_routes(Route::$routes_call);
    }
    
    public function setRequestObject($r, $result, $request, $obj) {
        Route::$request = $request;
    }
    
    private function includeFiles() {
        $path = __DIR__ . '/..';
        foreach (glob($path . '/Routes/*.php') as $filename) {
            include_once $filename;
        }
        
        if (is_dir($this->appDirectory)) {
            foreach (glob($this->appDirectory . '/Routes/*.php') as $filename) {
                include_once $filename;
            }
        }
    }
    
}