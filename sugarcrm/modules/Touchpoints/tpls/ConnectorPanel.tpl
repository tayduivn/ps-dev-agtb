{literal}
<script type="text/javascript">
function copy_value(field_name, field_value_id, color)
{
    var copy_element = document.getElementById(field_name + '_copy');
	var target_element = document.ScrubView[field_name];
	var source_element = document.getElementById(field_value_id);
	if(source_element.value != ''){
	    copy_element.value = source_element.value;
	    copy_element.style.background = '#'+color;
		target_element.value = source_element.value;
		// BEGIN SADEK SUGARINTERNAL CUSTOMIZATION
		if(field_name.substring(0, 8) == 'primary_'){
			document.getElementById('display_' + field_name.substring(8)).innerHTML = source_element.value;
		}
		if(field_name == 'annual_revenue2_c'){
			document.getElementById(field_name).onchange();
		}
		// END SADEK SUGARINTERNAL CUSTOMIZATION
	}
	return true;
}

function copy_all(source_name, color){
	for(i = 0; i < fieldArray.length; i++){
		var source_element = document.getElementById(source_name+'_'+fieldArray[i]);
		if(source_element.value != '' && document.ScrubView[fieldArray[i]]){
			var target_element = document.ScrubView[fieldArray[i]];
			target_element.value = source_element.value;
			var copy_element = document.getElementById(fieldArray[i] + '_copy');
			copy_element.value = source_element.value;
			copy_element.style.background = '#'+color;
			// BEGIN SADEK SUGARINTERNAL CUSTOMIZATION
			if(fieldArray[i].substring(0, 8) == 'primary_'){
				document.getElementById('display_' + fieldArray[i].substring(8)).innerHTML = source_element.value;
			}
			if(fieldArray[i] == 'annual_revenue'){
				document.getElementById(fieldArray[i]).onchange();
			}
			// END SADEK SUGARINTERNAL CUSTOMIZATION
		}
	}
}

function smart_copy(){
	for(j = 0; j < sourceArray.length; j++){
		var source_name = sourceArray[j];
		for(i = 0; i < fieldArray.length; i++){
			var source_element = document.getElementById(source_name+'_'+fieldArray[i]);
			if(source_element.value != '' && document.ScrubView[fieldArray[i]]){
				var target_element = document.ScrubView[fieldArray[i]];
				target_element.value = source_element.value;
				var copy_element = document.getElementById(fieldArray[i] + '_copy');
				copy_element.value = source_element.value;
				copy_element.style.background = source_element.style.background;
				// BEGIN SADEK SUGARINTERNAL CUSTOMIZATION
				if(fieldArray[i].substring(0, 8) == 'primary_'){
					document.getElementById('display_' + fieldArray[i].substring(8)).innerHTML = source_element.value;
				}
				if(fieldArray[i] == 'annual_revenue'){
					document.getElementById(fieldArray[i]).onchange();
				}
				// END SADEK SUGARINTERNAL CUSTOMIZATION
			}
		}
	}
}


function checkKeyDown(event) {
	e = getEvent(event);
	eL = getEventElement(e);
	if ((kc = e["keyCode"])) {
	    enterPressed = (kc == 13) ? true : false;
        if(enterPressed) {
		   SourceTabs.search();
		   freezeEvent(e);
		}
	}
}

function getEvent(event) {
	return (event ? event : window.event);
}

function getEventElement(e) {
	return (e.srcElement ? e.srcElement: (e.target ? e.target : e.currentTarget));
}

function freezeEvent(e) {
	if (e.preventDefault) e.preventDefault();
	e.returnValue = false;
	e.cancelBubble = true;
	if (e.stopPropagation) e.stopPropagation();
	return false;
}

function get_source_details(source, id, spanId){
		go = function() {
			oReturn = function(body, caption, width, theme) {
						return overlib(body, CAPTION, caption, STICKY, MOUSEOFF, 1000, WIDTH, width, CLOSETEXT, ('<img border=0 style="margin-left:2px; margin-right: 2px;" src=themes/' + theme + '/images/close.gif>'), CLOSETITLE, 'Click to Close', CLOSECLICK, FGCLASS, 'olFgClass', CGCLASS, 'olCgClass', BGCLASS, 'olBgClass', TEXTFONTCLASS, 'olFontClass', CAPTIONFONTCLASS, 'olCapFontClass', CLOSEFONTCLASS, 'olCloseFontClass', REF, spanId, REFC, 'LL', REFX, 13);
			},
			success = function(data) {
						eval(data.responseText);
	
						SUGAR.util.additionalDetailsCache[spanId] = new Array();
						SUGAR.util.additionalDetailsCache[spanId]['body'] = result['body'];
						SUGAR.util.additionalDetailsCache[spanId]['caption'] = result['caption'];
						SUGAR.util.additionalDetailsCache[spanId]['width'] = result['width'];
						SUGAR.util.additionalDetailsCache[spanId]['theme'] = result['theme'];
						ajaxStatus.hideStatus();
						return oReturn(SUGAR.util.additionalDetailsCache[spanId]['body'], SUGAR.util.additionalDetailsCache[spanId]['caption'], SUGAR.util.additionalDetailsCache[spanId]['width'], SUGAR.util.additionalDetailsCache[spanId]['theme']);
			}
	
					if(typeof SUGAR.util.additionalDetailsCache[spanId] != 'undefined')
						return oReturn(SUGAR.util.additionalDetailsCache[spanId]['body'], SUGAR.util.additionalDetailsCache[spanId]['caption'], SUGAR.util.additionalDetailsCache[spanId]['width'], SUGAR.util.additionalDetailsCache[spanId]['theme']);
	
					if(typeof SUGAR.util.additionalDetailsCalls[spanId] != 'undefined') // call already in progress
						return;
					ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_LOADING'));
					url = 'index.php?module=Connectors&action=RetrieveSourceDetails&source_id='+source+'&record_id='+id;
					SUGAR.util.additionalDetailsCalls[spanId] = YAHOO.util.Connect.asyncRequest('GET', url, {success: success, failure: success});
	
					return false;
		}
		SUGAR.util.additionalDetailsRpcCall = window.setTimeout('go()', 250);
}
	
function clear_source_details(){
	if(typeof SUGAR.util.additionalDetailsRpcCall == 'number') window.clearTimeout(SUGAR.util.additionalDetailsRpcCall);
}

var _tabView;
var _sourceArray = new Array();
var firstSource = '';
var isDirty = false;
var isHidden = false;

 
SourceTab.prototype.sourceId = '';
SourceTab.prototype.isEnabled = false;
SourceTab.prototype.isDataLoaded = false;
function SourceTab(sourceId){
	this.sourceId = sourceId;
}
SourceTab.prototype.hide = function(){
		var div = document.getElementById('div_'+this.sourceId);
        div.style.display = 'none';
        this.isHidden = true;
	}
SourceTab.prototype.show = function(){
		var div = document.getElementById('div_'+this.sourceId);
        div.style.display = 'block';
        this.isHidden = false;
	}
SourceTab.prototype.refreshData = function(first_load){
		if(first_load){
			SourceTabs.setSearching(true);
		}
		
		var source = this;  	
		var callback =	{
			success: function(data) {
				source.setData(data.responseText);
			},
			failure: function(data) {
				
			}		  
		}
		postData = 'module=Connectors&action=RetrieveSource&record={/literal}{$RECORD}{literal}&to_pdf=true&source_id='+this.sourceId;
		var cObj = YAHOO.util.Connect.asyncRequest('POST','index.php?'+postData, callback);
	}
SourceTab.prototype.setData = function(data){
		var div = document.getElementById('div_'+this.sourceId);

		child_nodes = div.childNodes;
		for(x in child_nodes) {
		    if(typeof child_nodes[x] == 'object') {
		       div.removeChild(child_nodes[x]);
		    }
		}
		div.innerHTML = '';

		//Add in new node
		var newdiv = document.createElement("div");
		newdiv.innerHTML = data;
		div.appendChild(newdiv);
		
		setTimeout("SourceTabs.setSearching(false)", 2000);
		this.isDataLoaded = true;
	}
SourceTab.prototype.isEmpty = function(){
	var div = document.getElementById('div_'+this.sourceId);
	if(div.innerHTML == ''){
		return true;
	}else{
		return false;
	}
}
 
var SourceTabs = {

    init : function() {    
  		//SourceTabs.setSearching(true); 
    },
    
    setSearching : function(searching){
    	var btn_search = document.getElementById('btn_search');
    	if(searching){
    		btn_search.value = {/literal}"{$MOD.LBL_SEARCHING_BUTTON_LABEL}"{literal};;
    	}else{
    		btn_search.value = {/literal}"      {$APP.LBL_SEARCH_BUTTON_LABEL}      "{literal};
    	}
    },
    
    search : function() {        
    	SourceTabs.setSearching(true);
		var formObject = document.getElementById('SearchForm'); 
		if(typeof formObject != 'undefined') {
	    	YAHOO.util.Connect.setForm('SearchForm', false); 
	    	var cObj = YAHOO.util.Connect.asyncRequest('POST','index.php?module=Connectors&action=SetSearch',
	         {
	          success: function() {SourceTabs.refreshActiveConnector();},
	          failure: function() {}
	         }
	        );
        }
    },
    
    clearForm : function() {
    	var formObject = document.getElementById('SearchForm'); 
    	if(typeof formObject != 'undefined') {
	    	for(i=0; i <formObject.elements.length; i++){
				if(typeof formObject.elements[i] != 'undefined' && formObject.elements[i].type == 'text'){
				   formObject.elements[i].value='';
				}
			}
		}
    }, 
    
    refreshActiveConnector : function(){
    	 try{
	    	for(var i = 0; i <= _sourceArray.length; i++){
	    		var sTab = _sourceArray[i];
		    	if(!sTab.isHidden){
		    		sTab.refreshData();
		    		return;
		    	}
		     }
	     }catch(err){
	     
	     }
    },
    
    refreshConnectors : function(){
    	 try{
	    	for(var i = 0; i <= _sourceArray.length; i++){
	    		var sTab = _sourceArray[i];
		    	if(sTab.isDataLoaded){
		    		sTab.refreshData();
		    	}
		     }
	     }catch(err){
	     
	     }
    },
    
    loadTab : function(tab, previousKey) {
        SourceTabs.getSearchForm(tab);
        for (var i = 0; i < _sourceArray.length; i++){
			var sTab = _sourceArray[i];
             if(sTab.sourceId && sTab.sourceId != tab) {
                sTab.hide();
             } else{
				if(isDirty || sTab.isEmpty()){
					SourceTabs.setSearching(true);
	        		sTab.refreshData();
	        	}
	        	sTab.show();
	        }
        }
	     
     },
     
     getSearchForm : function(source_id) {
    	var searchDataDiv = document.getElementById('div_search_form_data_'+source_id);
    	var searchDiv = document.getElementById('div_search_form');
    	if(searchDataDiv.innerHTML == ''){
	    	var callback =	{
				success: function(data) {
					searchDataDiv.innerHTML = data.responseText;
					searchDiv.innerHTML = data.responseText;
				},
				failure: function(data) {
					
				}		  
			}
			postData = 'module=Connectors&action=GetSearchForm&tpl=modules/Touchpoints/tpls/ConnectorSearchForm.tpl&merge_module={/literal}{$module}{literal}&record={/literal}{$RECORD}{literal}&source_id='+source_id;
			var cObj = YAHOO.util.Connect.asyncRequest('POST','index.php', callback, postData);
		} else{
			searchDiv.innerHTML = searchDataDiv.innerHTML;
		}
     }
}


function run_merge() { 

	var callback =	{
		success: function(data) {
			var div_merge_panel = document.getElementById('div_merge_panel');
			newdiv = document.createElement('div');
			newdiv.innerHTML = data.responseText;

			child_nodes = div_merge_panel.childNodes;
			for(x in child_nodes) {
			    if(typeof child_nodes[x] == 'object') {
			       div_merge_panel.removeChild(child_nodes[x]);
			    }
			}
            div_merge_panel.innerHTML = '';
		
			div_merge_panel.appendChild(newdiv);
		    SUGAR.util.evalScript(data.responseText);
		    show_merge_form();
		},
		failure: function(data) {
			
		}		  
	}
	
	recordIds = '';

	for(source_id in _sourceArray) {
	    id = _sourceArray[source_id].sourceId + '_id';
	    if(typeof document.forms['ConnectorStep1'][id] != 'undefined') {
	       if(typeof document.forms['ConnectorStep1'][id].length == 'undefined') {
	           recordIds += '&' + id + '=' +  escape(document.forms['ConnectorStep1'][id].value);
	       } else {
	       	   i = 0;
		       while(i < document.forms['ConnectorStep1'][id].length) {    
		           if(document.forms['ConnectorStep1'][id][i].checked) {
		              recordIds += '&' + id + '=' +  escape(document.forms['ConnectorStep1'][id][i].value);
		           }
		           i++;
		       }
	       }
	    }
	}
	
	if(recordIds != '') {
	    YAHOO.util.Connect.setForm('SearchForm', false); 
		postData = 'module=Touchpoints&action=Step2&record={/literal}{$RECORD}{literal}&to_pdf=true' + recordIds;
		var cObj = YAHOO.util.Connect.asyncRequest('POST','index.php', callback, postData); 
	} else {
	    alert("Please select one record from connector list before attempting to merge.");
	}
     
}


function show_merge_form() {
    //Set the style properties this way as IE was having troubles with rendering the tabs correctly
    document.getElementById('merge_panel').style.display = 'block';
    document.getElementById('merge_panel').style.overflow = 'auto';
    document.getElementById('merge_panel').style.height = 'auto';
    document.getElementById('merge_panel').style.width = 'auto';
    document.getElementById('search_panel').style.display = 'none';
    
}

function show_search_form() {
    //Set the style properties this way as IE was having troubles with rendering the tabs correctly
    document.getElementById('merge_panel').style.display = 'none';
    document.getElementById('search_panel').style.display = 'block';
    document.getElementById('search_panel').style.overflow = 'auto';
    document.getElementById('search_panel').style.height = 'auto';
    document.getElementById('search_panel').style.width = 'auto';               
}
</script>
{/literal}


<div id="merge_panel" {if $show_merge_panel}class="showConnectorPanel"{else}class="hideConnectorPanel"{/if}>
{$MERGE_DIV}
</div>
<div id="search_panel" {if !$show_merge_panel}class="showConnectorPanel"{else}class="hideConnectorPanel"{/if}>
{$SEARCH_DIV}
</div>