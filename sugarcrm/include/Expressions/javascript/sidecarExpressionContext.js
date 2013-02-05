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
            def =   this.view.context.get("model").fields[varname],
            result;

        //Relate fields are only string on the client side, so return the variable name back.
        if(def.type == "link")
            value = varname;

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
        this.lockedFields = this.lockedFields || [];
        if (!this.lockedFields[varname])
        {
            this.lockedFields[varname] = true;
            var el = this.getElement(varname);
            if (el) {
                SUGAR.forms.FlashField($(el).parents('[data-fieldname="' + varname + '"]'), null, varname);
            }
            var ret = this.view.context.get("model").set(varname, value);
            this.lockedFields = [];
            return  ret;
        }
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
    addClass : function(varname, css_class, includeLabel){
        var def = this.view.getFieldMeta(varname),
            props = includeLabel ? ["css_class", "cell_css"] : ["css_class"],
            el = this.getElement(varname),
                        parent = $(el).closest('div.record-cell');

        _.each(props, function(prop) {
            if (!def[prop]) {
                def[prop] = css_class;
            } else if (def[prop].indexOf(css_class) == -1){
                def[prop] += " " + css_class;
            }
        });
        this.view.setFieldMeta(varname, def);

        $(el).addClass(css_class);
        if (includeLabel && parent) {
            parent.addClass(css_class);
        }

    },
    removeClass : function(varname, css_class, includeLabel) {
        var def = this.view.getFieldMeta(varname),
            props = includeLabel ? ["css_class", "cell_css"] : ["css_class"],
            el = this.getElement(varname),
            parent = $(el).closest('div.record-cell');

        _.each(props, function(prop) {
            if (def[prop] && def[prop].indexOf(css_class) != -1) {
                def[prop] = $.trim((" " + def[prop] + " ").replace(new RegExp(' ' + css_class + ' '), ""));
            }
        });
        this.view.setFieldMeta(varname, def);

        $(el).removeClass(css_class);
        if (includeLabel && parent) {
            parent.removeClass(css_class);
        }
    },
    getLink : function(variable) {
        var model = this.view.context.get("model");
        if (model && model.fields && model.fields[variable])
            return model.fields[variable];
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
        var model = this.view.context.get("model");
        for (var link in fields)
        {
            var value = _.extend(model.get(link) || {}, fields[link]);
            model.set(link, value);
        }
    },
    getRelatedFieldValues : function(fields, module, record)
    {
        var self = this,
            api = App.api;
        if (fields.length > 0){
            module = module || this.view.context.get("module");
            record = record || this.view.context.get("model").get("id");
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
            var data = {id: record, action:"related"},
                params = {module: module, fields: JSON.stringify(fields)};
                api.call("read", api.buildURL("ExpressionEngine", "related", data, params), data, params, {
                    success: function(resp){
                        self.setRelatedFields(resp);
                        return resp;
                }});
        }
        return null;
    },
    getRelatedField : function(link, ftype, field){
        var linkDef = _.extend({}, this.getLink(link)),
            linkValues = this.view.model.get(link) || {},
            currId;

        if (ftype == "related"){
            return this._handleRelateExpression(link, field);
        }
        //Run server side ajax Call
        else {
            if (typeof(linkValues[ftype]) == "undefined" || typeof(linkValues[ftype][field]) == "undefined")
            {
                var params = {link: link, type: ftype};
                if (field)
                    params.relate = field;
                this.getRelatedFieldValues([params]);
            } else {
                return linkValues[ftype][field];
            }
        }

        if (typeof(linkValues[ftype]) == "undefined")
            return "";

        return linkValues[ftype];

    },
    _handleRelateExpression : function(link, field){
        var relContext = this.view.context.getChildContext({link:link}),
            col = relContext.get("collection"),
            fields = relContext.get('fields') || [],
            self = this,
            //If we can't get related data, return blank.
            ret = "";

        if (field && !_.contains(fields, field)) {
            fields.push(field);
            relContext.prepare();
            col = relContext.get("collection");
            //Call set in case fields was not already on the context
            relContext.set('fields', fields);
            if (relContext._dataFetched){
                relContext.resetLoadFlag();
            }
            relContext.loadData({success:function(){
                // We will fire the link change event once the load is complete to re-fire the dependency with the correct data.
                self.view.model.trigger("change:" + link);
            }});
        }
        else if (relContext._dataFetched && col.page > 0) {
            if (col.length > 0) {
                ret =  col.models[0].get(field);
            }
        } else {
            // This link is currently being loaded (with the field we need). Collection's don't fire a sync/fetch event,
            // so we need to use doWhen to known when the load is complete.
            // We will fire the link change event once the load is complete to re-fire the dependency with the correct data.
            SUGAR.App.utils.doWhen(function(){return col.page > 0}, function(){
                self.view.model.trigger("change:" + link);
            });
        }
        return ret;
    },
    clearRelatedFieldCache : function(link, view){
        if (!view) view = AH.lastView;

        if(!AH.LINKS[view][link])
            return false;

        delete (AH.LINKS[view][link]["relId"]);
        delete (AH.LINKS[view][link]["related"]);

        return true;
    },
    fireOnLoad : function(dep) {
        this.view.model.once("change", SUGAR.forms.Trigger.fire, dep.trigger);
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

SUGAR.forms.Dependency.fromMeta = function(meta, context){
    var condition = meta.trigger || "true",
        triggerFields = meta.triggerFields || SEC.parser.getFieldsFromExpression(condition),
        actions = meta.actions || [],
        falseActions = meta.notActions || [],
        onLoad = meta.onload || false,
        actionObjects = [],
        falseActionObjects = [];

    //Without any trigger fields (or a condition with variables), we can't create a trigger
    if (_.isEmpty(triggerFields))
        return null;
    //No actions means no reason to create a dependency
    if (_.isEmpty(actions) && _.isEmpty(falseActions))
        return null;


    _.each(actions, function(actionDef)
    {
        if (!actionDef.action || !SUGAR.forms[actionDef.action + "Action"])
            return;
        actionObjects.push(new SUGAR.forms[actionDef.action + "Action"](actionDef.params));
    });
    _.each(falseActions, function(actionDef)
    {
        if (!actionDef.action || !SUGAR.forms[actionDef.action + "Action"])
            return;
        falseActionObjects.push(new SUGAR.forms[actionDef.action + "Action"](actionDef.params));
    });

    return new SUGAR.forms.Dependency(
        new SUGAR.forms.Trigger(triggerFields, condition, context),
        actionObjects, falseActionObjects, onLoad, context
    );
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

        if (actions instanceof SUGAR.forms.AbstractAction)
            actions = [actions];

	    for (var i in actions) {
            var action = actions[i];
            if (typeof action.exec == "function") {
                action.setContext(this.context);
                if (this.context.view && _.isEmpty(this.context.view.fields) && action.afterRender)
                {
                    this.context.view.once('render', function(){
                        this.exec();
                    }, action);
                } else {
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
        context = context || this.context;
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
    };

    //Register SugarLogic as a plugin to sidecar.
    if (SUGAR.App && SUGAR.App.plugins) {
        SUGAR.App.plugins.register('SugarLogic', 'view', {
            onAttach: function() {
                this.on("init", function(){
                    this.deps = [];
                    var slContext = new SUGAR.expressions.SidecarExpressionContext(this);
                    _.each(this.options.meta.dependencies, function(dep) {
                        var newDep = SUGAR.forms.Dependency.fromMeta(dep, slContext);
                        if (newDep)
                            this.deps.push(newDep);
                    }, this);
                });
           }
       });
    } else if (console.error) {
        console.error("unable to find the plugin manager");
    }


})();
