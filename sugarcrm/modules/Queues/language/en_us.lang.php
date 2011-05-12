<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=int ONLY
$mod_strings = array (	'LBL_DETAIL'				=> 'Details',
						'LBL_MODULE_NAME'			=> 'Queues',
						'LBL_NAME'					=> 'Queue Name:',
						'LBL_STATUS'				=> 'Status:',
						'LBL_PARENTS'				=> 'Parent Queues',
						'LBL_CHILDREN'				=> 'Child Queues',
						'LBL_TYPE'					=> 'Type',
						'LBL_CHOOSE_WHICH'			=> 'Choose Which',
						'LBL_AVAILABLE_QUEUES'		=> 'Available Queues',
						'LBL_CHILD_QUEUES'			=> 'Add/Remove Child Queues',
						'LBL_PARENT_QUEUES'			=> 'Add/Remove Parent Queues',
						'LBL_CONNECTED_QUEUES'		=> 'Connected Queues',
						'LBL_REMOVED_TABS'			=> 'Removed',
						'LBL_INHERITS_FROM'			=> 'Inherits From:',
						'LBL_DISTRIBUTES_TO'		=> 'Distributes To:',
						'LBL_WORKFLOWS_USED'		=> 'Distribution Method:',
						'LBL_NUMBER_ITEMS'			=> 'Items Queued:',
						'LBL_NONE'					=> 'None',
						'LBL_BASIC'					=> 'Queue Information',
						'LBL_INHERITANCE'			=> 'Hierarchy Details',
						// Relationship Labels
						'LBL_CHILD_QUEUES_REL'		=> 'Child Queue Relationship',
						'LBL_PARENT_QUEUES_REL'		=> 'Parent Queue Relationship',
						'LBL_QUEUES_WORKFLOW_REL'	=> 'Queues Workflow Relationship',
						'LBL_QUEUES_EMAILS_REL'		=> 'Queues Emails Relationship',
						// List Labels
						'LBL_LIST_FORM'				=> 'Queues:',
						'LBL_LIST_FORM_TITLE'		=> 'Queue List:',
						'LBL_LIST_NAME'				=> 'Queue Name:',
						'LBL_LIST_PARENT'			=> 'Parent Queue:',
						'LBL_LIST_STATUS'			=> 'Status:',
						'LBL_LIST_TYPE'				=> 'Type:',
						'LBL_LIST_STATUS'			=> 'Status:',
						'LBL_LIST_QUEUED_ITEMS'		=> 'Items Queued',
						'LBL_LIST_WORKFLOWS'		=> 'Distribution',
						// Home screen labels
						'LBL_HOME_TITLE'			=> 'My Items: ',
						'LBL_BEAN_NAME'				=> 'Item',
						'LBL_ASSOC_EVENT'			=> 'Associated Task',
						'LBL_INSTANT_ACTION'		=> 'Instant Action',
						'LBL_CREATE_NEW_CASE'		=> 'Create a new Case',
						'LBL_GET_SOME'				=> 'Get More Items',
						'LBL_REPLY'					=> 'Quick Reply',
						// Subpanel Labels
						'LBL_EMAILS_SUBPANEL_TITLE' => 'Queued Emails',
						// Workflow function labels
						'LBL_WF_ROUNDROBIN'			=> 'Round-Robin Distribution',
						'LBL_WF_MANUALPICK'			=> 'Manual Pick Distribution',
						'LBL_WF_LEASTBUSY'			=> 'Least Busy Distribution',
						// Menu Links
					 /* LBL_LNK_ */
					 'LNK_LIST_MAILBOXES'	=> 'All Mailboxes',
					 'LNK_LIST_CREATE_NEW'	=> 'Monitor New Mailbox',
					 'LNK_LIST_QUEUES'		=> 'All Queues',
					 'LNK_NEW_QUEUES'		=> 'Create New Queue',
					 'LNK_LIST_SCHEDULER'	=> 'Schedulers',
					 'LNK_LIST_TEST_IMPORT'	=> 'Test Email Import',
						'LNK_LIST_QUEUES'			=> 'All Queues',
						'LNK_NEW_QUEUES'			=> 'Create New Queue',
						'LNK_SEED_QUEUES'			=> 'Seed Queues From Teams',
						/* DOM_ */
						'DOM_LBL_NONE'				=> '--None--',
						'DOM_ACTION_TYPE'			=> array ('Leads'	=> 'Create Lead',
															  'Cases'	=> 'Create Case'),
	'LBL_EDITLAYOUT' => 'Edit Layout' /*for 508 compliance fix*/,
	'LBL_EMAILS' => 'Emails' /*for 508 compliance fix*/,
	'LBL_CASES' => 'Cases' /*for 508 compliance fix*/,
);
?>
