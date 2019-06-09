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

describe('Base.View.TabbedDashboardView', function() {
    var view;
    var app;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.createView('base', 'Home', 'tabbed-dashboard');
    });

    afterEach(function() {
        sinon.collection.restore();
        view = null;
    });

    describe('initialize', function() {
        it('should have tabs based on the given metadata', function() {
            sinon.collection.stub(view, 'getLastStateKey');
            sinon.collection.stub(app.user.lastState, 'set');

            var tabs = [
                {name: 'tab 0'},
                {
                    name: 'tab 1',
                    badges: [{cssClass: 'label-important', text: '5'}],
                },
            ];
            view.initialize({
                meta: {
                    activeTab: 1,
                    tabs: tabs,
                },
            });

            expect(view.activeTab).toEqual(1);
            expect(view.tabs).toEqual(tabs);
        });
    });

    describe('tabClicked', function() {
        var triggerStub;

        beforeEach(function() {
            triggerStub = sinon.collection.stub(view.context, 'trigger');
            sinon.collection.stub(view, '$').withArgs('tab 1').returns({
                data: sinon.collection.stub().withArgs('index').returns(1)
            });
        });

        it('should trigger tabbed-dashboard:switch-tab on the context if the active tab changed', function() {
            view.activeTab = 0;

            view.tabClicked({currentTarget: 'tab 1'});

            expect(triggerStub).toHaveBeenCalledWith('tabbed-dashboard:switch-tab', 1);
        });

        it('should not do anything if the active tab did not change', function() {
            view.activeTab = 1;

            view.tabClicked({currentTarget: 'tab 1'});

            expect(triggerStub).not.toHaveBeenCalled();
        });
    });

    describe('events', function() {
        it('should re-render on tabbed-dashboard:update', function() {
            var renderStub = sinon.collection.stub(view, 'render');
            view.context.trigger('tabbed-dashboard:update');
            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('_initTabs', function() {
        it('should set last visited tab as active ', function() {
            sinon.collection.stub(view, 'getLastStateKey').returns('key');
            sinon.collection.stub(app.user.lastState, 'get').returns(2);
            view.activeTab = 1;
            view._initTabs();
            expect(view.activeTab).toEqual(2);
        });
    });

    describe('getLastStatekey', function() {
        it('should return a key', function() {
            view.model.set('id', 'my_dashboard_id');
            expect(view.getLastStateKey()).toEqual('my_dashboard_id.last_tab');
        });
    });

    describe('_isDashboardTab', function() {
        it('should return true for dashboard tab', function() {
            var tab0 = {name: 'tab0', components: {rows: ['row 1, tab 0', 'row 2, tab 0'], width: 22}};
            view.tabs = [tab0];
            expect(view._isDashboardTab(0)).toBeTruthy();
        });

        it('should return false for non-dashboard tab', function() {
            var tab1 = {name: 'tab1', components: {view: 'multi-line-list'}};
            view.tabs = [tab1];
            expect(view._isDashboardTab(0)).toBeFalsy();
        });
    });

    describe('_setMode', function() {
        var $tab;

        beforeEach(function() {
            var tab0 = {name: 'tab0', components: {rows: ['row 1, tab 0', 'row 2, tab 0'], width: 22}};
            var tab1 = {name: 'tab1', components: {view: 'multi-line-list'}};
            view.tabs = [tab0, tab1];
            view.activeTab = 0;
            $tab = {
                addClass: sinon.collection.stub(),
                removeClass: sinon.collection.stub()
            };
            sinon.collection.stub(view, '$').returns({
                closest: sinon.collection.stub().returns($tab)
            });
        });

        it('should disable tab', function() {
            view._setMode('edit');
            expect($tab.addClass).toHaveBeenCalledWith('disabled');
        });

        it('should enable tab', function() {
            view._setMode('view');
            expect($tab.removeClass).toHaveBeenCalledWith('disabled');
        });
    });
});
