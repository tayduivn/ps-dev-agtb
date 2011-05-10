<?php
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
$sugar_config_si = array(
//DB and Connection settings
'setup_db_host_name' => 'DB_SERVER',
'setup_db_sugarsales_user' => 'INST_DB_NAME',
'setup_db_sugarsales_password' => 'SITE_PASS',
'setup_db_database_name' => 'INST_DB_NAME',
'setup_db_type' => 'mysql',
'setup_db_create_database' => true,
'setup_db_create_sugarsales_user' => 0,
'setup_db_drop_tables' => false,
'setup_db_username_is_privileged' => false,
'setup_db_admin_user_name' => 'DB_USER',
'setup_db_admin_password' => 'DB_PASS',

//misc install settings
'demoData' => 'DEMO_DATA',
'setup_site_url' => 'URL',
'setup_site_admin_password' => 'SITE_PASS',
'setup_license_key_users' => 'LIC_USERS',
'setup_license_key_expire_date' => 'LIC_EXPIRATION',
'setup_license_key' => 'LIC_KEY',
'setup_num_lic_oc' => 'LIC_OC',
'setup_site_sugarbeet_automatic_checks' => true,
'setup_system_name' => 'SugarCRM - Commercial Open Source CRM',

//locale settings
'default_currency_iso4217' => 'USD',
'default_currency_name' => 'US Dollar',
'default_currency_significant_digits' => '2',
'default_currency_symbol' => '$',
'default_date_format' => 'Y-m-d',
'default_time_format' => 'H:i',
'default_decimal_seperator' => '.',
'default_export_charset' => 'UTF-8',
'default_language' => 'en_us',
'default_locale_name_format' => 's f l',
'default_number_grouping_seperator' => ',',
'export_delimiter' => ',',

);

?>
