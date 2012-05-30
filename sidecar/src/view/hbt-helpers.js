/**
 * Handlebars helpers.
 *
 * These functions are to be used in handlebars templates.
 * @class Handlebars.helpers
 * @singleton
 */
(function(app) {

    /**
     * Creates a field widget.
     * @method field
     * @param {View.View} view Parent view
     * @param {Data.Bean} model Reference to the model
     * @return {Object} HTML placeholder for the widget as handlebars safe string.
     */
    Handlebars.registerHelper('field', function(view, model) {
        // Handlebars passes a special hash object (template params) as the last argument
        // So, if model is not specified then the model parameter is actually this hash object
        // Hence, the following hack
        if (!(model instanceof Backbone.Model)) model = null;

        var field = app.view.createField({
            def: this,
            view: view,
            model: model
        });

        return field.getPlaceholder();
    });

    /**
     * Creates a field widget.
     *
     * This helper is used for fields that don't have view definition.
     *
     * @method fieldOfType
     * @param {String} type Field type
     * @param {String} label Label key
     * @return {Object} HTML placeholder for the widget as handlebars safe string.
     */
    Handlebars.registerHelper('fieldOfType', function(type, label) {
        var def = {
            type: type,
            name: type,
            label: label
        };

        var field = app.view.createField({
            def: def,
            view: this
        });

        return field.getPlaceholder();
    });

    /**
     * Creates a field widget for a given field name.
     * @method fieldWithName
     * @param {View.View} view Parent view
     * @param {String} name Field name
     * @param {Data.Bean} model Reference to the model
     * @param {String} viewName Name of the view template to use for the field
     * @return {String} HTML placeholder for the widget.
     */
    Handlebars.registerHelper('fieldWithName', function(view, name, model, viewName) {
        if (!(model instanceof Backbone.Model)) model = null;
        viewName = _.isString(viewName) ? viewName : null;
        var field = app.view.createField({
            def: { name: name, type: "base" },
            view: view,
            model: model,
            viewName: viewName || null // override fallback field template
        });

        return field.getPlaceholder();
    });

    /**
     * @method eachOptions
     * @param {Core.Context} context
     * @param {Function} block
     * @return {String}
     */
    Handlebars.registerHelper('eachOptions', function(context, block) {
        // Retrieve app list strings
        var options = app.lang.getAppListStrings(context),
            ret = "",
            iterator;

        if (_.isArray(options)) {
            iterator = function(element) {
                ret = ret + block(element);
            };
        } else if (_.isObject(options)) { // Is object evaluates arrays to true, so put it second
            iterator = function(value, key) {
                ret = ret + block({key: key, value: value});
            };
        }

        _.each(options, iterator, this);

        return ret;
    });

    /**
     * @method buildRoute
     * @param {Core.Context} context
     * @param {Data.Bean} model
     * @param {String} action
     * @param params
     * @return {String}
     */
    Handlebars.registerHelper('buildRoute', function(context, model, action, params) {
        model = model || context.get("model");

        var id = model.id,
            route;

        params = params || {};

        if (action == 'create') {
            id = '';
        }

        route = app.router.buildRoute(context.get("module"), id, action, params);
        return new Handlebars.SafeString(route);
    });

    /**
     * Extracts bean field value.
     * @method getFieldValue
     * @param {Data.Bean} bean Bean instance.
     * @param {String} field Field name.
     * @param {String} defaultValue(optional) Default value to return if field is not set on a bean.
     * @return {String} Field value of the given bean. If field is not set the default value or empty string.
     */
    Handlebars.registerHelper('getFieldValue', function(bean, field, defaultValue) {
        return bean.get(field) || defaultValue || "";
    });


    /**
     * @method contains
     * @param val
     * @param {Object/Array} array
     * @return {String} block Block inside the condition=
     */
    Handlebars.registerHelper('has', function(val, array, block) {
        if (!block) return "";
        
        // Since we need to check both just val = val 2 and also if val is in an array, we cast
        // non arrays into arrays
        if (!_.isArray(array) && !_.isObject(array)) {
            array = [array];
        }

        if (_.find(array, function(item) {
            return item === val;
        })) {
            return block(this);
        }

        return block.inverse(this);
    });

    /**
     * @method eq
     * @param val1
     * @param val2
     * @return {String} block Block inside the condition
     */
    Handlebars.registerHelper('eq', function(val1, val2, block) {
        if (!block) return "";

        if (val1 == val2) {
            return block(this);
        }

        return block.inverse(this);
    });

    /**
     * @method notEq // inverse of eq
     * @param val1
     * @param val2
     * @return {String} block Block inside the condition
     */
    Handlebars.registerHelper('notEq', function(val1, val2, block) {
        if (!block) return "";

        if (val1 != val2) {
            return block(this);
        }

        return block.inverse(this);
    });

    /**
     * @method log
     * @param value
     */
    Handlebars.registerHelper("log", function(value) {
        app.logger.debug("*****HBT: Current Context*****");
        app.logger.debug(this);
        app.logger.debug("*****HBT: Current Value*****");
        app.logger.debug(value);
        console.log(value);
        app.logger.debug("***********************");
    });

    /**
     * Retrieves a label string.
     * @method getLabel
     * @param {String} key Key of the label.
     * @param {String} module(optional) Module name.
     * @return {String} The string for the given label key.
     */
    Handlebars.registerHelper("getLabel", function(key, module){
       return app.lang.get(key, module);
    });

    /**
     * Retrieves a singular module name as string.
     * @method getSingularModuleName
     * @param {String} module Module name.
     * @param {Boolean} lowerCased(optional) If provided, will be lower cased.
     * @return {String} The singular name as string for the given module.
     */
    Handlebars.registerHelper("getSingularModuleName", function(module, lowerCased){
        var singularModules = SUGAR.App.lang.getAppListStrings("moduleListSingular"),
            singular = '';
        if(singularModules && singularModules[module]) {
            singular = (singularModules[module]) ? singularModules[module] : '';
            singular = lowerCased ? singular.toLowerCase() : singular;
        } else {
            app.logger.error("Could not get singular module name for: "+module);
        }
        return singular;
    });
    
})(SUGAR.App);
