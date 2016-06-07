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
                name: "GlobalDeliveryConfigPage",
                route: "NotificationCenter/config/default",
                callback: function () {
                    app.drawer.open({
                        layout: 'config-drawer',
                        context: {
                            module: 'NotificationCenter',
                            section: 'default',
                            fromRouter: true
                        }
                    });
                }
            },
            {
                name: "UserDeliveryConfigPage",
                route: "NotificationCenter/config",
                callback: function () {
                    app.drawer.open({
                        layout: 'config-drawer',
                        context: {
                            module: 'NotificationCenter',
                            section: null,
                            fromRouter: true
                        }
                    });
                }
            },
            {
                name: "NotificationCenter",
                route: "NotificationCenter",
                callback: function () {
                    app.router.redirect('Home');
                }
            },
            {
                name: "NotificationCenterSubscriptions",
                route: "NotificationCenterSubscriptions",
                callback: function () {
                    app.router.redirect('Home');
                }
            }
        ];

        app.router.addRoutes(routes);
    });
})(SUGAR.App);
