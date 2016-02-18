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
describe('NotificationCenter.View.ConfigHeaderButtons', function() {
    var app, layout, view, context, sandbox, module = 'NotificationCenter';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.declareData('base', module, true, false);
        layout = SugarTest.createLayout('base', module, 'config-drawer', null, null, true);
        context = app.context.getContext({model: layout.model});
        SugarTest.loadComponent('base', 'view', 'config-header-buttons');
        view = SugarTest.createView('base', module, 'config-header-buttons', {}, context, true, layout);
        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        view.dispose();
        view = null;
        sandbox.restore();
    });

    describe('render()', function() {
        var button, hide;
        beforeEach(function() {
            button = SugarTest.createField({
                client: 'base',
                name: 'reset_all_button',
                type: 'button',
                viewName: 'detail'
            });
            sandbox.stub(view, '_super');
            hide = sandbox.spy(button, 'hide');
            view.fields['reset_all_button'] = button;
        });

        afterEach(function() {
            button = null;
            hide = null;
        });

        it('should not hide reset_all_button for user-mode', function() {
            view.model.set('configMode', 'user');
            view.render();
            expect(hide).not.toHaveBeenCalled();
        });

        it('should hide reset_all_button for admin-mode', function() {
            view.model.set('configMode', 'global');
            view.render();
            expect(hide).toHaveBeenCalled();
        });
    });

    describe('resetConfig()', function() {
        it('should show confirmation alert popup', function() {
            var alertOpen = sandbox.stub(app.alert, 'show');
            view.resetConfig();
            expect(alertOpen).toHaveBeenCalled();
        });

        it('should reset all model\'s attributes to default', function() {
            sandbox.stub(app.alert, 'show', function(msg, param) {
                param.onConfirm();
            });
            var reset = sandbox.spy(view.model, 'resetToDefault');
            view.resetConfig();
            expect(reset).toHaveBeenCalledWith('all');
        });
    });

    describe('_saveConfig()', function() {
        var server, urlRegExp, updateAddresses;

        beforeEach(function() {
            urlRegExp = new RegExp('.*rest/v10/' + module + '/config.*');
            server = sandbox.useFakeServer();
            server.respondWith("PUT", urlRegExp, [200, {  "Content-Type": "application/json"}, JSON.stringify({})]);
            updateAddresses = sandbox.stub(view.model, 'updateCarriersAddresses');
            sandbox.stub(view, 'showSavedConfirmation');
        });

        it('should ask model to update carriers\' addresses', function() {
            view._saveConfig();
            server.respond();
            expect(updateAddresses).toHaveBeenCalled();
        });

        it('should navigate browser back on successful model save', function() {
            var goBack = sandbox.spy(app.router, 'goBack');

            view._saveConfig();
            server.respond();
            expect(goBack).toHaveBeenCalled();
        });
    });

    describe('cancelConfig()', function() {
        it ('should navigate browser back', function() {
            var goBack = sandbox.spy(app.router, 'goBack');
            view.cancelConfig();
            expect(goBack).toHaveBeenCalled();
        });
    });
});
