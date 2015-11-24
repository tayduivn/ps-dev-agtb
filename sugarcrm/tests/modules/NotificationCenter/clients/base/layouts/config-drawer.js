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
describe('NotificationCenter.Layout.ConfigDrawer', function() {
    var app, layout, sandbox, module;

    beforeEach(function() {
        app = SugarTest.app;
        module = 'NotificationCenter';
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.declareData('base', module, true, false);
        layout = SugarTest.createLayout('base', module, 'config-drawer', null, null, true);
        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        layout = null;
        sandbox.restore();
    });

    describe('initialize()', function() {
        var options = {};

        beforeEach(function() {
            options.context = app.context.getContext();
            options.context.set('model', new Backbone.Model());
        });

        it('should set up class property "section" to "global" if config section is "default"', function() {
            options.context.set('section', 'default');
            layout.initialize(options);
            expect(layout.section).toBe('global');
        });

        it('should leave class property "section" as "user" if config section is not defined', function() {
            layout.initialize(options);
            expect(layout.section).toBe('user');
        });

        it('should set up model attribute "configMode" to "global" if config section is "default"', function() {
            options.context.set('section', 'default');
            layout.initialize(options);
            expect(layout.model.get('configMode')).toBe('global');
        });

        it('should set up model attribute "configMode" to "user" if config section is not defined', function() {
            layout.initialize(options);
            expect(layout.model.get('configMode')).toBe('user');
        });
    });

    describe('loadConfig()', function() {
        var server, urlRegExp;

        beforeEach(function() {
            urlRegExp = new RegExp('.*rest/v10/' + module + '/config.*');
            server = sandbox.useFakeServer();
            server.respondWith("GET", urlRegExp, [200, {  "Content-Type": "application/json"}, JSON.stringify({})]);
        });

        using('methods',
            ['replaceDefaultToActualValues', 'setSelectedAddresses'],
            function(method) {
                it('should call model\'s method on a successful get of the config-model from server', function() {
                    var spiedMethod = sandbox.spy(layout.model, method);
                    layout.loadConfig();
                    server.respond();
                    expect(spiedMethod).toHaveBeenCalled();
                });
            });
    });

    describe('_checkConfigMetadata()', function() {
        it('should always return true', function() {
            expect(layout._checkConfigMetadata()).toBeTruthy();
        });
    });

    describe('_checkUserAccess()', function() {
        it('should restrict regular user to access global configuration', function() {
            sandbox.stub(app.user, 'get').returns('user');
            layout.section = 'global';
            expect(layout._checkUserAccess()).toBeFalsy();
        });

        it('should allow regular user to access his configuration', function() {
            sandbox.stub(app.user, 'get').returns('user');
            layout.section = 'user';
            expect(layout._checkUserAccess()).toBeTruthy();
        });

        it('should allow admin to access global configuration', function() {
            sandbox.stub(app.user, 'get').returns('admin');
            layout.section = 'global';
            expect(layout._checkUserAccess()).toBeTruthy();
        });

        it('should allow admin to access his configuration', function() {
            sandbox.stub(app.user, 'get').returns('admin');
            layout.section = 'user';
            expect(layout._checkUserAccess()).toBeTruthy();
        });
    });
});
