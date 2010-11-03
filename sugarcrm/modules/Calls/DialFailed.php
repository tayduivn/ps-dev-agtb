<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $current_user;

echo "\n<p>\n";
echo get_module_title($mod_strings['LBL_MODULE_NAME'], "Dial Failed!", true);
echo "\n</p>\n";
echo '<p>Please check your PBX Details<br/>
If the problem still persists, please contact your Administrator';
?>
