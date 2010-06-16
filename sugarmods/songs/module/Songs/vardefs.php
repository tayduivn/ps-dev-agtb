<?php
/**
 * Table definition file for the project table
 *
 * PHP version 4
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: vardefs.php 13951 2006-06-12 19:44:03Z awu $

$dictionary['Song'] = array(
	'table' => 'songs',
	'fields' => array(
		'id' => array(
			'name' => 'id',
			'vname' => 'LBL_ID',
			'required' => true,
			'type' => 'id',
			'reportable'=>false,
		),
		'date_entered' => array(
			'name' => 'date_entered',
			'vname' => 'LBL_DATE_ENTERED',
			'type' => 'datetime',
			'required' => true,
		),
		'created_by' => array(
			'name' => 'created_by',
			'rname' => 'user_name',
			'id_name' => 'modified_user_id',
			'vname' => 'LBL_CREATED_BY',
			'type' => 'assigned_user_name',
			'table' => 'users',
			'isnull' => 'false',
			'dbType' => 'id',
		),
		'date_modified' => array(
			'name' => 'date_modified',
			'vname' => 'LBL_DATE_MODIFIED',
			'type' => 'datetime',
			'required' => true,
		),
		'modified_user_id' => array(
			'name' => 'modified_user_id',
			'rname' => 'user_name',
			'id_name' => 'modified_user_id',
			'vname' => 'LBL_MODIFIED_USER_ID',
			'type' => 'assigned_user_name',
			'table' => 'users',
			'isnull' => 'false',
			'dbType' => 'id',
			'required' => true,
			'default' => '',
			'reportable'=>true,
		),
		'deleted' => array(
			'name' => 'deleted',
			'vname' => 'LBL_DELETED',
			'type' => 'bool',
			'required' => true,
			'default' => '0',
		),	
		'title' => array(
			'name' => 'title',
			'vname' => 'LBL_TITLE',
			'required' => true,
			'type' => 'varchar',
			'len' => 255,
		),
		'length' => array(
			'name' => 'length',
			'vname' => 'LBL_LENGTH',
			'type' => 'int',
			'default'=> 0
		),
		'description' => array(
			'name' => 'description',
			'vname' => 'LBL_COMMENT',
			'type' => 'text',
		),
		'bitrate' => array(
			'name' => 'bitrate',
			'vname' => 'LBL_BITRATE',
			'type' => 'varchar',
			'len' => 20,
		),
		'explicit' => array(
			'name' => 'explicit',
			'vname' => 'LBL_EXPLICIT',
			'type' => 'bool',
		),	
   		'genre' => 
  		array (
    		'name' => 'genre',
    		'vname' => 'LBL_GENRE',
    		'type' => 'enum',
    		'options' => 'song_genre_dom',
		    'len'=>'36',
  		),
   		'format' => 
  		array (
    		'name' => 'format',
    		'vname' => 'LBL_FORMAT',
    		'type' => 'enum',
    		'options' => 'song_format_dom',
		    'len'=>'36',
  		),  
  		'artists' => 
  			array (
  			'name' => 'artists',
    		'type' => 'link',
    		'relationship' => 'contacts_songs',
    		'source'=>'non-db',
  		),
  		'albums' => 
  			array (
  			'name' => 'albums',
    		'type' => 'link',
    		'relationship' => 'products_songs',
    		'source'=>'non-db',
  		),  		
	),
	
	'indices' => array(
		array('name' =>'song_primary_key_index',
			'type' =>'primary',
			'fields'=>array('id')
			),
	),
);
?>