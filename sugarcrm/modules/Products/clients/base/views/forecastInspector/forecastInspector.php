<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * $Id$
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
$viewdefs['Products']['base']['view']['forecastInspector'] = array(
    'templateMeta' => array(
        'maxColumns' => '1',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
        ),
    ),
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_DEFAULT',
            'fields' => array(
                array(
                    'name' => 'name',
                    'type' => 'relate',
                    'id_name' => 'id',
                    'module' => 'Products',
                ),
                array(
                    'name' => 'likely_case',
                    'type' => 'currency',
                    'convertToBase'=> true,
                    'showTransactionalAmount'=>true,
                    ),
                array(
                    'name' => 'best_case',
                    'type' => 'currency',
                    'convertToBase'=> true,
                    'showTransactionalAmount'=>true,
                    ),
                array(
                    'name' => 'worst_case',
                    'type' => 'currency',
                    'convertToBase'=> true,
                    'showTransactionalAmount'=>true,
                    ),
                array(
                    'name' => 'account_name',
                    'type' => 'relate',
                    'id_name' => 'account_id',
                    'module' => 'Accounts',
                ),
                array(
                    'name' => 'opportunity_name',
                    'type' => 'relate',
                    'id_name' => 'opportunity_id',
                    'module' => 'Opportunities',
                ),
                'date_closed',
                'next_step',
                'probability',
                'lead_source',
                'campaign_name',
                array(
                    'name' => 'sales_stage',
                    'type' => 'enum',
                    ),
                'description'
            )
        )
    ),
);
?>