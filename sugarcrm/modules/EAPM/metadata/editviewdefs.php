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
 *******************************************************************************/
$module_name = 'EAPM';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array('maxColumns' => '2',
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'),
                                            array('label' => '10', 'field' => '30')
                                            ),
                            'form' => array(
                                'hidden'=>array('<input name="assigned_user_id" type="hidden" value="{$fields.assigned_user_id.value}" autocomplete="off">'),
                                'buttons' =>
                                array (
                                  0 => 'SAVE',
                                  array (
                                    'customCode' => '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="window.location.href=\'index.php?action=EditView&module=Users&record={$return_id}\'; return false;" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">',
                                  ),
                                ),
                                'headerTpl'=>'modules/EAPM/tpls/EditViewHeader.tpl',
                                'footerTpl'=>'modules/EAPM/tpls/EditViewFooter.tpl',),
                                            ),

 'panels' =>array (
  'default' =>
  array (
    array(
        array(
            'name' => 'application',
            'displayParams'=>array('required'=>true)
        ), 'active',
    ),
    array (
        array('name' => 'name', 'displayParams' => array('required' => true) ),
        array('name'=>'password', 'type'=>'password', 'displayParams' => array('required' => true) ),
    ),
    array (
        array('name' => 'url',
              'displayParams' => array('required' => true),
              'customCode' => '<input type=\'text\' name=\'url\' id=\'url\' size=\'30\' maxlength=\'255\' value=\'{$fields.url.value}\' title=\'\' tabindex=\'104\' ><br>{$MOD.LBL_OMIT_URL}',
            )
    ),
    array (
        'description',
    ),
  ),

),

);
?>
