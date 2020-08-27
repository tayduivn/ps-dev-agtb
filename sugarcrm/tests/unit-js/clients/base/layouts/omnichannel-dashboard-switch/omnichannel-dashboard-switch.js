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
                    dispose: disposeStub,
                    triggerBefore: function() {
                        return true;
                    }
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
            var disposeStub = sinon.collection.stub();
            dashboardSwitch._components = [
                {
                    dispose: disposeStub,
                    triggerBefore: function() {
                        return true;
                    }
                }
            ];
            var toggleStub = sinon.collection.stub();
            var closeStub = sinon.collection.stub();
            dashboardSwitch.layout = {
                isExpanded: function() {
                    return true;
                },
                toggle: toggleStub,
                off: $.noop,
                close: closeStub
            };
            dashboardSwitch.contactIds = ['fakeId'];
            dashboardSwitch.removeAllDashboards();
            expect(disposeStub).toHaveBeenCalled();
            expect(dashboardSwitch.contactIds.length).toEqual(0);
            expect(toggleStub).toHaveBeenCalled();
            expect(closeStub).toHaveBeenCalled();
        });
    });

    describe('getDashboard', function() {
        it('should get the dashboard per the specified contact id', function() {
            dashboardSwitch.contactIds = ['123'];

            dashboardSwitch._components = [
                {
                    dispose: sinon.collection.stub(),
                    triggerBefore: sinon.collection.stub(),
                }
            ];

            var actual = dashboardSwitch.getDashboard('123');

            expect(actual).not.toEqual(null);
        });
    });

    describe('handleIncomingCall', function() {
        it('should call search api with appropriate params', function() {
            var endpointStub = sinon.stub().returns({phoneNumber: '+11234567890'});
            var connectionStub = sinon.stub().returns({
                getEndpoint: endpointStub
            });
            var contact = {
                getInitialConnection: connectionStub
            };
            var expected = {
                q: '+11234567890',
                fields: 'phone_home, phone_mobile, phone_work, phone_other, assistant_phone',
                module_list: 'Contacts',
                max_num: app.config.maxSearchQueryResult
            };
            sinon.collection.stub(app.api, 'search');
            dashboardSwitch.handleIncomingCall(contact);
            expect(app.api.search.args[0][0]).toEqual(expected);
        });
    });

    describe('_setContactModel', function() {
        using('different result sets and contactIds', [
            // Conditions met
            {
                contact: {contactId: 'abc123'},
                data: {nextOffset: -1, records: [{id: 'def456'}]},
                contactIds: ['abc123'],
                matchExpected: true
            },
            // Multiple API results
            {
                contact: {contactId: 'abc123'},
                data: {nextOffset: -1, records: [{id: 'def456'}, {id: 'ghi789'}]},
                contactIds: ['abc123'],
                matchExpected: false
            },
            // No matching contactId on layout
            {
                contact: {contactId: 'abc123'},
                data: {nextOffset: -1, records: [{id: 'def456'}]},
                contactIds: ['poi098'],
                matchExpected: false
            }
        ], function(values) {
            it('should set the tab model if a match is found', function() {
                var setModelStub = sinon.stub();
                var switchTabStub = sinon.stub();
                sinon.collection.stub(app.data, 'createBean').returns('Mocked Return');
                dashboardSwitch.contactIds = values.contactIds;
                dashboardSwitch._components = [{
                    setModel: setModelStub,
                    dispose: function() {},
                    switchTab: switchTabStub
                }];

                dashboardSwitch._setContactModel(values.contact, values.data);

                expect(app.data.createBean.callCount).toBe(values.matchExpected ? 1 : 0);
                expect(setModelStub.callCount).toBe(values.matchExpected ? 1 : 0);
                if (values.matchExpected) {
                    expect(app.data.createBean).toHaveBeenCalledWith('Contacts', values.data.records[0]);
                    expect(setModelStub).toHaveBeenCalledWith(1, 'Mocked Return');
                    expect(switchTabStub).toHaveBeenCalledWith(1);
                }
            });
        });
    });

    describe('_clearButtonClicked', function() {
        using('different existing contactId sets', [
            {
                contactIds: ['1', '2', '3'],
                idParam: '2',
                expectedIndex: 1
            },{
                contactIds: ['1', '4', '8'],
                idParam: '5',
                expectedIndex: -1
            },{
                contactIds: ['2',],
                idParam: '2',
                expectedIndex: 0
            },{
                contactIds: [],
                idParam: '1',
                expectedIndex: -1
            }
        ], function(values) {
            it('should remove the dashboard with the appropriate index', function() {
                sinon.collection.stub(dashboardSwitch, '_removeDashboard');
                dashboardSwitch.contactIds = values.contactIds;
                dashboardSwitch._clearButtonClicked(values.idParam);
                if (values.expectedIndex !== -1) {
                    expect(dashboardSwitch._removeDashboard).toHaveBeenCalledWith(values.expectedIndex);
                } else {
                    expect(dashboardSwitch._removeDashboard).not.toHaveBeenCalled();
                }
            });
        });
    });
});
