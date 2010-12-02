<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$viewdefs['Prospects']['EditView'] = array(
    'templateMeta' => array('maxColumns' => '2', 
                            //BEGIN SUGARCRM flav=pro || flav=sales ONLY
                            'useTabs' => true,
                            //END SUGARCRM flav=pro || flav=sales ONLY
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'), 
                                            array('label' => '10', 'field' => '30')
                                            ),
     ),
 'panels' =>array (
  'lbl_prospect_information' => 
  array (
    
    array (
      array (
        'name' => 'first_name',
        'customCode' => '{html_options name="salutation" id="salutation" options=$fields.salutation.options selected=$fields.salutation.value}' 
      . '&nbsp;<input name="first_name"  id="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
      ),
    ),
    
    array (
      array('name'=>'last_name',
            'displayParams'=>array('required'=>true),
      ),
      'phone_work',
    ),
    
    array (
      'title',
      'phone_mobile',
    ),
    
    array (
      'department',
      'phone_fax',
    ),
    
    array (
      'account_name',
    ),
    
    array (
      array (
	      'name' => 'primary_address_street',
          'hideLabel' => true,      
	      'type' => 'address',
	      'displayParams'=>array('key'=>'primary', 'rows'=>2, 'cols'=>30, 'maxlength'=>150),
      ),
      
      array (
	      'name' => 'alt_address_street',
	      'hideLabel'=>true,
	      'type' => 'address',
	      'displayParams'=>array('key'=>'alt', 'copy'=>'primary', 'rows'=>2, 'cols'=>30, 'maxlength'=>150),      
      ),
    ),
    array('email1'),
    array (
      array('name'=>'description', 
            'label'=>'LBL_DESCRIPTION'),
    ),
    ),
  'LBL_MORE_INFORMATION' => array(
    array (
      'do_not_call',
    ),
    ),
  'LBL_PANEL_ASSIGNMENT' => array(
    array (
	  'assigned_user_name',
	  //BEGIN SUGARCRM flav=pro ONLY
	  array('name'=>'team_name','displayParams'=>array('required'=>true)),
	  //END SUGARCRM flav=pro ONLY
    ),    

  ),
)


);
?>