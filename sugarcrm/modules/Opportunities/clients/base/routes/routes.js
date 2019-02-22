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
        var routes = [{
            name: 'pipeline-view',
            route: 'Opportunities/pipeline',
            callback: function(module) {
                app.controller.loadView({
                    module: 'Opportunities',
                    layout: 'pipeline-records'
                });
            }
        }];

        /*
         * Triggering the event on init will go over all those listeners
         * and add the routes to the router.
         */
        app.router.addRoutes(routes);
    });
})(SUGAR.App);
