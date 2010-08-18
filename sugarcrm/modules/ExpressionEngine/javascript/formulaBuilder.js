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
SUGAR.expressions.validateCurrExpression = function(silent) {
	try {
		var expression = Dom.get('formulaInput').value;
		for (var i = 0; i < fieldsArray.length; i++){
			var replace = "";
			if (fieldsArray[i][1] == 'number')
				replace = "0";
			else if (fieldsArray[i][1] == 'boolean')
				replace = "true";
			else if (fieldsArray[i][1] == 'string')
				replace = '"Hello"';
				
			var replaceEx = new RegExp('\\$' + fieldsArray[i][0], "g");
			expression = expression.replace(replaceEx, replace);
		}
		var result = new SUGAR.expressions.ExpressionParser().evaluate(expression);
		result = result.evaluate();
		if (typeof (silent) == 'undefined' || !silent) 
			Msg.show({msg: "Validation Sucessfull"});
		
		return true;
	} catch (e) {
		if (e.message)
			Msg.show({
                title: "Validation Failed",
                msg: e.message
            });
		else
			Msg.show({
                title: "Validation Failed",
                msg: e
            });
		return false;
	}
}
SUGAR.expressions.saveCurrentExpression = function(target)
{
	if (!SUGAR.expressions.validateCurrExpression(true))
		return false;
	if (YAHOO.lang.isString(target))
		target = Dom.get(target);
	target.value = Dom.get("formulaInput").value;
	return true;
}

SUGAR.expressions.GridToolTip = {
	tipCache : [ ],
	currentHelpFunc : "",
	showFunctionDescription: function(tip, func) {
		var ggt = SUGAR.expressions.GridToolTip;
		if (ggt.currentHelpFunc == func)
			return;
		ggt.currentHelpFunc = func;
		var cache = ggt.tipCache;
		
		if (typeof cache[func] != 'undefined') {
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
	}
	var fieldDS = new YAHOO.util.LocalDataSource(fieldsArray, {
		responseType: YAHOO.util.LocalDataSource.TYPE_JSARRAY,
		responseSchema: {
		   resultsList : "relationships",
		   fields : ['name', 'type']
	    }
	});
	var fieldsGrid = new YAHOO.widget.ScrollingDataTable('fieldsGrid',
		[
		    {key:'name', label: "Fields", width: 200, sortable: true},
		    {key:'type', label: "&nbsp;", width: 20, sortable: true, formatter:typeFormatter}
		],
		fieldDS,
	    {height: "200px", MSG_EMPTY: SUGAR.language.get('ModuleBuilder','LBL_NO_FIELDS')}
	);
	fieldsGrid.on("rowClickEvent", function(e){
		Dom.get("formulaInput").value += "$" + YAHOO.lang.trim(e.target.firstChild.innerText);
	});
	
	fieldDS.queryMatchContains = true;
	var fieldAC = new YAHOO.widget.AutoComplete("formulaFieldsSearch","fieldSearchResults", fieldDS);
	fieldAC.doBeforeLoadData = function( sQuery , oResponse , oPayload ) {
		fieldsGrid.initializeTable();
		fieldsGrid.addRows(oResponse.results);
		fieldsGrid.render();
    }
	var fieldsJSON =  [];
	for(var i in fieldsArray)
	{
		fieldsJSON[i] = {name: fieldsArray[i][0], type: fieldsArray[i][1]};
	}
	Dom.get("formulaFieldsSearch").onkeyup = function() {
		if (this.value == '') {
			fieldsGrid.initializeTable();
			fieldsGrid.addRows(fieldsJSON);
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
	fieldsGrid.render();
	SUGAR.expressions.fieldGrid = fieldsGrid;
	
	var functionsArray = SUGAR.expressions.getFunctionList(); 
	var funcDS = new YAHOO.util.LocalDataSource(functionsArray, 
	{
		responseType: YAHOO.util.LocalDataSource.TYPE_JSARRAY,
		responseSchema: 
		{
		   resultsList : "relationships",
		   fields : ['name', 'type']
	    }
	});
	var functionsGrid = new YAHOO.widget.ScrollingDataTable('functionsGrid',
		[
		    {key:'name', label: "Functions", width: 200, sortable: true},
		    {key:'type', label: "&nbsp;", width: 20, sortable: true, formatter:typeFormatter}
		],
		funcDS,
	    {height: "200px", MSG_EMPTY: SUGAR.language.get('ModuleBuilder','LBL_NO_FUNCS')}
	);
	
	functionsGrid.on("rowClickEvent", function(e){
		Dom.get("formulaInput").value +=  YAHOO.lang.trim(e.target.firstChild.innerText) + '(';
	});
	
	var funcTip = new YAHOO.widget.Tooltip("functionsTooltip", {
		context: "functionsGrid",
		text: "",
		showDelay: 300,
		zindex: 25
	});
	
	funcTip.table = functionsGrid;
	
	funcTip.contextMouseOverEvent.subscribe(function(context, e){
		var target = e[1].target;
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
		functionsGrid.initializeTable();
		functionsGrid.addRows(oResponse.results);
		functionsGrid.render();
    }
	var funcsJSON =  [];
	for(var i in functionsArray)
	{
		funcsJSON[i] = {name: functionsArray[i][0], type: functionsArray[i][1]};
	}
	Dom.get("formulaFuncSearch").onkeyup = function() {
		if (this.value == '') {
			Dom.addClass(this, "empty");
			functionsGrid.initializeTable();
			functionsGrid.addRows(funcsJSON);
			functionsGrid.render();
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
	functionsGrid.render();

	Dom.setStyle(Dom.get("formulaBuilder").parentNode, "padding", "0");
};

//SUGAR.expressions.FormulaPanel.show();
