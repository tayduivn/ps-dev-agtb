<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement 
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.  
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may 
 *not use this file except in compliance with the License. Under the terms of the license, You 
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or 
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
 * $Id: Save.php,v 1.23 2006/06/06 17:58:22 majed Exp $
 * Description:  Saves an Account record and then redirects the browser to the
 * defined return URL.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

global $portal;
set_time_limit(3600);
ini_set('default_socket_timeout', 360);

$module = 'Notes';

if(!empty($_REQUEST['id'])) $id = $_REQUEST['id'];
else $id = '';

$nameValues = array();
$valuesToSave = array('id', 'name', 'description');
foreach($valuesToSave as $name) {
    if(!empty($_REQUEST[$name]))  
        $nameValues[] = array('name' => $name, 'value' => $_REQUEST[$name]);
}

$portal->save($module, $nameValues); 

if(!empty($_REQUEST['parent_type']) && !empty($_REQUEST['parent_id'])) { 
    $portal->relateNote($_REQUEST['id'], $_REQUEST['parent_type'], $_REQUEST['parent_id']);
}

if(!empty($_REQUEST['remove_attachment'])) {
    $portal->removeAttachmentFromNote($_REQUEST['id']);
}

if(!empty($_FILES['filename'])) {
    $portal->setAttachmentToNote($_REQUEST['id'], $_FILES['filename']['tmp_name'], $_FILES['filename']['name']);
}


if(!empty($_REQUEST['returnmodule']) && !empty($_REQUEST['returnaction']) && !empty($_REQUEST['id'])) {
    $header = 'index.php?module=' . $_REQUEST['returnmodule'] . '&action=' . $_REQUEST['returnaction'] . '&id=' . $_REQUEST['id'];
}
else {
    $header = 'index.php?module=Cases&action=index';
}
header('Location: ' . $header);
?>