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
describe('Base.Layout.OmnichannelConsole', function() {
    var console;
    var app;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'layout', 'omnichannel-console');
        console = SugarTest.createLayout('base', 'layout', 'omnichannel-console', {});
        app = SugarTest.app;
        app.routing.start();
    });

    afterEach(function() {
        sinon.collection.restore();
        console.dispose();
        app.router.stop();
    });

    describe('open', function() {
        it('should show the console if not yet open', function() {
            sinon.collection.stub(app.router, 'on');
            sinon.collection.stub(console, '_setSize');
            console.currentState = '';
            var elShowStub = sinon.collection.stub(console.$el, 'show');
            console.open();
            expect(elShowStub).toHaveBeenCalled();
            expect(console.currentState).toEqual('idle');
        });

        it('should not try to show the console again if already open', function() {
            var elShowStub = sinon.collection.stub(console.$el, 'show');
            console.currentState = 'idle';
            console.open();
            expect(elShowStub).not.toHaveBeenCalled();
            expect(console.currentState).toEqual('idle');
        });
    });

    describe('isOpen', function() {
        it('should return false if not yet open', function() {
            console.currentState = '';
            expect(console.isOpen()).toBeFalsy();
        });

        it('should return true if already open', function() {
            console.currentState = 'idle';
            expect(console.isOpen()).toBeTruthy();
        });
    });

    describe('closeImmediately', function() {
        it('should close console', function() {
            var elHideStub = sinon.collection.stub(console.$el, 'hide');
            sinon.collection.stub(console, '_offEvents');
            console.currentState = 'idle';
            console.closeImmediately();
            expect(elHideStub).toHaveBeenCalled();
            expect(console.currentState).toBe('');
        });
    });

    describe('toggle', function() {
        var elToggleStub;

        beforeEach(function() {
            elToggleStub = sinon.collection.stub(console.$el, 'animate');
        });

        it('should toggle if console is open', function() {
            sinon.collection.stub(console, '$').returns({
                toggle: $.noop
            });
            console.currentState = 'idle';
            console.toggle();
            expect(elToggleStub).toHaveBeenCalled();
        });

        it('should not toggle if console is not open', function() {
            console.currentState = '';
            console.toggle();
            expect(elToggleStub).not.toHaveBeenCalled();
        });
    });

    describe('close', function() {
        var elHideStub;

        beforeEach(function() {
            elHideStub = sinon.collection.stub(console.$el, 'hide');
        });

        it('should close the console', function() {
            sinon.collection.stub(console, '_offEvents');
            console.currentState = 'idle';
            console.close();
            expect(elHideStub).toHaveBeenCalled();
            expect(console.currentState).toEqual('');
        });

        it('should not close if teh console is already closed', function() {
            console.currentState = '';
            console.close();
            expect(elHideStub).not.toHaveBeenCalled();
        });
    });

    describe('_addQuickcreateModelDataToContext', function() {
        it('should add model data to context for the quickcreate drawer', function() {
            console.getComponent = function() {
                return {
                    activeContact: 123,
                    getContactInfo: function(id) {
                        return {
                            phone_work: '+01234567890',
                        };
                    },
                };
            };

            sinon.collection.stub(console, 'isOpen').returns(true);
            sinon.collection.stub(console, 'getContactModelDataForQuickcreate').returns({
                account_id: 456,
                account_name: 'Account 1',
            });

            console._addQuickcreateModelDataToContext();

            expect(console.context.get('quickcreateModelData')).toEqual({
                phone_work: '+01234567890',
                account_id: 456,
                account_name: 'Account 1',
                no_success_label_link: true,
            });
        });
    });

    describe('_handleClosedQuickcreateDrawer', function() {
        it('should perform various actions after the quickcreate drawer is closed', function() {
            var module = 'Cases';

            var moduleTabIndex = {
                Contacts: 1,
                Cases: 2,
            };

            var setModelStub = sinon.collection.stub();
            var switchTabStub = sinon.collection.stub();
            var openStub = sinon.collection.stub(console, 'open');

            sinon.collection.stub(console, '_getOmnichannelDashboard').returns({
                moduleTabIndex: moduleTabIndex,
                setModel: setModelStub,
                switchTab: switchTabStub,
            });

            var model = app.data.createBean(module);

            model.set({
                _module: module,
                primary_contact_id: 123,
            });

            console.context.set('quickcreateCreatedModel', model);

            var fetchStub = sinon.collection.stub(console, 'fetchModelData');

            console._handleClosedQuickcreateDrawer();

            expect(fetchStub).toHaveBeenCalled();
            expect(setModelStub).toHaveBeenCalledWith(moduleTabIndex[module], model);
            expect(switchTabStub).toHaveBeenCalledWith(moduleTabIndex[module]);
            expect(console.context.get('quickcreateCreatedModel')).toBeUndefined();
            expect(openStub).toHaveBeenCalled();
        });
    });

    describe('getContactModelDataForQuickcreate', function() {
        it('should get model data from the Contact for the quickcreate drawer', function() {
            var model = app.data.createBean('Contacts');

            model.set({
                id: 123,
                name: 'Customer',
                account_id: 456,
                account_name: 'Account 1',
            });

            var moduleTabIndex = {
                Contacts: 1,
                Cases: 2,
            };

            sinon.collection.stub(console, '_getOmnichannelDashboard').returns({
                moduleTabIndex: moduleTabIndex,
                tabModels: [
                    {},
                    model,
                ]
            });

            var actual = console.getContactModelDataForQuickcreate();

            expect(actual).toEqual({
                primary_contact_id: 123,
                primary_contact_name: 'Customer',
                account_id: 456,
                account_name: 'Account 1',
            });
        });
    });
});
