<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
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
