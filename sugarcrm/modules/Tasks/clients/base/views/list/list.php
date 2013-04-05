<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */


$viewdefs['Tasks']['base']['view']['list'] = array(
    'panels' =>
    array(
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
                array(
                    'name' => 'name',
                    'width' =>  40,
                    'link' => true,
                    'label' => 'LBL_LIST_SUBJECT',
                    'default' => true,
                ),
                array(
                    'name' => 'contact_name',
                    'width' => '20', 
                    'label' => 'LBL_LIST_CONTACT', 
                    'link' => true,
                    'id' => 'CONTACT_ID',
                    'module' => 'Contacts',
                    'default' => true,
                    'ACLTag' => 'CONTACT',
                    'related_fields' => array('contact_id'),
                    'sortable' => false,
                ),
                array(
                    'name' => 'parent_name',
                    'width'   => '20',
                    'label'   => 'LBL_LIST_RELATED_TO',
                    'dynamic_module' => 'PARENT_TYPE',
                    'id' => 'PARENT_ID',
                    'link' => true, 
                    'default' => true,
                    'sortable' => false,
                    'ACLTag' => 'PARENT',
                    'related_fields' => array('parent_id', 'parent_type'),
                ),
                array(
                    'name' => 'date_due',
                    'width' => '15', 
                    'label' => 'LBL_LIST_DUE_DATE', 
                    'link' => false,
                    'default' => true,
                ),
                array(
                    'name' => 'time_due',
                    'width' => '15', 
                    'label' => 'LBL_LIST_DUE_TIME', 
                    'sortable' => false, 
                    'link' => false,
                    'default' => true,
                ),
                //BEGIN SUGARCRM flav=pro ONLY
                array(
                    'name' => 'team_name',
                    'width' => '2', 
                    'label' => 'LBL_LIST_TEAM',
                    'default' => false,
                    'sortable' => false,
                ),
                //END SUGARCRM flav=pro ONLY
                array(
                    'name' => 'assigned_user_name',
                    'width' => '2', 
                    'label' => 'LBL_LIST_ASSIGNED_TO_NAME',
                    'id' => 'ASSIGNED_USER_ID',
                    'default' => true,
                    'sortable' => false,
                ),
                array(
                    'name' => 'date_start',
                    'width' => '5', 
                    'label' => 'LBL_LIST_START_DATE', 
                    'link' => false,
                    'default' => false,
                ),
                array(
                    'name' => 'status',
                    'width' => '10', 
                    'label' => 'LBL_LIST_STATUS', 
                    'link' => false,
                    'default' => false,
                ),
                array (
                    'name' => 'date_entered',
                    'width' => '10',
                    'label' => 'LBL_DATE_ENTERED',
                    'default' => true,
                ),
            ),
        ),
    ),
);
