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
describe('Base.Layout.OmnichannelDashboardSwitch', function() {
    var dashboardSwitch;
    var app;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'layout', 'omnichannel-dashboard-switch');
        dashboardSwitch = SugarTest.createLayout('base', 'layout', 'omnichannel-dashboard-switch');
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.collection.restore();
        dashboardSwitch.dispose();
    });

    describe('showDashboard', function() {
        it('should create a new dashboard', function() {
            var createStub = sinon.collection.stub(dashboardSwitch, '_createDashboard');
            var toggleStub = sinon.collection.stub();
            dashboardSwitch.layout = {
                isExpanded: function() {
                    return false;
                },
                toggle: toggleStub,
                off: $.noop
            };
            dashboardSwitch.contactIds = [];
            dashboardSwitch.showDashboard('fakeId');
            expect(createStub).toHaveBeenCalled();
            expect(dashboardSwitch.contactIds).toEqual(['fakeId']);
            expect(toggleStub).toHaveBeenCalled();
        });

        it('should not create a new dashboard', function() {
            var createStub = sinon.collection.stub(dashboardSwitch, '_createDashboard');
            dashboardSwitch.layout = {
                isExpanded: function() {
                    return true;
                },
                off: $.noop
            };
            var cssStub = sinon.collection.stub();
            dashboardSwitch.contactIds = ['fakeId'];
            dashboardSwitch._components = [{
                $el: {
                    css: cssStub
                },
                dispose: $.noop
            }];
            dashboardSwitch.showDashboard('fakeId');
            expect(createStub).not.toHaveBeenCalled();
            expect(cssStub).toHaveBeenCalled();
            expect(dashboardSwitch.contactIds).toEqual(['fakeId']);
        });
    });

    describe('removeDashboard', function() {
        it('should remove dashboard', function() {
            var disposeStub = sinon.collection.stub();
            dashboardSwitch._components = [
                {
                    dispose: disposeStub
                }
            ];
            var toggleStub = sinon.collection.stub();
            dashboardSwitch.layout = {
                isExpanded: function() {
                    return false;
                },
                toggle: toggleStub,
                off: $.noop
            };
            dashboardSwitch.contactIds = ['fakeId'];
            dashboardSwitch.removeDashboard('fakeId');
            expect(disposeStub).toHaveBeenCalled();
            expect(dashboardSwitch.contactIds.length).toEqual(0);
            expect(toggleStub).not.toHaveBeenCalled();
        });
    });

    describe('removeAllDashboards', function() {
        it('should remove dashboards and show ccp only', function() {
            var disposeStub = sinon.collection.stub(dashboardSwitch, '_disposeComponents');
            var toggleStub = sinon.collection.stub();
            dashboardSwitch.layout = {
                isExpanded: function() {
                    return true;
                },
                toggle: toggleStub,
                off: $.noop
            };
            dashboardSwitch.contactIds = ['fakeId'];
            dashboardSwitch.removeAllDashboards();
            expect(disposeStub).toHaveBeenCalled();
            expect(dashboardSwitch.contactIds.length).toEqual(0);
            expect(toggleStub).toHaveBeenCalled();
        });
    });
});
