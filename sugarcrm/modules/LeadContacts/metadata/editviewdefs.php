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
$viewdefs['LeadContacts']['EditView'] = array (
    'templateMeta' => 
    array (
      'maxColumns' => '2',
      'form' => array('footerTpl'=>'modules/LeadContacts/tpls/footer.tpl'),
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
      'lbl_contact_information' => 
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
            'name' => 'leadaccount_name',
            'displayParams' => 
            array (
              'required' => true,
            ),
            'customCode' => '{if $smarty.request.prospect_id}{literal}<script type="text/javascript">document.getElementById(\'leadaccount_name_label\').innerHTML = \'\';</script>{/literal}{else}<input type="text" name="leadaccount_name" class="sqsEnabled" tabindex="l" id="leadaccount_name" size="" value="{$fields.leadaccount_name.value}" title="" autocomplete="off"  >
<input type="hidden" name="leadaccount_id" id="leadaccount_id" value="{$fields.leadaccount_id.value}">
<input type="button" name="btn_leadaccount_name" tabindex="l" title="Select [Alt+T]" accessKey="T" class="button" value="Select" onclick=\'{literal}open_popup("LeadAccounts", 600, 400, "", true, false, {"call_back_function":"set_return","form_name":"{/literal}{$form_name}{literal}","field_to_name_array":{"id":"leadaccount_id","name":"leadaccount_name"}}, "single", true);{/literal}\'>
<input type="button" name="btn_clr_leadaccount_name" tabindex="l" title="Clear [Alt+C]" accessKey="C" class="button" onclick="this.form.leadaccount_name.value = \'\'; this.form.leadaccount_id.value = \'\';" value="Clear">
{/if}',
            ),
          1 => 
          array (
            'name' => 'phone_fax',
            'label' => 'LBL_FAX_PHONE',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
          1 => 
          array (
            'name' => 'do_not_call',
            'label' => 'LBL_DO_NOT_CALL',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'team_name',
            'displayParams' => 
            array (
              'display' => true,
            ),
            'label' => 'LBL_TEAM',
          ),
          1 => 'status',
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'converted',
            'label' => 'LBL_CONVERTED',
          ),
          1 => 
          array (
            'name' => 'score',
            'label' => 'LBL_SCORE',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'campaign_name',
            'label' => 'LBL_CAMPAIGN',
          ),
          1 => 'last_interaction_date',
        ),
      ),
      'lbl_email_addresses' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'email1',
            'label' => 'LBL_EMAIL_ADDRESS',
          ),
        ),
      ),
      'lbl_address_information' => 
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
    ),
  );
