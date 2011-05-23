{* Revenue Line Items EditView *}

{* level definition from vardefs *}
{capture name="level"}{{sugarvar key='revitems_level'}}{/capture}
{assign var="level" value=$smarty.capture.level}
{if $level}
	{capture name="level_ibm"}{{sugarvar key='revitems_level_ibm'}}{/capture}
	{assign var="level_ibm" value=$smarty.capture.level_ibm}
{/if}

{* search type field definition *}
{if $level == 0}
	{capture name="search_type_field"}{{sugarvar key='revitems_search_type_field'}}{/capture}
	{assign var="search_type_field" value=$smarty.capture.search_type_field}
{/if}


{* field name from vardefs *}
{capture name="revitem_key"}{{sugarvar key='name'}}{/capture}
{assign var="revitem_key" value=$smarty.capture.revitem_key}

{* hidden value *}
<input type="hidden" id="{$revitem_key}" name="{$revitem_key}" value="{{sugarvar key='value'}}">

{* form value = product name, populated dynamically afterwards *}
<input id="{$revitem_key}-input" name="{$revitem_key}-input" size="45" value="" type="text" style="vertical-align: top;">

{* level label and dropdown image *} 
{if $level}
<div id="{$revitem_key}-dd-image" name="{$revitem_key}-dd-image" style="display:none;">
<img src="{sugar_getimagepath file="down_arrow.png"}" onclick="document.getElementById('{$revitem_key}-input').focus();"/></div>
(Level {$level_ibm})
{/if}

{* image animation *}
<div id="{$revitem_key}-loader-image" name="{$revitem_key}-loader-image" style="display:none;">
<img src="{sugar_getimagepath file="revitems-loader.gif"}" /></div>

{* load global script for global search field only *}
{if $level == 0}
	<script type="text/javascript" src="{sugar_getjspath file="custom/include/javascript/RevenueLineItems.js"}"></script>
{/if}

<script type="text/javascript">
{literal}

// Add field specific object
SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal} = [];

// Level->Field definition
fieldLevel = "{/literal}{$level}{literal}";
if(fieldLevel == parseInt(fieldLevel)) {
	SUGAR.ibm_revenueLineItems.Fields[fieldLevel] = "{/literal}{$revitem_key}{literal}";
}

// Autocomplete search field
YUI().use("autocomplete", "autocomplete-filters", "autocomplete-highlighters", function (Y) {

	// text and hidden id field
	SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputNode = Y.one('#{/literal}{$revitem_key}{literal}-input');
	SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputHidden = Y.one('#{/literal}{$revitem_key}{literal}');

	// global search type
	SUGAR.ibm_revenueLineItems.Global.searchTypeField = Y.one('#{/literal}{$search_type_field}{literal}');
	
	// image animations
	SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.loaderImage = Y.one('#{/literal}{$revitem_key}{literal}-loader-image');

	// dropdown image
	SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.ddImage = Y.one('#{/literal}{$revitem_key}{literal}-dd-image');
	
	// auto completion settings
	SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.minQLen = 0;

	// set minimum query length
	{/literal}{if $level}{literal}
		SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.minQLen = 0;
	{/literal}{else}{literal}
		SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.minQLen = 1;
	{/literal}{/if}{literal}
	
			
	SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.queryDelay = 500;
	SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputNode.plug(Y.Plugin.AutoComplete, {
		activateFirstItem: true,
		tabSelect: true,
		allowBrowserAutocomplete: false,
		alwaysShowList: false,
		minQueryLength: SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.minQLen,
		queryDelay: SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.queryDelay,
		requestTemplate: function(query) {
			// pass the parentid to the query for subitem filtering
			parentLevel = {/literal}{$level}{literal} - 1;
			parentid = '';
			if(parentLevel > 0) {
				fieldName = SUGAR.ibm_revenueLineItems.Fields[parentLevel];
				parentid = eval("SUGAR.ibm_revenueLineItems." + fieldName + ".inputHidden.get('value')");
			}

			// global search type
			searchType = eval("SUGAR.ibm_revenueLineItems.Global.searchTypeField.get('value')");

			return '&type=search&searchtype=' + searchType + '&level={/literal}{$level}{literal}&q=' + query + '&parentid=' + parentid;
		},
		source: SUGAR.ibm_revenueLineItems.Global.search,
		resultTextLocator: 'text',
		resultHighlighter: 'phraseMatch',
		resultFilters: 'phraseMatch',
	});

{/literal}{if $level != 0}{literal}
	//initially load the product name from the saved bean
	recordId = SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputHidden.get('value');
	if (recordId) {
		SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.loaderImage.setStyle('display', 'inline');
		SUGAR.ibm_revenueLineItems.Global.load.sendRequest( {
			// replace plus sign in query
			request: "&q=" + recordId.replace(/\+/g, "%2b") + "&type=load",
			callback: loadCallback
		});
	}

{/literal}{/if}{literal}	

{/literal}{if $level != 0}{literal}
	// when they focus away from the field
	SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputNode.on('blur', function(e) {

		// if no hidden id set, clear form field 
		if (SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputHidden.get('value') == '') {
			SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputNode.set('value', '');
		}

		// if form field is empty, clear hidden value and clear children
		if (SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputNode.get('value') == '') {
			SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputHidden.set('value', '');
			// BEGIN sadek - FIRE CHANGE EVENT TO TRIGGER SUGAR LOGIC ON THE MAIN WINDOW
			var tempEvent = window.document.createEvent('HTMLEvents');
			tempEvent.initEvent('change', true, true);
			var el = document.getElementById('{/literal}{$revitem_key}{literal}');
			if(el != null){
				el.dispatchEvent(tempEvent);
			}
			// END sadek - FIRE CHANGE EVENT TO TRIGGER SUGAR LOGIC ON THE MAIN WINDOW
			for(i = {/literal}{$level}{literal} + 1; i < SUGAR.ibm_revenueLineItems.Fields.length; i++) {
				fieldName = SUGAR.ibm_revenueLineItems.Fields[i];
				eval("SUGAR.ibm_revenueLineItems." + fieldName + ".inputHidden.set('value', '')");
				eval("SUGAR.ibm_revenueLineItems." + fieldName + ".inputNode.set('value', '')");
				// BEGIN sadek - FIRE CHANGE EVENT TO TRIGGER SUGAR LOGIC ON THE MAIN WINDOW
				var tempEvent = window.document.createEvent('HTMLEvents');
				tempEvent.initEvent('change', true, true);
				var el = document.getElementById(fieldName);
				if(el != null){
					el.dispatchEvent(tempEvent);
				}
				// END sadek - FIRE CHANGE EVENT TO TRIGGER SUGAR LOGIC ON THE MAIN WINDOW
			}	
		}
	});

	// init dropdown image toggle depending on current state
	initDD('{/literal}{$revitem_key}{literal}','{/literal}{$fields.search_type.value}{literal}');
			
{/literal}{/if}{literal}

	// trigger autocomplete
	SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputNode.ac.on('query', function () {
		SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputHidden.set('value', '');
		// BEGIN sadek - FIRE CHANGE EVENT TO TRIGGER SUGAR LOGIC ON THE MAIN WINDOW
		var tempEvent = window.document.createEvent('HTMLEvents');
		tempEvent.initEvent('change', true, true);
		var el = document.getElementById('{/literal}{$revitem_key}{literal}');
		if(el != null){
			el.dispatchEvent(tempEvent);
		}
		// END sadek - FIRE CHANGE EVENT TO TRIGGER SUGAR LOGIC ON THE MAIN WINDOW
	});

	{/literal}
	{* allow empty queries for sei products, only on non-global search boxes *} 
	{if $level}
	{literal}
				
	// send out empty query on focus when sei products is selected
	SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputNode.on('focus', function () {
		if(eval("SUGAR.ibm_revenueLineItems.Global.searchTypeField.get('value')") == 'sei') {
			SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputNode.ac.sendRequest('');
		}
	});
			
	{/literal}
	{/if}
	{literal}

		
	// after selecting an entry from the list
	SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputNode.ac.on('select', function(e) {


{* load routine for global search box *}
{/literal}{if $level == 0}{literal}

		// trigger image animations
		SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.loaderImage.setStyle('display','inline');

		// empty all levels
		clearAllLevels();

		// load the selected item into the required level
		recordId = e.result.raw.key;
		SUGAR.ibm_revenueLineItems.Global.load.sendRequest( {
			// replace plus sign in query
			request: "&q=" + recordId.replace(/\+/g, "%2b") + "&type=load",
			callback: loadCallback
		});
		
		// lookup parents, callback function updates the form
		SUGAR.ibm_revenueLineItems.Global.lookup.sendRequest( {
			// replacing plus sign as this is a special get character
			request: "&q=" + recordId.replace(/\+/g,"%2b") + "&type=lookup&level=" + e.result.raw.level,
			callback: lookupCallback
		});

		// empty global search field
		fieldName = SUGAR.ibm_revenueLineItems.Fields[0];
		eval("SUGAR.ibm_revenueLineItems." + fieldName + ".inputNode.set('value', '')");
		
{* load routine for normal search boxes *}
{/literal}{else}{literal}

		// trigger image animations
		if({/literal}{$level}{literal} > 1) {
			SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.loaderImage.setStyle('display','inline');
		}

		// set hidden id value
		SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputHidden.set('value', e.result.raw.key);
		
		// BEGIN sadek - FIRE CHANGE EVENT TO TRIGGER SUGAR LOGIC ON THE MAIN WINDOW
		var tempEvent = window.document.createEvent('HTMLEvents');
		tempEvent.initEvent('change', true, true);
		var el = document.getElementById('{/literal}{$revitem_key}{literal}');
		if(el != null){
			el.dispatchEvent(tempEvent);
		}
		// END sadek - FIRE CHANGE EVENT TO TRIGGER SUGAR LOGIC ON THE MAIN WINDOW
		
		// clear children
		for(i = {/literal}{$level}{literal} + 1; i < SUGAR.ibm_revenueLineItems.Fields.length; i++) {
			fieldName = SUGAR.ibm_revenueLineItems.Fields[i];
			eval("SUGAR.ibm_revenueLineItems." + fieldName + ".inputHidden.set('value', '')");
			eval("SUGAR.ibm_revenueLineItems." + fieldName + ".inputNode.set('value', '')");
			// BEGIN sadek - FIRE CHANGE EVENT TO TRIGGER SUGAR LOGIC ON THE MAIN WINDOW
			var tempEvent = window.document.createEvent('HTMLEvents');
			tempEvent.initEvent('change', true, true);
			var el = document.getElementById(fieldName);
			if(el != null){
				el.dispatchEvent(tempEvent);
			}
			// END sadek - FIRE CHANGE EVENT TO TRIGGER SUGAR LOGIC ON THE MAIN WINDOW
		}

		// lookup parents, callback function updates the form if not toplevel
		if({/literal}{$level}{literal} > 1) {
			recordId = SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputHidden.get('value');
			SUGAR.ibm_revenueLineItems.Global.lookup.sendRequest( {
				// replacing plus sign as this is a special get character
				request: "&q=" + recordId.replace(/\+/g,"%2b") + "&type=lookup&level={/literal}{$level}{literal}",
				callback: lookupCallback
			});
		}

		// get our focus away from this field (FIXME: none of these seem to work ...)
		//document.getElementById('name-input').focus();
		SUGAR.ibm_revenueLineItems.{/literal}{$revitem_key}{literal}.inputNode.ac.blur();
		
{/literal}{/if}{literal}

	});
});

{/literal}

{* logic used when search type radio selection changes *}
{if $level == 0}

	{literal}
	//called onclick when changing search type (product vs sei)
	function changeSearchType(setSearchType) {

		// track search type in hidden input to be able to read it easily in YUI
		document.getElementById('{/literal}{$search_type_field}{literal}').value=setSearchType; 

		// change global filter label, search footer and toggle dropdown image
		if(setSearchType == 'sei') {
			searchLabel = 'Search for SEI Solutions:';
			searchFooter = 'Or, find SEI Solutions by level using the fields below.';
			ddStyle = 'inline';
		} else {
			searchLabel = 'Search for Product Offerings:';
			searchFooter = 'Or, find Product Offerings by level using the fields below.';
			ddStyle = 'none';
		}
		document.getElementById('{/literal}{$revitem_key}{literal}_label').innerHTML = searchLabel;
		document.getElementById('{/literal}{$revitem_key}{literal}_footer').innerHTML = searchFooter;
		clearAllLevels();
		toggleDD(setSearchType);
	}

	// toggle all dropdown images depending on search type
	function toggleDD(setSearchType) {
		for(i = 1; i < SUGAR.ibm_revenueLineItems.Fields.length; i++) {
			fieldName = SUGAR.ibm_revenueLineItems.Fields[i];
			initDD(fieldName, setSearchType);
		}
	}

	// toggle single dropdown image
	function initDD(fieldName, setSearchType) {
		if(setSearchType == 'sei') {
			ddStyle = 'inline';
		} else {
			ddStyle = 'none';
		}
		eval("SUGAR.ibm_revenueLineItems." + fieldName + ".ddImage.setStyle('display', ddStyle)");
	}
	
	// clear all levels
	function clearAllLevels() {
		for(i = 1; i < SUGAR.ibm_revenueLineItems.Fields.length; i++) {
			fieldName = SUGAR.ibm_revenueLineItems.Fields[i];
			eval("SUGAR.ibm_revenueLineItems." + fieldName + ".inputHidden.set('value', '')");
			eval("SUGAR.ibm_revenueLineItems." + fieldName + ".inputNode.set('value', '')");
			// BEGIN sadek - FIRE CHANGE EVENT TO TRIGGER SUGAR LOGIC ON THE MAIN WINDOW
			var tempEvent = window.document.createEvent('HTMLEvents');
			tempEvent.initEvent('change', true, true);
			var el = document.getElementById(fieldName);
			if(el != null){
				el.dispatchEvent(tempEvent);
			}
			// END sadek - FIRE CHANGE EVENT TO TRIGGER SUGAR LOGIC ON THE MAIN WINDOW
		}
	}

	{/literal}
		
{/if}
</script>
