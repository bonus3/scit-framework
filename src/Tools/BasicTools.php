<?php

namespace SCIT\Tools;

defined('SCIT_PATH') or die('Error');

class BasicTools {
    
    public static function extractParams($func) {
        if (is_array($func)) {
            $call = $func[1];
        } else {
            $call = $func;
        }
        
        $params = explode(':', $call);
        $method = array_shift($params);
        return [
            'method' => is_array($func) ? [$func[0], $method] : $method,
            'params' => $params
        ];
    }
    
}