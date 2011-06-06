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
$searchdefs ['Opportunities'] = 
array (
  'layout' => 
  array (
    'basic_search' => 
    array (
      0 => 'name',
      1 => 'opportunity_type',
      2 => 'account_name',
      3 => 
      array (
        'name' => 'current_user_only',
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
      ),
    ),
    'advanced_search' => 
    array (
      'name' => 
      array (
        'name' => 'name',
        'label' => 'LBL_OPPORTUNITY_NAME',
        'default' => true,
        'width' => '10%',
      ),
      'amount' => 
      array (
        'name' => 'amount',
        'label' => 'LBL_AMOUNT',
        'default' => true,
        'currency_format' => true,
        'width' => '10%',
      ),
      'account_name' => 
      array (
        'name' => 'account_name',
        'label' => 'LBL_ACCOUNT_NAME',
        'default' => true,
        'width' => '10%',
      ),
      'date_closed' => 
      array (
        'name' => 'date_closed',
        'label' => 'LBL_DATE_CLOSED',
        'default' => true,
        'width' => '10%',
      ),
      'opportunity_type' => 
      array (
        'width' => '10%',
        'label' => 'LBL_TYPE',
        'sortable' => false,
        'default' => true,
        'name' => 'opportunity_type',
      ),
      'lead_source' => 
      array (
        'name' => 'lead_source',
        'label' => 'LBL_LEAD_SOURCE',
        'default' => true,
        'sortable' => false,
        'width' => '10%',
      ),
      'next_step' => 
      array (
        'name' => 'next_step',
        'label' => 'LBL_NEXT_STEP',
        'default' => true,
        'width' => '10%',
      ),
      'sales_stage' => 
      array (
        'name' => 'sales_stage',
        'label' => 'LBL_SALES_STAGE',
        'default' => true,
        'sortable' => false,
        'width' => '10%',
      ),
      'probability' => 
      array (
        'name' => 'probability',
        'label' => 'LBL_PROBABILITY',
        'default' => true,
        'width' => '10%',
      ),
      'assigned_user_id' => 
      array (
        'name' => 'assigned_user_id',
        'type' => 'enum',
        'label' => 'LBL_ASSIGNED_TO',
        'function' => 
        array (
          'name' => 'get_user_array',
          'params' => 
          array (
            0 => false,
          ),
        ),
        'default' => true,
        'sortable' => false,
        'width' => '10%',
      ),
      'partner_assigned_to_c' => 
      array (
        'width' => '10%',
        'label' => 'Partner_Assigned_To_c',
        'default' => true,
        'name' => 'partner_assigned_to_c',
      ),
      'order_number' => 
      array (
        'width' => '10%',
        'label' => 'LBL_ORDER_NUMBER',
        'default' => true,
        'name' => 'order_number',
      ),
    ),
  ),
  'templateMeta' => 
  array (
    'maxColumns' => '3',
    'widths' => 
    array (
      'label' => '10',
      'field' => '30',
    ),
  ),
);
?>
