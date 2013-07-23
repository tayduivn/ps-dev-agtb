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

$dictionary['Styleguide'] = array(
    'table' => 'styleguide',
    'fields' => array (
        'parent_type' => array(
            'name'=>'parent_type',
            'vname'=>'LBL_PARENT_TYPE',
            'type' =>'parent_type',
            'dbType' => 'varchar',
            'group'=>'parent_name',
            'options'=> 'parent_type_display',
            'len'=> '255',
            'studio' => array('wirelesslistview'=>false),
            'comment' => 'Sugar module the Note is associated with',
        ),
        'parent_id' => array(
            'name'=>'parent_id',
            'vname'=>'LBL_PARENT_ID',
            'type'=>'id',
            'required'=>false,
            'reportable'=>true,
            'comment' => 'The ID of the Sugar item specified in parent_type',
        ),
        'description' => array (
            'name' => 'description',
            'vname' => 'LBL_NOTE_STATUS',
            'type' => 'text',
            'comment' => 'Full text of the note',
        ),
        'parent_name' => array(
            'name'=> 'parent_name',
            'parent_type'=>'record_type_display' ,
            'type_name'=>'parent_type',
            'id_name'=>'parent_id', 'vname'=>'LBL_RELATED_TO',
            'type'=>'parent',
            'source'=>'non-db',
            'options'=> 'record_type_display_notes',
            'studio' => true,
        ),
        'file_mime_type' => array (
            'name' => 'file_mime_type',
            'vname' => 'LBL_FILE_MIME_TYPE',
            'type' => 'varchar',
            'len' => '100',
            'comment' => 'Attachment MIME type',
            'importable' => false,
        ),
        'file_url'=> array(
            'name'=>'file_url',
            'vname' => 'LBL_FILE_URL',
            'type'=>'varchar',
            'source'=>'non-db',
            'reportable'=>false,
            'comment' => 'Path to file (can be URL)',
            'importable' => false,
        ),
        'filename' => array (
            'name' => 'filename',
            'vname' => 'LBL_FILENAME',
            'type' => 'file',
            'dbType' => 'varchar',
            'len' => '255',
            'reportable'=>true,
            'comment' => 'File name associated with the note (attachment)',
            'importable' => false,
        ),
        'currency_id' => array(
            'name' => 'currency_id',
            'dbType' => 'id',
            'vname' => 'LBL_CURRENCY_ID',
            'type' => 'currency_id',
            'function' => array('name' => 'getCurrencyDropDown', 'returns' => 'html'),
            'required' => false,
            'reportable' => false,
            'default'=>'-99',
            'comment' => 'Currency of the product',
        ),
        'list_price' =>  array(
            'name' => 'list_price',
            'vname' => 'LBL_LIST_PRICE',
            'type' => 'currency',
            'len' => '26,6',
            'audited' => true,
            'comment' => 'List price of product ("List" in Quote)',
        ),
    ),
    'indices' => array (

    ),
    'relationships' => array (

    ),
);

if (!class_exists('VardefManager')){
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('Styleguide','Styleguide', array('person', 'default', 'assignable'));
