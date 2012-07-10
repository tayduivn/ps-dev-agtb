<?php

require_once('modules/Users/authentication/SugarAuthenticate/SugarAuthenticateUser.php');

class SummerAuthenticateUser extends AuthenticationController {
    public function authenticateUser($name, $password, $fallback = false) {
        $sa = SugarAccess::getInstance();
        $userData = $sa->authenticate($name, $password);

        return $userData["userinfo"]["id"];
    }
}