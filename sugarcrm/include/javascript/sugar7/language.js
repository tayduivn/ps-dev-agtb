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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
(function(app) {
    app.events.on('app:init', function() {
        app.lang = _.extend(app.lang, {
            /**
             * Retrieves module singular form name.
             *
             * @param {String} module Module name.
             * @return {String} Module singular form.
             */
            getModuleSingular: function(module) {
                var modString = app.metadata.getStrings('mod_strings')[module],
                    moduleSingular = (modString ? modString['LBL_MODULE_NAME_SINGULAR'] : '') ||
                        app.lang.getAppListStrings('moduleListSingular')[module] ||
                        app.lang.getAppListStrings('moduleList')[module] ||
                        module;

                return moduleSingular;
            }
        });
    });
})(SUGAR.App);
