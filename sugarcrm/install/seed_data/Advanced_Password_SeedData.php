<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2005 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require('config.php');
global $sugar_config;
global $timedate;
//BEGIN SUGARCRM flav=pro ONLY
require_once('modules/Teams/Team.php');
$Team= new team();
$Team_id=$Team->retrieve_team_id('Administrator');
//END SUGARCRM flav=pro ONLY

//Sent when the admin generate a new password
$EmailTemp = new EmailTemplate();
$subj ='New account information';
$desc = 'This template is used when the System Administrator sends a new password to a user.';
$body = '<div><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width="550" align=\"\&quot;\&quot;center\&quot;\&quot;\"><tbody><tr><td colspan=\"2\"><p>Here is your account username and temporary password:</p><p>Username : $contact_user_user_name </p><p>Password : $contact_user_user_hash </p><br><p>'.$GLOBALS['sugar_config']['site_url'].'/index.php</p><br><p>After you log in using the above password, you may be required to reset the password to one of your own choice.</p>   </td>         </tr><tr><td colspan=\"2\"></td>         </tr> </tbody></table> </div>';
$txt_body = 
'
Here is your account username and temporary password:
Username : $contact_user_user_name
Password : $contact_user_user_hash

'.$GLOBALS['sugar_config']['site_url'].'/index.php

After you log in using the above password, you may be required to reset the password to one of your own choice.';
$name = 'System-generated password email';

$EmailTemp->name = $name;
$EmailTemp->description = $desc;
$EmailTemp->subject = $subj;
$EmailTemp->body = $txt_body;
$EmailTemp->body_html = $body;
$EmailTemp->deleted = 0;
//BEGIN SUGARCRM flav=pro ONLY
$EmailTemp->team_id = $Team_id;
//END SUGARCRM flav=pro ONLY
$EmailTemp->published = 'off';
$EmailTemp->text_only = 0;
$id =$EmailTemp->save();

$sugar_config['passwordsetting']['generatepasswordtmpl'] = $id;
$sugar_config['passwordsetting']['forgotpasswordON'] = true;
$sugar_config['passwordsetting']['SystemGeneratedPasswordON'] = true;
$sugar_config['passwordsetting']['systexpirationtime'] = 7;
$sugar_config['passwordsetting']['systexpiration'] = 1;
$sugar_config['passwordsetting']['linkexpiration'] = true;
$sugar_config['passwordsetting']['linkexpirationtime'] = 24;
$sugar_config['passwordsetting']['linkexpirationtype'] = 60;
$sugar_config['passwordsetting']['minpwdlength'] = 6;
$sugar_config['passwordsetting']['oneupper'] = true;
$sugar_config['passwordsetting']['onelower'] = true;
$sugar_config['passwordsetting']['onenumber'] = true;

$result = $EmailTemp->db->query("INSERT INTO config (value, category, name) VALUES ('$id','password', 'System-generated password email')");


//User generate a link to set a new password
$EmailTemp = new EmailTemplate();
$subj ='Reset your account password';
$desc = "This template is used to send a user a link to click to reset the user's account password.";
$body = '<div><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width="550" align=\"\&quot;\&quot;center\&quot;\&quot;\"><tbody><tr><td colspan=\"2\"><p>You recently requested on $contact_user_pwd_last_changed to be able to reset your account password. </p><p>Click on the link below to reset your password:</p><p> $contact_user_link_guid </p>  </td>         </tr><tr><td colspan=\"2\"></td>         </tr> </tbody></table> </div>';
$txt_body = 
'
You recently requested on $contact_user_pwd_last_changed to be able to reset your account password.

Click on the link below to reset your password:

$contact_user_link_guid';
$name = 'Forgot Password email';

$EmailTemp->name = $name;
$EmailTemp->description = $desc;
$EmailTemp->subject = $subj;
$EmailTemp->body = $txt_body;
$EmailTemp->body_html = $body;
$EmailTemp->deleted = 0;
//BEGIN SUGARCRM flav=pro ONLY
$EmailTemp->team_id = $Team_id;
//END SUGARCRM flav=pro ONLY
$EmailTemp->published = 'off';
$EmailTemp->text_only = 0;
$id =$EmailTemp->save();
$sugar_config['passwordsetting']['lostpasswordtmpl'] = $id;
 
$result = $EmailTemp->db->query("INSERT INTO config (value, category, name) VALUES ('$id','password', 'Forgot Password email')");

//rebuildConfigFile($sugar_config, $sugar_config['sugar_version']);
write_array_to_file( "sugar_config", $sugar_config, "config.php");

?>