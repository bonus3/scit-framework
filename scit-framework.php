<?php
/*
Plugin Name:  SCIT Framework
Plugin URI:   http://andersonsg.com.br
Description:  SCIT Framework
Version:      1.0
Author:       Anderson Gonçalves - SCIT Team
Author URI:   http://scit.com.br
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  scit-framework
Domain Path:  /
*/

if (!defined('SCIT_PATH')) {
    define('SCIT_PATH', dirname(__FILE__));
}

global $bonus_autoload;
$bonus_autoload = require __DIR__ . '/vendor/autoload.php';
