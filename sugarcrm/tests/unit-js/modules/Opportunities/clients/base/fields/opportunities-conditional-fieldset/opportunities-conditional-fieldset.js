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
describe('Opportunities.Base.Fields.ConditionalFieldsetField', function() {
    var app;
    var context;
    var field;
    var fieldDef;
    var model;
    var moduleName;

    beforeEach(function() {
        app = SugarTest.app;
        moduleName = 'Opportunities';
        model = app.data.createBean(moduleName, {
            id: '123test',
            name: 'Lórem ipsum dolor sit àmêt, ut úsu ómnés tatión imperdiet.'
        });
        context = new app.Context();
        context.set({model: model});
        fieldDef = {
            fields: ['sales_stage']
        };
    });

    afterEach(function() {
        sinon.collection.restore();
        app = null;
        context = null;
        model = null;
        fieldDef = null;
        field = null;
    });

    describe('initialize', function() {
        beforeEach(function() {
            sinon.collection.stub(app.metadata, 'getModule')
            .withArgs('Opportunities', 'config')
            .returns({
                opps_view_by: 'RevenueLineItems'
            });
            sinon.collection.stub(model, 'on');
            sinon.collection.stub(app.lang, 'get');
            sinon.collection.stub(app.lang, 'getModuleName');
        });

        afterEach(function() {
            sinon.collection.restore();
        });

        using('detail and edit view names', ['detail', 'edit'], function(viewName) {
            it('should add bool and set type to fieldset in detail/edit view', function() {
                field = SugarTest.createField(
                    'base',
                    'snarf',
                    'opportunities-conditional-fieldset',
                    viewName,
                    fieldDef,
                    moduleName,
                    model,
                    context,
                    true
                );
                expect(field.options.def.fields[1].type).toBe('bool');
                expect(field.options.def.fields[1].name.indexOf('_should_cascade'))
                .toBeGreaterThan(-1);
                expect(app.lang.get).toHaveBeenCalledWith('LBL_UPDATE_OPPORTUNITIES_RLIS', 'Opportunities');
                expect(app.lang.getModuleName).toHaveBeenCalledWith('RevenueLineItems', {plural: true});
            });
        });

        it('should not add bool in create view', function() {
            var field = SugarTest.createField(
                'base',
                'snarf',
                'opportunities-conditional-fieldset',
                'create',
                fieldDef,
                moduleName,
                model,
                context,
                true
            );
            expect(field.options.def.fields.length).toEqual(1);
        });
    });

    describe('_disableBaseField', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'snarf',
                'opportunities-conditional-fieldset',
                'detail',
                fieldDef,
                moduleName,
                model,
                context,
                true
            );
        });

        afterEach(function() {
            field.dispose();
            field = null;
        });
        using('different bool field values',
              [[true, false, 0], [false, true, 2]],
              function(boolValue, shouldDisable, callCount) {
            it('should disable the basefield appropriately', function() {
                var setDisabled = sinon.stub();
                sinon.collection.stub(field.view, 'getField', function() {
                    return {
                        setDisabled: setDisabled
                    };
                });
                sinon.collection.stub(model, 'get', function() {
                    return boolValue;
                });
                sinon.collection.stub(model, 'set');
                field._toggleBaseField();
                expect(setDisabled).toHaveBeenCalledWith(shouldDisable, {
                    'trigger': false
                });
                expect(model.set.callCount).toBe(callCount);
            });
        });
    });

    describe('setCascadeValue', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'snarf',
                'opportunities-conditional-fieldset',
                'detail',
                fieldDef,
                moduleName,
                model,
                context,
                true
            );
        });

        afterEach(function() {
            field.dispose();
            field = null;
        });
        using('different bool field values', [
            ['Prospecting', true, 'Qualification', 'Qualification'],
            ['Prospecting', false, 'Qualification', ''],
            ['Prospecting', true, 'Prospecting', '']
        ], function(initialValue, boolValue, baseValue, cascadeValue) {
            it('should set the cascade field value appropriately', function() {
                field.boolFieldName = 'bool';
                field.baseFieldName = 'base';
                sinon.collection.stub(model, 'get')
                .withArgs('bool').returns(boolValue)
                .withArgs('base').returns(baseValue);
                sinon.collection.stub(model, 'getSynced').returns(initialValue);
                sinon.collection.stub(model, 'set');

                field._setCascadeValue();
                expect(field.model.set).toHaveBeenCalledWith('base_cascade', cascadeValue);
                sinon.collection.restore();
            });
        });
    });
});
