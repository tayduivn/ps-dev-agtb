<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['tracker'] = array(
    'table' => 'tracker',
    'fields' => array(
        array(
            'name' => 'id',
            'type' => 'int',
            'len' => '11',
            'isnull' => 'false',
            'auto_increment' => true
        ) ,
        array(
            'name' => 'user_id',
            'type' => 'varchar',
            'len' => '36',
            'isnull' => 'false',
        ) ,
        array(
            'name' => 'module_name',
            'type' => 'varchar',
            'len' => '255',
            'isnull' => 'false',
        ) ,
        array(
            'name' => 'item_id',
            'type' => 'varchar',
            'len' => '36',
            'isnull' => 'false',
        ) ,
        array(
            'name' => 'item_summary',
            'type' => 'varchar',
            'len' => '255',
            'isnull' => 'false',
        ) ,
        array(
            'name' => 'date_modified',
            'type' => 'datetime',
            'isnull' => 'false',
        ) ,
        array(
            'name' => 'action',
            'type' => 'varchar',
            'len' => '255',
            'isnull' => 'false',
        ) ,
	array(
            'name' => 'session_id',
            'vname' => 'LBL_SESSION_ID',
            'type' => 'varchar',
            'len' => '36',
            'isnull' => 'true',
	),
        array(
            'name' => 'visible',
            'type' => 'bool',
            'len' => '1',
            'default' => '0'
        ) ,
    ) ,
    'indices' => array(
        array(
            'name' => 'trackerpk',
            'type' => 'primary',
            'fields' => array(
                'id'
            )
        ) ,
        array(
            'name' => 'idx_tracker_iid',
            'type' => 'index',
            'fields' => array(
                'item_id',
            ),
        ),
        array(
            // shortened name to comply with Oracle length restriction
            'name' => 'idx_tracker_userid_vis_id',
            'type' => 'index',
            'fields' => array(
                'user_id',
                'visible',
                'id',
            ),
        ),
        array(
        	// shortened name to comply with Oracle length restriction
            'name' => 'idx_tracker_userid_itemid_vis',
            'type' => 'index',
            'fields' => array(
                'user_id',
                'item_id',
                'visible'
            ),
        ),
    )
);
