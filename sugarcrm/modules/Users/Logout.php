<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

$GLOBALS['current_user']->call_custom_logic('before_logout');

// code from REST logout
if (isset($_SESSION['oauth2']) && !empty($_SESSION['oauth2']['refresh_token'])) {
    $oauth2Server = SugarOAuth2Server::getOAuth2Server();
    $oauth2Server->unsetRefreshToken($_SESSION['oauth2']['refresh_token']);
}

if (isset($_SESSION['platform'])) {
    setcookie(
        RestService::DOWNLOAD_COOKIE . '_' . $_SESSION['platform'],
        false,
        -1,
        ini_get('session.cookie_path'),
        ini_get('session.cookie_domain'),
        ini_get('session.cookie_secure'),
        true
    );
}

SugarApplication::endSession();
$_SESSION = array();
setcookie(
    session_name(),
    '',
    time() - 3600,
    ini_get('session.cookie_path'),
    ini_get('session.cookie_domain'),
    ini_get('session.cookie_secure'),
    ini_get('session.cookie_httponly')
);

LogicHook::initialize();
$GLOBALS['logic_hook']->call_custom_logic('Users', 'after_logout');

/** @var AuthenticationController $authController */
$authController->authController->logout();
