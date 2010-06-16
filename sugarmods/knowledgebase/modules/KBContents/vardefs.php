<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
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
 * by SugarCRM are Copyright (C) 2007 SugarCRM, Inc.; All Rights Reserved.
 * /*********************************************************************************
 * $Id: vardefs.php 20505 2007-02-28 00:18:41 +0000 (Wed, 28 Feb 2007) vineet $
 * Description: TODO:  To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/



// $Id: vardefs.php  2007-07-02 17:14:54 +0000 (Wed, 07 Feb 2007) vineet $

$dictionary['KBContent'] = array(
	'table' => 'kbcontents',
	'audited' => true,
	'mysqlengine' => 'MyISAM',
	'comment' => 'A content represents information about document',
	'fields' => array (
		'id' => array (
			'name' => 'id',
			'type' => 'id',
			'vname' => 'LBL_ID',
			'required' => true,
			'reportable' => false,
			'comment' => 'Unique identifer'
		),
		
		'kbdocument_body' => array (
			'name' => 'kbdocument_body',
			'vname' => 'LBL_TEXT_BODY',
			'type' => 'longtext',
			'comment' => 'Article body',
		),		
		'document_revision_id' => array (
			'name' => 'document_revision_id',
			'vname' => 'LBL_DOCUMENT_REVISION_ID',
			'type' => 'id',
			'audited' => true,
			'reportable' => false,
			'comment' => 'The document revision id to which this content is associated'
		),
		
		'created_by_link' => array (
			'name' => 'created_by_link',
			'type' => 'link',
			'relationship' => 'contents_created_by',
			'vname' => 'LBL_CREATED_BY_USER',
			'link_type' => 'one',
			'module' => 'Users',
			'bean_name' => 'User',
			'source' => 'non-db',
		),
		'date_entered' => array (
			'name' => 'date_entered',
			'vname' => 'LBL_DATE_ENTERED',
			'type' => 'datetime',
			'comment' => 'Date record created'
		),
		'date_modified' => array (
			'name' => 'date_modified',
			'vname' => 'LBL_DATE_MODIFIED',
			'type' => 'datetime',
			'comment' => 'Date record last modified'
		),
		'deleted' => array (
			'name' => 'deleted',
			'vname' => 'LBL_DELETED',
			'type' => 'bool',
			'default' => 0,
			'reportable' => false,
			'comment' => 'Record deletion indicator'
		),
		'modified_user_id' => array (
			'name' => 'modified_user_id',
			'rname' => 'user_name',
			'id_name' => 'modified_user_id',
			'vname' => 'LBL_MODIFIED',
			'type' => 'assigned_user_name',
			'table' => 'users',
			'isnull' => false,
			'reportable' => true,
			'dbType' => 'id'
		), 
		'modified_user_link' => array (
			'name' => 'modified_user_link',
			'type' => 'link',
			'relationship' => 'contents_modified_user',
			'vname' => 'LBL_MODIFIED_BY_USER',
			'link_type' => 'one',
			'module' => 'Users',
			'bean_name' => 'User',
			'source' => 'non-db',
		),
		'team_id' => array (
			'name' => 'team_id',
			'vname' => 'LBL_TEAM_ID',
			'reportable' => false,
			'dbtype' => 'id',
			'type' => 'team_list',
			'audited' => true,
			'comment' => 'Team ID for the contract'
		),
		'team_link' => array (
			'name' => 'team_link',
			'type' => 'link',
			'relationship' => 'contents_team',
			'vname' => 'LBL_TEAMS_LINK',
			'link_type' => 'one',
			'module' => 'Teams',
			'bean_name' => 'Team',
			'source' => 'non-db',
		),
		'team_name' => array (
			'name' => 'team_name',
			'rname' => 'name',
			'id_name' => 'team_id',
			'vname' => 'LBL_TEAM',
			'type' => 'relate',
			'table' => 'teams',
			'isnull' => 'true',
			'module' => 'Teams',
			'massupdate' => false,
			'dbType' => 'varchar',
			'source'=>'non-db',
			'len' => 36,
			'link'=>'team_link'
		),
						
	),
	'indices' => array (
       array('name' =>'kbcontentspk', 'type' =>'primary', 'fields'=>array('id')), 
       array('name' =>'kbcontentsftk', 'type' =>'fulltext','fields'=>array('kbdocument_body'),'stoplist'=>''),                   
       ),
);
?>
