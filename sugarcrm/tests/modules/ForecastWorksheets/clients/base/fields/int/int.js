/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

describe("ForecastWorksheets.Base.Field.Int", function () {

    var app, field, moduleName = 'ForecastWorksheets';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadPlugin('ClickToEdit')
        SugarTest.loadComponent('base', 'field', 'int');

        var fieldDef = {
            "name": "test_field",
            "type": "int",
            "len": 4
        };

        field = SugarTest.createField("base", "int", 'int', 'record', fieldDef, moduleName, null, null, true);
    });

    afterEach(function() {
        delete app.plugins.plugins['field']['ClickToEdit'];
        delete app.plugins.plugins['view']['CteTabbing'];
        field = null;
        app = null;
    });

    it('should have ClickToEdit Plugin', function() {
        expect(field.plugins).toContain('ClickToEdit');
    });

    describe('ClickToEdit fieldValueChanged', function() {
        var sandbox = sinon.sandbox.create();
        beforeEach(function() {
            field.value = '1';
        });
        afterEach(function() {
            field.value = undefined;
            sandbox.restore();
        });

        it('should return true', function() {
            sandbox.stub(field.$el, 'find', function() {
                return {
                    val: function() {
                        return '+1';
                    }
                }
            });
            expect(field.fieldValueChanged(field)).toBeTruthy();
        });

        it('should return false', function() {
            sandbox.stub(field.$el, 'find', function() {
                return {
                    val: function() {
                        return '1';
                    }
                }
            });
            expect(field.fieldValueChanged(field)).toBeFalsy();
        });
    });
});
