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
     * @param {String} context options key
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
     * Builds a route.
     * @method buildRoute
     * @param {Core.Context} context
     * @param {Data.Bean} model
     * @param {String} action
     * @param params
     * @return {String}
     */
    Handlebars.registerHelper('buildRoute', function(context, model, action, params) {
        model = model || context.get("model");

        var id = model.id;

        params = params || {};

        if (action == 'create') {
            id = '';
        }

        return new Handlebars.SafeString(app.router.buildRoute(context.get("module"), id, action, params));
    });

    /**
     * Builds a model route.
     * @method modelRoute
     * @param {Data.Bean} model
     * @param {String} action(optional)
     * @return {String}
     */
    Handlebars.registerHelper('modelRoute', function(model, action) {
        action = _.isString(action) ? action : null;
        var id = action == "create" ? "" : model.id;
        return new Handlebars.SafeString(app.router.buildRoute(model.module, id, action));
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
     * Executes a given block if a given array has a value.
     * @method has
     * @param {String/Object} val value
     * @param {Object/Array} array or hash object
     * @return {String} Result of the `block` execution if the `array` contains `val` or the result of the inverse block.
     */
    Handlebars.registerHelper('has', function(val, array, block) {
        if (!block) return "";

        // Since we need to check both just val = val 2 and also if val is in an array, we cast
        // non arrays into arrays
        if (!_.isArray(array) && !_.isObject(array)) {
            array = [array];
        }

        return _.include(array, val) ? block(this) : block.inverse(this);
    });

    /**
     * We require sortable to be the default if not defined in either field viewdef or vardefs. Otherwise, 
     * we use whatever is provided in either field vardefs or field's viewdefs where the view def has more
     * specificity.
     * @method has
     * @param {String} module name
     * @param {Object} the field view defintion (e.g. looping through meta.panels.field it will be 'this')
     * @return {String} Result of the `block` execution if sortable, otherwise empty string. 
     */
    Handlebars.registerHelper('isSortable', function(module, fieldViewdef, block) {
        if (!block) return "";
        
        var fieldVardef = app.metadata.getModule(module).fields[fieldViewdef.name];

        if(!_.isUndefined(fieldViewdef.sortable) ? fieldViewdef.sortable : (!_.isUndefined(fieldVardef.sortable) ? fieldVardef.sortable : true)) {
            return block(this);
        } else {
            return '';
        }
    });

    /**
     * Executes a given block if a given values are equal.
     * @method eq
     * @param {String} val1 first value to compare
     * @param {String} val2 second value to compare.
     * @return {String} Result of the `block` execution if the given values are equal or the result of the inverse block.
     */
    Handlebars.registerHelper('eq', function(val1, val2, block) {
        if (!block) return "";
        return val1 == val2 ? block(this) : block.inverse(this);
    });

    /**
     * Opposite of `eq` helper.
     * @method notEq
     * @param {String} val1 first value to compare
     * @param {String} val2 second value to compare.
     * @return {String} Result of the `block` execution if the given values are not equal or the result of the inverse block.
     */
    Handlebars.registerHelper('notEq', function(val1, val2, block) {
        if (!block) return "";
        return val1 != val2 ? block(this) : block.inverse(this);
    });

    /**
     * Same as eq helper but second value is a {String} regex expression. Unfortunately, we have to do this because the
     * Handlebar's parser gets confused by regex literals like /foo/
     * @method match
     * @param {String} val1 first value to compare
     * @param {String} val2 A String representing a RegExp constructor argument. So if RegExp('foo.*') is the desired regex,
     * val2 would contain "foo.*". No support for modifiers.
     * @return {String} Result of the `block` execution if the given values are equal or the result of the inverse block.
     */
    Handlebars.registerHelper('match', function(val1, val2, block) {
        var re;
        if (!block) return "";
        re = new RegExp(val2);
        if (re.test(val1)) {
            return block(this);
        } else {
            return block.inverse(this);
        }
    });

    /**
     * Same as notEq helper but second value is a {String} regex expression.
     * @method notMatch
     * @param {String} val1 first value to compare
     * @param {String} val2 A String representing a RegExp constructor argument. So if RegExp('foo.*') is the desired regex,
     * val2 would contain "foo.*". No support for modifiers.
     * @return {String} Result of the `block` execution if the given values are not equal or the result of the inverse block.
     */
    Handlebars.registerHelper('notMatch', function(val1, val2, block) {
        var re;
        if (!block) return "";
        re = new RegExp(val2);
        if (!re.test(val1)) {
            return block(this);
        } else {
            return block.inverse(this);
        }
    });

    /**
     * Logs a value.
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

    // Deprecated
    // TODO: Remove this helper once everybody migrates to "str"
    Handlebars.registerHelper("getLabel", function(key, module) {
        return Handlebars.helpers.str.apply(this, arguments);
    });

    /**
     * Retrives a string by key.
     *
     * The helper queries {@link Core.LanguageHelper} module to retrieve an i18n-ed string.
     * @method str
     * @param {String} key Key of the label.
     * @param {String} module(optional) Module name.
     * @return {String} The string for the given label key.
     */
    Handlebars.registerHelper("str", function(key, module) {
        module = _.isString(module) ? module : null;
        return app.lang.get(key, module);
    });

    /**
     * Wrap the date into a time element
     * This helper allows to implement a plugin that will parse each time element and
     * convert the date into a relative time with a timer.
     *
     * @method timeago
     * @param {String} dateString like `YYYY-MM-DD hh:mm:ss`.
     * @return {String} the relative time like `10 minutes ago`.
     */
    Handlebars.registerHelper("timeago", function(dateString) {
        // TODO: Replace `span` with a `time` element. It was removed because impossible to do innerHTML on a `time` element in IE8
        var wrapper = "<span class=\"relativetime\" title=\"" + dateString + "\">" +
            dateString +
            "</span>";

        return new Handlebars.SafeString(wrapper);
    });

})(SUGAR.App);
