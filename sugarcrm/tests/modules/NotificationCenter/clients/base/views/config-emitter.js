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
describe('NotificationCenter.View.ConfigEmitter', function() {
    var app, layout, view, meta, context, sandbox,
        module = 'NotificationCenter', emitter = 'test-emitter';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.declareData('base', module, true, false);
        layout = SugarTest.createLayout('base', module, 'config-drawer', null, null, true);
        context = app.context.getContext({model: layout.model});
        meta = {
            emitter: emitter
        };
        view = SugarTest.createView('base', module, 'config-emitter', meta, context, true, layout);
        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        view.dispose();
        view = null;
        layout = null;
        sandbox.restore();
    });

    it('should call populateCarriersAndEventsLists() when it is rendered', function() {
        var method = sandbox.stub(view, 'populateCarriersAndEventsLists');
        view.render();
        expect(method).toHaveBeenCalled();
    });

    it('should call displayResetButton() when it is rendered', function() {
        var method = sandbox.stub(view, 'displayResetButton');
        sandbox.stub(view, 'populateCarriersAndEventsLists');
        view.render();
        expect(method).toHaveBeenCalled();
    });

    describe('initialize()', function() {
        it('should call _getLabelAndDescriptionMeta()', function() {
            var method = sandbox.stub(view, '_getLabelAndDescriptionMeta');
            view.initialize({meta: meta, context: context});
            expect(method).toHaveBeenCalled();
        });

        it('should call setup title', function() {
            sandbox.stub(app.lang, 'get', function() {
                return 'foo';
            });
            view.initialize({meta: meta, context: context});
            expect(view.title).toBe('foo');
        });

        it('should call createResetButton() when we are in User config mode', function() {
            var method = sandbox.spy(view, 'createResetButton');
            context.get('model').set('configMode', 'user');
            view.initialize({meta: meta, context: context});
            expect(method).toHaveBeenCalled();
        });

        it('should not call createResetButton() when we are in Admin config mode', function() {
            var method = sandbox.spy(view, 'createResetButton');
            context.get('model').set('configMode', 'default');
            view.initialize({meta: meta, context: context});
            expect(method).not.toHaveBeenCalled();
        });
    });

    describe('displayResetButton()', function() {
        var button;

        beforeEach(function() {
            button = SugarTest.createField({
                client: 'base',
                name: 'reset_to_default_button',
                type: 'button',
                viewName: 'detail'
            });
            sandbox.stub(view, 'getField').returns(button);
        });

        it('should hide "reset" button if emitter has default settings', function() {
            var hide = sandbox.spy(button, 'hide');
            sandbox.stub(view.model, 'isEmitterDefaultConfigured').returns(true);
            view.displayResetButton();
            expect(hide).toHaveBeenCalled();
        });

        it('should show "reset" button if emitter does not have default settings', function() {
            var show = sandbox.spy(button, 'show');
            sandbox.stub(view.model, 'isEmitterDefaultConfigured').returns(false);
            view.displayResetButton();
            expect(show).toHaveBeenCalled();
        });
    });

    describe('populateCarriersAndEventsLists()', function() {
        var carriers, events;
        beforeEach(function() {
            carriers = [
                {foo: 'test1'},
                {bar: 'test2'}
            ];
            events = ['event1', 'event2'];
            view.model.set('configMode', 'default');
            view.model.set('carriers', carriers);
            sandbox.stub(view, 'generateRowsAndColumns').returns(events);
        });

        it('should populate carriers list', function() {
            view.populateCarriersAndEventsLists();
            expect(view.carriersList).toBe(carriers);
        });

        it('should populate events list', function() {
            view.populateCarriersAndEventsLists();
            expect(view.eventsList).toBe(events);
        });
    });

    describe('generateRowsAndColumns()', function() {
        var rows, config, carriers = {};

        beforeEach(function() {
            view.model.set('configMode', 'default');
        });

        it('should return empty rows if no emitters found in model', function() {
            rows = view.generateRowsAndColumns();
            view.model.set('config', {});
            expect(rows.length).toBe(0);
        });

        it('should return only one row for view\'s emitter', function() {
            config = {
                'test-emitter': {'event1': {}},
                'test-emitter-2': {'event1': {}}
            };
            view.model.set('config', config);
            rows = view.generateRowsAndColumns();
            expect(rows.length).toBe(1);
        });

        it('should return row with empty columns if no carriers are found in model', function() {
            config = {
                'test-emitter': {'event1': {}}
            };
            view.model.set('config', config);
            rows = view.generateRowsAndColumns();
            expect(rows[0].columns.length).toBe(0);
        });

        it('should return row with columns for every carrier found in model', function() {
            config = {
                'test-emitter': {'event1': {}}
            };
            carriers = {
                foo: {},
                bar: {}
            };
            view.model.set('config', config);
            view.carriersList = carriers;
            rows = view.generateRowsAndColumns();
            expect(rows[0].columns.length).toBe(2);
        });

        it('should return row with one column that contains correct metadata', function() {
            config = {
                'test-emitter': {'event1': {}}
            };
            carriers = {
                foo: {}
            };
            view.model.set('config', config);
            view.carriersList = carriers;
            rows = view.generateRowsAndColumns();
            expect(rows[0].columns[0].type).toBe('carrier-switcher');
            expect(rows[0].columns[0].event).toBe('event1');
            expect(rows[0].columns[0].carrier).toBe('foo');
            expect(rows[0].columns[0].emitter).toBe('test-emitter');
        });

        it('should return row with correct event switcher field metadata', function() {
            config = {
                'test-emitter': {'event1': {}}
            };
            view.model.set('config', config);
            rows = view.generateRowsAndColumns();
            expect(rows[0].rowSwitcher).toBeDefined();
            expect(rows[0].rowSwitcher.type).toBe('event-switcher');
            expect(rows[0].rowSwitcher.event).toBe('event1');
            expect(rows[0].rowSwitcher.emitter).toBe('test-emitter');
        });
    });

    describe('createResetButton()', function() {
        it('should create reset button in view\'s metadata', function() {
            view.createResetButton();
            expect(view.meta.buttons.length).toBe(1);
            expect(view.meta.buttons[0].type).toBe('button');
        });
    });

    describe('handleResetToDefault()', function() {
        it('should return false to stop click propagation', function() {
            sandbox.stub(app.alert, 'show');
            expect(view.handleResetToDefault()).toBeFalsy();
        });

        it('should show confirmation alert popup', function() {
            var alertOpen = sandbox.stub(app.alert, 'show');
            view.handleResetToDefault();
            expect(alertOpen).toHaveBeenCalled();
        });

        it('should reset model\'s attributes of this view\'s emitter to default', function() {
            sandbox.stub(app.alert, 'show', function(msg, param) {
                param.onConfirm();
            });
            var reset = sandbox.spy(view.model, 'resetToDefault');
            view.handleResetToDefault();
            expect(reset).toHaveBeenCalledWith(emitter);
        });

        it('should replace wildcard in popup message to this view\'s label', function() {
            var alertShow = sandbox.stub(app.alert, 'show');
            view.meta.label = 'Bar';
            sandbox.stub(app.lang, 'get', function() {
                return 'Foo % Baz';
            });
            view.handleResetToDefault();
            expect(alertShow.getCall(0).args[1].messages).toBe('Foo Bar Baz');
        });
    });
});
