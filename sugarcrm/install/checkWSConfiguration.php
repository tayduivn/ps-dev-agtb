<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Check WebSocket configuration.
 * @param bool $silent
 */
function checkWSConfiguration($silent = false)
{
    installLog("Begin WebSocket Configuration Check Process *************");

    global $mod_strings;
    $errors = array();

    copyInputsIntoSession();

    if (trim($_SESSION['websockets']['client']['url']) == '') {
        $errors['ERR_WEB_SOCKET_CLIENT_URL'] = $mod_strings['ERR_WEB_SOCKET_CLIENT_URL'];
    } else {
        if (!SugarSocket::checkWSSettings($_SESSION['websockets']['client']['url'], 'client')) {
            $errors['ERR_WEB_SOCKET_CLIENT_ERROR'] = str_replace("{{WSURL}}", 'Client', $mod_strings['ERR_WEB_SOCKET_ERROR']);
        }
    }

    if (trim($_SESSION['websockets']['server']['url']) == '') {
        $errors['ERR_WEB_SOCKET_SERVER_URL'] = $mod_strings['ERR_WEB_SOCKET_SERVER_URL'];
    } else {
        if (!SugarSocket::checkWSSettings($_SESSION['websockets']['server']['url'], 'server')) {
            $errors['ERR_WEB_SOCKET_SERVER_ERROR'] = str_replace("{{WSURL}}", 'Server', $mod_strings['ERR_WEB_SOCKET_ERROR']);
        }
    }

    if (trim($_SESSION['websockets']['public_secret']) == '') {
        $errors['ERR_WEB_SOCKET_SECRET'] = $mod_strings['ERR_WEB_SOCKET_SECRET'];
    }

    if ($silent) {
        return $errors;
    } else {
        printErrors($errors);
    }

    installLog("End WebSocket Configuration Check Process *************");
}

function printErrors($errors)
{
    global $mod_strings;

    if (count($errors) == 0) {
        echo 'wsCheckPassed';
        installLog("SUCCESS:: no errors detected!");
    } else {
        installLog("FATAL:: errors have been detected!  User will not be allowed to continue.  Errors are as follow:");

        $validationErr = "<p>{$mod_strings['LBL_SITECFG_FIX_ERRORS']}</p>";
        $validationErr .= '<ul>';
        foreach ($errors as $key => $erMsg) {
            $validationErr .= '<li class="error">' . $erMsg . '</li>';
            installLog(".. {$erMsg}");
        }
        $validationErr .= '</ul>';
        $validationErr .= '</div>';

        echo $validationErr;
    }
}

function copyInputsIntoSession()
{
    if (isset($_REQUEST['websockets']['client']['url'])) {
        $_SESSION['websockets']['client']['url'] =
            filter_var(trim($_REQUEST['websockets']['client']['url']), FILTER_VALIDATE_URL) ?
                trim($_REQUEST['websockets']['client']['url']) :
                '';
        $_SESSION['websockets']['client']['balancer'] =
            SugarSocket::isBalancer($_SESSION['websockets']['client']['url']);
    }

    if (isset($_REQUEST['websockets']['server']['url'])) {
        $_SESSION['websockets']['server']['url'] =
            filter_var(trim($_REQUEST['websockets']['server']['url']), FILTER_VALIDATE_URL) ?
                trim($_REQUEST['websockets']['server']['url']) :
                '';
        // No need to save server balancer configuration.
    }

    if (isset($_REQUEST['websockets']['public_secret'])) {
        $_SESSION['websockets']['public_secret'] = $_REQUEST['websockets']['public_secret'];
    }
}
