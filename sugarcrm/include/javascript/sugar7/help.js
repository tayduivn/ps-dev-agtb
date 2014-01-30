/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

(function(app) {
    /**
     * Help Helper. Provides access for getting help for module/view specific actions
     *
     * @class Core.HelpHelper
     * @singleton
     * @alias SUGAR.App.help
     */
    app.augment("help", {

        /**
         * Keep a cache of the loaded modules labels
         * @param {Object|undefined}
         */
        _moduleLabelMap: undefined,

        /**
         * Return the help text for a module and view.
         *
         * @param {String} module Module that we are currently on
         * @param {String} view The view that help text is needed for
         * @param {Object} (context) The view that help text is needed for
         * @return {Object} Object containing title, body and more_help
         */
        get: function (module, view, context) {
            var objModule = _.extend({
                    'module_name' : app.lang.getModuleSingular(module),
                    'plural_module_name' : app.lang.getAppListStrings('moduleList')[module] || module
                }, context || {}, this._getModuleLabelMap()),
                viewName = this._cleanupViewName(view).toUpperCase();

                return {
                    'title' : this._get('LBL_HELP_' + viewName + '_TITLE', module, objModule),
                    'body' :  this._get('LBL_HELP_' + viewName, module, objModule),
                    'more_help': this._get('LBL_HELP_MORE_INFO', module, objModule)
                };
        },

        /**
         * Get the label text, if the text is equal to the label name, the it will return undefined
         *
         * @param {String} label The Label we want to load
         * @param {String} module The module to look in first
         * @param {Object} context The context that should be passed to the app.lang.get call
         * @returns {String|undefined}
         * @private
         */
        _get: function(label, module, context) {
            var text = app.lang.get(label, module, context);

            if (_.isEqual(label, text)) {
                return undefined;
            }

            return text;
        },

        /**
         * Standardize the view names
         *
         * @param {String} viewName
         * @returns {String}
         * @private
         */
        _cleanupViewName: function(viewName) {
            switch(viewName.toLowerCase()) {
                case 'list':
                    return 'records';
                case 'detail':
                    return 'record';
                case 'create-actions':
                    return 'create';
                default:
                    return viewName;
            }
        },

        /**
         * Compile a list of modules from the moduleList and moduleListSingular language strings
         *
         * This list is passed into the app.lang.get when app.help.get is called so you can reference other modules
         * in the help text
         *
         * @returns {Object}
         * @private
         */
        _getModuleLabelMap: function() {
            if (_.isUndefined(this._moduleLabelMap)) {
                var singularModules = {},
                    modules = {};
                _.each(app.lang.getAppListStrings('moduleListSingular'), function(module) {
                    singularModules[module.replace(/\s/g, "").toLowerCase() + "_module"] = module;
                }, this);
                _.each(app.lang.getAppListStrings('moduleList'), function(module) {
                    modules[module.replace(/\s/g, "").toLowerCase() + "_module"] = module;
                }, this);

                // combine them into one master object and save it on the object
                this._moduleLabelMap = _.extend(singularModules, modules);
            }

            return this._moduleLabelMap;
        },

        /**
         * Clear the _moduleLabelMap variable, this happens when app:sync:complete is fired.
         */
        clearModuleLabelMap: function() {
            this._moduleLabelMap = undefined;
        }
    });

    app.events.on("app:sync:complete", function() {
        app.help.clearModuleLabelMap();
    });

})(SUGAR.App);
