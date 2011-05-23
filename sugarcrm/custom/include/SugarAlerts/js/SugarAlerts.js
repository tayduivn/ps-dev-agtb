if(typeof SUGAR == 'undefined') SUGAR = {};
if(typeof SUGAR.SugarAlerts == 'undefined') { // since the dashlet can be included multiple times a page, don't redefine these functions
	SUGAR.SugarAlerts = {};
	SUGAR.SugarAlerts.removeAlert = function(id, type) {
		var el = document.getElementById("row_" + id);
		if(el != null){
			el.style.display = 'none';
		}
		
		// remove from SugarAlerts
		var url = "index.php?" + SUGAR.util.paramsToUrl({ 
			module:"Accounts", 
			action:"sugar_alert_action", 
			record: id, 
			type:type,
			alert_action:"delete"
		}); 
	 
		var results_name = eval("(" + http_fetch_sync(url).responseText + ")"); 
		if(typeof(results_name['error']) != 'undefined'){ 
			return; 
		} 
	};
	SUGAR.SugarAlerts.getUnreadCount = function(type){
		// remove from SugarAlerts
		var url = "index.php?" + SUGAR.util.paramsToUrl({ 
			module:"Accounts", 
			action:"sugar_alert_action", 
			type:type,
			alert_action:"getUnreadCount"
		}); 
	 
		var results_name = eval("(" + http_fetch_sync(url).responseText + ")"); 
		if(typeof(results_name['error']) != 'undefined'){ 
			return false;
		}
		else if(typeof(results_name['success']) != 'undefined'){
			var unreadCount = results_name['count'];
			return unreadCount;
		}
	}
	SUGAR.SugarAlerts.displayCubeType = function(type, alertCount){
		if(type == 'none'){
			var els = document.getElementsByName('dcmenuSugarCube_none');
			if(els[0] != null){
				els[0].style.display='';
			}
			var els = document.getElementsByName('dcmenuSugarCube_alerts');
			if(els[0] != null){
				els[0].style.display='none';
			}
		}
		if(type == 'alerts'){
			var notif_el = document.getElementById('notifCount');
			if(notif_el != null){
				notif_el.innerHTML = alertCount;
			}
			var els = document.getElementsByName('dcmenuSugarCube_alerts');
			if(els[0] != null){
				els[0].style.display='';
			}
			var els = document.getElementsByName('dcmenuSugarCube_none');
			if(els[0] != null){
				els[0].style.display='none';
			}
		}
	}
	SUGAR.SugarAlerts.dynamicNotification = function(){
		var count = SUGAR.SugarAlerts.getUnreadCount('cube');
		if(count !== false){
			if(count > 0){
				SUGAR.SugarAlerts.displayCubeType('alerts', count);
			}
			else{
				SUGAR.SugarAlerts.displayCubeType('none', 0);
			}
		}
		var t=setTimeout("SUGAR.SugarAlerts.dynamicNotification()", 15000);
	}
}
