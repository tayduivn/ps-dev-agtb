<?php
 if(!defined('sugarEntry'))define('sugarEntry', true);
/************************************************************
 * Initial Normalize Phone Numbers
 *
 * Author: Felix Nilam
 * Date: 28/03/2008
 ***********************************************************/

require_once('include/entryPoint.php');
require_once('modules/Users/User.php');
require_once('modules/Administration/Administration.php');

session_start();

// login as admin
global $current_user;
$current_user = new User();
$current_user->retrieve('1');

// normalize all phones
require_once('modules/Administration/NormalizePhones.php');

// write the uae_config
require('UAE/SugarCRM/include/uae_config.php');
$config_string = "<?php\n";
$config_string .= "\$uae_config = array(\n";
$config_string .= "'normalized' => '1',\n";
foreach($uae_config as $key => $value){
	if($key == 'normalized') continue;
	$config_string .= "'".$key."' => '".$value."',\n";
}
$config_string .= ");\n?>";

$fp = fopen('UAE/SugarCRM/include/uae_config.php','w');
fwrite($fp, $config_string);
fclose($fp);

if(isset($_SESSION['renormalize_phones'])){
	unset($_SESSION['renormalize_phones']);
}

include('cache/normalize_status.php');

echo "$total_phone phone numbers updated.";
?>
