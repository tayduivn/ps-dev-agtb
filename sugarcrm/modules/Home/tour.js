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
	target: "#moduleTab_AllHome", 
	title: SUGAR.language.get('Home', 'LBL_TOUR_HOME'),
	content: SUGAR.language.get('Home', 'LBL_TOUR_HOME_DESCRIPTION'),
	placement: "bottom"
};
modals[1] = {
	target: "#moduleTab_AllAccounts", 
	title: SUGAR.language.get('Home', 'LBL_TOUR_MODULES'),
	content: SUGAR.language.get('Home', 'LBL_TOUR_MODULES_DESCRIPTION'),
	placement: "bottom"
};
modals[2] = {
	target: "#moduleTabExtraMenuAll", 
	title: SUGAR.language.get('Home', 'LBL_TOUR_MORE'),
	content: SUGAR.language.get('Home', 'LBL_TOUR_MORE_DESCRIPTION'),
	placement: "bottom"
};
modals[3] = {
	target: "#dcmenuSearchDiv", 
	title: SUGAR.language.get('Home', 'LBL_TOUR_SEARCH'),
	content: SUGAR.language.get('Home', 'LBL_TOUR_SEARCH_DESCRIPTION'),
	placement: "bottom"
};
modals[4] = {
	target: $("#dcmenuSugarCube").length == 0 ? "#dcmenuSugarCubeEmpty" : "#dcmenuSugarCube",
	title: SUGAR.language.get('Home', 'LBL_TOUR_NOTIFICATIONS'),
	content: SUGAR.language.get('Home', 'LBL_TOUR_NOTIFICATIONS_DESCRIPTION'),
	placement: "bottom"
};
modals[5] = {
	target: "#globalLinksModule", 
	title: SUGAR.language.get('Home', 'LBL_TOUR_PROFILE'),
	content: SUGAR.language.get('Home', 'LBL_TOUR_PROFILE_DESCRIPTION'),
	placement: "bottom"
};
modals[6] = {
	target: "#quickCreate",
	title: SUGAR.language.get('Home', 'LBL_TOUR_QUICKCREATE'),
	content: SUGAR.language.get('Home', 'LBL_TOUR_QUICKCREATE_DESCRIPTION'),
	placement: "bottom right",
    leftOffset: rtl ? -40 : 40,
    topOffset: -10
};
modals[7] = {
	target: "#arrow",
	title: SUGAR.language.get('Home', 'LBL_TOUR_FOOTER'),
	content: SUGAR.language.get('Home', 'LBL_TOUR_FOOTER_DESCRIPTION'),
	placement: "top right",
    leftOffset: rtl ? -90 : 80,
    topOffset: -40
};
modals[8] = {
	target: "#integrations", 
	title: SUGAR.language.get('Home', 'LBL_TOUR_CUSTOM'),
	content: SUGAR.language.get('Home', 'LBL_TOUR_CUSTOM_DESCRIPTION'),
	placement: "top",
    leftOffset: rtl ? 30 : -30
};
modals[9] = {
	target: "#logo", 
	title: SUGAR.language.get('Home', 'LBL_TOUR_BRAND'),
	content: SUGAR.language.get('Home', 'LBL_TOUR_BRAND_DESCRIPTION'),
	placement: "top"
};



$(document).ready(function() {
	SUGAR.tour.init({
		id: 'tour',
		modals: modals,
		modalUrl: "index.php?module=Home&action=tour&to_pdf=1",
		prefUrl: "index.php?module=Users&action=UpdateTourStatus&to_pdf=true&viewed=true",
        className: 'whatsnew',
		onTourFinish: function() {
				$('#bootstrapJs').remove();
				$('#popoverext').remove();
				$('#bounce').remove();
				$('#bootstrapCss').remove();
				$('#tourCss').remove();
				$('#tourJs').remove();
				$('#whatsNewsJs').remove();
			}
		});	
});
	