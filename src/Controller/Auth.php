<?php

namespace SCIT\Controller;

defined('SCIT_PATH') or die('Error');

class Auth extends Controller {
    
    public function login(\WP_REST_Request $request) {
        $username = $request->get_param('username');
        $password = $request->get_param('password');
        
        $user = get_user_by('login', $username);
        if ($user && wp_check_password($password, $user->user_pass, $user->ID)) {
            $token = \SCIT\Auth\Basic::generateToken($user);
            
            return [
                'data' => [
                    'code' => 'success_login',
                    'data' => ['token' => $token]
                ]
            ];
        }
        return [
            'data' => [
                'code' => 'invalid_login',
                'data' => __('Username or password invalid.', 'cupomapi')
            ],
            'status' => 403
        ];
    }
    
}