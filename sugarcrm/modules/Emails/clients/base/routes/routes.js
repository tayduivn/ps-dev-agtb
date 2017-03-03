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
        var module = 'Emails';

        var routes = [
            {
                name: 'emails_drafts',
                route: module + '/drafts/:id',
                callback: function(id) {
                    var model = app.data.createBean(module, {id: id});

                    model.fetch({
                        view: 'create',
                        success: onSuccess
                    });

                    function onSuccess(model) {
                        var context;
                        var options;
                        var prevLayout = app.controller.context.get('layout');

                        if (model.get('state') !== 'Draft') {
                            // Handle routing for email that used to be a draft
                            app.router.record(module, id);
                        } else if (prevLayout && prevLayout !== 'login') {
                            // Routing from a page in the app - open drawer
                            context = {
                                create: true,
                                module: 'Emails',
                                model: model,
                                fromRouter: true
                            };

                            app.drawer.open({
                                layout: 'create',
                                context: context
                            }, function(context, model) {
                                if (model && model.module === app.controller.context.get('module')) {
                                    app.controller.context.reloadData();
                                }
                            });
                        } else {
                            // Routing from login or outside the app - load view
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
                }
            }
        ];

        app.router.addRoutes(routes);
    });
})(SUGAR.App);
