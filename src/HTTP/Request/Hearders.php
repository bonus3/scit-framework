<?php

namespace SCIT\HTTP\Request;

defined('SCIT_PATH') or die('Error');

class Headers {
    
    private $headers = [];
    
    public function setHeader($property, $value) {
        $this->headers[] = $property . ': ' . $value;
    }
    
    public function applyHeards() {
        $headers = $this->headers;
        remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
        add_filter('rest_pre_serve_request', function ($value) use ($headers) {
            foreach ($headers as $header) {
                header($header);
            }
            return $value;
        });
    }
    
}