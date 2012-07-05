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
$config['builds']['ult']['flav'] = array('ult','pro','ent');
$config['builds']['ult']['languages']= array(
'bg_BG',
'cs_CZ',
'da_DK',
'de_DE',
'es_ES',
'et_EE',
'fr_FR',
'he_IL',
'hu_HU',
'it_it',
'lt_LT',
'ja_JP',
'nb_NO',
'nl_NL',
'pl_PL',
'pt_PT',
'ro_RO',
'ru_RU',
'sv_SE',
'tr_TR',
'zh_CN',
'pt_BR',
'ca_ES',
'en_UK',
'sr_RS',
);
$config['builds']['ult']['lic'] = array('sub');
$config['blackList']['ult'] = array(
'sugarcrm/build'=>1,
'sugarcrm/themes/Awesome80s'=>1,
'sugarcrm/themes/BoldMove'=>1,
'sugarcrm/themes/FinalFrontier'=>1,
'sugarcrm/themes/GoldenGate'=>1,
'sugarcrm/themes/Legacy'=>1,
'sugarcrm/themes/Links'=>1,
'sugarcrm/themes/Love'=>1,
'sugarcrm/themes/Paradise'=>1,
'sugarcrm/themes/Retro'=>1,
'sugarcrm/themes/RipCurl'=>1,
'sugarcrm/themes/RipCurlorg'=>1,
'sugarcrm/themes/Shred'=>1,
'sugarcrm/themes/Sugar2006'=>1,
'sugarcrm/themes/SugarClassic'=>1,
'sugarcrm/themes/SugarIE6'=>1,
'sugarcrm/themes/SugarLite'=>1,
'sugarcrm/themes/Sunset'=>1,
'sugarcrm/themes/TrailBlazers'=>1,
'sugarcrm/themes/VintageSugar'=>1,
'sugarcrm/themes/WhiteSands'=>1,

'sugarcrm/include/externalAPI/LotusLiveDirect'=>1,
'sugarcrm/include/externalAPI/LotusLiveCastIron'=>1,

'sugarcrm/themes/default/images/gmail_logo.png'=>1,
'sugarcrm/themes/default/images/yahoomail_logo.png'=>1,
'sugarcrm/themes/default/images/exchange_logo.png'=>1,

'sugarcrm/modules/DCEActions'=>1,
'sugarcrm/modules/DCEClients'=>1,
'sugarcrm/modules/DCEClusters'=>1,
'sugarcrm/modules/DCEDataBases'=>1,
'sugarcrm/modules/DCEInstances'=>1,
'sugarcrm/modules/DCEReports'=>1,
'sugarcrm/modules/DCETemplates'=>1,
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

'sugarcrm/modules/QueryBuilder'=>1,
'sugarcrm/modules/Queues'=>1,

'sugarcrm/include/images/sugarsales_lg.png'=>1,
'sugarcrm/include/images/sugarsales_lg_dce.png'=>1,
'sugarcrm/include/images/sugarsales_lg_ent.png'=>1,
'sugarcrm/include/images/sugarsales_lg_express.png'=>1,
'sugarcrm/include/images/sugarsales_lg_open.png'=>1,
'sugarcrm/include/images/sugarsales_lg_corp.png'=>1,
'sugarcrm/include/images/sugar_md.png'=>1,
'sugarcrm/include/images/sugar_md_dev.png'=>1,
'sugarcrm/include/images/sugar_md_dce.png'=>1,
'sugarcrm/include/images/sugar_md_express.png'=>1,
'sugarcrm/include/images/sugar_md_open.png'=>1,
'sugarcrm/include/images/sugar_md_sales.png'=>1,
'sugarcrm/include/images/sugar_md_corp.png'=>1,

'sugarcrm/modules/SugarFollowing'=>1,
'sugarcrm/themes/default/images/user_follow.png'=>1,    
'sugarcrm/themes/default/images/user_unfollow.png'=>1,

'sugarcrm/include/EditView/InlineEdit.css'=>1,
'sugarcrm/include/EditView/InlineEdit.js'=>1,
'sugarcrm/include/EditView/InlineEdit.php'=>1,
'sugarcrm/include/MVC/View/views/view.inlinefield.php'=>1,
'sugarcrm/include/MVC/View/views/view.inlinefieldsave.php'=>1,
);
$build = 'ult';
