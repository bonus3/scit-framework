<?php

namespace SCIT\Auth;

use SCIT\Routing\Route;

defined('SCIT_PATH') or die('Error');

class Basic implements IAuth {
    
    private $args;
    private $route;
    
    public function __construct($route, $args = null) {
        $this->route = $route;
        $this->args = $args;
    }
    
    public function auth() {
        $authorization = apache_request_headers()['Authorization'];
        $authorization = explode(' ', $authorization);
        $token = isset($authorization[1]) && $authorization[0] === 'Basic' ? $authorization[1] : '';
        $user = $this->checkToken($token);
        if ($user === false) {
            return false;
        }
        wp_set_current_user($user->ID);
        if (!$this->validate()) {
            wp_logout();
            return false;
        }
        return true;
    }
    
    public static function generateToken($user = null) {
        $user = $user ? $user : wp_get_current_user();
        $token = password_hash(self::getStringDecrypt($user), PASSWORD_BCRYPT);
        return $user->ID . '|' . $token;
    }
    
    private static function getStringDecrypt($user) {
        return $user->user_email .
            $user->user_login .
            $user->user_registered .
            $user->user_pass;
    }
    
    public function checkToken($token) {
        $data = explode('|', $token);
        if (count($data) !== 2) {
            return false;
        }
        $user = get_user_by('ID', $data[0]);
        if (!$user || is_wp_error($user)) {
            return false;
        }
        return password_verify($this->getStringDecrypt($user), $data[1]) === true ? $user : false;
    }
    
    private function validate() {
        return $this->validateRoles() && $this->validateCaps();
    }
    
    private function validateRoles() {
        if (isset($this->args['roles']) && is_array($this->args['roles'])) {
            return !empty(array_intersect($this->args['roles'], wp_get_current_user()->roles));
        }
        return true;
    }
    
    private function validateCaps() {
        if (isset($this->args['caps']) && is_array($this->args['caps'])) {
            $validate = true;
            foreach ($this->args['caps'] as $caps) {
                if (!current_user_can($caps)) {
                    $validate = false;
                }
            }
            return $validate;
        }
        return true;
    }
    
}