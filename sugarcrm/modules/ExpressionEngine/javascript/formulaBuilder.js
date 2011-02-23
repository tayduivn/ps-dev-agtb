//FILE SUGARCRM flav=pro ONLY
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
SUGAR.expressions.initFormulaBuilder = function() {
	var Dom = YAHOO.util.Dom,
		Connect = YAHOO.util.Connect,
		Msg = YAHOO.SUGAR.MessageBox;
		
/**
 * @author dwheeler
 */
/**
 * Run through the javascript function cache to find all the loaded functions.
 */
SUGAR.expressions.getFunctionList = function(){
	var typeMap = SUGAR.expressions.Expression.TYPE_MAP;
	var funcMap = SUGAR.FunctionMap;
	var funcList = [];
	for (var i in funcMap) {
		if (typeof funcMap[i] == "function" && funcMap[i].prototype){
			for (var j in typeMap){
				if (funcMap[i].prototype instanceof typeMap[j]) {
					funcList[funcList.length] = [i, j];
					break;
				}
			}
		}
	}
	return funcList;
};

/**
 * Pulls the current expression from the input field, replaces variables and validates through the parser.
 */
SUGAR.expressions.setReturnTypes = function(t, vMap)
{
	var see = SUGAR.expressions.Expression;
	if (t.type == "variable")
	{
		if(typeof(vMap[t.name]) == "undefined")
			throw ("Unknown field: " + t.name);
		else
			t.returnType = vMap[t.name];
	}
	if (t.type == "function")
	{
		for(var i in t.args)
		{
			SUGAR.expressions.setReturnTypes(t.args[i], vMap);
		}
		var fMap = SUGAR.FunctionMap;
		if(typeof(fMap[t.name]) == "undefined")
			throw (t.name + ": No such function defined");
		for (var j in see.TYPE_MAP){
			if (fMap[t.name].prototype instanceof see.TYPE_MAP[j]) {
				t.returnType = j;
				break;
			}
		}
		if(!t.returnType)
			throw (t.name + ": No known return type!");
	}
}

SUGAR.expressions.validateReturnTypes = function(t)
{
	if (t.type == "function")
	{
		//Depth first recursion
		for(var i in t.args)
		{
			SUGAR.expressions.validateReturnTypes(t.args[i]);
		}
		
		var fMap = SUGAR.FunctionMap;
		var see = SUGAR.expressions.Expression;
		if(typeof(fMap[t.name]) == "undefined")
			throw (t.name + ": No such function defined");
		
		var types = fMap[t.name].prototype.getParameterTypes();
		var count = fMap[t.name].prototype.getParamCount();
		
		// check parameter count
		if ( count == see.INFINITY && t.args.length == 0 ) {
			throw (t.name + ": Requires at least one parameter");
		}
		if ( count != see.INFINITY && t.args instanceof Array && t.args.length != count ) {
			throw (t.name + ": Requires exactly " + count + " parameter(s)");
		}
		
		if ( typeof(types) == 'string' ) {
			for (var i = 0 ; i < t.args.length ; i ++ ) {
				if(!t.args[i].returnType)
					throw (t.name + ": No known return type!");
				if ( !fMap[t.name].prototype.isProperType(new see.TYPE_MAP[t.args[i].returnType],types)) {
					throw (t.name + ": All parameters must be of type '" + types + "'");
				}
			}
		}
		else {
			for ( var i = 0 ; i < types.length ; i++ ) {
				if ( !fMap[t.name].prototype.isProperType(new see.TYPE_MAP[t.args[i].returnType],types[i]) ) {
					throw (t.name + ": The parameter at index " + i + " must be of type " + types[i] );
				}
			}
		}
	}
};
SUGAR.expressions.validateCurrExpression = function(silent, matchType) {
	try {
		var varTypeMap = {};
		for (var i = 0; i < fieldsArray.length; i++){
			varTypeMap[fieldsArray[i][0]] = fieldsArray[i][1];
		}
		var expression = YAHOO.lang.trim(Dom.get('formulaInput').value);
		SUGAR.expressions.ExpressionParser.prototype.validate(expression);
		var tokens = new SUGAR.expressions.ExpressionParser().tokenize(expression);
		SUGAR.expressions.setReturnTypes(tokens, varTypeMap);
		SUGAR.expressions.validateReturnTypes(tokens);
		if (matchType && matchType != tokens.returnType)
		{
			Msg.show({
                title: SUGAR.language.get("ModuleBuilder", "LBL_FORMULA_INVALID"),
                msg: SUGAR.language.get("ModuleBuilder", "LBL_FORMULA_TYPE") + matchType
            });
			return false;
		}
		
		if (typeof (silent) == 'undefined' || !silent) 
			Msg.show({msg: "Validation Sucessfull"});
		
		return true;
	} catch (e) {
			Msg.show({
                title: SUGAR.language.get("ModuleBuilder", "LBL_FORMULA_INVALID"),
                msg: e.message ? e.message : e
            });
		return false;
	}
}
SUGAR.expressions.saveCurrentExpression = function(target, returnType)
{
	if (!SUGAR.expressions.validateCurrExpression(true, returnType))
		return false;
	if (YAHOO.lang.isString(target))
		target = Dom.get(target);
	target.value = Dom.get("formulaInput").value;
	return true;
}

SUGAR.expressions.GridToolTip = {
	tipCache : { },
	currentHelpFunc : "",
	showFunctionDescription: function(tip, func) {
		var ggt = SUGAR.expressions.GridToolTip;
		if (ggt.currentHelpFunc == func)
			return;
		ggt.currentHelpFunc = func;
		var cache = ggt.tipCache;
		
		if (typeof cache[func] == 'string') {
			tip.cfg.setProperty("text", cache[func]);
		} else {
			cache[func] = "loading...";
			tip.cfg.setProperty("text", cache[func]);
			ggt.tip = tip;
			Connect.asyncRequest(
			    Connect.method, 
			    Connect.url + '&' + SUGAR.util.paramsToUrl({
			    	"function": func, 
			    	action: "functionDetail", 
			    	module: "ExpressionEngine"
			    }),
			    {success: ggt.showAjaxResponse, failure: function(){}}
			);
		}
	},
	showAjaxResponse: function (o) {
		var ggt = SUGAR.expressions.GridToolTip;
		var r = YAHOO.lang.JSON.parse(o.responseText);
		ggt.tipCache[r.func] = r.desc;
		if (r.func == ggt.currentHelpFunc) {
			ggt.tip.cfg.setProperty("text", r.desc);
		}
	}
};

	var typeFormatter = function(el, rec, col, data)
	{
		var out = "";
		switch(data)
		{
			case "string":
				out = "string"; break;
			case "_number":
			case "number":
				out = "num"; break;
			case "time":
				out = "date"; break;
			case "enum":
				out = "enum"; break;
			case "boolean":
				out = "bool"; break;
			case "date":
				out = "date"; break;
			default:
				out = "generic";
		}
		el.innerHTML = '<img src="themes/default/images/SugarLogic/icon_' + out + '_16.png"></img>';
	};
	var fieldFormatter = function(el, rec, col, data)
	{
		el.innerHTML = "$" + data;
	};
	var visibleFields = [];
	var fieldsJSON =  [];
	var j = 0;
	for(var i in fieldsArray)
	{
		//Hide relate(link) fields from the user, but allow them to be used.
		if(fieldsArray[i][1] != "relate") {
			visibleFields[j] = fieldsArray[i];
			fieldsJSON[j] = {name: fieldsArray[i][0], type: fieldsArray[i][1]};
			j++;
		}
	}

	var fieldDS = new YAHOO.util.LocalDataSource(visibleFields, {
		responseType: YAHOO.util.LocalDataSource.TYPE_JSARRAY,
		responseSchema: {
		   resultsList : "relationships",
		   fields : ['name', 'type']
	    }
	});
	var fieldsGrid = new YAHOO.widget.ScrollingDataTable('fieldsGrid',
		[
		    {key:'name', label: "Fields", width: 200, sortable: true, formatter: fieldFormatter},
		    {key:'type', label: "&nbsp;", width: 20, sortable: true, formatter:typeFormatter}
		],
		fieldDS,
	    {height: "200px", MSG_EMPTY: SUGAR.language.get('ModuleBuilder','LBL_NO_FIELDS')}
	);
	fieldsGrid.on("rowClickEvent", function(e){
		var record = this.getRecord(e.target);
		Dom.get("formulaInput").value += "$" + record.getData().name;
	});
	fieldsGrid.on("sortedByChange", function(e){
		if(e.newValue)
			fieldsGrid.sortedColumn = e.newValue;
	});
	
	fieldDS.queryMatchContains = true;
	var fieldAC = new YAHOO.widget.AutoComplete("formulaFieldsSearch","fieldSearchResults", fieldDS);
	fieldAC.doBeforeLoadData = function( sQuery , oResponse , oPayload ) {
		fieldsGrid.initializeTable();
		fieldsGrid.addRows(oResponse.results);
		fieldsGrid.sortColumn(fieldsGrid.sortedColumn.column, fieldsGrid.sortedColumn.dir);
		fieldsGrid.render();
    }


	Dom.get("formulaFieldsSearch").onkeyup = function() {
		if (this.value == '') {
			fieldsGrid.initializeTable();
			fieldsGrid.addRows(fieldsJSON);
			fieldsGrid.sortColumn(fieldsGrid.sortedColumn.column, fieldsGrid.sortedColumn.dir);
			fieldsGrid.render();
		} // if
	}
	Dom.get("formulaFieldsSearch").onfocus = function() {
		if (Dom.hasClass(this, "empty"))
		{
			this.value = '';
			Dom.removeClass(this, "empty");
		}
	}
	Dom.get("formulaFieldsSearch").onblur = function() {
		if (this.value == '')
		{
			this.value = SUGAR.language.get("ModuleBuilder", "LBL_SEARCH_FIELDS");
			Dom.addClass(this, "empty");
		}
	}
	fieldsGrid.sortColumn(fieldsGrid.getColumn(0))
	fieldsGrid.render();
	SUGAR.expressions.fieldGrid = fieldsGrid;
	var functionsArray = SUGAR.expressions.getFunctionList();
	var usedClasses = { };
	if (!SUGAR.expressions.funcGridData) {
		SUGAR.expressions.funcGridData = [];
		for (var i in functionsArray)
		{
			var fName = functionsArray[i][0];
			//Internal Sugar functions that most users will not find useful
			switch (fName) {
			case "isValidTime":
			case "isAlpha":
			case "doBothExist":
			case "isValidPhone":
			case "isRequiredCollection":
			case "isNumeric":
			case "isValidDBName":
			case "isAlphaNumeric":
			case "stddev":
			case "charAt":
			case "formatName":
			case "rollup":
			case "count":
				continue;
				break;
			}
			//For now, hide date functions in the formula builder as they are unstable.
			if (functionsArray[i][1] == "time")
				continue;
			if (usedClasses[SUGAR.FunctionMap[fName].prototype.className])
				continue;
			if (functionsArray[i][1] == "number")
				SUGAR.expressions.funcGridData.push([functionsArray[i][0], "_number"]);
			else
				SUGAR.expressions.funcGridData.push(functionsArray[i]);
			
			usedClasses[SUGAR.FunctionMap[fName].prototype.className] = true;
		}
	}
	var funcDS = new YAHOO.util.LocalDataSource(SUGAR.expressions.funcGridData, 
	{
		responseType: YAHOO.util.LocalDataSource.TYPE_JSARRAY,
		responseSchema: 
		{
		   resultsList : "relationships",
		   fields : ['name', 'type']
	    }
	});
	var fg = SUGAR.expressions.functionsGrid = new YAHOO.widget.ScrollingDataTable('functionsGrid',
		[
		    {key:'name', label: "Functions", width: 200, sortable: true},
		    {key:'type', label: "&nbsp;", width: 20, sortable: true, formatter:typeFormatter}
		],
		funcDS,
	    {height: "200px", MSG_EMPTY: SUGAR.language.get('ModuleBuilder','LBL_NO_FUNCS')}
	);
	
	fg.on("rowClickEvent", function(e){
		var record = this.getRecord(e.target);
		Dom.get("formulaInput").value +=  record.getData().name + '(';
	});
	fg.on("sortedByChange", function(e){
		if(e.newValue)
		    SUGAR.expressions.functionsGrid.sortedColumn = e.newValue;
	});
	
	var funcTip = new YAHOO.widget.Tooltip("functionsTooltip", {
		context: "functionsGrid",
		text: "",
		showDelay: 300,
		zindex: 25
	});
	
	funcTip.table = fg;
	
	funcTip.contextMouseOverEvent.subscribe(function(context, e){
		var target =  e[1].srcElement  ? e[1].srcElement : e[1].target;
		if ((Dom.hasClass(target, "yui-dt-bd"))) {return false;}
		
		var row = this.table.getRecord(target);
		if (!row) {return false;}
		
		if (this.timer)
			this.timer.cancel();
		
		this.timer = YAHOO.lang.later(250, this, function(funcName){
			SUGAR.expressions.GridToolTip.showFunctionDescription(this, funcName);
		}, row.getData()['name']);
		
		return true;
	});
	funcDS.queryMatchContains = true;
	var funcAC = new YAHOO.widget.AutoComplete("formulaFuncSearch","funcSearchResults", funcDS);
	funcAC.doBeforeLoadData = function( sQuery , oResponse , oPayload ) {
		var fg = SUGAR.expressions.functionsGrid;
		fg.initializeTable();
		fg.addRows(oResponse.results);
		fg.sortColumn(fg.sortedColumn.column, fg.sortedColumn.dir);
		fg.render();
    }
	if (!SUGAR.expressions.funcionListJSON)
	{
		SUGAR.expressions.funcionListJSON =  [];
		for(var i in SUGAR.expressions.funcGridData)
		{
			SUGAR.expressions.funcionListJSON[i] = {name: SUGAR.expressions.funcGridData[i][0], type: SUGAR.expressions.funcGridData[i][1]};
		}
	}
	Dom.get("formulaFuncSearch").onkeyup = function() {
		if (this.value == '') {
			Dom.addClass(this, "empty");
			var fg = SUGAR.expressions.functionsGrid;
			fg.initializeTable();
			fg.addRows(SUGAR.expressions.funcionListJSON);
			fg.sortColumn(fg.sortedColumn.column, fg.sortedColumn.dir);
			fg.render();
		}
	}
	Dom.get("formulaFuncSearch").onfocus = function() {
		if (Dom.hasClass(this, "empty"))
		{
			this.value = '';
			Dom.removeClass(this, "empty");
		}
	}
	Dom.get("formulaFuncSearch").onblur = function() {
		if (this.value == '')
		{
			this.value = SUGAR.language.get("ModuleBuilder", "LBL_SEARCH_FUNCS");
			Dom.addClass(this, "empty");
		}
	}
	fg.render();
	fg.sortColumn(fg.getColumn(1));

	Dom.setStyle(Dom.get("formulaBuilder").parentNode, "padding", "0");
	
	if(ModuleBuilder && ModuleBuilder.formulaEditorWindow)
		ModuleBuilder.formulaEditorWindow.center();
};