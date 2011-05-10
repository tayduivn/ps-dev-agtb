<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Default English language strings
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

// $Id: en_us.lang.php 13782 2006-06-06 17:58:55 +0000 (Tue, 06 Jun 2006) majed $

$mod_strings = array (
	'LBL_MODULE_NAME' => '项目',
	'LBL_MODULE_TITLE' => '项目:首页',
	'LBL_SEARCH_FORM_TITLE' => '查找项目',
    'LBL_LIST_FORM_TITLE' => '项目列表',
    'LBL_HISTORY_TITLE' => '历史记录',

	'LBL_ID' => '编号:',
	'LBL_DATE_ENTERED' => '输入日期:',
	'LBL_DATE_MODIFIED' => '修改日期:',
	'LBL_ASSIGNED_USER_ID' => '负责人:',
	'LBL_MODIFIED_USER_ID' => '修改人编号:',
	'LBL_CREATED_BY' => '创建人:',
	'LBL_TEAM_ID' => '团队:',
	'LBL_NAME' => '名称:',
	'LBL_DESCRIPTION' => '说明:',
	'LBL_DELETED' => '已删除:',
	'LBL_DATE_START' => '开始日期:',
	'LBL_DATE_END' => '结束日期:',
	'LBL_PRIORITY' => '优先级:',

	'LBL_TOTAL_ESTIMATED_EFFORT' => '估算总时间(小时):',
	'LBL_TOTAL_ACTUAL_EFFORT' => '实际总时间(小时):',

	'LBL_LIST_NAME' => '名称',
	'LBL_LIST_ASSIGNED_USER_ID' => '负责人',
	'LBL_LIST_TOTAL_ESTIMATED_EFFORT' => '估算总时间(小时)',
	'LBL_LIST_TOTAL_ACTUAL_EFFORT' => '实际总时间(小时)',

	'LBL_PROJECT_SUBPANEL_TITLE' => '项目',
	'LBL_PROJECT_TASK_SUBPANEL_TITLE' => '项目任务',
	'LBL_CONTACT_SUBPANEL_TITLE' => '联系人',
	'LBL_ACCOUNT_SUBPANEL_TITLE' => '客户',
	'LBL_OPPORTUNITY_SUBPANEL_TITLE' => '商业机会',
	'LBL_QUOTE_SUBPANEL_TITLE' => '报价',

	'CONTACT_REMOVE_PROJECT_CONFIRM' => '您确定要从这个项目移除联系人?',
	
	'LNK_NEW_PROJECT'	=> '新增项目',
	'LNK_PROJECT_LIST'	=> '项目列表',
	'LNK_NEW_PROJECT_TASK'	=> '新增项目任务',
	'LNK_PROJECT_TASK_LIST'	=> '项目任务',
	'LBL_DEFAULT_SUBPANEL_TITLE' => '项目',
	'LBL_ACTIVITIES_TITLE'=> '活动',
    'LBL_ACTIVITIES_SUBPANEL_TITLE'=> '活动',
	'LBL_HISTORY_SUBPANEL_TITLE'=> '历史记录',
	'LBL_QUICK_NEW_PROJECT'	=> '新建项目',
	
	'LBL_PROJECT_TASKS_SUBPANEL_TITLE' => '项目任务',
	'LBL_CONTACTS_SUBPANEL_TITLE' => '联系人',
	'LBL_ACCOUNTS_SUBPANEL_TITLE' => '客户',
	'LBL_OPPORTUNITIES_SUBPANEL_TITLE' => '商业机会',
	//BEGIN SUGARCRM flav=pro ONLY 
	'LBL_QUOTES_SUBPANEL_TITLE' => '报价',
	//END SUGARCRM flav=pro ONLY 

    'LBL_TASK_ID' => '编号',
    'LBL_TASK_NAME' => '任务名称',
    'LBL_DURATION' => '期间',
    'LBL_START' => '开始',
    'LBL_FINISH' => '完成',
    'LBL_PREDECESSORS' => '紧前任务',
    'LBL_PERCENT_COMPLETE' => '%完成',
    'LBL_RESOURCE_NAMES' => '资源',    
    
    'LBL_TASK_ID_WIDGET' => '编号',
    'LBL_TASK_NAME_WIDGET' => '说明',
    'LBL_DURATION_WIDGET' => '持续时间',
    'LBL_START_WIDGET' => '开始日期',
    'LBL_FINISH_WIDGET' => '结束日期',
    'LBL_PREDECESSORS_WIDGET' => '紧前任务',
    'LBL_PERCENT_COMPLETE_WIDGET' => '完成百分比',
    'LBL_RESOURCE_NAMES_WIDGET' => '资源',    
    'LBL_EDIT_PROJECT_TASKS_TITLE'=> '编辑项目任务',    
    
    'LBL_INSERT_BUTTON' => '插入行',
    'LBL_INDENT_BUTTON' => '增加缩进量',
    'LBL_OUTDENT_BUTTON' => '减少缩进量',
    'LBL_SAVE_BUTTON' => '保存',
    'LBL_COPY_BUTTON' => '复制',
    'LBL_PASTE_BUTTON' => '粘贴',   
    'LBL_DELETE_BUTTON' => '删除',   
    'LBL_CUT_BUTTON' => '剪切', 
    'LBL_WEEK_BUTTON' => '周',
    'LBL_BIWEEK_BUTTON' => '2周',
    'LBL_MONTH_BUTTON' => '月',
    
    'ERR_PERCENT_COMPLETE' => '%完成必须是在0到100之间的一个值。',   
    'ERR_DATE' => '指定日期不是工作日。',
    'ERR_PREDECESSORS_INPUT' => '在紧前任务字段中，输入值必须是“1”或“1，2”',
    'ERR_PREDECESSORS_OUT_OF_RANGE' => '紧前任务字段中的值大于行数。',   
    'ERR_PREDECESSOR_CYCLE_FAIL' => '指定的紧前任务引起依赖循环。',
     
    'NTC_DELETE_TASK_AND_SUBTASKS' => '您确定要删除这个任务和它的子任务吗?',
    
    'LBL_PROJECT_HOLIDAY_SUBPANEL_TITLE' => '资源的节假日',   
  
       
);
?>