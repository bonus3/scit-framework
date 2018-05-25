<?php

namespace SCIT\Auth;

use SCIT\Routing\Route;

defined('SCIT_PATH') or die('Error');

class Basic implements IAuth {
    
    public function __construct() {
        //add_filter('rest_authentication_errors', [$this, 'auth']);
    }
    
    public function auth() {
        $authorizarion = apache_request_headers()['Authorization'];
        $authorizarion = explode(' ', $authorizarion);
        $token = isset($authorizarion[1]) && $authorizarion[0] === 'Basic' ? $authorizarion[1] : '';
        $user = $this->checkToken($token);
        if ($user === false) {
            return false;
        }
        wp_set_current_user($user->ID);
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
        return password_verify($this->getStringDecrypt($user), $data[1]) === false ? $user : false;
    }
    
}