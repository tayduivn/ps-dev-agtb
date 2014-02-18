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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
(function(app) {

    if (!$.fn.timeago) {
        return;
    }

    app.events.on('app:init', function() {

        /**
         * Plugin to keep timeago labels live.
         *
         * @deprecated since 7.2.0. Please use {@link Plugin.RelativeTime}
         * plugin.
         */
        app.plugins.register('Timeago', ['view'], {
            onAttach: function(component, plugin) {
                component.on('render', function() {
                    component.$('span.relativetime').timeago({
                        logger: SUGAR.App.logger,
                        date: SUGAR.App.date,
                        lang: SUGAR.App.lang,
                        template: SUGAR.App.template,
                        dateFormat: app.user.getPreference('datepref'),
                        timeFormat: app.user.getPreference('timepref')
                    });
                });
            }
        });
    });
})(SUGAR.App);
