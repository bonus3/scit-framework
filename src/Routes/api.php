<?php

use SCIT\Routing\Route;

Route::group('auth', function () {
    Route::post('', [\SCIT\Controller\Auth::class, 'login'])
        ->validation('username', 'basic.required')
        ->validation('password', 'basic.required');
});