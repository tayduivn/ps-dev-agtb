/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


(function() {

SUGAR.forms = SUGAR.forms || {};
SUGAR.forms.animation = SUGAR.forms.animation || {};

/**
 * An expression context is used to retrieve variables when evaluating expressions.
 * the default class only returns the empty string.
 */
var SE = SUGAR.expressions,
    SEC = SE.SidecarExpressionContext = function(view){
    this.view = view;

}
SUGAR.util.extend(SEC, SE.ExpressionContext, {
    getValue : function(varname)
    {
        var value = this.view.context.get("model").get(varname),
            result;
        //Relate fields are only string on the client side, so return the variable name back.
        /*if(AH.LINKS[this.formName][varname])
            value = varname;
        else
            value = AH.getValue(varname, this.formName); */

        if (typeof(value) == "string")
        {
            value = value.replace(/\n/g, "");
            if ((/^(\s*)$/).exec(value) != null || value === "")
            {
                result = SEC.parser.toConstant('""');
            }
            // test if value is a number or boolean
            else if ( SE.isNumeric(value) ) {
                result = SEC.parser.toConstant(SE.unFormatNumber(value));
            }
            // assume string
            else {
                result =  SEC.parser.toConstant('"' + value + '"');
            }
        } else if (typeof(value) == "object" && value != null && value.getTime) {
            //This is probably a date object that we must convert to an expression
            var d = new SE.DateExpression("");
            d.evaluate = function(){return this.value};
            d.value = value;
            result =  d;
        } else {
            result = SEC.parser.toConstant('""');
        }

        return result;

    },
    setValue : function(varname, value)
    {
        var el = this.getElement(varname);
        if (el) {
            SUGAR.forms.FlashField(el, null, varname);
        }
        return  this.view.context.get("model").set(varname, value);
    },
    addListener : function(varname, callback, scope)
    {
        var model = this.view.context.get("model");
        model.off("change:" + varname, callback, scope);
        model.on("change:" + varname, callback, scope);
    },
    getElement : function(varname) {
        var field = this.view.getField(varname);
        if (field && field.el)
            return field.el;
    },
    getLink : function(variable, view) {
        if (!view) view = AH.lastView;

        if(AH.LINKS[view][variable])
            return AH.LINKS[view][variable];
    },
    cacheRelatedField : function(link, ftype, value, view)
    {
        if (!view) view = AH.lastView;

        if(!AH.LINKS[view][link])
            return false;

        //If there is already a value cached for this link, we need to merge in the new field values
        if (typeof(AH.LINKS[view][link][ftype]) == "object" && typeof(value == "object"))
        {
            for(var i in value)
            {
                AH.LINKS[view][link][ftype][i] = value[i];
            }
        }
        else
            AH.LINKS[view][link][ftype] = value;

        return true;
    },
    getCachedRelatedField : function(link, ftype, view)
    {
        if (!view) view = AH.lastView;

        if(!AH.LINKS[view][link] || AH.LINKS[view][link][ftype])
            return null;

        return AH.LINKS[view][link][ftype];
    },
    showError : function(variable, error)
    {
    	// retrieve the variable
    	var field = AH.getElement(variable);

    	if ( field == null )
    		return null;

    	add_error_style(field.form.name, field, error, false);
    },
    clearError : function(variable)
    {
    	// retrieve the variable
    	var field = AH.getElement(variable);
    	if ( field == null )
    		return;

    	for(var i in inputsWithErrors)
    	{
    		if (inputsWithErrors[i] == field)
    		{
    			if ( field.parentNode.className.indexOf('x-form-field-wrap') != -1 )
                {
    				field.parentNode.parentNode.removeChild(field.parentNode.parentNode.lastChild);
                }
                else
                {
                	field.parentNode.removeChild(field.parentNode.lastChild);
                }
    			delete inputsWithErrors[i];
    			return;
    		}
    	}
    },
    setStyle : function(variable, styles)
    {
    	// retrieve the variable
    	var field = AH.getElement(variable);
    	if ( field == null )	return null;

    	// set the styles
    	for ( var property in styles ) {
    		YAHOO.util.Dom.setStyle(field, property + "", styles[property]);
    	}
    },
    setRelatedFields : function(fields){
        for (var link in fields)
        {
            for (var type in fields[link])
            {
                AH.cacheRelatedField(link, type, fields[link][type]);
            }
        }
    },
    getRelatedFieldValues : function(fields, module, record)
    {
        if (fields.length > 0){
            module = module || SUGAR.forms.AssignmentHandler.getValue("module") || DCMenu.module;
            record = record || SUGAR.forms.AssignmentHandler.getValue("record") || DCMenu.record;
            for (var i = 0; i < fields.length; i++)
            {
                //Related fields require a current related id
                if (fields[i].type == "related")
                {
                    var linkDef = SUGAR.forms.AssignmentHandler.getLink(fields[i].link);
                    if (linkDef && linkDef.id_name && linkDef.module) {
                        var idField = document.getElementById(linkDef.id_name);
                        if (idField && idField.tagName == "INPUT")
                        {
                            fields[i].relId = SUGAR.forms.AssignmentHandler.getValue(linkDef.id_name, false, true);
                            fields[i].relModule = linkDef.module;
                        }
                    }
                }
            }
            var r = http_fetch_sync("index.php", SUGAR.util.paramsToUrl({
                module:"ExpressionEngine",
                action:"getRelatedValues",
                record_id: record,
                tmodule: module,
                fields: YAHOO.lang.JSON.stringify(fields),
                to_pdf: 1
            }));
            try {
                var ret = YAHOO.lang.JSON.parse(r.responseText);
                AH.setRelatedFields(ret);
                return ret;
            } catch(e){}
        }
        return null;
    },
    getRelatedField : function(link, ftype, field, view){
        if (!view)
            view = AH.lastView;
        else
            AH.lastView = view;


        if(!AH.LINKS[view][link])
            return null;

        var linkDef = SUGAR.forms.AssignmentHandler.getLink(link);
        var currId;
        if (linkDef.id_name)
         {
             currId = SUGAR.forms.AssignmentHandler.getValue(linkDef.id_name, false, true);
         }

        if (typeof(linkDef[ftype]) == "undefined"
            || (field && typeof(linkDef[ftype][field]) == "undefined")
            || (ftype == "related" && linkDef.relId != currId)
        ){
            var params = {link: link, type: ftype};
            if (field)
                params.relate = field;
            AH.getRelatedFieldValues([params]);
            //Reload the link now that getRelatedFieldValues has been called.
            linkDef = SUGAR.forms.AssignmentHandler.getLink(link);
        }

        if (typeof(linkDef[ftype]) == "undefined")
            return null;
        //Everything but count requires specifying a related field to use, so make sure to check that field retrieved correctly
        if (field) {
            //If we didn't load the field we wanted, return null
            if (typeof(linkDef[ftype][field]) == "undefined")
                return null;
            else
                return linkDef[ftype][field];
        }

        return linkDef[ftype];

    },
    clearRelatedFieldCache : function(link, view){
        if (!view) view = AH.lastView;

        if(!AH.LINKS[view][link])
            return false;

        delete (AH.LINKS[view][link]["relId"]);
        delete (AH.LINKS[view][link]["related"]);

        return true;
    },
    reset : function() {

    }
});

/**
 * @STATIC
 * The Default expression parser.
 */
SEC.parser = new SUGAR.expressions.ExpressionParser();

/**
 * @STATIC
 * Parses expressions given a variable map.<br>
 */
SEC.evalVariableExpression = function(expression, view)
{
	return SEC.parser.evaluate(expression, new SEC(view));
}

/**
 * A dependency is an object representation of a variable being dependent
 * on other variables. For example A being the sum of B and C where A is
 * 'dependent' on B and C.
 */
SUGAR.forms.Dependency = function(trigger, actions, falseActions, testOnLoad, context)
{
	this.actions = actions;
	this.falseActions = falseActions;
	this.context = context;
    trigger.setContext(this.context);
    trigger.setDependency(this);
	this.trigger = trigger;
	if (testOnLoad) {
	    context.fireOnLoad(this);
	}

}


/**
 * Triggers this dependency to be re-evaluated again.
 */
SUGAR.forms.Dependency.prototype.fire = function(undo)
{
	try {
		var actions = this.actions;
		if (undo && this.falseActions != null)
			actions = this.falseActions;

		if (actions instanceof SUGAR.forms.AbstractAction) {
			actions.setContext(this.context);
			actions.exec();
		} else {
			for (var i in actions) {
				var action = actions[i];
				if (typeof action.exec == "function") {
					action.setContext(this.context);
					action.exec();
				}
			}
		}
	} catch (e) {
		if (!SUGAR.isIE && console && console.log){
			console.log('ERROR: ' + e);
		}
		return;
	}
};

SUGAR.forms.Dependency.prototype.getRelatedFields = function () {
    var parser = SEC.parser,
        fields = parser.getRelatedFieldsFromFormula(this.trigger.condition);
    //parse will search a list of actions for formulas with relate fields
    var parse = function (actions) {
        if (actions instanceof SUGAR.forms.AbstractAction) {
            actions = [actions];
        }
        for (var i in actions) {
            var action = actions[i];
            //Iterate over all the properties of the action to see if they are formulas with relate fields
            if (typeof action.exec == "function") {
                for (var p in action) {
                    if (typeof action[p] == "string")
                        fields = $.merge(fields, parser.getRelatedFieldsFromFormula(action[p]));
                }
            }
        }
    }
    parse(this.actions);
    parse(this.falseActions);
    return fields;
}


    SUGAR.forms.AbstractAction = function (target) {
        this.target = target;
    };

    SUGAR.forms.AbstractAction.prototype.exec = function () {

    }

    SUGAR.forms.AbstractAction.prototype.setContext = function (context) {
        this.context = context;
    }

    SUGAR.forms.AbstractAction.prototype.evalExpression = function (exp, context) {
        return SEC.parser.evaluate(exp, context).evaluate();
    }

    /**
     * This object resembles a trigger where a change in any of the specified
     * variables triggers the dependencies to be re-evaluated again.
     */
    SUGAR.forms.Trigger = function (variables, condition, context) {
        this.variables = variables;
        this.condition = condition;
        this.context = context;
        this.dependency = { };
        this._attachListeners();
    }

    /**
     * Attaches a 'change' listener to all the fields that cause
     * the condition to be re-evaluated again.
     */
    SUGAR.forms.Trigger.prototype._attachListeners = function () {
        if (!(this.variables instanceof Array)) {
            this.variables = [this.variables];
        }

        for (var i = 0; i < this.variables.length; i++) {
            this.context.addListener(this.variables[i], SUGAR.forms.Trigger.fire, this, true);
        }
    }

    /**
     * Attaches a 'change' listener to all the fields that cause
     * the condition to be re-evaluated again.
     */
    SUGAR.forms.Trigger.prototype.setDependency = function (dep) {
        this.dependency = dep;
    }

    SUGAR.forms.Trigger.prototype.setContext = function (context) {
        this.context = context;
    }

    /**
     * @STATIC
     * This is the function that is called when a 'change' event
     * is triggered. If the condition is true, then it triggers
     * all the dependencies.
     */
    SUGAR.forms.Trigger.fire = function () {
        // eval the condition
        var eval, val;
        try {
            eval = SEC.parser.evaluate(this.condition, this.context);
        } catch (e) {
            if (!SUGAR.isIE && console && console.log) {
                console.log('ERROR:' + e + "; in Condition: " + this.condition);
            }
        }

        // evaluate the result
        if (typeof(eval) != 'undefined')
            val = eval.evaluate();

        // if the condition is met
        if (val == SUGAR.expressions.Expression.TRUE) {
            // single dependency
            if (this.dependency instanceof SUGAR.forms.Dependency) {
                this.dependency.fire(false);
                return;
            }
        } else if (val == SUGAR.expressions.Expression.FALSE) {
            // single dependency
            if (this.dependency instanceof SUGAR.forms.Dependency) {
                this.dependency.fire(true);
                return;
            }
        }
    }

    SUGAR.forms.flashInProgress = {};
    /**
     * @STATIC
     * Animates a field when by changing it's background color to
     * a shade of light red and back.
     */
    SUGAR.forms.FlashField = function (field, to_color, key) {
        if (typeof(field) == 'undefined' || (!key && !field.id))
            return;
        key = key || field.id;
        if (SUGAR.forms.flashInProgress[key])
            return;

        SUGAR.forms.flashInProgress[key] = true;

        to_color = to_color || '#FF8F8F';
        // store the original background color but default to white
        var original = field.style && field.style.backgroundColor ? field.style.backgroundColor : '#FFFFFF' ;


        $(field).animate({
            backgroundColor : to_color
        }, 200, function(){
            $(field).animate({
                backgroundColor : original
            }, 200, function(){
                delete SUGAR.forms.flashInProgress[key];
            });
        });
    }
})();
