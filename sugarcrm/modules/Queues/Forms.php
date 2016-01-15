<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
//FILE SUGARCRM flav=int ONLY
/**
 * custom implementation to support two chooser objects
 */
function get_chooser_js($left1, $left2, $right1, $right2) {
$the_script  = "

<script type=\"text/javascript\" language=\"Javascript\">
<!--  to hide script contents from old browsers

function set_chooser() {
	var ".$left1."_concat = '';
	var ".$right1."_concat = '';
	var ".$left2."_concat = '';
	var ".$right2."_concat = '';

	for(i=0; i < object_refs['".$left1."'].options.length ;i++) {
		".$left1."_concat += \"".$left1."[]=\"+object_refs['".$left1."'].options[i].value+\"&\";
	}

	for(i=0; i < object_refs['".$left2."'].options.length ;i++) {
		".$left2."_concat += \"".$left2."[]=\"+object_refs['".$left2."'].options[i].value+\"&\";
	}

	if(typeof object_refs['".$right1."'] != 'undefined') {
		for(i=0; i < object_refs['".$right1."'].options.length ;i++) {
			".$right1."_concat += \"".$right1."[]=\"+object_refs['".$right1."'].options[i].value+\"&\";
		}
	}

	if(typeof object_refs['".$right2."'] != 'undefined') {
		for(i=0; i < object_refs['".$right2."'].options.length ;i++) {
			".$right2."_concat += \"".$right2."[]=\"+object_refs['".$right2."'].options[i].value+\"&\";
		}
	}

document.EditView.".$left1."_concat.value = ".$left1."_concat;
document.EditView.".$right1."_concat.value = ".$right1."_concat;
document.EditView.".$left2."_concat.value = ".$left2."_concat;
document.EditView.".$right2."_concat.value = ".$right2."_concat;

}
// end hiding contents from old browsers  -->
</script>";

return $the_script;
}

function get_confsettings_js() {
  global $mod_strings;
  global $app_strings;

  $lbl_last_name = $mod_strings['LBL_MAIL_FROMADDRESS'];
  $err_missing_required_fields = $app_strings['ERR_MISSING_REQUIRED_FIELDS'];

  return <<<EOQ

<script type="text/javascript" language="Javascript">
<!--  to hide script contents from old browsers

function notify_setrequired(f) {
  document.getElementById("smtp_settings").style.display = (f.mail_sendtype.value == "SMTP") ? "inline" : "none";
  document.getElementById("smtp_settings").style.visibility = (f.mail_sendtype.value == "SMTP") ? "visible" : "hidden";
  document.getElementById("smtp_auth").style.display = (f.mail_smtpauth_req.checked) ? "inline" : "none";
  document.getElementById("smtp_auth").style.visibility = (f.mail_smtpauth_req.checked) ? "visible" : "hidden";
  return true;
}
function add_checks(f) {
  removeFromValidate('EditView', 'mail_smtpserver');
  removeFromValidate('EditView', 'mail_smtpport');
  removeFromValidate('EditView', 'mail_smtpuser');
  removeFromValidate('EditView', 'mail_smtppass');

  if (f.mail_sendtype.value == "SMTP") {
    addToValidate('EditView', 'mail_smtpserver', 'varchar', 'true', '{$mod_strings['LBL_MAIL_SMTPSERVER']}');
    addToValidate('EditView', 'mail_smtpport', 'int', 'true', '{$mod_strings['LBL_MAIL_SMTPPORT']}');
    if (f.mail_smtpauth_req.checked) {
      addToValidate('EditView', 'mail_smtpuser', 'varchar', 'true', '{$mod_strings['LBL_MAIL_SMTPUSER']}');
      addToValidate('EditView', 'mail_smtppass', 'varchar', 'true', '{$mod_strings['LBL_MAIL_SMTPPASS']}');
    }
  }
  return true;
}

notify_setrequired(document.EditView);

// end hiding contents from old browsers  -->
</script>

EOQ;
}


 
 
 ?>
