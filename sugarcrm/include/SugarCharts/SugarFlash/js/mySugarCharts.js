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

// $Id: customMySugarCharts.js 2010-12-01 23:11:36Z lhuynh $

SUGAR.mySugar.sugarCharts = function() {

var activeTab = activePage;
var charts = new Object();

	return {

		loadSugarCharts: function(activeTab) {
			for (id in charts[activeTab]){
				if(id != 'undefined'){
				//alert(charts[activeTab][id]['chartType']);
					loadSugarChart(
									charts[activeTab][id]['name'], 
									charts[activeTab][id]['xmlFile'], 
									charts[activeTab][id]['width'], 
									charts[activeTab][id]['height'],
									charts[activeTab][id]['styleSheet'],
									charts[activeTab][id]['colorScheme'],
									charts[activeTab][id]['langFile']);
				}
			}
		},
		
		
				 
        clearChartsArray: function(){
			charts[activeTab] = new Object();
		},
		
		addToChartsArrayJson: function(json,activeTab) {
			
			for (id in json) {
						SUGAR.mySugar.sugarCharts.addToChartsArray(
												 json[id]['id'], 
 												 json[id]['xmlFile'],
												 json[id]['width'],
												 json[id]['height'],
												 json[id]['styleSheet'],
												 json[id]['colorScheme'],
												 json[id]['langFile'],
												 activeTab);
				}
		},
		
		
		addToChartsArray: function(name, xmlFile, width, height, styleSheet, colorScheme, langFile,activeTab){

			if (charts[activeTab] == null){
				charts[activeTab] = new Object();
			}
			charts[activeTab][name] = new Object();
			charts[activeTab][name]['name'] = name;
			charts[activeTab][name]['xmlFile'] = xmlFile;
			charts[activeTab][name]['width'] = width;
			charts[activeTab][name]['height'] = height;
			charts[activeTab][name]['styleSheet'] = styleSheet;
			charts[activeTab][name]['colorScheme'] = colorScheme;	
			charts[activeTab][name]['langFile'] = langFile;				
		}
		
	}
}();
