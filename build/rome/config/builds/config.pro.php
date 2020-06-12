<?php
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
$config['builds']['pro']['flav'] = array('pro');
$config['builds']['pro']['languages']= array(
'bg_BG',
'cs_CZ',
'da_DK',
'de_DE',
'el_EL',
'es_ES',
'fr_FR',
'he_IL',
'hu_HU',
'hr_HR',
'it_it',
'lt_LT',
'ja_JP',
'ko_KR',
'lv_LV',
'nb_NO',
'nl_NL',
'pl_PL',
'pt_PT',
'ro_RO',
'ru_RU',
'sv_SE',
'th_TH',
'tr_TR',
'zh_TW',
'zh_CN',
'pt_BR',
'ca_ES',
'en_UK',
'sr_RS',
'sk_SK',
'sq_AL',
'et_EE',
'es_LA',
'fi_FI',
'ar_SA',
'uk_UA',
);
$config['blackList']['pro'] = array(
'sugarcrm/modules/CustomQueries'=>1,
'sugarcrm/modules/DataSets'=>1,
'sugarcrm/modules/ReportMaker'=>1,

'sugarcrm/include/images/sugarsales_lg_ult.png'=>1,
'sugarcrm/include/images/sugar_md_ent.png'=>1,
'sugarcrm/include/images/sugar_md_ult.png'=>1,

// portal
'sugarcrm/clients/portal' => 1,
'sugarcrm/data/visibility/portal' => 1,
'sugarcrm/modules/Bugs/clients/portal' => 1,
'sugarcrm/modules/Cases/clients/portal' => 1,
'sugarcrm/modules/Contacts/clients/portal' => 1,
'sugarcrm/modules/Home/clients/portal' => 1,
'sugarcrm/modules/KBContents/clients/portal' => 1,
'sugarcrm/modules/Notes/clients/portal' => 1,
'sugarcrm/portal2' => 1,
'sugarcrm/src/Portal/Search' => 1,
'sugarcrm/tests/{old}/clients/portal' => 1,
'sugarcrm/tests/{old}/src/Portal' => 1,
'sugarcrm/tests/unit-php/clients/portal' => 1,
'sugarcrm/tests/unit-php/src/Portal' => 1,

// Locked fields implementation for ENT and above
'sugarcrm/include/SugarObjects/implements/lockable_fields' => 1,
'sugarcrm/metadata/locked_field_bean_relMetaData.php' => 1,
'sugarcrm/modules/pmse_Project/pmse_BpmProcessDefinition/LockedFieldsRelatedModulesUtilities.php' => 1,
'sugarcrm/include/SugarFields/Fields/Locked_fields/SugarFieldLocked_fields.php' => 1,

// Customer Service
'sugarcrm/clients/base/views/dashablerecord' => 1,
'sugarcrm/clients/base/views/multi-line-list' => 1,
'sugarcrm/modules/BusinessCenters' => 1,
'sugarcrm/tests/unit-js/clients/base/views/dashablerecord' => 1,
'sugarcrm/tests/unit-js/clients/base/views/multi-line-list' => 1,
'sugarcrm/tests/unit-js/modules/BusinessCenters' => 1,
'sugarcrm/tests/{old}/modules/BusinessCenters' => 1,
'sugarcrm/themes/RacerX/images/icon_BusinessCenters_32.png' => 1,
'sugarcrm/include/SugarObjects/implements/sla_fields/language/en_us.lang.php' => 1,
'sugarcrm/include/SugarObjects/implements/sla_fields/vardefs.php' => 1,
'sugarcrm/include/SugarObjects/implements/business_hours/language/en_us.lang.php' => 1,
'sugarcrm/include/SugarObjects/implements/business_hours/vardefs.php' => 1,
'sugarcrm/modules/ChangeTimers' => 1,
'sugarcrm/clients/base/views/activity-timeline' => 1,
'sugarcrm/tests/unit-js/clients/base/views/activity-timeline' => 1,

// Business Center holidays relationship
'sugarcrm/metadata/business_centers_holidaysMetaData.php' => 1,

// SugarBPM
'sugarcrm/modules/pmse_Business_Rules'=>1,
'sugarcrm/modules/pmse_Emails_Templates'=>1,
'sugarcrm/modules/pmse_Inbox'=>1,
'sugarcrm/modules/pmse_Project'=>1,

'sugarcrm/tests/unit-php/modules/pmse_Inbox'=>1,
'sugarcrm/tests/unit-js/include/javascript/pmse' => 1,

'sugarcrm/src/ProcessManager' => 1,
'sugarcrm/tests/{old}/src/ProcessManager' => 1,
'sugarcrm/tests/{old}/modules/pmse_Business_Rules' => 1,
'sugarcrm/tests/{old}/modules/pmse_Emails_Templates' => 1,
'sugarcrm/tests/{old}/modules/pmse_Inbox' => 1,
'sugarcrm/tests/{old}/modules/pmse_Project' =>1,
'sugarcrm/tests/{old}/pmse' =>1,

// Out of the box Business Process Management data
'sugarcrm/install/BusinessProcesses' => 1,

// Commentlog Dashlet
'sugarcrm/clients/base/views/commentlog-dashlet' => 1,
'sugarcrm/tests/unit-js/clients/base/views/commentlog-dashlet' => 1,
'sugarcrm/clients/base/fields/commentlog/dashlet.hbs' => 1,

// Console configuration
'sugarcrm/modules/ConsoleConfiguration' => 1,
'sugarcrm/tests/unit-js/modules/ConsoleConfiguration' => 1,
'sugarcrm/tests/{old}/modules/ConsoleConfiguration' => 1,

// Shift Exceptions Dashlet
'sugarcrm/clients/base/views/shift-exceptions-dashlet' => 1,
'sugarcrm/tests/unit-js/clients/base/views/shift-exceptions-dashlet' => 1,

// Workforce Management
'sugarcrm/modules/Shifts' => 1,
'sugarcrm/modules/ShiftExceptions' => 1,
'sugarcrm/tests/unit-js/modules/Shifts' => 1,
'sugarcrm/tests/unit-js/modules/ShiftExceptions' => 1,
'sugarcrm/tests/{old}/modules/Shifts' => 1,

// Purchases and PLIs
'sugarcrm/modules/Purchases' => 1,
'sugarcrm/themes/RacerX/images/icon_Purchases_32.png' => 1,
'sugarcrm/metadata/accounts_purchasesMetaData.php' => 1,
'sugarcrm/metadata/cases_purchasesMetaData.php' => 1,
'sugarcrm/metadata/contacts_purchasesMetaData.php' => 1,
'sugarcrm/metadata/documents_purchasesMetaData.php' => 1,
'sugarcrm/tests/{old}/modules/Purchases' => 1,
'sugarcrm/tests/unit-js/modules/Purchases' => 1,
'sugarcrm/metadata/documents_purchasedlineitemsMetaData.php' => 1,
'sugarcrm/modules/PurchasedLineItems' => 1,
'sugarcrm/include/SugarQueue/jobs/SugarJobCreatePurchasesAndPLIs.php' => 1,
'sugarcrm/tests/{old}/include/SugarQueue/jobs/SugarJobCreatePurchasesAndPLIsTest.php' => 1,
'sugarcrm/tests/{old}/modules/PurchasedLineItems' => 1,
'sugarcrm/tests/{old}/SugarTestPurchasedLineItemUtilities.php' => 1,
'sugarcrm/tests/{old}/SugarTestPurchasesUtilities.php' => 1,
'sugarcrm/tests/unit-js/modules/PurchasedLineItems' => 1,
'sugarcrm/themes/RacerX/images/icon_PurchasedLineItems_32.png' => 1,
);
