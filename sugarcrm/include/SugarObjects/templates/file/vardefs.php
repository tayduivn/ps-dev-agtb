<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id$
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
 
 $vardefs=array(
  'fields' => array (

  'document_name' =>
  array (
    'name' => 'document_name',
    'vname' => 'LBL_NAME',
    'type' => 'name',
  	'link' => true, // bug 39288 
	'dbType' => 'varchar',
    'len' => '255',
    'required'=>true,
    'unified_search' => true,
    'full_text_search' => array('boost' => 3),
    'duplicate_on_record_copy' => 'always',
  ),

'name'=>
  array(
	'name'=>'name',
	'source'=>'non-db',
	'type'=>'varchar',
	'db_concat_fields'=> array(0=>'document_name'),
    'duplicate_on_record_copy' => 'always',
	),

'filename' =>
  array (
    'name' => 'filename',
    'vname' => 'LBL_FILENAME',
    'type' => 'varchar',
    'required'=>true,
	'importable' => 'required',
    'len' => '255',
    'studio' => 'false',
    'duplicate_on_record_copy' => 'always',
  ),
  'file_ext' =>
  array (
    'name' => 'file_ext',
    'vname' => 'LBL_FILE_EXTENSION',
    'type' => 'varchar',
    'len' => 100,
    'duplicate_on_record_copy' => 'always',
  ),
  'file_mime_type' =>
  array (
    'name' => 'file_mime_type',
    'vname' => 'LBL_MIME',
    'type' => 'varchar',
    'len' => '100',
    'duplicate_on_record_copy' => 'always',
  ),


'uploadfile' =>
  array (
     'name'=>'uploadfile',
     'vname' => 'LBL_FILE_UPLOAD',
     'type' => 'file',
     'source' => 'non-db',
     'duplicate_on_record_copy' => 'always',
    //'noChange' => true,
    // jwhitcraft BUG44657 - Take this out as it was causing the remove button not to show up on custom modules
  ),

'active_date' =>
  array (
    'name' => 'active_date',
    'vname' => 'LBL_DOC_ACTIVE_DATE',
    'type' => 'date',
	'required'=>true,
    'importable' => 'required',
    'display_default' => 'now',
    'duplicate_on_record_copy' => 'always',
  ),

'exp_date' =>
  array (
    'name' => 'exp_date',
    'vname' => 'LBL_DOC_EXP_DATE',
    'type' => 'date',
    'duplicate_on_record_copy' => 'always',
  ),

  'category_id' =>
  array (
    'name' => 'category_id',
    'vname' => 'LBL_SF_CATEGORY',
    'type' => 'enum',
    'len' => 100,
    'options' => 'document_category_dom',
    'reportable'=>false,
    'duplicate_on_record_copy' => 'always',
  ),

  'subcategory_id' =>
  array (
    'name' => 'subcategory_id',
    'vname' => 'LBL_SF_SUBCATEGORY',
    'type' => 'enum',
    'len' => 100,
    'options' => 'document_subcategory_dom',
    'reportable'=>false,
    'duplicate_on_record_copy' => 'always',
  ),

  'status_id' =>
  array (
    'name' => 'status_id',
    'vname' => 'LBL_DOC_STATUS',
    'type' => 'enum',
    'len' => 100,
    'options' => 'document_status_dom',
    'reportable'=>false,
    'duplicate_on_record_copy' => 'always',
  ),

  'status' =>
  array (
    'name' => 'status',
    'vname' => 'LBL_DOC_STATUS',
    'type' => 'varchar',
    'source' => 'non-db',
    'duplicate_on_record_copy' => 'always',
    'Comment' => 'Document status for Meta-Data framework',
  ),
 )
);

