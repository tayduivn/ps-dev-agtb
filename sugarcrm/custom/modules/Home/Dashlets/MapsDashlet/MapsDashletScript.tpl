{*

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

// $Id: MapsDashletScript.tpl,v 1.1 2006/10/11 00:53:31 clint Exp $

*}


{literal}
<script type="text/javascript">
var YAHOO=window.YAHOO||{}; YAHOO.namespace=function(_1){ if(!_1||!_1.length){ return null; } var _2=_1.split("."); var _3=YAHOO; for(var i=(_2[0]=="YAHOO")?1:0;i<_2.length;++i){ _3[_2[i]]=_3[_2[i]]||{}; _3=_3[_2[i]]; } return _3; }; YAHOO.namespace("util"); YAHOO.namespace("widget"); YAHOO.namespace("example"); var YMAPPID = "SUGARCRMMAPSAPPID"; function _ywjs(inc) { var o='<'+'script src="'+inc+'"'+' type="text/javascript"><'+'/script>'; document.write(o); } _ywjs('http://api.maps.yahoo.com/v3.4/aj/ymapapi.js');
</script>
</script>
<script type="text/javascript" src="include/javascript/popup_parent_helper.js?s={$sugar_version}&c={$js_custom_version}"></script>
<script>
if(typeof Maps == 'undefined') { // since the dashlet can be included multiple times a page, don't redefine these functions
	Maps = function() {
	var _map;
	 _yimg = new YImage('{/literal}{$site_url}{literal}/include/images/sugar_icon.ico', new YSize(15,15));
	    return {
	    	/**
	    	 * Called when the textarea is blurred
	    	 */
	        blur: function(ta, id) {
	        	ajaxStatus.showStatus('{/literal}{$saving}{literal}'); // show that AJAX call is happening
	        	// what data to post to the dashlet
    	    	postData = 'to_pdf=1&module=Home&action=CallMethodDashlet&method=mapRecord&id=' + id + '&trackingNumber=' + ta.value;
				var cObj = YAHOO.util.Connect.asyncRequest('POST','index.php', 
								  {success: Maps.saved, failure: Maps.saved}, postData);
	        },
	        click: function(id) {
	        	Maps.drawMap(id, false);
	        			
				if(document.getElementById("{/literal}maps_mapping_type_{$id}{literal}").value == 'find'){
					var address1 = document.getElementById('maps_input_primary_address_street_'+id).value;
					var address_city = document.getElementById('maps_input_primary_address_city_'+id).value;
					var address_state = document.getElementById('maps_input_primary_address_state_'+id).value;
					var address_postalcode = document.getElementById('maps_input_primary_address_postalcode_'+id).value;
					var address_country = document.getElementById('maps_input_primary_address_country_'+id).value;
					YEvent.Capture(_map,EventsList.onEndGeoCode, Maps.EndGeoCode);
					_map.geoCodeAddress(address1+" "+address_city+" ,"+address_state+" "+address_postalcode+" "+address_country);
				}else{
					var address1 = document.getElementById('maps_input_my_address_'+id).value;
					var distance = document.getElementById('maps_input_my_dist_'+id).value;
					//YEvent.Capture(_map,EventsList.onEndGeoCode, Maps.EndGeoCodeMyAddress);
					//_map.geoCodeAddress(address1);
					ajaxStatus.showStatus('{/literal}{$saving}{literal}'); // show that AJAX call is happening
	        	// what data to post to the dashlet
    	    	postData = 'to_pdf=1&module=Home&action=CallMethodDashlet&method=getClosest&id=' + id + '&zip='+address1+'&distance='+distance;
				var cObj = YAHOO.util.Connect.asyncRequest('POST','index.php', 
								  {success: Maps.saved, failure: Maps.saved}, postData);
				}
				
				
	        },
	        drawMap: function(id, firstTime){
	        	_map = new YMap(document.getElementById('maps_output_'+id));
	        	_map.addTypeControl();
					_map.addPanControl(); 
				// Add a slider zoom control 
				if(firstTime){
					_map.addZoomLong();
				}
				 
				_map.enableDragMap();
				
				//add HQ
			
				var hqPoint = new YGeoPoint(37.324077,-122.014068);
				var hqOutput = "SugarCRM Inc.<br>10050 North Wolfe Road<br>SW2-130<br>Cupertino, CA 95014 USA<br>408.454.6900";
				var marker = new YMarker(hqPoint, _yimg);
				marker.addAutoExpand(hqOutput);
	        	_map.addOverlay(marker); 
				
				// Set map type to either of: YAHOO_MAP_SAT YAHOO_MAP_HYB YAHOO_MAP_REG
				//map.setMapType(YAHOO_MAP_SAT);
				
				//Get valid map types, returns array [YAHOO_MAP_REG, YAHOO_MAP_SAT, YAHOO_MAP_HYB]
				var myMapTypes = _map.getMapTypes(); 
				_map.drawZoomAndCenter(hqPoint, 15);
	        },
	        EndGeoCode: function(data)
	        {
	        	var marker = new YMarker(data.GeoPoint, _yimg);
				// Add a label to the marker
				id = '{/literal}{$id}{literal}';
				var address1 = document.getElementById('maps_input_primary_address_street_'+id).value;
				var address_city = document.getElementById('maps_input_primary_address_city_'+id).value;
				var address_state = document.getElementById('maps_input_primary_address_state_'+id).value;
				var address_postalcode = document.getElementById('maps_input_primary_address_postalcode_'+id).value;
				var address_country = document.getElementById('maps_input_primary_address_country_'+id).value;
				var phone_work = document.getElementById('maps_input_phone_work_'+id).value;
				to_yahoo_url = "http://maps.yahoo.com/dd?taddr="+address1+"&tcsz="+address_city+"+"+address_state+"+"+address_postalcode+"&tcountry="+address_country;
				from_yahoo_url = "http://maps.yahoo.com/dd?addr="+address1+"&csz="+address_city+"+"+address_state+"+"+address_postalcode+"&country="+address_country;
				output = address1+" "+address_city+" ,"+address_state+" "+address_postalcode+" "+address_country+"<br>"+phone_work+"<br><a href='"+from_yahoo_url+"' target='top'>Directions from here</a><br><a href='"+to_yahoo_url+"' target='top'>Directions to here</a>";
				marker.addAutoExpand(output);
	        	data.ThisMap.addOverlay(marker); 	
	        	data.ThisMap.drawZoomAndCenter(data.GeoPoint, 15);
	        },
	        EndGeoCodeMyAddress: function(data)
	        {
	        //console.debug("message",data) 
	        	var marker = new YMarker(data.GeoPoint);
				// Add a label to the marker
				id = '{/literal}{$id}{literal}';
				var address1 = document.getElementById('maps_input_my_address_'+id).value;
				//to_yahoo_url = "http://maps.yahoo.com/dd?taddr="+address1+"&tcsz="+address_city+"+"+address_state+"+"+address_postalcode+"&tcountry="+address_country;
				//from_yahoo_url = "http://maps.yahoo.com/dd?addr="+address1+"&csz="+address_city+"+"+address_state+"+"+address_postalcode+"&country="+address_country;
				output = address1;
				marker.addAutoExpand(output);
	        	data.ThisMap.addOverlay(marker); 	
	        	data.ThisMap.drawZoomAndCenter(data.GeoPoint, 5);
	        },
		    /**
	    	 * Called when the textarea is double clicked on
	    	 */
			edit: function(divObj, id) {
				ta = document.getElementById('maps_input_' + id);
				if(isIE) ta.value = divObj.innerHTML.replace(/<br>/gi, "\n");
				else ta.value = divObj.innerHTML.replace(/<br>/gi, '');
				
				divObj.style.display = 'none';
				ta.style.display = '';
				ta.focus();
			},
		    /**
	    	 * handle the response of the saveText method
	    	 */
	        saved: function(data) {
	        
	        	eval(data.responseText);
	        	for (var x = 0; x < result['records_found'].length; x++)
   				{
   					var marker = new YMarker(result['records_found'][x]['address_postalcode'], _yimg);
   					var address1 = result['records_found'][x]['address_street'];
   					var address_city = result['records_found'][x]['address_city'];
   					var address_state = result['records_found'][x]['address_state'];
   					var address_postalcode = result['records_found'][x]['address_postalcode'];
   					var address_country = result['records_found'][x]['address_country']; 
   					to_yahoo_url = "http://maps.yahoo.com/dd?taddr="+address1+"&tcsz="+address_city+"+"+address_state+"+"+address_postalcode+"&tcountry="+address_country;
					from_yahoo_url = "http://maps.yahoo.com/dd?addr="+address1+"&csz="+address_city+"+"+address_state+"+"+address_postalcode+"&country="+address_country;
   					var output = result['records_found'][x]['address_street']+" "+result['records_found'][x]['address_city']+", "+result['records_found'][x]['address_state']+" "+result['records_found'][x]['address_postalcode']+" "+result['records_found'][x]['address_country']+"<br>"+result['records_found'][x]['phone_office']+"<br><a href='"+from_yahoo_url+"' target='top'>Directions from here</a><br><a href='"+to_yahoo_url+"' target='top'>Directions to here</a>";;
					marker.addAutoExpand(output);
   					_map.addOverlay(marker);
   				}
   				var marker = new YMarker(result['center_zip'], _yimg);
   				marker.addAutoExpand("You ("+result['center_zip']+")");
   				_map.addOverlay(marker);
   				_map.drawZoomAndCenter(result['center_zip'], 15);
	           	ajaxStatus.showStatus('{/literal}{$saved}{literal}');

				//ta.style.display = 'none';
				//theDiv.style.display = '';
	           	window.setTimeout('ajaxStatus.hideStatus()', 2000);
	        },
	        openPopup: function(){
	        	element = document.getElementById("{/literal}maps_type_{$id}{literal}");
	        	new_module = element.value;
	        	if(new_module == 'Contacts' || new_module == 'Leads') {
					encoded_popup = "{/literal}{$encoded_popup_request_data_contacts}{literal}";
				}
				else {
					encoded_popup = "{/literal}{$encoded_popup_request_data_other}{literal}";
				}	
	        	open_popup(element.value, 600, 400, "&request_data="+encoded_popup, true, false);
	        }
	    };
	}();
} 
</script>{/literal}
{literal}
<script type="text/javascript" src="include/JSON.js?s={$sugar_version}&c={$js_custom_version}"></script><script type="text/javascript">
var GLOBAL_REGISTRY = new Object();

GLOBAL_REGISTRY.config = {"site_url":"{/literal}{$site_url}{literal}"};
GLOBAL_REGISTRY.meta = new Object();
GLOBAL_REGISTRY.meta.modules = new Object();

GLOBAL_REGISTRY.current_user = {"theme":"Sugar","fields":{"id":"1","user_name":"admin","first_name":"","last_name":"Administrator","full_name":"Administrator","email":"roger@sugarcrm.com","dst_start":"2006-04-02 03:00:00","dst_end":"2006-10-29 01:00:00","gmt_offset":-300,"date_time_format":{"date":"Y-m-d","time":"H:i","userGmt":"(GMT-5)","userGmtOffset":-5}},"module":"User"};
</script>
		<script type="text/javascript" src="include/jsolait/init.js?s={$sugar_version}&c={$js_custom_version}"></script>
		<script type="text/javascript" src="include/jsolait/lib/urllib.js?s={$sugar_version}&c={$js_custom_version}"></script>

		<script type="text/javascript" src="include/javascript/jsclass_base.js?s={$sugar_version}&c={$js_custom_version}"></script>
		<script type="text/javascript" src="include/javascript/jsclass_async.js?s={$sugar_version}&c={$js_custom_version}"></script>
		<script type="text/javascript">sqsWaitGif = "themes/Sugar/images/sqsWait.gif";</script>
		<script type="text/javascript" src="include/javascript/quicksearch.js?s={$sugar_version}&c={$js_custom_version}"></script>
		<script type="text/javascript">
		function changeQS() {
			element = document.getElementById("{/literal}maps_type_{$id}{literal}");
			new_module = element.value;
			if(new_module == 'Contacts' || new_module == 'Leads') {
				sqs_objects['{/literal}maps_input_{$id}{literal}']['disable'] = true;
				document.getElementById("{/literal}maps_input_{$id}{literal}").readOnly = true;
			}
			else {
				sqs_objects['{/literal}maps_input_{$id}{literal}']['disable'] = false;
				document.getElementById("{/literal}maps_input_{$id}{literal}").readOnly = false;
			}	
			sqs_objects['{/literal}maps_input_{$id}{literal}']['module'] = new_module;	
		}
		
		function selectDiv() {
			element = document.getElementById("{/literal}maps_mapping_type_{$id}{literal}");
			type = element.value;
			if(type == 'closest') {
				document.getElementById("{/literal}maps_find_div_{$id}{literal}").style.visibility = 'hidden';
				document.getElementById("{/literal}maps_closest_div_{$id}{literal}").style.visibility = 'visible';
			}
			else {
				document.getElementById("{/literal}maps_find_div_{$id}{literal}").style.visibility = 'visible';
				document.getElementById("{/literal}maps_closest_div_{$id}{literal}").style.visibility = 'hidden';
			}	
		}
		</script>
		<script type="text/javascript" language="javascript">sqs_objects = {"{/literal}maps_input_{$id}{literal}":{"method":"query","modules":["Accounts"],"group":"or","field_list":["name","id", "billing_address_street", "billing_address_city", "billing_address_state", "billing_address_postalcode", "billing_address_country", "phone_office"],"populate_list":["{/literal}maps_input_{$id}{literal}", "{/literal}maps_input_id_{$id}{literal}", "{/literal}maps_input_primary_address_street_{$id}{literal}", "{/literal}maps_input_primary_address_city_{$id}{literal}", "{/literal}maps_input_primary_address_state_{$id}{literal}", "{/literal}maps_input_primary_address_postalcode_{$id}{literal}", "{/literal}maps_input_primary_address_country_{$id}{literal}", "{/literal}maps_input_phone_work_{$id}{literal}"],"conditions":[{"name":"name","op":"like_custom","end":"%","value":""}],"order":"name","limit":"30","no_match_text":"No Match"}}</script>
{/literal}