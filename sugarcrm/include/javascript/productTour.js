/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

var rtl = rtl == "undefined" ? false : rtl;
var modals=new Array();
modals[0] = {
    title: SUGAR.language.get('Home', 'LBL_TOUR_PDF_MANAGER'),
    content: SUGAR.language.get('Home', 'LBL_TOUR_PDF_MANAGER_DESCRIPTION')
};
modals[1] = {
    title: SUGAR.language.get('Home', 'LBL_TOUR_MEETINGS_CALLS'),
    content: SUGAR.language.get('Home', 'LBL_TOUR_MEETINGS_CALLS_DESCRIPTION')
};
modals[2] = {
    target: "#dcmenuSugarCube",
    title: SUGAR.language.get('Home', 'LBL_TOUR_NOTIFICATIONS'),
    content: SUGAR.language.get('Home', 'LBL_TOUR_NOTIFICATIONS_DESCRIPTION'),
    placement: "bottom"
};
modals[3] = {
    title: SUGAR.language.get('Home', 'LBL_TOUR_SPELL_CHECK'),
    content: SUGAR.language.get('Home', 'LBL_TOUR_SPELL_CHECK_DESCRIPTION')
};
modals[4] = {
    title: SUGAR.language.get('Home', 'LBL_TOUR_IE8'),
    content: SUGAR.language.get('Home', 'LBL_TOUR_IE8_DESCRIPTION')
};

