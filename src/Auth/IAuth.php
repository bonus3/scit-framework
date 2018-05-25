<?php

namespace SCIT\Auth;

defined('SCIT_PATH') or die('Error');

interface IAuth {
    public function auth();
}