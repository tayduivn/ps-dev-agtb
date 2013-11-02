<?php

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
        die("Failed to initialize the upgrader, please check you're logged in as admin");
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
