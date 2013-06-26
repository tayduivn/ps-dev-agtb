<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$viewdefs['Notes']['base']['view']['list'] = array(
    'panels' => array(
        array(
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array (
                    'name' => 'name',
                    'width' => '40%',
                    'label' => 'LBL_LIST_SUBJECT',
                    'link' => true,
                    'enabled' => true,
                    'default' => true,
                ),
                array (
                    'name' => 'contact_name',
                    'width' => '20%',
                    'label' => 'LBL_LIST_CONTACT',
                    'link' => true,
                    'id' => 'CONTACT_ID',
                    'module' => 'Contacts',
                    'enabled' => true,
                    'default' => true,
                    'sortable' => false,
                    'ACLTag' => 'CONTACT',
                    'related_fields' =>
                    array (
                        0 => 'contact_id',
                    ),
                ),
                array (
                    'name' => 'parent_name',
                    'width' => '20%',
                    'label' => 'LBL_LIST_RELATED_TO',
                    'dynamic_module' => 'PARENT_TYPE',
                    'id' => 'PARENT_ID',
                    'link' => true,
                    'enabled' => true,
                    'default' => true,
                    'sortable' => false,
                    'ACLTag' => 'PARENT',
                    'related_fields' =>
                    array (
                        0 => 'parent_id',
                        1 => 'parent_type',
                    ),
                ),
                array (
                    'name' => 'filename',
                    'width' => '20%',
                    'label' => 'LBL_LIST_FILENAME',
                    'enabled' => true,
                    'default' => true,
                    'type' => 'file',
                    'related_fields' =>
                    array (
                        0 => 'file_url',
                        1 => 'id',
                    ),
                    'displayParams' =>
                    array(
                        'module' => 'Notes',
                    ),
                ),
                array (
                    'name' => 'created_by_name',
                    'type' => 'relate',
                    'label' => 'LBL_CREATED_BY',
                    'width' => '10%',
                    'enabled' => true,
                    'default' => true,
                    'sortable' => false,
                    'related_fields' =>  array ( 'created_by' ),
                ),
            ),

        ),
    ),
);
