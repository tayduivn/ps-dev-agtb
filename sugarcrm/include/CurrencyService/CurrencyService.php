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
/*********************************************************************************

 * Description:
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 * *******************************************************************************/

class CurrencyService {
	var $currencyDefault;
	var $currencyFrom;
	var $currencyTo;

	var $numbers;
	var $db;
	
	/**
	 * sole constructor
	 */
	function CurrencyService() {
		global $sugar_config;
		
        if(!class_exists('DBManagerFactory')) {
            
        }
		$this->db = &DBManagerFactory::getInstance();
		
	}
	
	/**
	 * inserts default (usually US Dollar) as default currency
	 */
	function insertDefaults() {
		global $sugar_config;
		
		$insert=true;
		
		if($insert) {
			$q = "INSERT INTO currencies (id, name, symbol, iso4217, conversion_rate, status, deleted, date_entered, date_modified, created_by)
					VALUES('".create_guid()."', 
						'{$sugar_config['default_currency_name']}',
						'{$sugar_config['default_currency_symbol']}',
						'{$sugar_config['default_currency_iso4217']}',
						1.0, 'Active', 0, '".date($GLOBALS['timedate']->get_db_date_time_format())."', '".date($GLOBALS['timedate']->get_db_date_time_format())."', '1')";
		}	
	}
	
} // end class def
?>