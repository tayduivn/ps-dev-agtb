//FILE SUGARCRM flav=een ONLY
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
	showFunctionDescription: function(target, func) {
		var ggt = SUGAR.expressions.GridToolTip;
		if (ggt.currentHelpFunc == func)
			return;
		ggt.currentHelpFunc = func;
		var cache = ggt.tipCache;
		var t = Dom.get(target);
		if (typeof cache[func] != 'undefined') {
			t.innerHTML = cache[func];
		} else {
			t.innerHTML = "loading...";
			cache[func] = "loading...";
			ggt.descTarget = t;
			Connect.asyncRequest(
			    Connect.method, 
			    Connect.url + '&' + ModuleBuilder.paramsToUrl({
			    	"function": func, 
			    	action: "functionDetail", 
			    	module: "ExpressionEngine"
			    }), 
			    {success: ggt.showAjaxResponse, failure: ModuleBuilder.failed}
			);
		}
	},
	showAjaxResponse: function (o) {
		var ggt = SUGAR.expressions.GridToolTip;
		var r = YAHOO.lang.JSON.parse(o.responseText);
		ggt.tipCache[r.func] = r.desc;
		if (r.func == ggt.currentHelpFunc)
			ggt.descTarget.innerHTML = r.desc;
	}
};

var grid = new YAHOO.widget.ScrollingDataTable('fieldsGrid',
		[
		    {key:'name', label: "Fields", width: 200, sortable: true}
		],
		new YAHOO.util.LocalDataSource(fieldsArray, {
			responseType: YAHOO.util.LocalDataSource.TYPE_JSARRAY,
			responseSchema: {
			   resultsList : "relationships",
			   fields : ['name']
		    }
		}),
	    {height: "200px", MSG_EMPTY: SUGAR.language.get('ModuleBuilder','LBL_NO_RELS')}
	);
	grid.render();
	
	var functionsArray = SUGAR.expressions.getFunctionList(); 
	grid = new YAHOO.widget.ScrollingDataTable('functionsGrid',
			[
			    {key:'name', label: "Functions", width: 200, sortable: true}
			],
			new YAHOO.util.LocalDataSource(functionsArray, {
				responseType: YAHOO.util.LocalDataSource.TYPE_JSARRAY,
				responseSchema: {
				   resultsList : "relationships",
				   fields : ['name']
			    }
			}),
		    {height: "200px", MSG_EMPTY: SUGAR.language.get('ModuleBuilder','LBL_NO_RELS')}
		);
		grid.subscribe("rowMouseoverEvent", function(e){
			var ggt = SUGAR.expressions.GridToolTip;
			if (ggt.timer)
				ggt.timer.cancel();
			ggt.timer = YAHOO.lang.later(250, this, function(e){
				ggt.showFunctionDescription("functionDesc", e.target.textContent);
			}, e);
			
		});
		grid.render();

	Dom.setStyle(Dom.get("formulaBuilder").parentNode, "padding", "0");
};

//SUGAR.expressions.FormulaPanel.show();
