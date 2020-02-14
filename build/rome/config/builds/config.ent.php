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
$config['builds']['ent']['flav'] = array('ent','pro');
$config['builds']['ent']['languages']= array(
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
$config['blackList']['ent'] = array(
'sugarcrm/build'=>1,

'sugarcrm/modules/DCEActions'=>1,
'sugarcrm/modules/DCEClients'=>1,
'sugarcrm/modules/DCEClusters'=>1,
'sugarcrm/modules/DCEDataBases'=>1,
'sugarcrm/modules/DCEInstances'=>1,
'sugarcrm/modules/DCEReports'=>1,
'sugarcrm/modules/Charts/Dashlets/DCEActionsByTypesDashlet'=>1,

'sugarcrm/themes/default/images/dce_settings.gif'=>1,
'sugarcrm/themes/default/images/DCEClusters.gif'=>1,
'sugarcrm/themes/default/images/DCEInstances.gif'=>1,
'sugarcrm/themes/default/images/DCElicensingReport.gif'=>1,
'sugarcrm/themes/default/images/DCETemplates.gif'=>1,
'sugarcrm/themes/default/images/DCEDataBases.gif'=>1,
'sugarcrm/themes/default/images/createDCEClusters.gif'=>1,
'sugarcrm/themes/default/images/createDCEInstances.gif'=>1,
'sugarcrm/themes/default/images/createDCETemplates.gif'=>1,
'sugarcrm/themes/default/images/createDCEDataBases.gif'=>1,
'sugarcrm/themes/default/images/icon_DCEActions_32.gif'=>1,
'sugarcrm/themes/default/images/icon_DCEDataBases_32.gif'=>1,
'sugarcrm/themes/default/images/icon_DCEInstances_32.gif'=>1,
'sugarcrm/themes/default/images/icon_DCEClusters_32.gif'=>1,
'sugarcrm/themes/default/images/icon_DCETemplates_32.gif'=>1,
'sugarcrm/themes/default/images/icon_DCEReports_32.gif'=>1,

'sugarcrm/modules/Queues'=>1,

'sugarcrm/include/images/sugarsales_lg_ult.png'=>1,
'sugarcrm/include/images/sugar_md.png'=>1,
'sugarcrm/include/images/sugar_md_ult.png'=>1,

'sugarcrm/styleguide/styleguide'=>1,
'sugarcrm/styleguide/tests'=>1,
// SP-1071 disable unsupported legacy connectors for 7.0
'sugarcrm/modules/Connectors/connectors/sources/ext/rest/linkedin'=>1,
);
