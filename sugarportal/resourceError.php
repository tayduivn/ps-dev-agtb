<?php
if(!defined('sugarEntry'))define('sugarEntry', true);

require_once('include/utils.php');
require_once('config.php');

if(isset($_SESSION['authenticated_user_language']) && $_SESSION['authenticated_user_language'] != '') {
    $current_language = $_SESSION['authenticated_user_language'];
} else {
    $current_language = $GLOBALS['sugar_config']['default_language'];
}

//set module and application string arrays based upon selected language
$app_strings = return_application_language($GLOBALS['sugar_config']['default_language']);

echo $_REQUEST['msg'];
echo "<br>";
echo $GLOBALS['app_strings']['ERR_RESOURCE_MANAGEMENT_INFO'];

?>