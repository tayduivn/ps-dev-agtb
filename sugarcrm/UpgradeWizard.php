<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

// if we're in upgrade, upgrade files will be preserved in special place
if(empty($_REQUEST['action']) || empty($_REQUEST['token'])) {
    $files_dir = 'modules/UpgradeWizard/';
} else {
    session_start();
    if(!empty($_SESSION['upgrade_dir'])) {
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
    $reply = array("status" => "error", "message" => $upg->error?$upg->error:"Stage {$_REQUEST['action']} failed");
}
$msg = ob_get_clean();

if(!empty($msg)) {
    if(!empty($reply['message'])) {
        $reply['message'] .= $msg;
    } else {
        $reply['message'] = $msg;
    }
}
header("Content-Type: text/json");
echo json_encode($reply);
