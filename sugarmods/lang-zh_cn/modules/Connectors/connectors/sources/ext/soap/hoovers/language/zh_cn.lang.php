<?php
// FILE SUGARCRM flav=pro ONLY 
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement 
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.  
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may 
 *not use this file except in compliance with the License. Under the terms of the license, You 
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or 
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

$connector_strings = array (
    //licensing information shown in config screen

    'LBL_LICENSING_INFO' => '<table border="0" cellspacing="1"><tr><td valign="top" width="35%" class="dataLabel"><image src="' . getWebPath('modules/Connectors/connectors/sources/ext/soap/hoovers/images/hooversLogo.gif') . '" border="0"></td><td width="65%" valign="top" class="dataLabel">' .
                            'Hoovers&#169; 向sugarCRM的用户免费提供最新的公司信息。  欲获取详细信息请访问 <a href="http://www.hoovers.com" target="_blank">http://www.hoovers.com</a>.</td></tr></table>',    
    
    //search mapping information shown in config screen
    'LBL_SEARCH_FIELDS_INFO' => '以下字段信息是由Hoovers&#169查询接口提供： 公司名称， 城市， 省， 国家和邮政编码。',
    
    //vardef labels
	'LBL_ID' => '编号',
	'LBL_NAME' => '公司名称',
	/*
	'LBL_DUNS' => 'DUNS',
	'LBL_PARENT_DUNS' => 'Parent DUNS',
    */
	'LBL_STREET' => '街道',
	'LBL_CITY' => '城市',
	'LBL_STATE' => '省',
	'LBL_COUNTRY' => '国家',
	'LBL_ZIP' => '邮政编码',
	'LBL_FINSALES' => '年销售额',
	
	//Configuration labels
	'hoovers_endpoint' => '端点地址',
	'hoovers_wsdl' => 'WSDL地址',
	'hoovers_api_key' => '接口密钥',
);

?>