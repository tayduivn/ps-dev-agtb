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
    'LBL_LICENSING_INFO' => '<table border="0" cellspacing="1"><tr><td valign="top" width="35%" class="dataLabel"><image src="http://www.jigsaw.com/images/cornerstone/header/jigsawLogo.jpg" border="0"></td><td width="65%" class="dataLabel">' .
                            'Jigsaw&#169; 向SugarCRM的所有用户免费提供公司数据 - 无需注册！ ' .
                            '注册用户可以使用Jigsaw&#169;的超过上千万个商业联系人目录， ' .
                            '包含名字，头衔，电子邮件地址以及电话号码。 ' .
                            '申请注册请访问:<a style="cursor:pointer" href="http://www.jigsaw.com" target="_blank">http://www.jigsaw.com</a>.</td></tr>',

    //vardef labels
	'LBL_ID' => '公司编码',
	'LBL_COMPANY_NAME' => '公司名称',
	'LBL_CITY' => '城市',
	'LBL_STREET' => '街道',
	'LBL_STATE' => '省',
	'LBL_COUNTRY' => '国家',
	'LBL_PHONE' => '电话',
	'LBL_SIC_CODE' => 'SIC编码',
	'LBL_REVENUE' => '年营业收入',
	'LBL_REVENUE_RANGE' => '年度营收预期',
	'LBL_OWNERSHIP' => '隶属',
	'LBL_WEBSITE' => '网站',
	'LBL_LINKED_IN_JIGSAW' => 'Jigsaw网站',
	'LBL_INDUSTRY1' => '第一产业',
	'LBL_STOCK_SYMBOL' => '股票代码',
	'LBL_STOCK_EXCHANGE' => '证券交易所',
	'LBL_CREATED_ON' => '简介创建日期',
	'LBL_EMPLOYEE_COUNT' => '员工规模',
	'LBL_EMPLOYEE_RANGE' => '人数范围',
	'LBL_ADDRESS' => '地址',
	
	//Error messages
	'ERROR_API_CALL' => '错误: ',
	
	//Configuration labels
	'range_end' => '记录个数最大值',
	'jigsaw_wsdl' => 'WSDL地址',
	'jigsaw_api_key' => '接口密钥',
);

?>