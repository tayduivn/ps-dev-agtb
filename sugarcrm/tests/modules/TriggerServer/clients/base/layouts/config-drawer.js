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

describe('TriggerServer.Layout.ConfigDrawer', function () {
    var app, layout, sandbox, module;

    beforeEach(function () {
        app = SugarTest.app;
        module = 'TriggerServer';
        sandbox = sinon.sandbox.create();

        layout = SugarTest.createLayout('base', module, 'config-drawer', null, null, true);
    });

    afterEach(function () {
        layout = null;
        sandbox.restore();
    });

    describe('loadConfig()', function () {
        var url, mockApi, options;

        beforeEach(function () {
            url = 'rest/v10/' + (Math.random() * 1000);
            mockApi = sandbox.mock(app.api);
            options = {
                params: Math.random() * 1000,
                attributes: Math.random() * 1000
            };
        });

        it('should call buildUrl', function () {
            mockApi.expects('buildURL').once().withArgs(layout.module, 'config', null, options.params);
            layout.loadConfig(options);
            mockApi.verify();
        });

        it('should call `call` function', function () {
            sandbox.stub(app.api, 'buildURL').returns(url);
            mockApi.expects('call').once().withArgs('READ', url, options.attributes);
            layout.loadConfig(options);
            mockApi.verify();
        });
    });

    describe('_checkConfigMetadata()', function () {
        it('should always return true', function () {
            expect(layout._checkConfigMetadata()).toBeTruthy();
        });
    });

    describe('_checkUserAccess()', function () {
        it('should not allow regular user to access trigger server configuration', function () {
            sandbox.stub(app.user, 'get').returns('user');
            expect(layout._checkUserAccess()).toBeFalsy();
        });

        it('should allow admin to access trigger server configuration', function () {
            sandbox.stub(app.user, 'get').returns('admin');
            expect(layout._checkUserAccess()).toBeTruthy();
        });
    });
});
