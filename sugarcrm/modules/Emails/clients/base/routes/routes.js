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
    app.events.on('router:init', function() {
        var module = 'Emails',
            routes,
            openEmailDrawer,
            useSugarEmailClient;

        openEmailDrawer = function(model) {
            var context = {
                create: true,
                module: 'Emails',
                fromRouter: true
            };

            if (model) {
                context.model = model;
            }

            app.drawer.open({
                layout: 'create',
                context: context
            }, function(context, model) {
                if (model && model.module === app.controller.context.get('module')) {
                    app.controller.context.reloadData();
                }
            });
        };

        useSugarEmailClient = function() {
            var emailClientPreference = app.user.getPreference('email_client_preference');

            return (emailClientPreference &&
            emailClientPreference.type === 'sugar' &&
            app.acl.hasAccess('edit', 'Emails'));
        };

        routes = [
            {
                name: 'emails_create',
                route: module + '/create',
                callback: function() {
                    var prevLayout = app.controller.context.get('layout');

                    if (prevLayout && prevLayout !== 'login') {
                        openEmailDrawer();
                    } else {
                        app.router.create(module);
                    }
                }
            },
            {
                name: 'emails_record',
                route: module + '/:id',
                callback: function(id) {
                    var options,
                        model = app.data.createBean(module, {id: id});

                    model.fetch({
                        view: 'create',
                        success: function(model) {
                            var prevLayout = app.controller.context.get('layout');

                            if (model.get('state') !== 'Draft' || !useSugarEmailClient()) {
                                app.router.record(module, id);
                            } else if (prevLayout && prevLayout !== 'login') {
                                openEmailDrawer(model);
                            } else {
                                options = {
                                    module: module,
                                    layout: 'create',
                                    action: 'edit',
                                    model: model,
                                    create: true
                                };
                                app.controller.loadView(options);
                            }
                        }
                    });
                }
            }
        ];

        app.router.addRoutes(routes);
    });
})(SUGAR.App);
