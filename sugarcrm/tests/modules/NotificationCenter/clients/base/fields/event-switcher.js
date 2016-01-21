/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

describe('NotificationCenter.Field.EventSwitcher', function() {
    var app, field, sandbox,
        module = 'NotificationCenter',
        fieldType = 'event-switcher',
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

    it('should set up "config" property for user mode', function() {
        model.set('configMode', 'user');
        model.set('personal', {config: config});
        field = SugarTest.createField('base', 'dummy', fieldType, 'default', fieldDef, module, model, null, true);
        expect(field.config).toBe(config);
    });

    describe('format()', function() {
        using('values and config filter data',
            [
                [false, []],
                [true, [['foo', 1], ['foo', 0]]],
                [true, [['foo', 1], ['baz', 1]]],
                [true, [['foo', '']]],
                [false, [['', '']]]
            ],
            function(status, filterData) {
                it('should set field status in accordance with event filters data', function() {
                    model.set('config', {emitter1: {event1: {filter1: filterData}}});
                    field = SugarTest.createField('base', 'dummy', fieldType, 'default',
                        fieldDef, module, model, null, true);
                    expect(field.format().status).toBe(status);
                });
            });

        using('carriers data',
            [
                [true, {foo: {status: false}}],
                [true, {foo: {status: false}, bar: {status: false}}],
                [false, {foo: {status: true}}],
                [false, {foo: {status: true}, bar: {status: false}}],
                [true, {}]
            ],
            function(status, carriersData) {
                it('should set field "disabled" meta-property in accordance with carriers status data', function() {
                    model.set('carriers', carriersData);
                    field = SugarTest.createField('base', 'dummy', fieldType, 'default',
                        fieldDef, module, model, null, true);
                    expect(field.format().disabled).toBe(status);
                });
            });
    });

    describe('bindDomChange()', function() {
        using('values and config filter data',
            [
                [false, [['foo', '']], 2],
                [false, [['', '']], 1],
                [false, [], 1],
                [false, [['foo', ''], ['', '']], 2],
                [true, [['foo', ''], ['', '']], 1],
                [true, [['', '']], 0],
                [true, [], 0],
                [true, [['foo', '']], 1]
            ],
            function(isChecked, existingFilterData, filterSize) {
                it('should add or delete [empty, empty] marker-data to/from its event filters', function() {
                    model.set('config', {emitter1: {event1: {filter1: existingFilterData}}});
                    field = SugarTest.createField('base', 'dummy', fieldType, 'default',
                        fieldDef, module, model, null, true);
                    field.render();
                    field.$(field.fieldTag).prop('checked', isChecked).trigger('change');
                    var filters = field.model.get('config')['emitter1']['event1']['filter1'];
                    expect(filters.length).toEqual(filterSize);
                });
            });

        using('events',
            ['change:event:emitter1:event1', 'change:personal:emitter:emitter1'],
            function(event) {
                it('should cause model to trigger event', function() {
                    var modelEvent = sandbox.spy(field.model, 'trigger');
                    field.render();
                    field.$(field.fieldTag).prop('checked', true).trigger('change');
                    expect(modelEvent).toHaveBeenCalledWith(event);
                });
            });
    });

    describe('handleCarrierSwitcherChange()', function() {
        var render, allCarrierSwitchersUncheckedStub;

        beforeEach(function() {
            render = sandbox.spy(field, 'render');
            allCarrierSwitchersUncheckedStub = sandbox.stub(field, 'allCarrierSwitchersUnchecked');
        });

        it('should not call render() if given a wrong event', function() {
            allCarrierSwitchersUncheckedStub.returns(true);
            field.handleCarrierSwitcherChange('wrongEvent');
            expect(render).not.toHaveBeenCalled();
        });

        it('should call render() if given a correct event', function() {
            allCarrierSwitchersUncheckedStub.returns(true);
            field.handleCarrierSwitcherChange('event1');
            expect(render).toHaveBeenCalled();
        });

        it('should not call render() if not all switchers are unchecked', function() {
            allCarrierSwitchersUncheckedStub.returns(false);
            field.handleCarrierSwitcherChange('event1');
            expect(render).not.toHaveBeenCalled();
        });

        it('should cause [empty, empty] marker to be added', function() {
            allCarrierSwitchersUncheckedStub.returns(true);
            field.render();
            field.handleCarrierSwitcherChange('event1');
            var filter = field.model.get('config')['emitter1']['event1']['filter1'];
            expect(filter.length).toEqual(1);
            expect(filter[0]).toEqual(['', '']);
        });
    });
});
