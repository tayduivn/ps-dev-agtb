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
 * $Id: Menu.php 55254 2010-03-12 16:57:56Z roger $
 * Description:  
 ********************************************************************************/

global $mod_strings;
global $current_user;

if (!is_admin($current_user))
{
   $module_menu = Array(
   
	Array("index.php?module=ReportMaker&action=EditView&return_module=ReportMaker&return_action=DetailView", $mod_strings['LNK_NEW_REPORTMAKER'],"CreateReport"),
	Array("index.php?module=ReportMaker&action=index&return_module=ReportMaker&return_action=index", $mod_strings['LNK_LIST_REPORTMAKER'],"ReportMaker"),
//BEGIN SUGARCRM flav=int ONLY
	Array("index.php?module=QueryBuilder&action=EditView&return_module=QueryBuilder&return_action=DetailView", $mod_strings['LNK_NEW_QUERYBUILDER'],"CreateQuery"),
	Array("index.php?module=QueryBuilder&action=index&return_module=QueryBuilder&return_action=DetailView", $mod_strings['LNK_QUERYBUILDER'],"QueryBuilder"),
//END SUGARCRM flav=int ONLY
	Array("index.php?module=DataSets&action=EditView&return_module=DataSets&return_action=DetailView", $mod_strings['LNK_NEW_DATASET'],"CreateDataSet"),
	Array("index.php?module=DataSets&action=index&return_module=DataSets&return_action=index", $mod_strings['LNK_LIST_DATASET'],"DataSets"),
	Array("index.php?module=Reports&action=index", $mod_strings['LBL_ALL_REPORTS'],"Reports", 'Reports'),

	);
} else {
	
	$module_menu = Array(
	
	Array("index.php?module=ReportMaker&action=EditView&return_module=ReportMaker&return_action=DetailView", $mod_strings['LNK_NEW_REPORTMAKER'],"CreateReport"),
	Array("index.php?module=ReportMaker&action=index&return_module=ReportMaker&return_action=index", $mod_strings['LNK_LIST_REPORTMAKER'],"ReportMaker"),
	Array("index.php?module=CustomQueries&action=EditView&return_module=CustomQueries&return_action=DetailView", $mod_strings['LNK_NEW_CUSTOMQUERY'],"CreateCustomQuery"),
	Array("index.php?module=CustomQueries&action=index&return_module=CustomQueries&return_action=DetailView", $mod_strings['LNK_CUSTOMQUERIES'],"CustomQueries"),
//BEGIN SUGARCRM flav=int ONLY
	Array("index.php?module=QueryBuilder&action=EditView&return_module=QueryBuilder&return_action=DetailView", $mod_strings['LNK_NEW_QUERYBUILDER'],"CreateQuery"),
	Array("index.php?module=QueryBuilder&action=index&return_module=QueryBuilder&return_action=DetailView", $mod_strings['LNK_QUERYBUILDER'],"QueryBuilder"),
//END SUGARCRM flav=int ONLY
	Array("index.php?module=DataSets&action=EditView&return_module=DataSets&return_action=DetailView", $mod_strings['LNK_NEW_DATASET'],"CreateDataSet"),
	Array("index.php?module=DataSets&action=index&return_module=DataSets&return_action=index", $mod_strings['LNK_LIST_DATASET'],"DataSets"),
	Array("index.php?module=Reports&action=index", $mod_strings['LBL_ALL_REPORTS'],"Reports", 'Reports'),

	);
}	



?>
