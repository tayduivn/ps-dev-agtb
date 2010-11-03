<?php
require_once('modules/fonuae_PBXSettings/fonuae_PBXSettings.php');
// find the PBX Settings
global $current_user;
$pbx_settings = new fonuae_PBXSettings();
$pbx_settings->retrieve_by_string_fields(array('assigned_user_id' => $current_user->id));

// add the FONcall javascripts
echo '<script type="text/javascript" language="Javascript">';
echo "var pbx_setting_id = '".$pbx_settings->id."';\n";
echo file_get_contents("fonality/include/FONcall/uaeclick2call.js");
echo '</script>';
?>
