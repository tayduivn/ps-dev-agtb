/**
 * Handlebars helpers.
 *
 * These functions are to be used in handlebars templates.
 * @class Handlebars.helpers
 * @singleton
 */
(function(app) {

    var _sfid = 0;

    /**
     * Creates field widget.
     * @method field
     * @param {Core.Context} context
     * @param {View.View} view
     * @param {Data.Bean} bean
     * @return {String} HTML placeholder for the widget.
     */
    Handlebars.registerHelper('field', function(context, view, bean) {
        var placeholder = '<span sfuuid="' + (++_sfid) + '"></span>';
        var sf = (view.sugarFields[_sfid] = app.view.createField({
            def: this,
            view: view,
            context: context,
            model: bean || context.get("model")
        }));

        sf.sfid = _sfid;

        return new Handlebars.SafeString(placeholder);
    });

    /**
     * Adds a button
     */
    Handlebars.registerHelper('button', function(type, label) {
        var placeholder = '<span sfuuid="' + (++_sfid) + '"></span>';
        var def = { type: type, name: type, label: label };
        var view = this;

        var sf = (view.sugarFields[_sfid] = app.view.createField({
            def: def,
            view: this,
            context: this.context,
            model: this.context.get("model")
        }));

        sf.sfid = _sfid;

        return new Handlebars.SafeString(placeholder);
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
            }
        } else if (_.isObject(options)) { // Is object evaluates arrays to true, so put it second
            iterator = function(value, key) {
                ret = ret + block({key: key, value: value});
            }
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
     * @param {Boolean} retTrue
     * @param {Boolean} retFalse
     * @return {String}
     */
    Handlebars.registerHelper('eq', function(val1, val2, retTrue, retFalse) {
        if (val1 == val2) {
            return retTrue;
        }

        return (retFalse != undefined) ? retFalse : "";
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
     * @method getLabel
     * @param {String} string
     * @param {String}
     */
    Handlebars.registerHelper("getLabel", function(string, module){
       var result = app.lang.get(string, module);
       return result;
    });

})(SUGAR.App);