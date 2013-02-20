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
$module_name = 'Activities';
$viewdefs[$module_name]['base']['menu']['header'] = array(
    array(
        'route'=>'#Calls/create',
        'label' =>'LNK_NEW_CALL',
        'acl_action'=>'edit',
        'acl_module'=>'Calls',
        'icon' => 'icon-plus',
    ),
    array(
        'route'=>'#bwc/index.php?module='.$module_name.'&action=EditView&return_module='.$module_name.'&return_action=DetailView',
        'label' =>'LNK_NEW_MEETING',
        'acl_action'=>'edit',
        'acl_module'=>$module_name,
        'icon' => 'icon-plus',
    ),
    array(
        'route'=>'#Tasks/create',
        'label' =>'LNK_NEW_TASK',
        'acl_action'=>'edit',
        'acl_module'=>'Tasks',
        'icon' => 'icon-plus',
    ),
    array(
        'route'=>'#Notes/create',
        'label' =>'LNK_NEW_NOTE',
        'acl_action'=>'edit',
        'acl_module'=>'Notes',
        'icon' => 'icon-plus',
    ),

    array(
        'route'=>'#Calls/',
        'label' =>'LNK_CALL_LIST',
        'acl_action'=>'list',
        'acl_module'=>'Calls',
        'icon' => 'icon-reorder',
    ),
    array(
        'route'=>'#Meetings/',
        'label' =>'LNK_CALL_LIST',
        'acl_action'=>'list',
        'acl_module'=>'Meetings',
        'icon' => 'icon-reorder',
    ),
    array(
        'route'=>'#Tasks/',
        'label' =>'LNK_CALL_LIST',
        'acl_action'=>'list',
        'acl_module'=>'Tasks',
        'icon' => 'icon-reorder',
    ),
    array(
        'route'=>'#Notes/',
        'label' =>'LNK_CALL_LIST',
        'acl_action'=>'list',
        'acl_module'=>'Notes',
        'icon' => 'icon-reorder',
    ),

    array(
        'route'=>'#bwc/index.php?module=Import&action=Step1&import_module=Calls&return_module=Calls&return_action=index',
        'label' =>'LNK_IMPORT_CALLS',
        'acl_action'=>'import',
        'acl_module'=>'Calls',
        'icon' => 'icon-upload-alternative',
    ),
    array(
        'route'=>'#bwc/index.php?module=Import&action=Step1&import_module=Meetings&return_module=Meetings&return_action=index',
        'label' =>'LNK_IMPORT_MEETINGS',
        'acl_action'=>'import',
        'acl_module'=>'Meetings',
        'icon' => 'icon-upload-alternative',
    ),
    array(
        'route'=>'#bwc/index.php?module=Import&action=Step1&import_module=Tasks&return_module=Tasks&return_action=index',
        'label' =>'LNK_IMPORT_TASKS',
        'acl_action'=>'import',
        'acl_module'=>'Tasks',
        'icon' => 'icon-upload-alternative',
    ),
    array(
        'route'=>'#bwc/index.php?module=Import&action=Step1&import_module=Notes&return_module=Notes&return_action=index',
        'label' =>'LNK_IMPORT_NOTES',
        'acl_action'=>'import',
        'acl_module'=>'Notes',
        'icon' => 'icon-upload-alternative',
    ),
);