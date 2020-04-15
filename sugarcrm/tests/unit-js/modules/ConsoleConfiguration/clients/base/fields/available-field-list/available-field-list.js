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
describe('ConsoleConfiguration.Fields.AvailableFieldList', function() {
    var app;
    var module = 'ConsoleConfiguration';
    var field;
    var fieldName;
    var model;

    beforeEach(function() {
        app = SugarTest.app;

        model = app.data.createBean(module);
        model.set({
            enabled_module: 'Accounts',
        });

        var enabledModule = model.get('enabled_module');

        sinon.collection.stub(app.metadata, 'getView').withArgs(enabledModule, 'multi-line-list').returns({
            panels: [
                {
                    fields: [],
                },
            ],
        });

        SugarTest.loadComponent('base', 'field', 'base');
        field = SugarTest.createField(
            'base',
            fieldName,
            'available-field-list',
            'edit',
            {},
            module,
            model,
            null,
            true
        );
    });

    afterEach(function() {
        sinon.collection.restore();
        field.dispose();
        model.dispose();
    });

    describe('initialize', function() {
        it('should call setAvailableFields once', function() {
            var setAvailableFieldsSpy = sinon.collection.spy(field, 'setAvailableFields');

            field.initialize(field.options);

            expect(setAvailableFieldsSpy.calledOnce).toBe(true);
        });
    });

    describe('isFieldSupported', function() {
        using('various field names and types', [{
            fieldDef: {name: 'deleted', type: 'bool'},
            expected: false
        },{
            fieldDef: {name: 'id', type: 'id'},
            expected: false
        },{
            fieldDef: {name: 'somelink', type: 'link'},
            expected: false
        },{
            fieldDef: {name: 'modified_user_id', type: 'assigned_user_name', dbType: 'id'},
            expected: false
        },{
            fieldDef: {name: 'name', type: 'name'},
            expected: true
        },{
            fieldDef: {name: 'modified_by_name', type: 'relate'},
            expected: true
        }], function(value) {
            it('should return proper boolean to indicate if the field shuold be available', function() {
                var actual = field.isFieldSupported(value.fieldDef, []);

                expect(actual).toEqual(value.expected);
            });
        });
    });

    describe('hasNoStudioSupport', function() {
        using('various studio settings', [{
            fieldDef: {name: 'deleted', type: 'bool'},
            expected: false
        },{
            fieldDef: {name: 'id', type: 'id', studio: false},
            expected: true
        },{
            fieldDef: {name: 'id', type: 'id', studio: 'false'},
            expected: true
        },{
            fieldDef: {name: 'id', type: 'id', studio: true},
            expected: false
        },{
            fieldDef: {name: 'id', type: 'id', studio: {listview: false}},
            expected: true
        },{
            fieldDef: {name: 'id', type: 'id', studio: {listview: 'false'}},
            expected: true
        },{
            fieldDef: {name: 'id', type: 'id', studio: {listview: true}},
            expected: false
        },{
            fieldDef: {name: 'id', type: 'widget', studio: false},
            expected: false
        },{
            fieldDef: {name: 'id', type: 'widget', studio: {listview: false}},
            expected: false
        }], function(value) {
            it('should return proper boolean to indicate if the field shuold be available by studio', function() {
                var actual = field.hasNoStudioSupport(value.fieldDef);

                expect(actual).toEqual(value.expected);
            });
        });
    });
});

