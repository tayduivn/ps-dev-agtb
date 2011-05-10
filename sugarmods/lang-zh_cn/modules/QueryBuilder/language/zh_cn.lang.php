<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: en_us.lang.php 13782 2006-06-06 17:58:55Z majed $
 * Description:  Defines the English language pack for the base application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$mod_strings = array (
  'LBL_MODULE_NAME' => '查询生成器',
  'LBL_MODULE_TITLE' => '查询生成器:首页',
  'LBL_SEARCH_FORM_TITLE' => '查找查询生成器',
  'LBL_LIST_FORM_TITLE' => '查询生成器列表',
  'LBL_NEW_FORM_TITLE' => '新增记录',
  'LBL_PRODUCT' => '产品:',
  'LBL_RELATED_PRODUCTS' => '相关产品',
  'LBL_LIST_NAME' => '名称',
  'LBL_LIST_QUERY_TYPE' => '类型',
  'LBL_LIST_BASE_MODULE' => '主要模块',
  'LBL_LIST_QUERY_LOCKED' => '锁定?',
  'LBL_NAME' => '查询生成器名称:',
  'LBL_DESCRIPTION' => '说明:',
  'LBL_QUERY_TYPE' => '查询类型:',
  'LBL_QUERY_LOCKED' => '查询锁定:',
  'LBL_BASE_MODULE' => '主要模块:',
  
  
  
  
  
  
  'LNK_LIST_REPORTMAKER' => '报表列表',
  'LNK_NEW_REPORTMAKER' => '新增报表',
  'LNK_LIST_DATASET' => '数据设置列表',
  'LNK_NEW_DATASET' => '新增数据设置',
  'LNK_NEW_CUSTOMQUERY' => '新增自定义查询',
  'LNK_CUSTOMQUERIES' => '自定义查询',
  'LNK_NEW_QUERYBUILDER' => '新增查询',
  'LNK_QUERYBUILDER' => '查询生成器',
  'LBL_ALL_REPORTS' => '所有报表',
  
  'NTC_DELETE_CONFIRMATION' => '您确定要删除这条记录?',
  'ERR_DELETE_RECORD' => '必须指定记录编号才能删除产品。',
  
    //for subpanel under the reports
  'LBL_ADD_COLUMN _BUTTON_TITLE' => '增加[Alt+C]',
  'LBL_ADD_COLUMN_BUTTON_KEY' => 'C',
  'LBL_ADD_COLUMN_BUTTON_LABEL' => '增加列',
  'LBL_ADD_GROUPBY_BUTTON_LABEL' => '增加组',
  'LBL_ADD_GROUPBY _BUTTON_TITLE' => '增加[Alt+G]',
  'LBL_ADD_GROUPBY_BUTTON_KEY' => 'G',
  'LBL_NEW_BUTTON_TITLE' => '增加[Alt+N]',
  'LBL_NEW_BUTTON_KEY' => 'N',
  'LBL_NEW_BUTTON_LABEL' => '新增',  
  'LBL_DETAILS_BUTTON_TITLE' => '报表细节[Alt+D]',
  'LBL_DETAILS_BUTTON_KEY' => 'D',
  'LBL_DETAILS_BUTTON_LABEL' => '报表细节',  
  'LBL_EDIT_BUTTON_TITLE' => '编辑报表[Alt+E]',
  'LBL_EDIT_BUTTON_KEY' => 'N',
  'LBL_EDIT_BUTTON_LABEL' => '编辑报表',  
  'LBL_RUN_BUTTON_TITLE' => '运行报表[Alt+R]',
  'LBL_RUN_BUTTON_KEY' => 'R',
  'LBL_RUN_BUTTON_LABEL' => '运行报表', 
  
  
  
  //New AQB Stuff
  	'LBL_COLUMN_DISPLAY_SWITCH' => '选择列类型:', 
	'LBL_COLUMN_NAME' => '列名称:',
	'LBL_COLUMN_MODULE' => '列模块:',
	
	'LBL_GROUPBY_MODULE' => '按模块分组:',
	'LBL_GROUPBY_FIELD' => '按字段分组:',
	'LBL_GROUPBY_CALC_MODULE' => '计算模块:',
	'LBL_GROUPBY_CALC_FIELD' => '计算字段:',
	'LBL_GROUPBY_CALC_TYPE' => '计算类型:',
	'LBL_GROUPBY_CALC' => '计算:',
	'LBL_GROUPBY_TYPE' => '分组类型:',
	'LBL_GROUPBY_AXIS' => '按轴分组:',
	'LBL_GROUPBY_QUALIFIER' => '时间间隔:',
	'LBL_GROUPBY_QUALIFIER_QTY' => '分组数量:',
	'LBL_GROUPBY_QUALIFIER_START' => '开始时间位置:',
	
	
	'LBL_CALC_NAME' => '计算名称:',
	'LBL_CALC_TYPE' => '计算类型:',
	

	'LBL_FINISHED_BUTTON_TITLE' => '完成',
	'LBL_FINISHED_BUTTON_LABEL' => '完成',
	
	'LBL_LEFT_FIELD' => '左侧字段:',
	'LBL_LEFT_MODULE' => '左侧模块:',
	
	'LBL_OPERATOR' => '运算符:',
	'LBL_RIGHT_FIELD' => '右侧字段:',
	'LBL_RIGHT_MODULE' => '右侧模块:',

	'LBL_CALC_ENCLOSED' => '封闭式计算:',
	'LBL_FILTER_TYPE' => '过滤器类型:',
	'LBL_LIST_ORDER' => '列表顺序:',
	
	'LBL_LEFT_TYPE' => '左边类型:',
	'LBL_RIGHT_TYPE' => '右边类型:',
	
	'LBL_LEFT_GROUP' => '左侧分组:',
	'LBL_LEFT_VALUE' => '左侧数值:',
	
	'LBL_RIGHT_GROUP' => '右侧分组:',
	'LBL_RIGHT_VALUE' => '右侧数值:',
	'LBL_PARENT_GROUP' => '父类组:',
	
  
);


?>
