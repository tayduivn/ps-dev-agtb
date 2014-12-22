<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
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

// if we're in upgrade, upgrade files will be preserved in special place
if(empty($_REQUEST['action']) || empty($_REQUEST['token'])) {
    $files_dir = 'modules/UpgradeWizard/';
} else {
    session_start();
    if (!empty($_SESSION['upgrade_dir']) && is_dir($_SESSION['upgrade_dir'])) {
        $files_dir = $_SESSION['upgrade_dir'];
    } else {
        $files_dir = 'modules/UpgradeWizard/';
    }
    session_write_close();
}
// we inlcude either original or the copy preserved so that upgrading won't mess it up
require_once "{$files_dir}WebUpgrader.php";
$upg = new WebUpgrader(dirname(__FILE__));
$upg->init();

// handle log export
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'exportlog') {
    if (!empty($_REQUEST['token']) && $upg->checkTokenAndAdmin($_REQUEST['token'])) {
        $file = $upg->context['log'];
        if (!file_exists($file)) {
            die('Log file does not exist');
        }
        header('Content-Type: application/text');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
    } else {
        die('Bad token. You can not export log file');
    }
    exit;
}

if(empty($_REQUEST['action']) || empty($_REQUEST['token'])) {
    $token = $upg->startUpgrade();
    if(!$token) {
        if(!$upg->error) {
            $errmsg = "Failed to initialize the upgrader, please check you're logged in as admin";
        } else {
            $errmsg = $upg->error;
        }
        die($errmsg);
    }
    if(!$upg->healthcheck()) {
        header("Location: index.php?module=HealthCheck&referrer=UpgradeWizard");
        exit;
    }
	$upg->displayUpgradePage();
	exit(0);
}
if(!$upg->startRequest($_REQUEST['token'])) {
    die("Bad token");
}

ob_start();
$res = $upg->process($_REQUEST['action']);
if($res !== false && $upg->success) {
    // OK
    $reply = array("status" => "ok", "data" => $res);
    if(!empty($upg->license)) {
        $reply['license'] = $upg->license;
    }
    if(!empty($upg->readme)) {
        $reply['readme'] = $upg->readme;
    }
} else {
    // error
    $reply = array("status" => "error", "message" => $upg->error?$upg->error:"Stage {$_REQUEST['action']} failed", 'data' => $res);
}
$msg = ob_get_clean();

if(!empty($msg)) {
    if(!empty($reply['message'])) {
        $reply['message'] .= $msg;
    } else {
        $reply['message'] = $msg;
    }
}
header("Content-Type: text/plain");
echo json_encode($reply);
