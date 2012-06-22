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

$dictionary['PdfManager'] = array(
    'table'=>'pdfmanager',
    'favorites' => false,
    'audited'=>false,
    'duplicate_merge'=>true,
    'fields'=>array (
        'base_module' =>
        array (
            'required' => true,
            'name' => 'base_module',
            'vname' => 'LBL_BASE_MODULE',
            'type' => 'enum',
            'massupdate' => 0,
            'default' => '',
            'comments' => '',
            'help' => '',
            'function' => 'getPdfManagerAvailableModules',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => 100,
            'size' => '20',
            'options' => 'moduleList',
            'studio' => false,
            'dependency' => false,
        ),
        'published' =>
        array (
            'required' => false,
            'name' => 'published',
            'vname' => 'LBL_PUBLISHED',
            'type' => 'enum',
            'massupdate' => 0,
            'default' => 'yes',
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => 100,
            'size' => '20',
            'options' => 'pdfmanager_yes_no_list',
            'studio' => false,
            'dependency' => false,
        ),
        'field' =>
        array (
            'required' => false,
            'name' => 'field',
            'vname' => 'LBL_FIELD',
            'type' => 'enum',
            'massupdate' => 0,
            'default' => '0',
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => 100,
            'size' => '20',
            'options' => 'Elastic_boost_options',
            'studio' => false,
            'dependency' => 'not(equal($base_module, "Reports"))',
        ),
        'body_html' =>
        array (
            'required' => false,
            'name' => 'body_html',
            'vname' => 'LBL_BODY_HTML',
            'type' => 'text',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'size' => '20',
            'studio' => false,
            'rows' => '4',
            'cols' => '20',
            'dependency' => 'not(equal($base_module, "Reports"))',
        ),
        'header_image' =>
        array (
            'required' => false,
            'name' => 'header_image',
            'vname' => 'LBL_HEADER_IMAGE',
            'type' => 'image',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => 255,
            'size' => '20',
            'studio' => false,
            'dbType' => 'varchar',
            'border' => '',
            'width' => '120',
            'height' => '',
            'dependency' => 'equal($base_module, "Reports")',
        ),
        'header_image_ext' =>
        array (
            'required' => false,
            'name' => 'header_image_ext',
            'vname' => '',
            'type' => 'text',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => 10,
            'size' => '10',
            'studio' => false,
            'dbType' => 'varchar',
            'border' => '',
            'width' => '10',
            'height' => '',
            'dependency' => 'equal($base_module, "Reports")',
        ),
        'author' =>
        array (
            'required' => true,
            'name' => 'author',
            'vname' => 'LBL_AUTHOR',
            'type' => 'varchar',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => '255',
            'size' => '20',
        ),
        'title' =>
        array (
            'required' => false,
            'name' => 'title',
            'vname' => 'LBL_TITLE',
            'type' => 'varchar',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => '255',
            'size' => '20',
        ),
        'subject' =>
        array (
            'required' => false,
            'name' => 'subject',
            'vname' => 'LBL_SUBJECT',
            'type' => 'varchar',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => '255',
            'size' => '20',
        ),
        'keywords' =>
        array (
            'required' => false,
            'name' => 'keywords',
            'vname' => 'LBL_KEYWORDS',
            'type' => 'varchar',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => '255',
            'size' => '20',
        ),
    ),
    'relationships'=>array (),
    'optimistic_locking'=>true,
    'unified_search'=>true,
);

if (!class_exists('VardefManager')) {
        require_once 'include/SugarObjects/VardefManager.php';
}
VardefManager::createVardef('PdfManager','PdfManager', array('basic','team_security','assignable'));
