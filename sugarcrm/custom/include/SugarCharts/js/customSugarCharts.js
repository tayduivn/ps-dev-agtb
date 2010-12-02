SUGAR.mySugar.customCharts = function() {

var activeTab = activePage;
var charts = new Object();

	return {
		loadCustomCharts: function(activeTab) {
			for (id in charts[activeTab]){
				if(id != 'undefined'){
				//alert(charts[activeTab][id]['chartType']);
					SUGAR.mySugar.customCharts.loadCustomChart(
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
	
		},
					
		loadCustomChart: function(chartId,jsonFilename,css,chartConfig) {

				var labelType, useGradients, nativeTextSupport, animate;
				(function() {
				  var ua = navigator.userAgent,
					  iStuff = ua.match(/iPhone/i) || ua.match(/iPad/i),
					  typeOfCanvas = typeof HTMLCanvasElement,
					  nativeCanvasSupport = (typeOfCanvas == 'object' || typeOfCanvas == 'function'),
					  textSupport = nativeCanvasSupport 
						&& (typeof document.createElement('canvas').getContext('2d').fillText == 'function');
				  //I'm setting this based on the fact that ExCanvas provides text support for IE
				  //and that as of today iPhone/iPad current text support is lame
				  labelType = (!nativeCanvasSupport || (textSupport && !iStuff))? 'Native' : 'HTML';
				  nativeTextSupport = labelType == 'Native';
				  useGradients = nativeCanvasSupport;
				  animate = !(iStuff || !nativeCanvasSupport);
				})();


			switch(chartConfig["chartType"]) {
			case "barChart":
				var handleFailure = function(o){
				alert('fail');
					if(o.responseText !== undefined){
						alert('failed');
					}
				}	
				var handleSuccess = function(o){

					if(o.responseText !== undefined && o.responseText != "No Data"){	
					var json = eval('('+o.responseText+')');

				var properties = $jit.util.splat(json.properties)[0];	
				var marginBottom = (chartConfig["orientation"] == 'vertical' && json.values.length > 8) ? 20*4 : 20;
				//init BarChart
				var barChart = new $jit.BarChart({
				  //id of the visualization container
				  injectInto: chartId,
				  //whether to add animations
				  animate: false,
				  //horizontal or vertical barcharts
				  orientation: chartConfig["orientation"],
				  hoveredColor: false,
				  Title: {
					text: properties['title'],
					size: 16,
					color: '#444444',
					offset: 20
				  },
				  Subtitle: {
					text: properties['subtitle'],
					size: 11,
					color: css["color"],
					offset: 20
				  },
				  Ticks: {
					enable: true,
					color: css["gridLineColor"]
				  },
				  //bars separation
				  barsOffset: 20,
				  //visualization offset
				  Margin: {
					top:20,
					left: 20,
					right: 20,
					bottom: marginBottom
				  },
				  Events: {
					enable: true,
					onClick: function(node) {  
					if(!node) return;  
					if(node.link == 'undefined' || node.link == '') return;
					window.location.href=node.link;
					}
				  },
				  //labels offset position
				  labelOffset: 5,
				  //bars style
				  type: useGradients? chartConfig["barType"]+':gradient' : chartConfig["barType"],
				  //whether to show the aggregation of the values
				  showAggregates:true,
				  //whether to show the labels for the bars
				  showLabels:true,
				  //labels style
				  Label: {
					type: labelType, //Native or HTML
					size: 10,
					family: css["font-family"],
					color: css["color"],
					colorAlt: "#ffffff"
				  },
				  //add tooltips
				  Tips: {
					enable: true,
					onShow: function(tip, elem) {
					  if(elem.link != 'undefined' && elem.link != '') {
						drillDown = "<br>Click to drilldown";
					  } else {
						drillDown = "";
					  }

					  if(elem.valuelabel != 'undefined' && elem.valuelabel != undefined && elem.valuelabel != '') {
						value = "elem.valuelabel";
					  } else {
						value = "elem.value";
					  }
					  eval("tip.innerHTML = '<b>' + elem."+chartConfig["tip"]+" + '</b>: ' + "+value+" + drillDown");
					}
				  }
				});
				//load JSON data.
				barChart.loadJSON(json);
				//end
				
				/*
				var list = $jit.id('id-list'),
					button = $jit.id('update'),
					orn = $jit.id('switch-orientation');
				//update json on click 'Update Data'
				$jit.util.addEvent(button, 'click', function() {
				  var util = $jit.util;
				  if(util.hasClass(button, 'gray')) return;
				  util.removeClass(button, 'white');
				  util.addClass(button, 'gray');
				  barChart.updateJSON(json2);
				});
				*/
				//dynamically add legend to list
				var list = $jit.id('legend'+chartId);
				var legend = barChart.getLegend(),
					rows = Math.ceil(legend["name"].length/5);
					table = "<table cellpadding='0' cellspacing='0' align='left'>";
				var j = 0;
				for(i=0;i<rows;i++) {
					table += "<tr>"; 
					for(td=0;td<5;td++) {
						
						table += '<td nowrap>';
						if(legend["name"][j] != undefined) {
							table += '<div class=\'query-color\' style=\'background-color:'
							  + legend["color"][j] +'\'>&nbsp;</div>' + legend["name"][j];
						}
						  
						table += '</td>';
						j++;
						}
					table += "</tr>"; 
				}
				
					table += "</table>";
				list.innerHTML = table;
				
					}
						}
						
				var callback =
				{
				  success:handleSuccess,
				  failure:handleFailure,
				  argument: { foo:'foo', bar:''}
				};
				
				var request = YAHOO.util.Connect.asyncRequest('GET', jsonFilename, callback);
				break;
				
				
				
			case "pieChart":

				var handleFailure = function(o){
				alert('fail');
					if(o.responseText !== undefined){
						alert('failed');
					}
				}	
				var handleSuccess = function(o){

					if(o.responseText !== undefined){			
					var json = eval('('+o.responseText+')');
					var properties = $jit.util.splat(json.properties)[0];	

						//init BarChart
				var pieChart = new $jit.PieChart({
				  //id of the visualization container
				  injectInto: chartId,
				  //whether to add animations
				  animate: false,
				  labelType: properties['labels'],
				  hoveredColor: false,
				  //offsets
				  offset: 50,
				  sliceOffset: 0,
				  labelOffset: 30,
				  //slice style
				  type: useGradients? chartConfig["pieType"]+':gradient' : chartConfig["pieType"],
				  //whether to show the labels for the slices
				  showLabels:true,
				  Events: {
					enable: true,
					onClick: function(node) {  
					if(!node) return;  
					if(node.link == 'undefined' || node.link == '') return;
					window.location.href=node.link;
					}
				  },
				  //label styling
				  Label: {
					type: labelType, //Native or HTML
					size: 10,
					family: css["font-family"],
					color: css["color"]
				  },
				  //enable tips
				  Tips: {
					enable: true,
					onShow: function(tip, elem) {
					  if(elem.link != 'undefined' && elem.link != '') {
						drillDown = "<br>Click to drilldown";
					  } else {
						drillDown = "";
					  }
					  
					  if(elem.valuelabel != 'undefined' && elem.valuelabel != undefined && elem.valuelabel != '') {
						value = "elem.valuelabel";
					  } else {
						value = "elem.value";
					  }
					   eval("tip.innerHTML = '<b>' + elem.label + '</b>: ' + "+ value +" + drillDown");
					}
				  }
				});
				//load JSON data.
				pieChart.loadJSON(json);
				//end
				//dynamically add legend to list
				var list = $jit.id('legend'+chartId);
				var legend = pieChart.getLegend(),
					rows = Math.ceil(legend["name"].length/5);
					table = "<table cellpadding='0' cellspacing='0' align='left'>";
				var j = 0;
				for(i=0;i<rows;i++) {
					table += "<tr>"; 
					for(td=0;td<5;td++) {
						
						table += '<td nowrap>';
						if(legend["name"][j] != undefined) {
							table += '<div class=\'query-color\' style=\'background-color:'
							  + legend["color"][j] +'\'>&nbsp;</div>' + legend["name"][j];
						}
						  
						table += '</td>';
						j++;
						}
					table += "</tr>"; 
				}
				
					table += "</table>";
				list.innerHTML = table;
				
				
							}
								}
								
				var callback =
				{
				  success:handleSuccess,
				  failure:handleFailure,
				  argument: { foo:'foo', bar:''}
				};
				
				var request = YAHOO.util.Connect.asyncRequest('GET', jsonFilename, callback);
							
				break;
				
				
			case "funnelChart":

				var handleFailure = function(o){
				alert('fail');
					if(o.responseText !== undefined){
						alert('failed');
					}
				}	
				var handleSuccess = function(o){

					if(o.responseText !== undefined && o.responseText != "No Data"){	
					var json = eval('('+o.responseText+')');

				var properties = $jit.util.splat(json.properties)[0];	

				//init Funnel Chart
				var funnelChart = new $jit.FunnelChart({
				  //id of the visualization container
				  injectInto: chartId,
				  //whether to add animations
				  animate: false,
				  //orientation setting should not be changed
				  orientation: "vertical",
				  hoveredColor: false,
				  Title: {
					text: properties['title'],
					size: 16,
					color: '#444444',
					offset: 20
				  },
				  Subtitle: {
					text: properties['subtitle'],
					size: 11,
					color: css["color"],
					offset: 20
				  },
				  //segment separation
				  segmentOffset: 20,
				  //visualization offset
				  Margin: {
					top:20,
					left: 20,
					right: 20,
					bottom: 20
				  },
				  Events: {
					enable: true,
					onClick: function(node) {  
					if(!node) return;  
					if(node.link == 'undefined' || node.link == '') return;
					window.location.href=node.link;
					}
				  },
				  //labels offset position
				  labelOffset: 10,
				  //bars style
				  type: useGradients? chartConfig["funnelType"]+':gradient' : chartConfig["funnelType"],
				  //whether to show the aggregation of the values
				  showAggregates:true,
				  //whether to show the labels for the bars
				  showLabels:true,
				  //labels style
				  Label: {
					type: labelType, //Native or HTML
					size: 10,
					family: css["font-family"],
					color: css["color"],
					colorAlt: "#ffffff"
				  },
				  //add tooltips
				  Tips: {
					enable: true,
					onShow: function(tip, elem) {
					  if(elem.link != 'undefined' && elem.link != '') {
						drillDown = "<br>Click to drilldown";
					  } else {
						drillDown = "";
					  }

					  if(elem.valuelabel != 'undefined' && elem.valuelabel != undefined && elem.valuelabel != '') {
						value = "elem.valuelabel";
					  } else {
						value = "elem.value";
					  }
					  eval("tip.innerHTML = '<b>' + elem."+chartConfig["tip"]+" + '</b>: ' + "+value+" + drillDown");
					}
				  }
				});
				//load JSON data.
				funnelChart.loadJSON(json);
				//end
				
				/*
				var list = $jit.id('id-list'),
					button = $jit.id('update'),
					orn = $jit.id('switch-orientation');
				//update json on click 'Update Data'
				$jit.util.addEvent(button, 'click', function() {
				  var util = $jit.util;
				  if(util.hasClass(button, 'gray')) return;
				  util.removeClass(button, 'white');
				  util.addClass(button, 'gray');
				  barChart.updateJSON(json2);
				});
				*/
				//dynamically add legend to list
				var list = $jit.id('legend'+chartId);
				var legend = funnelChart.getLegend(),
					rows = Math.ceil(legend["name"].length/5);
					table = "<table cellpadding='0' cellspacing='0' align='left'>";
				var j = 0;
				for(i=0;i<rows;i++) {
					table += "<tr>"; 
					for(td=0;td<5;td++) {
						
						table += '<td nowrap>';
						if(legend["name"][j] != undefined) {
							table += '<div class=\'query-color\' style=\'background-color:'
							  + legend["color"][j] +'\'>&nbsp;</div>' + legend["name"][j];
						}
						  
						table += '</td>';
						j++;
						}
					table += "</tr>"; 
				}
				
					table += "</table>";
				list.innerHTML = table;
				
					}
						}
						
				var callback =
				{
				  success:handleSuccess,
				  failure:handleFailure,
				  argument: { foo:'foo', bar:''}
				};
				
				var request = YAHOO.util.Connect.asyncRequest('GET', jsonFilename, callback);
				break;
				
				
				
			case "gaugeChart":

				var handleFailure = function(o){
				alert('fail');
					if(o.responseText !== undefined){
						alert('failed');
					}
				}	
				var handleSuccess = function(o){

					if(o.responseText !== undefined){			
					var json = eval('('+o.responseText+')');
					var properties = $jit.util.splat(json.properties)[0];	

						//init Gauge Chart
				var gaugeChart = new $jit.GaugeChart({
				  //id of the visualization container
				  injectInto: chartId,
				  //whether to add animations
				  animate: false,
				  labelType: properties['labels'],
				  hoveredColor: false,
				  Subtitle: {
					text: properties['subtitle'],
					size: 11,
					color: css["color"],
					offset: 5
				  },
				  //offsets
				  offset: 0,
				  sliceOffset: 0,
				  labelOffset: 30,
				  gaugeStyle: {
					backgroundColor: '#aaaaaa',
					borderColor: '#999999',
					needleColor: 'rgba(255,0,0,80)',
					borderSize: 4,
					positionFontSize: 24,
					positionOffset: 2
				  },
				  //slice style
				  type: useGradients? chartConfig["gaugeType"]+':gradient' : chartConfig["gaugeType"],
				  //whether to show the labels for the slices
				  showLabels:true,
				  Events: {
					enable: true,
					onClick: function(node) {  
					if(!node) return;  
					if(node.link == 'undefined' || node.link == '') return;
					window.location.href=node.link;
					}
				  },
				  //label styling
				  Label: {
					type: labelType, //Native or HTML
					size: 12,
					family: css["font-family"],
					color: css["color"]
				  },
				  //enable tips
				  Tips: {
					enable: true,
					onShow: function(tip, elem) {
					  if(elem.link != 'undefined' && elem.link != '') {
						drillDown = "<br>Click to drilldown";
					  } else {
						drillDown = "";
					  }
					  
					  if(elem.valuelabel != 'undefined' && elem.valuelabel != undefined && elem.valuelabel != '') {
						value = "elem.valuelabel";
					  } else {
						value = "elem.value";
					  }
					   eval("tip.innerHTML = '<b>' + elem.label + '</b>: ' + "+ value +" + drillDown");
					}
				  }
				});
				//load JSON data.
				gaugeChart.loadJSON(json);

				
				var list = $jit.id('legend'+chartId);
				var legend = gaugeChart.getLegend(),
					rows = Math.ceil(legend["name"].length/5);
					table = "<table cellpadding='0' cellspacing='0' align='left'>";
				var j = 1;
				for(i=0;i<rows;i++) {
					table += "<tr>"; 
					for(td=0;td<5;td++) {
						
						table += '<td nowrap>';
						if(legend["name"][j] != undefined) {
							table += '<div class=\'query-color\' style=\'background-color:'
							  + legend["color"][j] +'\'>&nbsp;</div>' + legend["name"][j];
						}
						  
						table += '</td>';
						j++;
						}
					table += "</tr>"; 
				}
				
					table += "</table>";
				list.innerHTML = table;
				
				
							}
								}
								
				var callback =
				{
				  success:handleSuccess,
				  failure:handleFailure,
				  argument: { foo:'foo', bar:''}
				};
				
				var request = YAHOO.util.Connect.asyncRequest('GET', jsonFilename, callback);
							
				break;
				
			}
		}
	}
}();