<?php

namespace SCIT\Controller;

defined('SCIT_PATH') or die('Error');

class Auth extends Controller {
    
    public function login(\WP_REST_Request $request) {
        $username = $request->get_param('username');
        $password = $request->get_param('password');
        
        $user = get_user_by('login', $username);
        if (wp_check_password($password, $user->user_pass, $user->ID)) {
            $token = \SCIT\Auth\Basic::generateToken($user);
            
            return ['token' => $token];
        }
        return new \WP_Error('invalid_login', 'Username or password invalid.', ['status' => 403]);
    }
    
}