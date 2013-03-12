<?php
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

$viewdefs['Bugs']['base']['view']['list'] = array(
    'panels' => array(
        array(
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                  'name'=>  'bug_number',
                   'default'=>true,
                ),
                array(
                    'name'=>  'name',
                    'default'=>true,
                ),
                array(
                    'name'=>  'status',
                    'default'=>true,
                ),
                array(
                    'name'=>  'type',
                    'default'=>true
                ),
                array(
                    'name'=>  'priority',
                    'default'=>true,
                ),
                array(
                    'name'=>  'fixed_in_release',
                    'default'=>true,
                ),
                array(
                    'name'=>  'assigned_user_name',
                    'default'=>true,
                    'sortable' => false,
                ),
                array(
                    'name'=>  'found_in_release',
                    'default'=>false,
                ),
                array(
                    'name'=>  'resolution',
                    'default'=>false,
                ),
                //BEGIN SUGARCRM flav=pro ONLY
                array(
                    'name'=>  'team_name',
                    'default'=>false,
                    'sortable' => false,
                ),
                //END SUGARCRM flav=pro ONLY
            ),

        ),
    ),
);
