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
$viewdefs['Touchpoints']['EditView'] = array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'SAVE',
          1 => 'CANCEL',
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
    ),
    'panels' => 
    array (
      'lbl_leadaccount_info' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'company_name',
            'displayParams' => 
            array (
              'required' => true,
            ),
            'label' => 'LBL_NAME',
          ),
          1 => 
          array (
            'name' => 'phone_work',
            'label' => 'LBL_OFFICE_PHONE',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'website',
            'label' => 'LBL_WEBSITE',
          ),
          1 => 
          array (
            'name' => 'phone_fax',
            'label' => 'LBL_FAX',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'ticker_symbol',
            'label' => 'LBL_TICKER_SYMBOL',
          ),
          1 => 
          array (
            'name' => 'phone_alternate',
            'label' => 'LBL_PHONE_ALT',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'rating',
            'label' => 'LBL_RATING',
          ),
          1 => 
          array (
            'name' => 'employees',
            'label' => 'LBL_NUMBER_OF_EMPLOYEES',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'ownership',
            'label' => 'LBL_OWNERSHIP',
          ),
          1 => 
          array (
            'name' => 'industry',
            'label' => 'LBL_INDUSTRY',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'annual_revenue',
            'label' => 'LBL_ANNUAL_REVENUE',
          ),
          1 => 
          array (
            'name' => 'referred_by',
            'label' => 'LBL_REFERED_BY',
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'lead_source',
            'label' => 'LBL_LEAD_SOURCE',
          ),
          1 => 
          array (
            'name' => 'lead_source_description',
            'label' => 'LBL_LEAD_SOURCE_DESCRIPTION',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'portal_name',
            'label' => 'LBL_PORTAL_NAME',
          ),
          1 => 
          array (
            'name' => 'portal_app',
            'label' => 'LBL_PORTAL_APP',
          ),
        ),
      ),
      'lbl_leadaccountaddress_information' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'primary_address_street',
            'hideLabel' => true,
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'primary',
              'rows' => 2,
              'cols' => 30,
              'maxlength' => 150,
            ),
            'label' => 'LBL_PRIMARY_ADDRESS_STREET',
          ),
          1 => 
          array (
            'name' => 'alt_address_street',
            'hideLabel' => true,
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'alt',
              'copy' => 'primary',
              'rows' => 2,
              'cols' => 30,
              'maxlength' => 150,
            ),
            'label' => 'LBL_ALT_ADDRESS_STREET',
          ),
        ),
      ),
      'lbl_leadcontact_info' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'first_name',
            'customCode' => '{html_options name="salutation" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
            'label' => 'LBL_FIRST_NAME',
          ),
          1 => 
          array (
            'name' => 'phone_work',
            'label' => 'LBL_OFFICE_PHONE',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'last_name',
            'displayParams' => 
            array (
              'required' => true,
            ),
            'label' => 'LBL_LAST_NAME',
          ),
          1 => 
          array (
            'name' => 'phone_mobile',
            'label' => 'LBL_MOBILE_PHONE',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'title',
            'label' => 'LBL_TITLE',
          ),
          1 => 
          array (
            'name' => 'phone_home',
            'label' => 'LBL_HOME_PHONE',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'department',
            'label' => 'LBL_DEPARTMENT',
          ),
          1 => 
          array (
            'name' => 'phone_other',
            'label' => 'LBL_OTHER_PHONE',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'phone_fax',
            'label' => 'LBL_FAX_PHONE',
          ),
          1 => 
          array (
            'name' => 'do_not_call',
            'label' => 'LBL_DO_NOT_CALL',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'campaign_name',
            'label' => 'LBL_CAMPAIGN',
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'email1',
            'label' => 'LBL_EMAIL',
          ),
        ),
      ),
      'lbl_leadcontactaddress_information' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'primary_address_street',
            'hideLabel' => true,
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'primary',
              'rows' => 2,
              'cols' => 30,
              'maxlength' => 150,
            ),
            'label' => 'LBL_PRIMARY_ADDRESS_STREET',
          ),
          1 => 
          array (
            'name' => 'alt_address_street',
            'hideLabel' => true,
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'alt',
              'copy' => 'primary',
              'rows' => 2,
              'cols' => 30,
              'maxlength' => 150,
            ),
            'label' => 'LBL_ALT_ADDRESS_STREET',
          ),
        ),
      ),
      'lbl_touchpoint_info' => array (
            1 => 
            array (
              0 => 
              array (
                'name' => 'score',
                'label' => 'LBL_SCORE',
              ),
              1 =>
              array (
                'name' => 'assigned_user_name',
                'label' => 'LBL_ASSIGNED_TO_NAME',
              ),
            ),
            2 => 
            array (
              0 => 
              array (
                'name' => 'scrubbed',
                'label' => 'LBL_SCRUBBED',
              ),
            ),
            3 => 
            array (
              0 => 
              array (
                'name' => 'discrepancies',
                'displayParams' => 
                array (
                  'cols' => 80,
                  'rows' => 6,
                ),
                'label' => 'LBL_DISCREPANCIES',
              ),
            ),
            4 => 
            array (
              0 => 
              array (
                'name' => 'description',
                'label' => 'LBL_DESCRIPTION',
              ),
            ),
        ),
      ),
);
