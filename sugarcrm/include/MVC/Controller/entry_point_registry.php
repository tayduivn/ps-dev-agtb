<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id$
 * Description:  Defines the English language pack for the base application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
$entry_point_registry = array(
	'download' => array('file' => 'download.php', 'auth' => true),
	'export' => array('file' => 'export.php', 'auth' => true),
	'export_dataset' => array('file' => 'export_dataset.php', 'auth' => true),
	'Changenewpassword' => array('file' => 'modules/Users/Changenewpassword.php', 'auth' => false),
	'GeneratePassword' => array('file' => 'modules/Users/GeneratePassword.php', 'auth' => false),
	'vCard' => array('file' => 'vCard.php', 'auth' => true),
	'pdf' => array('file' => 'pdf.php', 'auth' => true),
	'minify' => array('file' => 'jssource/minify.php', 'auth' => true),
	'json' => array('file' => 'json.php', 'auth' => true),
    'json_server' => array('file' => 'json_server.php', 'auth' => true),
	'HandleAjaxCall' => array('file' => 'HandleAjaxCall.php', 'auth' => true),
	'TreeData' => array('file' => 'TreeData.php', 'auth' => true),
	'ExampleLeadCapture' => array('file' => 'examples/ExampleLeadCapture.php', 'auth' => false),
	'FormValidationTest' => array('file' => 'examples/FormValidationTest.php', 'auth' => false),
	'ProgressBarTest' => array('file' => 'examples/ProgressBarTest.php', 'auth' => false),
	'SoapFullTest' => array('file' => 'examples/SoapFullTest.php', 'auth' => false),
	'SoapFullTest_Version2.php' => array('file' => 'examples/SoapFullTest_Version2.php', 'auth' => false),
	'SoapPortalFullTest' => array('file' => 'examples/SoapPortalFullTest.php', 'auth' => false),
	'SoapTest' => array('file' => 'examples/SoapTest.php', 'auth' => false),
	'SoapTestPortal' => array('file' => 'examples/SoapTestPortal.php', 'auth' => false),
	'SoapTestPortal2' => array('file' => 'examples/SoapTestPortal2.php', 'auth' => false),
//BEGIN SUGARCRM flav!=sales ONLY
    'image' => array('file' => 'modules/Campaigns/image.php', 'auth' => false),
    'campaign_trackerv2' => array('file' => 'modules/Campaigns/Tracker.php', 'auth' => false),
    'WebToLeadCapture' => array('file' => 'modules/Campaigns/WebToLeadCapture.php', 'auth' => false),
    'removeme' => array('file' => 'modules/Campaigns/RemoveMe.php', 'auth' => false),
//END SUGARCRM flav!=sales ONLY
    'acceptDecline' => array('file' => 'modules/Contacts/AcceptDecline.php', 'auth' => false),
    'leadCapture' => array('file' => 'modules/Leads/Capture.php', 'auth' => false),
    'process_queue' => array('file' => 'process_queue.php', 'auth' => true),
//BEGIN SUGARCRM flav=pro ONLY
	'process_workflow' => array('file' => 'process_workflow.php', 'auth' => true),
//END SUGARCRM flav=pro ONLY
//BEGIN SUGARCRM flav=int ONLY
	'schedulers' => array('file' => 'schedulers.php', 'auth' => true),
//END SUGARCRM flav=int ONLY
	'zipatcher' => array('file' => 'zipatcher.php', 'auth' => true),
    'mm_get_doc' => array('file' => 'modules/MailMerge/get_doc.php', 'auth' => true),
    'getImage' => array('file' => 'include/SugarTheme/getImage.php', 'auth' => false),
    'getCSS' => array('file' => 'include/SugarTheme/getCSS.php', 'auth' => false),
    'GenerateQuickComposeFrame' => array('file' => 'modules/Emails/GenerateQuickComposeFrame.php', 'auth' => true),
    'DetailUserRole' => array('file' => 'modules/ACLRoles/DetailUserRole.php', 'auth' => true),
    'getYUIComboFile' => array('file' => 'include/javascript/getYUIComboFile.php', 'auth' => false),
    'UploadFileCheck' => array('file' => 'modules/Configurator/UploadFileCheck.php', 'auth' => false),
    'SAML'=>  array('file' => 'modules/Users/authentication/SAMLAuthenticate/index.php', 'auth' => false),
);
?>
