// FILE SUGARCRM flav=ent ONLY
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
describe('ConsoleConfiguration.View.ConfigTabSettingsView', function() {
    var app;
    var view;
    var layout;
    var context;
    var ctxModel;
    var parentLayout;

    beforeEach(function() {
        app = SUGAR.App;

        context = app.context.getContext();
        ctxModel = app.data.createBean('ConsoleConfiguration');
        context.set('model', ctxModel);
        context.set('collection', app.data.createBeanCollection('ConsoleConfiguration'));

        SugarTest.loadComponent('base', 'layout', 'config-drawer');
        parentLayout = SugarTest.createLayout('base', null, 'base');
        layout = SugarTest.createLayout('base', 'ConsoleConfiguration', 'config-drawer', {},  context);
        layout.name = 'side-pane';
        layout.layout = parentLayout;

        view = SugarTest.createView('base', 'ConsoleConfiguration', 'config-tab-settings', {}, context, true, layout);
        sinon.collection.stub(view, '_super', function() {});
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
    });

    describe('bindDataChange', function() {
        beforeEach(function() {
            sinon.collection.stub(view, 'render', function() {});
            sinon.collection.stub(view.collection, 'on', function() {});
            view.bindDataChange();
        });

        it('should call view.context.on with change', function() {

            expect(view.collection.on).toHaveBeenCalledWith('add remove reset');
        });
    });

    describe('render', function() {
        beforeEach(function() {
            sinon.collection.stub(view.context, 'get', function() {});
            sinon.collection.stub(view, '$', function() {
                return {
                    tabs: sinon.collection.stub()
                };
            });
            sinon.collection.stub(view.context, 'trigger', function() {});
            view.render();
        });

        it('should call view._super with render', function() {

            expect(view._super).toHaveBeenCalledWith('render');
        });

        it('should call view.$ method with #tabs', function() {

            expect(view.$).toHaveBeenCalledWith('#tabs');
        });
    });
});
