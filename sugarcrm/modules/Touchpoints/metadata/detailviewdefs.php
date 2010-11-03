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
$viewdefs['Touchpoints']['DetailView'] = array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'footerTpl'=>'modules/Touchpoints/tpls/rawData.tpl',
        'headerTpl'=>'modules/Touchpoints/tpls/DetailViewHeader.tpl',
        'buttons' => 
        array (
          0 => 'EDIT',
          1 => 'DUPLICATE',
          2 => 'DELETE',
          3 => array (
              'customCode' => '<input title="{$MOD.LBL_SCRUB_TITLE}" accessKey="{$MOD.LBL_SCRUB_BUTTON_KEY}" type="button" class="button" onClick="document.location=\'index.php?module=Touchpoints&action=ScrubView&record={$fields.id.value}&return_module=Touchpoints&return_action=DetailView&return_id={$fields.id.value}\'" name="scrub" value="{$MOD.LBL_SCRUB}" {if !$SHOW_SCRUB}disabled="disabled" {/if}/>'
          ),
          4 => 
          array (
            'customCode' => '<input title="{$MOD.LBL_RESCRUB_TITLE}" accessKey="{$MOD.LBL_RESCRUB_BUTTON_KEY}" type="button" class="button" onClick="if ( confirm(\'{$MOD.LBL_RESCRUB_WARNING}\') ) document.location=\'index.php?module=Touchpoints&action=ScrubView&record={$fields.id.value}&return_module=Touchpoints&return_action=DetailView&return_id={$fields.id.value}&rescrub=true\'" name="convert" value="{$MOD.LBL_RESCRUB}" {if !$SHOW_RESCRUB}disabled="disabled" {/if}/>',
          ),
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
            'type' => 'link',
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
            'label' => 'LBL_OTHER_PHONE',
          ),
        ),
        3 => 
        array (
          0 => 'parent_name',
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
            'name' => 'rating',
            'label' => 'LBL_RATING',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'industry',
            'label' => 'LBL_INDUSTRY',
          ),
          1 => 
          array (
            'name' => 'annual_revenue',
            'label' => 'LBL_ANNUAL_REVENUE',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'primary_address_street',
            'label' => 'LBL_PRIMARY_ADDRESS',
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'primary',
            ),
          ),
          1 => 
          array (
            'name' => 'alt_address_street',
            'label' => 'LBL_ALT_ADDRESS',
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'alt',
            ),
          ),
        ),
        13 => 
        array (
          0 =>
          array (
            'name' => 'conversion_date',
            'label' => 'LBL_CONVERSION_DATE',
          ),
        ),
        14 => 
        array (
          0 => 
          array (
            'name' => 'referred_by',
            'label' => 'LBL_REFERED_BY',
          ),
          1 => 
          array (
            'name' => 'status',
            'label' => 'LBL_STATUS',
          ),
        ),
        15 => 
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
        16 => 
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
      ),
      'lbl_leadcontact_info' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'full_name',
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
            'name' => 'title',
            'label' => 'LBL_TITLE',
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
            'name' => 'department',
            'label' => 'LBL_DEPARTMENT',
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
            'name' => 'phone_fax',
            'label' => 'LBL_FAX_PHONE',
          ),
          1 => 
          array (
            'name' => 'phone_other',
            'label' => 'LBL_OTHER_PHONE',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'do_not_call',
            'label' => 'LBL_DO_NOT_CALL',
          ),
          1 => 
          array (
            'name' => 'email1',
            'label' => 'LBL_EMAIL_ADDRESS',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'primary_address_street',
            'label' => 'LBL_PRIMARY_ADDRESS',
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'primary',
            ),
          ),
          1 => 
          array (
            'name' => 'alt_address_street',
            'label' => 'LBL_ALT_ADDRESS',
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'alt',
            ),
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'campaign_id',
            'label' => 'LBL_CAMPAIGN',
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
          1 => 
          array (
            'name' => 'scrub_result',
            'label' => 'LBL_SCRUB_RESULT',
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
        7 => 
        array (
          0 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAM',
          ),
          1 => 
          array (
            'name' => 'date_modified',
            'label' => 'LBL_DATE_MODIFIED',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO',
          ),
          1 => 
          array (
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
            'label' => 'LBL_DATE_ENTERED',
          ),
        ),
      ),
    ),
  );
