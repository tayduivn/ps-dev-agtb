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
describe('Base.Layout.OmnichannelDashboard', function() {
    var console;
    var app;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'layout', 'omnichannel-dashboard');
        console = SugarTest.createLayout('base', 'layout', 'omnichannel-dashboard', {});
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.collection.restore();
        console.dispose();
    });

    describe('_render', function() {
        var onStub;
        var onContextStub;

        beforeEach(function() {
            sinon.collection.stub(console, '_super');
            onStub = sinon.collection.stub();
            onContextStub = sinon.collection.stub();
            sinon.collection.stub(console, '_getTabbedDashboard').returns({
                context: {
                    on: onContextStub
                },
                on: onStub
            });
        });

        afterEach(function() {
            sinon.collection.restore();
            console.dispose();
        });

        it('should register to tabbed-dashboard event', function() {
            console._onTabEvent = false;
            console._render();
            expect(onStub).toHaveBeenCalled();
            expect(onContextStub).toHaveBeenCalled();
            expect(console._onTabEvent).toBeTruthy();
        });

        it('should not register to tabbed-dashboard event', function() {
            console._onTabEvent = true;
            console._render();
            expect(onStub).not.toHaveBeenCalled();
            expect(onContextStub).not.toHaveBeenCalled();
            expect(console._onTabEvent).toBeTruthy();
        });
    });

    describe('initComponents', function() {
        it('should replace with omnichannel dashboard', function() {
            var fakeComponents = [
                {
                    layout: {
                        type: 'dashboard',
                        components: ['fake'],
                    }
                }
            ];
            var expectedDashboard = [
                {
                    view: {
                        name: 'tabbed-dashboard',
                        type: 'tabbed-dashboard',
                        sticky: false
                    }
                },
                {
                    layout: 'dashlet-main'
                }
            ];
            var superStub = sinon.collection.stub(console, '_super');
            console.initComponents(fakeComponents, null, null);
            expect(superStub.lastCall.args[1][0][0].layout.components).toEqual(expectedDashboard);
        });
    });

    describe('setTabModes', function() {
        var setTab;

        beforeEach(function() {
            setStub = sinon.collection.stub();
            sinon.collection.stub(console, '_getTabbedDashboard').returns({
                tabs: ['tab 1', 'tab2'],
                setTabMode: setStub
            });
        });

        afterEach(function() {
            sinon.collection.restore();
            console.dispose();
        });

        it('should disable tab2', function() {
            console.tabModels = ['model1'];
            console.setTabModes();
            expect(setStub.lastCall.args[1]).toBeFalsy();
        });

        it('should enable tab2', function() {
            console.tabModels = ['model1', 'model2'];
            console.setTabModes();
            expect(setStub.lastCall.args[1]).toBeTruthy();
        });
    });
});
