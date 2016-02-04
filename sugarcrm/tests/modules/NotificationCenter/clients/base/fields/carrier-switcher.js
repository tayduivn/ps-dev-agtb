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

describe('NotificationCenter.Field.CarrierSwitcher', function() {
    var app, field, sandbox,
        module = 'NotificationCenter',
        fieldType = 'carrier-switcher',
        fieldDef,
        layout,
        carriers,
        config,
        model;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        carriers = {foo: {status: false}};
        config = {emitter1: {event1: {filter1: []}}};
        SugarTest.testMetadata.init();
        SugarTest.declareData('base', module, true, false);
        layout = SugarTest.createLayout('base', module, 'config-drawer', null, null, true);
        model = layout.model;
        model.set('configMode', 'global');
        model.set('carriers', carriers);
        model.set('config', config);
        fieldDef = {
            name: 'dummy',
            type: fieldType,
            carrier: 'foo',
            emitter: 'emitter1',
            event: 'event1',
            view: 'default'
        };
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'default', module);
        SugarTest.testMetadata.set();
        field = SugarTest.createField('base', 'dummy', fieldType, 'default', fieldDef, module, model, null, true);
    });

    afterEach(function() {
        if (field) {
            field.dispose();
        }
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        sandbox.restore();
        app.cache.cutAll();
        layout = null;
        model = null;
        field = null;
    });

    it('should set up "config" property for default mode', function() {
        expect(field.config).toBe(config);
    });

    it('should set up "carriers" property for default mode', function() {
        expect(field.carriers).toBe(carriers);
    });

    it('should set up "config" property for user mode', function() {
        model.set('configMode', 'user');
        model.set('personal', {config: config});
        field = SugarTest.createField('base', 'dummy', fieldType, 'default', fieldDef, module, model, null, true);
        expect(field.config).toBe(config);
    });

    it('should set up "carriers" property for user mode', function() {
        model.set('configMode', 'user');
        model.set('personal', {carriers: carriers});
        field = SugarTest.createField('base', 'dummy', fieldType, 'default', fieldDef, module, model, null, true);
        expect(field.carriers).toBe(carriers);
    });

    describe('format()', function() {
        var result;

        using('statues and config filter data',
            [
                [true, [['foo', '']]],
                [true, [['foo', ''], ['bar', '']]],
                [true, [['foo', 0], ['bar', 1]]],
                [true, [['foo', 1]]],
                [false, [['foobaz', '']]],
                [false, [['bar', 0], ['baz', 0]]],
                [false, [['bar', 0], ['bar', 1]]],
                [false, [['', '']]]
            ],
            function(status, filterData) {
                it('should set field status in accordance with event filters data for this carrier', function() {
                    model.set('config', {emitter1: {event1: {filter1: filterData}}});
                    field = SugarTest.createField('base', 'dummy', fieldType, 'default',
                        fieldDef, module, model, null, true);
                    result = field.format();
                    expect(result.status).toBe(status);
                });
            });

        using('disabled values and config filter data',
            [
                [true, [[]], true],
                [true, [['', '']], true],
                [true, [['', '']], false],
                [false, [['foo', 1]], true],
                [false, [['foo', 0], ['foo', 1]], true],
                [false, [['foo', 0], ['bar', 0]], true],
                [false, [['foo', '']], true],
                [false, [['foo', ''], ['bar', '']], true],
                [true, [['foo', '']], false]
            ],
            function(value, filterData, carrierStatus) {
                it('should set field status in accordance with event filters data for this carrier', function() {
                    model.set('config', {emitter1: {event1: {filter1: filterData}}});
                    model.set('carriers', {foo: {status: carrierStatus}});
                    field = SugarTest.createField('base', 'dummy', fieldType, 'default',
                        fieldDef, module, model, null, true);
                    result = field.format();
                    expect(result.disabled).toBe(value);
                });
            });
    });

    describe('bindDomChange()', function() {
        using('statues and config filter data',
            [
                [true, [['foo', '']], 1],
                [true, [['foo', ''], ['bar', '']], 2],
                [true, [['baz', '']], 2],
                [false, [['foo', '']], 0],
                [false, [['baz', '']], 1],
                [false, [['baz', ''], ['bar', '']], 2]
            ],
            function(isChecked, existingFilterData, filterSize) {
                it('should add or delete its carrier data to/from its event filters', function() {
                    model.set('config', {emitter1: {event1: {filter1: existingFilterData}}});
                    field = SugarTest.createField('base', 'dummy', fieldType, 'default',
                        fieldDef, module, model, null, true);
                    field.render();
                    field.$(field.fieldTag).prop('checked', isChecked).trigger('change');
                    var filters = field.model.get('config')['emitter1']['event1']['filter1'];
                    expect(filters.length).toEqual(filterSize);
                });
            });

        it('should cause model to trigger "change:personal:emitter1" event', function() {
            var modelEvent = sandbox.spy(field.model, 'trigger');
            field.render();
            field.$(field.fieldTag).prop('checked', true).trigger('change');
            expect(modelEvent).toHaveBeenCalledWith('change:personal:emitter1');
        });
    });

    describe('handleEventSwitcherChange()', function() {
        using('params list',
            [true, false, undefined],
            function(param) {
                it('should call render() with any param', function() {
                    var render = sandbox.stub(field, 'render');
                    field.handleEventSwitcherChange(param);
                    expect(render).toHaveBeenCalled();
                });
            });

        using('carriers statues and config filter size',
            [[true, 1], [false, 0]],
            function(carrierStatus, filterArraySize) {
                it('should add carriers to config if corresponding carriers are enabled', function() {
                    carriers = {foo: {status: carrierStatus}};
                    config = {emitter1: {event1: {filter1: []}}};
                    model.set('config', config);
                    model.set('carriers', carriers);
                    field = SugarTest.createField('base', 'dummy', fieldType, 'default',
                        fieldDef, module, model, null, true);
                    field.render();
                    field.view.setElement($('<div></div>').append(field.$el));
                    field.handleEventSwitcherChange(true);
                    var filters = field.model.get('config')['emitter1']['event1']['filter1'];
                    expect(filters.length).toEqual(filterArraySize);
                });
            });
    });
});
