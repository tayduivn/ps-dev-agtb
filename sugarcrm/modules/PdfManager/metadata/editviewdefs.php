<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

//FILE SUGARCRM flav=pro ONLY

$viewdefs['PdfManager'] =
array (
  'EditView' =>
  array (
    'templateMeta' =>
    array (
        'form' => array(
                            'footerTpl' => 'modules/PdfManager/tpls/EditViewFooter.tpl',
                            'enctype'=>'multipart/form-data',
                            'hidden' => array(
                                '<input type="hidden" name="base_module_history" id="base_module_history" value="{$fields.base_module.value}">',
                            )
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
      'includes' => array (
          array (
              'file' => 'modules/PdfManager/javascript/PdfManager.js',
          ),
      ),
      'useTabs' => false,
      'syncDetailEditViews' => false,
    ),
    'panels' =>
    array (
      'default' =>
      array (
        0 =>
        array (
          0 => 'name',
          1 =>
          array (
            'name' => 'team_name',
            'displayParams' =>
            array (
              'display' => true,
            ),
          ),
        ),
        1 =>
        array (
          0 => array(   'name' => 'description',
                        'displayParams' => array('rows' => 1)
                    ),
        ),
        2 =>
        array (
          0 =>
          array (
            'name' => 'base_module',
            'label' => 'LBL_BASE_MODULE',
            'popupHelp' => 'LBL_BASE_MODULE_POPUP_HELP',
            'displayParams' =>
            array (
                'field' => array (
                    'onChange' => 'SUGAR.PdfManager.loadFields(this.value, \'\');',
                ),
            ),
          ),
          1 =>
          array (
            'name' => 'published',
            'label' => 'LBL_PUBLISHED',
            'popupHelp' => 'LBL_PUBLISHED_POPUP_HELP',
          ),
        ),
        3 =>
        array (
          0 =>
          array (
            'name' => 'field',
            'label' => 'LBL_FIELD',
            'customCode' => '{include file="modules/PdfManager/tpls/getFields.tpl"}',
            'popupHelp' => 'LBL_FIELD_POPUP_HELP',
          ),
        ),
        4 =>
        array (
          0 =>
          array (
            'name' => 'body_html',
            'label' => 'LBL_BODY_HTML',
            'popupHelp' => 'LBL_BODY_HTML_POPUP_HELP',
          ),
        ),
      ),
      'lbl_editview_panel1' =>
      array (
        0 =>
        array (
          0 =>
          array (
            'name' => 'author',
            'label' => 'LBL_AUTHOR',
          ),
          1 =>
          array (
            'name' => 'title',
            'label' => 'LBL_TITLE',
          ),
        ),
        1 =>
        array (
          0 =>
          array (
            'name' => 'subject',
            'label' => 'LBL_SUBJECT',
          ),
          1 =>
          array (
            'name' => 'keywords',
            'label' => 'LBL_KEYWORDS',
            'popupHelp' => 'LBL_KEYWORDS_POPUP_HELP'
          ),
        ),
      ),
    ),
  ),
);
