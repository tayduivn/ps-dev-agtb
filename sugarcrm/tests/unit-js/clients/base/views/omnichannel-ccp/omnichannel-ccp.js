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
describe('Base.View.OmnichannelCcpView', function() {
    var view;
    var sandbox;
    var layout;
    var app;
    beforeEach(function() {
        sandbox = sinon.sandbox.create();
        layout = {
            on: sinon.stub(),
            off: sinon.stub(),
            close: sinon.stub(),
            open: sinon.stub(),
            trigger: sinon.stub()
        };
        window.connect = {
            core: {
                terminate: sinon.stub(),
                initCCP: sinon.stub(),
                getEventBus: sinon.stub(),
                onViewContact: sinon.stub()
            },
            agent: sinon.stub(),
            contact: sinon.stub(),
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
                TERMINATED: 'TERMINATED',
                ACK_TIMEOUT: 'ACK_TIMEOUT'
            };
            connect.ContactEvents = {
                DESTROYED: 'DESTROYED'
            };
            view.loadGeneralEventListeners();
            expect(connect.core.getEventBus.calledOnce).toBeTruthy();
            expect(subscribeStub.callCount).toBe(3);
            expect(subscribeStub.getCall(0)).toHaveBeenCalledWith('TERMINATED');
            expect(subscribeStub.getCall(1)).toHaveBeenCalledWith('ACK_TIMEOUT');
            expect(subscribeStub.getCall(2)).toHaveBeenCalledWith('DESTROYED');
        });
    });

    describe('loadAgentEventListeners', function() {
        it('should call connect.agent and attach appropriate listeners', function() {
            view.loadAgentEventListeners();
            expect(connect.agent.calledOnce).toBeTruthy();
        });
    });

    describe('loadContactEventLIsteners', function() {
        it('should call connect.contact to attach event listeners', function() {
            view.loadContactEventListeners();
            expect(connect.contact.calledOnce).toBeTruthy();
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
        });
    });

    describe('initializeCCP', function() {
        using('different values for if the ccp has loaded', [true, false], function(loaded) {
            it('should initialize CCP only if not already loaded', function() {
                sandbox.stub(view, 'loadAgentEventListeners');
                sandbox.stub(view, 'loadGeneralEventListeners');
                sandbox.stub(view, 'loadContactEventListeners');
                view.ccpLoaded = loaded;
                view.initializeCCP();
                expect(connect.core.initCCP.callCount).toBe(loaded ? 0 : 1);
                expect(view.loadAgentEventListeners.callCount).toBe(loaded ? 0 : 1);
                expect(view.loadGeneralEventListeners.callCount).toBe(loaded ? 0 : 1);
                expect(view.loadContactEventListeners.callCount).toBe(loaded ? 0 : 1);
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
                expect($.getScript.callCount).toEqual(values.adminSuccess && !values.libLoaded ? 2 : 0);
            });
        });
    });

    describe('addContactToContactsList', function() {
        it('should add the contact to the connected contacts list', function() {
            var id = 123;
            var timestamp = '2020-07-29T12:00:00-04:00';

            var contact = {
                getContactId: function() {
                    return id;
                },
                getStatus: function() {
                    return {
                        timestamp: timestamp,
                    };
                },
            };

            expect(view.connectedContacts).toEqual({});
            view.addContactToContactsList(contact);
            var obj = {};
            obj[id] = {connectedTimestamp: timestamp};
            expect(view.connectedContacts).toEqual(obj);
        });
    });

    describe('removeContactFromContactsList', function() {
        it('should remove the contact from the connected contacts list', function() {
            var expected = {
                123: {
                    connectedTimestamp: '2020-07-29T12:00:00-04:00',
                },
            };

            view.connectedContacts = _.extendOwn({}, expected, {
                456: {
                    connectedTimestamp: '2020-07-29T15:00:00-04:00',
                },
            });

            var contact = {
                getContactId: function() {
                    return 456;
                },
            };

            view.removeContactFromContactsList(contact);
            expect(view.connectedContacts).toEqual(expected);
        });
    });

    describe('getGenericContactInfo', function() {
        it('should get generic contact info', function() {
            var time = '2020-07-29T12:00:00-04:00';

            var contact = {
                isInbound: function() {
                    return true;
                },
                getType: function() {
                    return 'chat';
                },
                getContactId: function() {
                    return 123;
                },
                getStatus: function() {
                    return {
                        timestamp: time,
                    };
                },
            };

            view.addContactToContactsList(contact);

            var actual = view.getGenericContactInfo(contact);

            expect(actual).toEqual({
                isContactInbound: true,
                contactType: 'chat',
                startTime: app.date(time),
            });
        });
    });

    describe('getVoiceContactInfo', function() {
        it('should get contact info for voice type', function() {
            var phoneNumber = '+01234567890';

            var contact = {
                getInitialConnection: function() {
                    return {
                        getEndpoint: function() {
                            return {
                                phoneNumber: phoneNumber,
                            };
                        },
                    };
                },
            };

            var actual = view.getVoiceContactInfo(contact);

            expect(actual).toEqual({
                phone_work: phoneNumber,
            });
        });
    });

    describe('getChatContactInfo', function() {
        it('should get contact info for chat type', function() {
            var contact = {
                _getData: function() {
                    return {
                        connections: [
                            {
                                type: 'inbound',
                                chatMediaInfo: {
                                    customerName: 'Customer'
                                },
                            },
                        ],
                    };
                },
            };

            var actual = view.getChatContactInfo(contact);

            expect(actual).toEqual({
                last_name: 'Customer',
                name: 'Customer',
            });
        });
    });

    describe('getContactConnectedTime', function() {
        it('should get the timestamp as a Utils/Date', function() {
            var time = '2020-07-29T12:00:00-04:00';
            var contact = {
                getContactId: function() {
                    return 123;
                },
                getStatus: function() {
                    return {
                        timestamp: time,
                    };
                },
            };

            view.addContactToContactsList(contact);

            var actual = view.getContactConnectedTime(contact);

            expect(actual).toEqual(app.date(time));
        });
    });

    describe('getRecordTitle', function() {
        afterEach(function() {
            sandbox.restore();
        });

        using('different contact data', [
            {
                module: 'Calls',
                time: '2020-07-29T12:00:00-04:00',
                formatUserStr: '07/29/2020 12:00pm',
                identifier: '+01234567890',
                contactTypeStr: 'Call',
                expected: {
                    direction: 'to'
                },
                data: {
                    contactType: 'voice',
                    phone_work: '+01234567890',
                    isContactInbound: false,
                },
            },
            {
                module: 'Messages',
                time: '2020-07-29T12:00:00-04:00',
                formatUserStr: '07/29/2020 12:00pm',
                identifier: 'Customer',
                contactTypeStr: 'Chat',
                expected: {
                    direction: 'from'
                },
                data: {
                    contactType: 'chat',
                    name: 'Customer',
                },
            },
        ], function(values) {
            it('should get the record title per contact data', function() {
                window.connect = _.extendOwn(window.connect, {
                    ContactType: {
                        VOICE: 'voice',
                        CHAT: 'chat',
                    },
                });

                var langGetStub = sandbox.stub(app.lang, 'get');
                sandbox.stub(app.date.fn, 'formatUser', function() {
                    return values.formatUserStr;
                });

                values.data.startTime = app.date(values.time);

                view.getRecordTitle(values.module, values.data);

                expect(langGetStub).toHaveBeenCalledWith('TPL_OMNICHANNEL_NEW_RECORD_TITLE', values.module, {
                    type: values.contactTypeStr,
                    direction: values.expected.direction,
                    identifier: values.identifier,
                    time: values.formatUserStr,
                });
            });
        });
    });

    describe('getNewModelForContact', function() {
        afterEach(function() {
            sandbox.restore();
        });

        using('different module and contact data', [
            {
                module: 'Calls',
                startTime: '2020-07-29T12:00:00-04:00',
                nowTime: '2020-07-29T13:30:00-04:00',
                durationHours: 1,
                durationMinutes: 30,
                recordTitle: 'Call from +01234567890 at 07/29/2020 12:00pm',
                data: {
                    contactType: 'voice',
                    isContactInbound: true,
                },
                expected: {
                    direction: 'Inbound',
                    duration_hours: 1,
                    duration_minutes: 30,
                    name: 'Call from +01234567890 at 07/29/2020 12:00pm',
                    date_start: '2020-07-29T12:00:00-04:00',
                    date_end: '2020-07-29T13:30:00-04:00',
                    status: 'Held',
                },
            },
            {
                module: 'Messages',
                startTime: '2020-07-29T12:00:00-04:00',
                nowTime: '2020-07-29T14:30:00-04:00',
                durationHours: 2,
                durationMinutes: 30,
                recordTitle: 'Chat from Customer at 07/29/2020 12:00pm',
                data: {
                    contactType: 'chat',
                },
                expected: {
                    name: 'Chat from Customer at 07/29/2020 12:00pm',
                    date_start: '2020-07-29T12:00:00-04:00',
                    date_end: '2020-07-29T14:30:00-04:00',
                    channel_type: 'Chat',
                },
            },
        ], function(values) {
            it('should get the record title per contact data', function() {
                window.connect = _.extendOwn(window.connect, {
                    ContactType: {
                        VOICE: 'voice',
                        CHAT: 'chat',
                    },
                });

                sandbox.stub(view, 'getTimeAndDuration', function() {
                    return {
                        startTime: values.startTime,
                        nowTime: values.nowTime,
                        durationHours: values.durationHours,
                        durationMinutes: values.durationMinutes,
                    };
                });
                sandbox.stub(view, 'getRecordTitle', function() {
                    return values.recordTitle;
                });

                var actual = view.getNewModelForContact(values.module, values.data);

                expect(actual.attributes).toEqual(values.expected);
            });
        });
    });

    describe('openCreateDrawer', function() {
        it('should open the create drawer', function() {
            var module = 'Messages';
            var model = {
                name: 'Chat from Customer at 07/29/2020 12:00pm',
                date_start: '2020-07-29T12:00:00-04:00',
                date_end: '2020-07-29T14:30:00-04:00',
            };

            app.drawer = {
                open: $.noop,
            };

            var drawerStub = sandbox.stub(app.drawer, 'open');

            view.openCreateDrawer(module, model);

            expect(drawerStub).toHaveBeenCalledWith({
                layout: 'create-no-cancel-button',
                context: {
                    create: true,
                    module: module,
                    model: model,
                },
            });
        });
    });

    describe('_handleIncomingContact', function() {
        using('different contact types', ['voice', 'chat'], function(type) {
            it('should open the layout, and trigger the incoming layout event', function() {
                var contact = {
                    getType: function() { return type; }
                };
                view._handleIncomingContact(contact);
                expect(layout.open.calledOnce).toBeTruthy();
                expect(layout.trigger).toHaveBeenCalledWith(type + ':incoming', contact);
            });
        });
    });

    describe('_setActiveContact', function() {
        it('should set the active contact to the supplied contact id', function() {
            var expected = {
                contactId: 123,
            };

            window.connect.Agent = function() {
                this.getContacts = function() {
                    return [{contactId: expected.contactId}];
                };
            };

            view._setActiveContact(expected.contactId);

            expect(view.activeContact).toEqual(expected);
        });
    });

    describe('_unsetActiveContact', function() {
        it('should unset the active contact and other relevant data', function() {
            view.activeContact = {
                contactId: 123,
            };

            var stub = sinon.stub();

            view.context = {
                unset: stub,
                off: sinon.stub(),
            };

            view._unsetActiveContact();

            expect(view.activeContact).toBeNull;
            expect(stub).toHaveBeenCalledWith('quickcreateModelData');
            expect(stub).toHaveBeenCalledWith('quickcreateCreatedModel');
        });
    });

    describe('getActiveContactId', function() {
        it('should get the contact id for the active contact', function() {
            view.activeContact = {
                getContactId: function() {
                    return '123';
                },
            };

            var actual = view.getActiveContactId();

            expect(actual).toEqual('123');
        });
    });
});
