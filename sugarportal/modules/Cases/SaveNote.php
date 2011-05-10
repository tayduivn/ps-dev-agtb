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
require_once('include/upload_file.php'); // handle file upload

global $portal;

$module = 'Notes';

if($_REQUEST['button'] != '  Cancel  '){
//echo $upload_file->final_move();
$nameValues = array();
$valuesToSave = array('name', 'description');
foreach($valuesToSave as $name) {
    if(!empty($_REQUEST[$name]))
        $nameValues[] = array('name' => $name, 'value' => $_REQUEST[$name]);
}

$result = $portal->save($module, $nameValues);
$portal->relateNote($result['id'], 'Cases', $_REQUEST['id']);

$noteId = $result['id'];
if(!empty($_FILES['filename'])) {
    $portal->setAttachmentToNote($noteId, $_FILES['filename']['tmp_name'], $_FILES['filename']['name']);
}
}
if(!empty($_REQUEST['returnmodule']) && !empty($_REQUEST['returnaction']) && !empty($_REQUEST['id'])) {
    $header = 'index.php?module=' . $_REQUEST['returnmodule'] . '&action=' . $_REQUEST['returnaction'] . '&id=' . $_REQUEST['id'];
}
else {
    $header = 'index.php?module=Cases&action=index';
}

header('Location: ' . $header);
?>