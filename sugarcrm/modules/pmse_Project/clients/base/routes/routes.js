/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
(function(app) {
    app.events.on('router:init', function(router) {
        /*
         * Add the pmse_Inbox module's routes to Sugar's router
         */
        var module = 'pmse_Project';
        var routes = [
            {
                name: 'record_layout',
                route: module + '/:id/layout/:view',
                callback: function(id, view) {
                    if (!app.router._moduleExists(module)) {
                        return;
                    }
                    app.router.record(module, id, null, view);
                }
            }
        ];

        app.router.addRoutes(routes);
    });
})(SUGAR.App);
