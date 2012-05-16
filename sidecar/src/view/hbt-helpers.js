/**
 * Handlebars helpers.
 *
 * These functions are to be used in handlebars templates.
 * @class Handlebars.helpers
 * @singleton
 */
(function(app) {

    var _getFieldPlaceholder = function(field) {
        return new Handlebars.SafeString('<span sfuuid="' + field.sfId + '"></span>');
    };

    /**
     * Creates a field widget.
     * @method field
     * @param {Core.Context} context
     * @param {View.View} view
     * @param {Data.Bean} bean
     * @return {Object} HTML placeholder for the widget as handlebars safe string.
     */
    Handlebars.registerHelper('field', function(context, view, bean) {
        var field = app.view.createField({
            def: this,
            view: view,
            context: context,
            model: bean
        });

        return _getFieldPlaceholder(field);
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
            view: this,
            context: this.context
        });

        return _getFieldPlaceholder(field);
    });

    /**
     * Creates a field widget for a given field name.
     * @method fieldWithName
     * @param {Core.Context} context Current app context
     * @param {View.View} view Parent view
     * @param {Data.Bean} bean
     * @param {String} name Field name
     * @param {String} viewName Specify it to call a template from another view
     * @return {String} HTML placeholder for the widget.
     */
    Handlebars.registerHelper('fieldWithName', function(context, view, bean, name, viewName) {
        var field = app.view.createField({
            def: { name: name, type: "base" },
            view: view,
            context: context,
            model: bean || context.get("model"),
            viewName: viewName || null // override view name (template for "default" view will be used instead of view.name)
        });

        return new Handlebars.SafeString('<span sfuuid="' + field.sfId + '"></span>');
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
     * @param {Boolean} retTrue
     * @param {Boolean} retFalse
     * @return {Boolean}
     */
    Handlebars.registerHelper('has', function(val, array, retTrue, retFalse) {
        // Since we need to check both just val = val 2 and also if val is in an array, we cast
        // non arrays into arrays
        if (!_.isArray(array) && !_.isObject(array)) {
            array = [array];
        }

        if (_.find(array, function(item) {
            return item === val;
        })) {
            return retTrue;
        }

        return (!_.isUndefined(retFalse)) ? retFalse : "";
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

})(SUGAR.App);