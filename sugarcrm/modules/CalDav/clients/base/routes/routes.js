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


(function (app) {
    app.events.on('router:init', function () {
        var routes = [
            {
                name: "UserCalDavConfigPage",
                route: "CalDav/config/user",
                callback: function () {
                    app.drawer.open({
                        layout: 'config-drawer',
                        context: {
                            module: 'CalDav',
                            section: 'user',
                            fromRouter: true
                        }
                    });
                }
            },
            {
                name: "GlobalCalDavConfigPage",
                route: "CalDav/config",
                callback: function () {
                    app.drawer.open({
                        layout: 'config-drawer',
                        context: {
                            module: 'CalDav',
                            section: null,
                            fromRouter: true
                        }
                    });
                }
            },
            {
                name: "RedirectToHomePage",
                route: "CalDav",
                callback: function () {
                    app.router.redirect('Home');
                }
            }
        ];

        app.router.addRoutes(routes);
    });
})(SUGAR.App);
