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
/*********************************************************************************
 * Description:
 * Created On: Oct 17, 2005
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): Chris Nojima
 ********************************************************************************/
// FILE SUGARCRM flav=int ONLY 
$mod_strings = array (	'LBL_DETAIL'				=> '细节',
						'LBL_MODULE_NAME'			=> '队列',
						'LBL_NAME'					=> '查询名称:',
						'LBL_STATUS'				=> '状态:',
						'LBL_PARENTS'				=> '父类查询',
						'LBL_CHILDREN'				=> '子类查询',
						'LBL_TYPE'					=> '类型',
						'LBL_CHOOSE_WHICH'			=> '选择哪个',
						'LBL_AVAILABLE_QUEUES'		=> '有效查询',
						'LBL_CHILD_QUEUES'			=> '增加/移除子查询',
						'LBL_PARENT_QUEUES'			=> '增加/移除父查询',
						'LBL_CONNECTED_QUEUES'		=> '已连接的查询',
						'LBL_REMOVED_TABS'			=> '已移除',
						'LBL_INHERITS_FROM'			=> '继承由:',
						'LBL_DISTRIBUTES_TO'		=> '分发到:',
						'LBL_WORKFLOWS_USED'		=> '分发方法:',
						'LBL_NUMBER_ITEMS'			=> '记录查询:',
						'LBL_NONE'					=> '无',
						'LBL_BASIC'					=> '查询信息',
						'LBL_INHERITANCE'			=> '层次细节',
						// Relationship Labels
						'LBL_CHILD_QUEUES_REL'		=> '子查询关系',
						'LBL_PARENT_QUEUES_REL'		=> '父查询关系',
						'LBL_QUEUES_WORKFLOW_REL'	=> '查询工作流程关系',
						'LBL_QUEUES_EMAILS_REL'		=> '查询电子邮件关系',
						// List Labels
						'LBL_LIST_FORM'				=> '查询:',
						'LBL_LIST_FORM_TITLE'		=> '查询列表:',
						'LBL_LIST_NAME'				=> '查询名称:',
						'LBL_LIST_PARENT'			=> '父类查询:',
						'LBL_LIST_STATUS'			=> '状态:',
						'LBL_LIST_TYPE'				=> '类型:',
						'LBL_LIST_STATUS'			=> '状态:',
						'LBL_LIST_QUEUED_ITEMS'		=> '记录查询',
						'LBL_LIST_WORKFLOWS'		=> '分发',
						// Home screen labels
						'LBL_HOME_TITLE'			=> '我的记录:',
						'LBL_BEAN_NAME'				=> '记录',
						'LBL_ASSOC_EVENT'			=> '相关任务',
						'LBL_INSTANT_ACTION'		=> '立即行动',
						'LBL_CREATE_NEW_CASE'		=> '新增用户反馈',
						'LBL_GET_SOME'				=> '获取更多记录',
						'LBL_REPLY'					=> '快速回复',
						// Subpanel Labels
						'LBL_EMAILS_SUBPANEL_TITLE' => '队列中的电子邮件',
						// Workflow function labels
						'LBL_WF_ROUNDROBIN'			=> '循环分发',
						'LBL_WF_MANUALPICK'			=> '手工挑选分发',
						'LBL_WF_LEASTBUSY'			=> '空闲分发',
						// Menu Links
					 /* LBL_LNK_ */
					 'LNK_LIST_MAILBOXES'	=> '所有邮件箱',
					 'LNK_LIST_CREATE_NEW'	=> '新增收件箱',
					 'LNK_LIST_QUEUES'		=> '所有队列',
					 'LNK_NEW_QUEUES'		=> '新增队列',
					 'LNK_LIST_SCHEDULER'	=> '工作计划',
					 'LNK_LIST_TEST_IMPORT'	=> '测试邮件导入',
						'LNK_LIST_QUEUES'			=> '所有队列',
						'LNK_NEW_QUEUES'			=> '新增队列',
						'LNK_SEED_QUEUES'			=> '团队的记录队列',
						/* DOM_ */
						'DOM_LBL_NONE'				=> '--无--',
						'DOM_ACTION_TYPE'			=> array ('Leads'	=> '新增潜在客户',
															  'Cases'	=> '新增客户反馈'),
					);
?>
