<?php

namespace SCIT\Validation;

abstract class Basic {
    
    public static function required($value, $request, $param) {
        return !empty($request->get_param($param));
    }
    
    public static function min($value, $request, $param, $min) {
        return is_numeric($value) && $value >= $min;
    }
    
}