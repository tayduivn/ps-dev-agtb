/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
(function(app) {
    app.events.on('router:init', function() {
        var routes = [
            {
                name: 'sg_index',
                route: 'Styleguide',
                callback: function() {
                    app.controller.loadView({
                        module: 'Styleguide',
                        layout: 'styleguide',
                        page_name: 'home'
                    });
                }
            },
            {
                name: 'sg_module',
                route: 'Styleguide/:layout/:resource',
                callback: function(layout, resource) {
                    var page = '',
                        field = '';
                    switch (layout) {
                        case 'field':
                            //route: "Styleguide/field/text"
                            page = 'field';
                            field = resource;
                            break;
                        case 'view':
                            //route: "Styleguide/view/list"
                            page = 'layouts_' + resource;
                            break;
                        case 'docs':
                            //route: "Styleguide/docs/base_grid"
                            page = resource;
                            break;
                        case 'layout':
                            //route: "Styleguide/layout/records"
                            layout = resource;
                            page = 'module';
                            break;
                        default:
                            app.logger.warn('Invalid route: ' + route);
                            break;
                    }
                    app.controller.loadView({
                        module: 'Styleguide',
                        layout: layout,
                        page_name: page,
                        field_type: field,
                        skipFetch: true
                    });
                }
            }
        ];

        app.router.addRoutes(routes);
    });
})(SUGAR.App);
