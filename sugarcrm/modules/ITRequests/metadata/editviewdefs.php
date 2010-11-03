<?php
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$viewdefs['ITRequests']['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'form' => array('headerTpl' => 'modules/ITRequests/tpls/header.tpl'),
        'widths' => array(
            array('label' => '15', 'field' => '30'),
            array('label' => '15', 'field' => '30')
        ),
    ),
    'panels' => array (
        'default' => array (
            array(
                array(
                    'name' => 'itrequest_number',
                    'customCode' => '{$fields.itrequest_number.value}'
                ),
                array(
                    'name' => 'assigned_user_name',
                )
            ),
            array('priority', array('name'=>'team_name', 'displayParams'=>array('display'=>true))),
            array('status', 'escalation_c'),
            array('target_date', ''),
            array('development_time',''),
            array('department_c'),
            array('department_category_c'),
            array(array(
                    'name' => 'name',
                    'displayParams' =>
                        array('size' => 100, 'required' => true)
                )),
            array(
                array(
                    'name' => 'description',
                    'displayParams' =>
                        array('rows' => 12, 'cols' => 120)
                )
            ),
            array(
                array(
                    'name' => 'resolution',
                    'displayParams' =>
                        array('rows' => 12, 'cols' => 120)
                )
            ),
        )
    ),
);
