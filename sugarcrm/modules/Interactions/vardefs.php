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

$dictionary['Interaction'] = array(
    'table' => 'interactions',
    'audited'=>true, 
    'unified_search' => false, 
    /* SADEK CUSTOMIZATION - BEGIN NEW CODE FOR FULL TEXT SEARCH */ 
    'full_text_unified' => true, 
    /* SADEK CUSTOMIZATION - END NEW CODE FOR FULL TEXT SEARCH */ 
    'duplicate_merge'=>true, 
    'comment' => 'Leads are persons of interest early in a sales cycle', 
    'fields' => array (           
        'parent_id' => array(
        	'name' => 'parent_id',
        	'type' => 'varchar',
        	'len' => '45',
        	'group'=>'parent_name',
        	'vname'=>'LBL_PARENT_ID',
            ),
        'parent_type' => array(
        	'name' => 'parent_type',
        	'type' => 'varchar',
        	'len' => '45',
        	'group'=>'parent_name',
        	'vname'=>'LBL_PARENT_TYPE',
            ),
        'parent_name'=> array(
        	'name'=> 'parent_name',
        	'parent_type'=>'record_type_display' ,
        	'type_name'=>'parent_type',
        	'id_name'=>'parent_id',
        	'vname'=>'LBL_LIST_RELATED_TO',
        	'type'=>'parent',
        	'group'=>'parent_name',
        	'source'=>'non-db',
        	'options'=> 'parent_type_display',
            ),
		'type' => array(
			'name' => 'type',
			'type' => 'varchar',
			'reportable'=>true,
            'vname' => 'LBL_TYPE',
			'len' => '50',
			),
		'source_id' => array(
			'name' => 'source_id',
			'type' => 'varchar',
			'len' => '45',
        	'group'=>'source_name',
        	'vname' => 'LBL_SOURCE_ID',
			),
		'source_module' => array(
			'name' => 'source_module',
        	'vname' => 'LBL_SOURCE_MODULE',
        	'type' => 'varchar',
        	'group'=>'source_name',
			'len' => '45',
			),
        'source_name'=> array(
        	'name'=> 'source_name',
        	'parent_type'=>'record_type_display' ,
        	'type_name'=>'source_module',
        	'id_name'=>'source_id',
        	'vname'=>'LBL_SOURCE',
        	'type'=>'parent',
        	'group'=>'source_name',
        	'source'=>'non-db',
        	'options'=> 'interaction_source_type_display',
            ),
		'scrub_complete_date' => array(
			'name' => 'scrub_complete_date',
            'vname' => 'LBL_SCRUB_COMPLETE_DATE',
			'reportable'=>true,
			'type' => 'datetime',
			),
		'start_date' => array(
			'name' => 'start_date',
            'vname' => 'LBL_START_DATE',
			'reportable'=>true,
			'type' => 'date',
			),
		'end_date' => array(
			'name' => 'end_date',
			'vname' => 'LBL_END_DATE',
			'reportable'=>true,
			'type' => 'date',
			),
		'score' => array(
			'name' => 'score',
			'vname' => 'LBL_SCORE',
			'reportable'=>true,
			'type' => 'score',
			'dbType' => 'decimal',
			'len' => '12,2',
			),
		// Sadek Pardot: New Pardot field to store the pardot visitory activity id
		'visitor_activity_id' => array(
			'name' => 'visitor_activity_id',
			'vname' => 'LBL_VISITOR_ACTIVITY_ID',
			'type' => 'int',
			'len' => '50',
			),
		// Sadek Pardot: New Pardot field to store the campaign_id

		'campaign_id' => array (
			'name' => 'campaign_id',
			'type' => 'enum',
			'dbtype'=>'id',
			'vname'=>'LBL_CAMPAIGN_NAME',
			'comment' => 'Campaign that generated lead',
			'options' => 'campaign_list',
			'massupdate' => false,
		),
		'campaign_name' =>
		array (
			'name' => 'campaign_name',
			'rname' => 'name',
			'id_name' => 'campaign_id',
			'vname' => 'LBL_CAMPAIGN_NAME',
			'type' => 'relate',
			'table' => 'campaigns',
			'join_name'=>'campaigns',
			'isnull' => 'true',
			'module' => 'Campaigns',
			'dbType' => 'varchar',
			'link'=>'campaigns',
			'len' => '255',
			'source'=>'non-db',
			'reportable' => 0,
//			'custom_type'=>1,
//			'ext2' => 'Campaigns',
//			'ext3' => 'campaign_id',
		),

		// Sadek Pardot: added relationship to touchpoints for reporting purposes 
		'touchpoints' =>
		array (
			'name' => 'touchpoints',
			'type' => 'link',
			'relationship' => 'touchpoints_interactions',
			'source'=>'non-db',
			'link_type'=>'one',
			'module'=>'Touchpoints',
			'bean_name'=>'Touchpoint',
			'vname'=>'LBL_TOUCHPOINTS',
		),
		'campaigns' =>
		array (
			'name' => 'campaigns',
			'type' => 'link',
			'relationship' => 'campaigns_interactions',
			'source'=>'non-db',
			'link_type'=>'one',
			'module'=>'Campaigns',
			'bean_name'=>'Campaign',
			'vname'=>'LBL_CAMPAIGNS',
		),
        'touchpoint_title' => array(
            'name' => 'touchpoint_title',
            'vname' => 'LBL_TITLE',
            'type' => 'varchar',
            'source' => 'non-db',
            ),
		),
	'indices' => array(
		'idx_int_parent_id' => array(
			'name' => 'idx_int_parent_id',
			'type' => 'index',
			'fields' => array(
				'0' => 'id',
				'1' => 'parent_id',
				),
			),
		'fk_interactions_parent_id' => array(
			'name' => 'fk_interactions_parent_id',
			'type' => 'index',
			'fields' => array(
				'0' => 'parent_id',
				),
			),
		'fk_source_id_module' => array(
			'name' => 'fk_source_id_module',
			'type' => 'index',
			'fields' => array(
				'0' => 'source_id',
				'1' => 'source_module',
				),
			),
		// Sadek Pardot: Index for the new visitor_activity_id field
		'fk_visitor_activity_id' => array(
			'name' => 'fk_visitor_activity_id',
			'type' => 'index',
			'fields' => array(
				'0' => 'visitor_activity_id',
				),
			),
        ),
	'relationships' => array(

           
		// Sadek Pardot: added relationship to touchpoints for reporting purposes 
	   'touchpoints_interactions' => array(
                'lhs_module'=> 'Touchpoints',
                'lhs_table'=> 'touchpoints',
                'lhs_key' => 'id',
                'rhs_module'=> 'Interactions',
                'rhs_table'=> 'interactions',
                'rhs_key' => 'source_id',
                'relationship_type'=>'one-to-many',
                ),
	   'campaigns_interactions' => array(
                'lhs_module'=> 'Campaigns',
                'lhs_table'=> 'campaigns',
                'lhs_key' => 'id',
                'rhs_module'=> 'Interactions',
                'rhs_table'=> 'interactions',
                'rhs_key' => 'campaign_id',
                'relationship_type'=>'one-to-many',
                ),
	   
		),
    );

VardefManager::createVardef('Interactions','Interaction', array('default', 'assignable',
// BEGIN SUGARCRM PRO ONLY
'team_security',
// END SUGARCRM PRO ONLY
));
