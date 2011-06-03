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
$viewdefs['Accounts']['DetailView'] = array (
  'templateMeta' => 
  array (
    'form' => 
    array (
      'buttons' => 
      array (
        0 => 'EDIT',
        1 => 'DUPLICATE',
        2 => 'DELETE',
        3 => 'FIND_DUPLICATES',
      ),
    ),
    'maxColumns' => '2',
    'widths' => 
    array (
      0 => 
      array (
        'label' => '10',
        'field' => '30',
      ),
      1 => 
      array (
        'label' => '10',
        'field' => '30',
      ),
    ),
    'includes' => 
    array (
      0 => 
      array (
        'file' => 'modules/Accounts/Account.js',
      ),
    ),
  ),
  'panels' => 
  array (
    'default' => 
    array (
      0 => 
      array (
        0 => 
        array (
          'name' => 'name',
          'label' => 'LBL_NAME',
        ),
        1 => 
        array (
          'name' => 'phone_office',
          'label' => 'LBL_PHONE_OFFICE',
        ),
      ),
      1 => 
      array (
        0 => 
        array (
          'name' => 'account_type',
          'label' => 'LBL_TYPE',
        ),
        1 => 
        array (
          'name' => 'phone_alternate',
          'label' => 'LBL_OTHER_PHONE',
        ),
      ),
      2 => 
      array (
        0 => 
        array (
          'name' => 'team_name',
          'label' => 'LBL_TEAM',
        ),
        1 => 
        array (
          'name' => 'phone_fax',
          'label' => 'LBL_FAX',
        ),
      ),
      3 => 
      array (
        0 => 
        array (
          'name' => 'assigned_user_name',
          'label' => 'LBL_ASSIGNED_TO',
        ),
        1 => 
        array (
          'name' => 'date_modified',
          'label' => 'LBL_DATE_MODIFIED',
          'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
        ),
      ),
      4 => 
      array (
        0 => NULL,
        1 => 
        array (
          'name' => 'date_entered',
          'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
          'label' => 'LBL_DATE_ENTERED',
        ),
      ),
      5 => 
      array (
        0 => 
        array (
          'name' => 'billing_address_street',
          'label' => 'LBL_BILLING_ADDRESS',
          'type' => 'address',
          'displayParams' => 
          array (
            'key' => 'billing',
          ),
        ),
        1 => 
        array (
          'name' => 'shipping_address_street',
          'label' => 'LBL_SHIPPING_ADDRESS',
          'type' => 'address',
          'displayParams' => 
          array (
            'key' => 'shipping',
          ),
        ),
      ),
      6 => 
      array (
        0 => 
        array (
          'name' => 'description',
          'label' => 'LBL_DESCRIPTION',
        ),
        1 => NULL,
      ),
      7 => 
      array (
        0 => 
        array (
          'name' => 'email1',
          'label' => 'LBL_EMAIL',
        ),
        1 => 
        array (
          'name' => 'website',
          'type' => 'link',
          'label' => 'LBL_WEBSITE',
          'displayParams' => 
          array (
            'link_target' => '_blank',
          ),
        ),
      ),
      8 => 
      array (
        0 => NULL,
        1 => NULL,
      ),
      11 => 
      array (
        0 => 'campaign_name',
      ),
    ),
  ),
);
?>
