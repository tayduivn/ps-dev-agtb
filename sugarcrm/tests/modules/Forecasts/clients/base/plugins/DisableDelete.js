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

describe('Forecasts.Base.Plugins.DisableDelete', function() {

    var app, field, moduleName = 'Opportunities', context, def, model;

    beforeEach(function() {
        app = SUGAR.App;
        context = app.context.getContext();

        SugarTest.loadFile('../modules/Forecasts/clients/base/plugins', 'DisableDelete', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        def = {
            type: 'rowaction',
            event: 'button:delete_button:click',
            name: 'delete_button',
            label: 'LBL_DELETE_BUTTON_LABEL',
            acl_action: 'delete'
        };

        model = new Backbone.Model({
            id: 'aaa',
            name: 'boo',
            module: moduleName

        });

        SugarTest.seedMetadata(true);
        app.metadata.getModule('Forecasts', 'config').is_setup = 1;
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');

    });

    afterEach(function() {
        delete app.plugins.plugins['field']['DisableDelete'];
        field = null;
        app = null;
        context = null;
        def = null;
        model = null;
    });

    //BEGIN SUGARCRM flav = ent ONLY
    describe('using sales_status: with closed items', function() {
        var sales_field = 'sales_status',
            label_key = '_STATUS';

        describe('when there are no closed RLIs', function() {

            beforeEach(function() {
                model.set('closed_revenue_line_items', 0);
            });

            describe('when status is Closed Won', function() {
                it('message should contain NOTICE_NO_DELETE_CLOSED', function() {
                    model.set(sales_field, 'Closed Won');
                    field = SugarTest.createField('base', 'delete_button', 'rowaction', 'detail', def, moduleName, model, context, true);
                    var message = field.removeDelete();
                    expect(message).toEqual('NOTICE_NO_DELETE_CLOSED' + label_key);
                });
            });

            describe('when status is not closed', function() {
                it('message should contain nothing', function() {
                    model.set(sales_field, 'In Progress');
                    field = SugarTest.createField('base', 'delete_button', 'rowaction', 'detail', def, moduleName, model, context, true);
                    var message = field.removeDelete();
                    expect(message).toEqual(null);
                });
            });
        });

        describe('when there are closed RLIs', function() {
            beforeEach(function() {
                model.set('closed_revenue_line_items', 1);
            });

            describe('when status is Closed Won', function() {
                it('message should contain NOTICE_NO_DELETE_CLOSED', function() {
                    model.set(sales_field, 'Closed Won');
                    field = SugarTest.createField('base', 'delete_button', 'rowaction', 'detail', def, moduleName, model, context, true);
                    var message = field.removeDelete();
                    expect(message).toEqual('NOTICE_NO_DELETE_CLOSED' + label_key);
                });
            });

            describe('when status is not closed', function() {
                it('message should contain NOTICE_NO_DELETE_CLOSED_RLIS', function() {
                    model.set(sales_field, 'In Progress');
                    field = SugarTest.createField('base', 'delete_button', 'rowaction', 'detail', def, moduleName, model, context, true);
                    var message = field.removeDelete();
                    expect(message).toEqual('NOTICE_NO_DELETE_CLOSED_RLIS');
                });
            });
        });

        describe('when sales_stage is used', function() {
            it('message should contain NOTICE_NO_DELETE_CLOSED', function() {
                model.set('closed_revenue_line_items', 0);
                model.set('sales_stage', 'Closed Won');
                field = SugarTest.createField('base', 'delete_button', 'rowaction', 'detail', def, moduleName, model, context, true);
                var message = field.removeDelete();
                expect(message).toEqual('NOTICE_NO_DELETE_CLOSED' + label_key);
            });
        });

        describe('when the button event is list:deleterow:fire', function() {
            it('message should contain NOTICE_NO_DELETE_CLOSED', function() {
                model.set('closed_revenue_line_items', 0);
                model.set('sales_stage', 'Closed Won');
                field = SugarTest.createField('base', 'delete_button', 'rowaction', 'detail', def, moduleName, model, context, true);
                field.def.event = 'list:deleterow:fire';
                var message = field.removeDelete();
                expect(message).toEqual('NOTICE_NO_DELETE_CLOSED' + label_key);
            });
        });
    });
    //END SUGARCRM flav = ent ONLY

    describe('using sales_stage: with closed items', function() {
        var sales_field = 'sales_stage',
            label_key = '_STAGE';

        beforeEach(function() {
            // testing when view_by is Opportunities
            app.metadata.getModule('Opportunities', 'config').opps_view_by = 'Opportunities';
        });

        afterEach(function() {
            app.metadata.getModule('Opportunities', 'config').opps_view_by = 'RevenueLineItems';
        });

        describe('when sales_stage is used', function() {
            it('message should contain NOTICE_NO_DELETE_CLOSED', function() {
                model.set('closed_revenue_line_items', 0);
                model.set(sales_field, 'Closed Won');
                field = SugarTest.createField('base', 'delete_button', 'rowaction', 'detail', def, moduleName, model, context, true);
                var message = field.removeDelete();
                expect(message).toEqual('NOTICE_NO_DELETE_CLOSED' + label_key);
            });
        });

        describe('when the button event is list:deleterow:fire', function() {
            it('message should contain NOTICE_NO_DELETE_CLOSED', function() {
                model.set('closed_revenue_line_items', 0);
                model.set(sales_field, 'Closed Won');
                field = SugarTest.createField('base', 'delete_button', 'rowaction', 'detail', def, moduleName, model, context, true);
                field.def.event = 'list:deleterow:fire';
                var message = field.removeDelete();
                expect(message).toEqual('NOTICE_NO_DELETE_CLOSED' + label_key);
            });
        });

    });
});
