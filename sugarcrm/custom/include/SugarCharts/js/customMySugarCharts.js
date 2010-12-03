SUGAR.mySugar.customCharts = function() {

var activeTab = activePage;
var charts = new Object();

	return {
		loadCustomCharts: function(activeTab) {
			for (id in charts[activeTab]){
				if(id != 'undefined'){
				//alert(charts[activeTab][id]['chartType']);
					loadCustomChart(
											 charts[activeTab][id]['chartId'], 
											 charts[activeTab][id]['jsonFilename'],
											 charts[activeTab][id]['css'],
											 charts[activeTab][id]['chartConfig']
											 );
				}
			}
		},

		addToCustomChartsJson: function(json,activeTab) {
			for (id in json) {
					if(json[id]['supported'] == "true") {
						SUGAR.mySugar.customCharts.addToCustomChartsArray(
												 json[id]['chartId'], 
 												 json[id]['filename'],
												 json[id]['css'],
												 json[id]['chartConfig'],
												 activeTab);
					}
				}
		},
		addToCustomChartsArray: function(chartId,jsonFilename,css,chartConfig,activeTab) {
			
			if (charts[activeTab] == null){
				charts[activeTab] = new Object();
			}
			charts[activeTab][chartId] = new Object();
			charts[activeTab][chartId]['chartId'] = chartId;
			charts[activeTab][chartId]['jsonFilename'] = jsonFilename;	
			charts[activeTab][chartId]['css'] = css;	
			charts[activeTab][chartId]['chartConfig'] = chartConfig;		
	
		}
		
	}
}();