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
$viewdefs['Calls']['SideQuickCreate'] = array(
    'templateMeta' => array('form'=>array('hidden'=>array('<input type="hidden" name="isSaveAndNew" value="false">',
                                                          '<input type="hidden" name="send_invites">',
                                                          '<input type="hidden" name="user_invitees">',
                                                          '<input type="hidden" name="contact_invitees">',
                                                          '<input type="hidden" name="duration_hours" value="0">',
                                                          '<input type="hidden" name="duration_minutes" value="15">',
                                                          '<input type="hidden" name="status" id="status" value="Planned">',
                                                     ),
                                          'headerTpl'=>'include/EditView/header.tpl',
                                          'footerTpl'=>'include/EditView/footer.tpl',
                                          'buttons'=>array('SAVE'),
    								      'button_location'=>'bottom'
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
    array(
        array('name'=>'name', 'label'=>'', 'customCode'=>'<div valign="top">{literal}<input type="radio" name="appttype" checked=true onchange="if(this.checked){this.form.module.value=\'Calls\'; this.form.return_module.value=\'Calls\'; this.form.direction.style.display = \'inline\';}">{/literal}{sugar_translate label="LBL_CALL"}{literal}<input type="radio" name="appttype" onchange="if(this.checked){this.form.module.value=\'Meetings\'; this.form.return_module.value=\'Meetings\'; this.form.direction.style.display=\'none\'}">{/literal}{sugar_translate label="LBL_MEETING"}</div>'),
    ),
    array (
      array('name'=>'name', 'displayParams'=>array('size'=>20, 'required'=>true)),
    ),  
    array (
      array('name'=>'date_start',       
            'type'=>'datetimecombo',
            'displayParams'=>array('required' => true, 'splitDateTime'=>true),
            'label'=>'LBL_DATE_TIME'),
    ),
   array (
      array('name'=>'parent_name', 'displayParams'=>array('size'=>11, 'selectOnly'=>true, 'split'=>true)),
    ), 
    array (
      array('name'=>'assigned_user_name', 'displayParams'=>array('required'=>true, 'size'=>11, 'selectOnly'=>true)),
    ),
  array (
      array (
        'name' => 'status',
        'displayParams' => array('required'=>true),
        'fields' => 
        array (
          array('name'=>'status'),
          array('name'=>'direction'),
        ),
      ),
    ),
  ),

 )


);