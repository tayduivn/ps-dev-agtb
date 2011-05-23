// global object
if(typeof(SUGAR.ibm_revenueLineItems) == 'undefined') SUGAR.ibm_revenueLineItems = {};
SUGAR.ibm_revenueLineItems.Global = [];

// init level->field definition
if(typeof(SUGAR.ibm_revenueLineItems.Fields) == 'undefined') SUGAR.ibm_revenueLineItems.Fields = [];

// Data source definition for search queries (used by autocomplete)
YUI().use('datasource', 'datasource-jsonschema', function (Y) {
	SUGAR.ibm_revenueLineItems.Global.search = new Y.DataSource.Get({
		source: 'index.php?module=ibm_revenueLineItems&action=ajaxproducts'
	});
	SUGAR.ibm_revenueLineItems.Global.search.plug(Y.Plugin.DataSourceJSONSchema, {
		schema: {
			resultListLocator: "option_items",
			resultFields: ["text", "key", "level"],
			matchKey: "text",
		}
	});
});

// Data source definition for lookup queries (used to populate parents)
YUI().use('datasource', 'datasource-jsonschema', function (Y) {
	SUGAR.ibm_revenueLineItems.Global.lookup = new Y.DataSource.Get({
		source: 'index.php?module=ibm_revenueLineItems&action=ajaxproducts'}),
		lookupCallback = {
			success: function(e) {
				// update levels as set by lookup result
				result = e.response.results;
				for (key in result) {
					levelId = result[key].level;
					fieldName = SUGAR.ibm_revenueLineItems.Fields[levelId];
					eval("SUGAR.ibm_revenueLineItems." + fieldName + ".inputHidden.set('value', '" + result[key].key + "')");
					eval("SUGAR.ibm_revenueLineItems." + fieldName + ".inputNode.set('value', '" + result[key].text + "')");
					// BEGIN sadek - FIRE CHANGE EVENT TO TRIGGER SUGAR LOGIC ON THE MAIN WINDOW
					var tempEvent = window.document.createEvent('HTMLEvents');
					tempEvent.initEvent('change', true, true);
					var el = document.getElementById(fieldName);
					if(el != null){
						el.dispatchEvent(tempEvent);
					}
					// END sadek - FIRE CHANGE EVENT TO TRIGGER SUGAR LOGIC ON THE MAIN WINDOW
				}
				
				// stop animation on the child level
				childLevel = result[key].childlevel;
				fieldName = SUGAR.ibm_revenueLineItems.Fields[childLevel];
				eval("SUGAR.ibm_revenueLineItems." + fieldName + ".loaderImage.setStyle('display', 'none')");
				
				// ensure animation on global search field is also stopped and the search box is cleared
				fieldName = SUGAR.ibm_revenueLineItems.Fields[0];
				eval("SUGAR.ibm_revenueLineItems." + fieldName + ".loaderImage.setStyle('display', 'none')");
				eval("SUGAR.ibm_revenueLineItems." + fieldName + ".inputNode.set('value', '')");
				
			},
			// its a doing nothing machine on failure
			failure: function(e) {
				alert('failure lookup !');
			},
		};
	SUGAR.ibm_revenueLineItems.Global.lookup.plug(Y.Plugin.DataSourceJSONSchema, {
		schema: {
			resultListLocator: "parents",
			resultFields: ["key", "text", "level", "childlevel"],
		}
	});
});

// Data source definition for loading initial values (used to populate a specific field)
YUI().use('datasource', 'datasource-jsonschema', function (Y) {
	SUGAR.ibm_revenueLineItems.Global.load = new Y.DataSource.Get({
		source: 'index.php?module=ibm_revenueLineItems&action=ajaxproducts'}),
		loadCallback = {
			success: function(e) {
				// update form field with product name
				result = e.response.results;
				for (key in result) {
					levelId = result[key].level;
					fieldName = SUGAR.ibm_revenueLineItems.Fields[levelId];
					eval("SUGAR.ibm_revenueLineItems." + fieldName + ".inputHidden.set('value', '" + result[key].key + "')");
					eval("SUGAR.ibm_revenueLineItems." + fieldName + ".inputNode.set('value', '" + result[key].text + "')");
				}

				// stop animation 
				eval("SUGAR.ibm_revenueLineItems." + fieldName + ".loaderImage.setStyle('display', 'none')");
			},
			// its a doing nothing machine on failure
			failure: function(e) {
				alert('failure load !');
			},
		};
	SUGAR.ibm_revenueLineItems.Global.load.plug(Y.Plugin.DataSourceJSONSchema, {
		schema: {
			resultListLocator: "product",
			resultFields: ["text", "key", "level"],
		}
	});
});
