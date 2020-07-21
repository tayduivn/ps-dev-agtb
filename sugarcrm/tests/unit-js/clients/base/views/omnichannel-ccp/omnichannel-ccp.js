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
describe('Base.Layout..OmnichannelCcpLayout', function() {
    var view;
    var sandbox;
    var layout;
    var app;
    beforeEach(function() {
        sandbox = sinon.sandbox.create();
        layout = {
            on: sinon.stub(),
            off: sinon.stub(),
            close: sinon.stub()
        };
        window.connect = {
            core: {
                terminate: sinon.stub(),
                initCCP: sinon.stub(),
                getEventBus: sinon.stub()
            },
            agent: sinon.stub(),
        };
        view = SugarTest.createView('base', 'Contacts', 'omnichannel-ccp', null, null, false, layout);
        app = SugarTest.app;
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        sandbox.restore();
        view = null;
        delete window.connect;
    });

    describe('loadAdminConfig', function() {
        using('different settings values', [
            {region: 'test-region', instance: 'test-instance', expected: true},
            {region: '', instance: 'test-instance', expected: false},
            {region: 'test-region', instance: '', expected: false},
            {region: undefined, instance: '', expected: false},
        ], function(values) {
            it('should load admin settings and return true if successful', function() {
                App.config.awsConnectInstanceName = values.instance;
                App.config.awsConnectRegion = values.region;
                var settingsLoaded = view._loadAdminConfig();
                expect(settingsLoaded).toEqual(values.expected);
                if (values.expected) {
                    var url = view.urlPrefix + values.instance + view.urlSuffix;
                    expect(view.defaultCCPOptions.ccpUrl).toEqual(url);
                    expect(view.defaultCCPOptions.region).toEqual(values.region);
                } else {
                    expect(view.defaultCCPOptions.ccpUrl).toBeUndefined();
                    expect(view.defaultCCPOptions.region).toBeUndefined();
                }
                delete App.config.awsConnectInstanceName;
                delete App.config.awsConnectRegion;
            });
        });
    });

    describe('_showNonConfiguredWarning', function() {
        it('should show warning with expected params', function() {
            sandbox.stub(app.alert, 'show');
            view._showNonConfiguredWarning();
            expect(app.alert.show).toHaveBeenCalledWith('omnichannel-not-configured', {
                level: 'warning',
                messages: 'ERROR_OMNICHANNEL_NOT_CONFIGURED'
            });
        });
    });

    describe('styleFooterButton', function() {
        using('different statuses', ['logged-in', 'logged-out'], function(status) {
            it('should trigger the layout context event', function() {
                var triggerStub = sinon.stub();
                view.layout.context = {
                    trigger: triggerStub
                };
                view.styleFooterButton(status);
                expect(triggerStub).toHaveBeenCalledWith('omnichannel:auth', status);
            });
        });
    });

    describe('loadGeneralEventListeners', function() {
        it('should call the relevant library functions', function() {
            var subscribeStub = sinon.stub();
            connect.core.getEventBus.returns({
                subscribe: subscribeStub
            });
            connect.EventType = {
                TERMINATED: 'TERMINATED'
            };
            view.loadGeneralEventListeners();
            expect(connect.core.getEventBus.calledOnce).toBeTruthy();
            expect(subscribeStub.callCount).toBe(2);
            expect(subscribeStub).toHaveBeenCalledWith('TERMINATED');
        });
    });

    describe('loadAgentEventListeners', function() {
        it('should call connect.agent and attach appropriate listeners', function() {
            view.loadAgentEventListeners();
            expect(connect.agent.calledOnce).toBeTruthy();
        });
    });

    describe('tearDownCCP', function() {
        it('should teardown all elements of the CCP', function() {
            var emptyStub = sinon.stub();
            sandbox.stub(view, 'styleFooterButton');
            sandbox.stub(view.$el, 'find', function() {
                return {empty: emptyStub};
            });
            view.ccpLoaded = true;
            view.tearDownCCP();
            expect(view.styleFooterButton).toHaveBeenCalledWith('logged-out');
            expect(emptyStub.calledOnce).toBeTruthy();
            expect(view.ccpLoaded).toBe(false);
            expect(layout.close.calledOnce).toBeTruthy();
        });
    });

    describe('initializeCCP', function() {
        using('different values for if the ccp has loaded', [true, false], function(loaded) {
            it('should initialize CCP only if not already loaded', function() {
                sandbox.stub(view, 'loadAgentEventListeners');
                sandbox.stub(view, 'loadGeneralEventListeners');
                view.ccpLoaded = loaded;
                view.initializeCCP();
                expect(connect.core.initCCP.calledOnce).toEqual(!loaded);
                expect(view.loadAgentEventListeners.calledOnce).toEqual(!loaded);
                expect(view.loadGeneralEventListeners.calledOnce).toEqual(!loaded);
                expect(view.ccpLoaded).toEqual(true);
            });
        });
    });

    describe('loadCCP', function() {
        using('different admin configs and library loaded combos', [
            {adminSuccess: true, libLoaded: true},
            {adminSuccess: false, libLoaded: true},
            {adminSuccess: true, libLoaded: false},
        ], function(values) {
            it('should fetch the connect library only if configured and not already loaded', function() {
                sandbox.stub(view, '_loadAdminConfig', function() {
                    return values.adminSuccess;
                });
                sandbox.stub(view, '_showNonConfiguredWarning');
                sandbox.stub(view, 'initializeCCP');
                sandbox.stub($, 'getScript');
                view.libraryLoaded = values.libLoaded;
                view.loadCCP();
                expect(view._loadAdminConfig.calledOnce).toBeTruthy();
                expect(view._showNonConfiguredWarning.callCount).toEqual(!values.adminSuccess ? 1 : 0);
                expect(view.initializeCCP.callCount).toEqual(values.adminSuccess && values.libLoaded ? 1 : 0);
                expect($.getScript.callCount).toEqual(values.adminSuccess && !values.libLoaded ? 1 : 0);
            });
        });
    });
});
