<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
$viewdefs['Leads']['SideQuickCreate'] = array(
    'templateMeta' => array('form'=>array('buttons'=>array('SAVE'),
    									  'headerTpl'=>'include/EditView/header.tpl',
    									  'footerTpl'=>'include/EditView/footer.tpl',
    									  'button_location'=>'bottom',
    									 ),
							'maxColumns' => '1',

							'panelClass'=>'none',
							'labelsOnTop'=>true,
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'),

                                        ),
),
 'panels' =>array (
  'DEFAULT' =>
  array (

    array (
      array('name'=>'first_name', 'displayParams'=>array('size'=>20)),
    ),
    array (
      array('name'=>'last_name',
            'displayParams'=>array('required'=>true, 'size'=>20),
      ),
    ),
    array (
     array('name'=>'phone_work', 'displayParams'=>array('size'=>20)),
    ),
   array (
     array('name'=>'email1', 'customCode'=>'<input type="text" name="emailAddress0" size=20><input type="hidden" name="emailAddressPrimaryFlag" value="emailAddress0"><input type="hidden" name="useEmailWidget" value="true"><script language="Javascript">addToValidate("form_SideQuickCreate_Leads", "emailAddress0", "email", false, SUGAR.language.get("app_strings", "LBL_EMAIL_ADDRESS_BOOK_EMAIL_ADDR"));</script>'),
    ),
    array (
      array('name'=>'assigned_user_name', 'displayParams'=>array('required'=>true, 'size'=>11, 'selectOnly'=>true)),
    ),
  ),

)


);
?>
