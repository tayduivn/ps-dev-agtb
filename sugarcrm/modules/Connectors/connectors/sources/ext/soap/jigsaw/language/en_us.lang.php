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
$connector_strings = array (
    //licensing information shown in config screen
    'LBL_LICENSING_INFO' => '<table border="0" cellspacing="1"><tr><td valign="top" width="35%" class="dataLabel"><image src="http://www.jigsaw.com/images/cornerstone/header/jigsawLogo.jpg" border="0"></td><td width="65%" class="dataLabel">' .
                            'Jigsaw&#169; provides free company data to all users of SugarCRM – no registration required! ' .
                            'Registered members can use Jigsaw&#169;\'s directory of over 10 million business contacts, ' .
                            'all complete with name, title, e-mail address, and telephone number. ' .
                            'Sign up at <a style="cursor:pointer" href="http://www.jigsaw.com" target="_blank">http://www.jigsaw.com</a>.</td></tr>',

    //vardef labels
	'LBL_ID' => 'Company ID',
	'LBL_COMPANY_NAME' => 'Company Name',
	'LBL_CITY' => 'City',
	'LBL_STREET' => 'Street',
	'LBL_STATE' => 'State',
	'LBL_ZIP' => 'Zip',
	'LBL_COUNTRY' => 'Country',
	'LBL_PHONE' => 'Phone',
	'LBL_SIC_CODE' => 'SIC Code',
	'LBL_REVENUE' => 'Annual Revenue',
	'LBL_REVENUE_RANGE' => 'Annual Revenue Estimate',
	'LBL_OWNERSHIP' => 'Ownership',
	'LBL_WEBSITE' => 'Website',
	'LBL_LINKED_IN_JIGSAW' => 'Link to Company in Jigsaw.com',
	'LBL_INDUSTRY1' => 'Primary Industry',
	'LBL_STOCK_SYMBOL' => 'Stock Symbol',
	'LBL_STOCK_EXCHANGE' => 'Stock Exchange',
	'LBL_CREATED_ON' => 'Date Profile Created',
	'LBL_EMPLOYEE_COUNT' => 'Employee Count',
	'LBL_EMPLOYEE_RANGE' => 'Headcount Range',
	'LBL_ADDRESS' => 'Address',
	
	//Configuration labels
	'range_end' => 'Maximum Number Of List Results',
	'jigsaw_wsdl' => 'WSDL URL',
	'jigsaw_api_key' => 'API Key',
);

?>