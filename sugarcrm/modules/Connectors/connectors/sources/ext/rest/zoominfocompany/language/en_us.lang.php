<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/*********************************************************************************

* Description:  Defines the English language pack for the base application.
* Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
* All Rights Reserved.
* Contributor(s): ______________________________________..
********************************************************************************/

$connector_strings = array (
    //licensing information shown in config screen
    'LBL_LICENSING_INFO' => '<table border="0" cellspacing="1"><tr><td valign="top" width="35%" class="dataLabel"><image src="' . getWebPath('modules/Connectors/connectors/sources/ext/rest/zoominfocompany/images/zoominfo.gif') . '" border="0"></td><td width="65%" valign="top" class="dataLabel">' .
                            'ZoomInfo provides deep information on over 45 million business people at over 5 million companies.  Learn more.  <a target="_blank" href="http://www.zoominfo.com/about">http://www.zoominfo.com/about</a></td></tr></table>',    
    
    'LBL_SEARCH_FIELDS_INFO' => 'The following fields are supported by the Zoominfo Company; API: Company Name, City, State and Country.',
        
    
    //vardef labels
	'LBL_COMPANY_ID' => 'ID',
	'LBL_COMPANY_NAME' => 'Company Name',
    'LBL_STREET' => 'Street',
	'LBL_CITY' => 'City',
	'LBL_ZIP' => 'Postal Code',
	'LBL_STATE' => 'State',
	'LBL_COUNTRY' => 'Country',
	'LBL_INDUSTRY' => 'Industry',
	'LBL_WEBSITE' => 'Website',
	'LBL_DESCRIPTION' => 'Description',
    'LBL_PHONE' => 'Phone',
	'LBL_COMPANY_TICKER' => 'Company Ticker',
	'LBL_ZOOMINFO_COMPANY_URL' => 'Company Profile URL',
	'LBL_REVENUE' => 'Annual Revenue',
	'LBL_EMPLOYEES' => 'Employees',
	
	//Configuration labels
	'company_search_url' => 'Company Search URL',
	'company_detail_url' => 'Company Detail URL',
    'partner_code' => 'Partner API Code',
	'api_key' => 'API KEY',
	
	//Other labels
	'ERROR_LBL_CONNECTION_PROBLEM' => 'Error: Unable to connect to the server for Zoominfo - Company connector.',
);

?>
