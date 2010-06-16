<?php
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
$errors = '';
if(empty($_POST))
{
	$errors = '<br>Welcome';
}else{
	if(empty($_REQUEST['instance_name'])){
		$errors = '<br>Please specify an instance name';
	}
	if(empty($_REQUEST['TEMPLATE_PATH']) ||  !is_dir($_REQUEST['TEMPLATE_PATH'])){
		$errors = '<br>Please specify a valid template path';
	}
	if(empty($_REQUEST['template_url'])){
		$errors = '<br>Please specify a template url';
	}
}

if(empty($errors)){
	require_once('createInstance.php');
	if(process_create_instance($_REQUEST['TEMPLATE_PATH'], $_REQUEST['instance_name'], $_REQUEST['template_url'])){
		echo '<br>Instance Created';
	}else{
		echo '<br>Failed To Create Instance';
	}
	
}else{
	$template_path = !empty($_REQUEST['TEMPLATE_PATH'])?$_REQUEST['TEMPLATE_PATH']:'';
	$instance_name = !empty($_REQUEST['instance_name'])?$_REQUEST['instance_name']:'';
	$template_url = !empty($_REQUEST['template_url'])?$_REQUEST['template_url']:'http://';
	if(!empty($_REQUEST['TEMPLATE_PATH']))echo '<br>please enter a valid template path<br>';
	echo <<<EOQ
	$errors
	<form method='post' action='instanceSetup.php'>
		Instance Path:&nbsp;&nbsp;<input type='text' name='instance_name' size='60' value='$instance_name'><br>
		Template Path: <input type='text' name='TEMPLATE_PATH' size='60' value='$template_path'>
		<br>Template URL:<input type='text' name='template_url' size='60' value='$template_url'>
		<input type='submit' value='Create Instance'>
	</form>
EOQ;

	
	
}


?>