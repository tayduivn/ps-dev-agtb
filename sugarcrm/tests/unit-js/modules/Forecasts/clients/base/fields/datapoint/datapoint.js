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

describe("Forecasts.Base.Field.Datapoint", function() {
    var app, field, fieldDef, moduleName = 'Forecasts', sandbox;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadFile("../include/javascript/sugar7", "utils", "js", function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        sinon.stub(app.utils, 'getColumnVisFromKeyMap', function() { return true; });

        SugarTest.loadComponent('base', 'field', 'base');

        fieldDef = {
            name: 'best_case',
            total_field: 'best_case',
            type: 'datapoint'
        };

    });

    afterEach(function() {
        app.utils.getColumnVisFromKeyMap.restore();
        field = null;
        fieldDef = null;
        app = null;
    });

    it('should have dataAccess', function() {
        sinon.stub(app.template, 'getField', function() {
            return function() {};
        });
        field = SugarTest.createField("base", "best_case", 'datapoint', 'list', fieldDef, moduleName, null, null, true);
        expect(field.hasDataAccess).toBeTruthy();
        expect(app.template.getField).not.toHaveBeenCalled();
        app.template.getField.restore();
    });

    it('should not have dataAccess', function() {
        sinon.stub(app.user, 'getAcls', function() {
            return {
                'ForecastWorksheets': {
                    'fields': {
                        'best_case': {
                            'read' : 'no',
                            'write' : 'no',
                            'create': 'no'
                        }
                    }
                }
            };
        });
        sinon.stub(app.template, 'getField', function() {
            return function() {};
        });
        field = SugarTest.createField("base", "best_case", 'datapoint', 'list', fieldDef, moduleName, null, null, true);
        expect(field.hasDataAccess).toBeFalsy();
        expect(app.template.getField).toHaveBeenCalled();
        app.user.getAcls.restore();
        app.template.getField.restore();
    });

    describe("when checkIfNeedsCommit is called", function() {
        beforeEach(function() {
            sandbox = sinon.sandbox.create();
            field = SugarTest.createField("base", "best_case", 'datapoint', 'list', fieldDef, moduleName, null, null, true);
            sandbox.stub(field.context, "trigger", function(){});
        });

        afterEach(function() {
            sandbox.restore();
        });

        describe("when the totals are equal", function() {
            beforeEach(function() {
                field.total=0;
                field.initial_total=0;
                field.checkIfNeedsCommit();
            });
            it("should not trigger 'forecasts:worksheet:needs_commit'", function() {
                expect(field.context.trigger).not.toHaveBeenCalled();
            });
        });

        describe("when the totals are not equal", function() {
            beforeEach(function() {
                field.total=1;
                field.initial_total=0;
                field.checkIfNeedsCommit();
            });
            it("should trigger 'forecasts:worksheet:needs_commit'", function() {
                expect(field.context.trigger).toHaveBeenCalledWith('forecasts:worksheet:needs_commit');
            });
        });
    });

    describe('_getArrowIconColorClass', function() {

        beforeEach(function() {
            field = SugarTest.createField("base", "best_case", 'datapoint', 'list', fieldDef, moduleName, null, null, true);
        });

        using(
            'various string and number combinations where the new number and old number are equal',
            [
                {
                    newValue: '',
                    oldValue: ''
                },
                {
                    newValue: '123.00',
                    oldValue: '123.00'
                },
                {
                    newValue: '1234567890123456789012345678901234567890123456789012345678901234567890.00',
                    oldValue: '1234567890123456789012345678901234567890123456789012345678901234567890.00',
                },
                {
                    newValue: '123.00',
                    oldValue: 123.001
                },
                {
                    newValue: 123.00,
                    oldValue: '123.00'
                },
                {
                    newValue: 123.00,
                    oldValue: 123.00
                },
            ],
            function(data) {
                it('should return empty string', function() {
                    var result = field._getArrowIconColorClass(data.newValue, data.oldValue);
                    expect(result).toEqual('');
                });
            }
        );

        using(
            'various string and number combinations where the new number is higher',
            [
                {
                    newValue: '123.00',
                    oldValue: ''
                },
                {
                    newValue: '123.01',
                    oldValue: '123.00'
                },
                {
                    newValue: '1123.00',
                    oldValue: '123.00'
                },
                {
                    newValue: '1234567890123456789012345678901234567890123456789012345678901234567890.00',
                    oldValue: '123.00',
                },
                {
                    newValue: '123.01',
                    oldValue: 123.00
                },
                {
                    newValue: 1123.00,
                    oldValue: '123.00'
                },
                {
                    newValue: 123.01,
                    oldValue: 123.00
                }
            ], function(data) {
                it('should return up arrow class', function() {
                    var result = field._getArrowIconColorClass(data.newValue, data.oldValue);
                    expect(result).toEqual(' fa-arrow-up font-green');
                });
            }
        );

        using(
            'various string and number combinations where the new number is lower',
            [
                {
                    newValue: '',
                    oldValue: '123.00'
                },
                {
                    newValue: '123.00',
                    oldValue: '123.01'
                },
                {
                    newValue: '123.00',
                    oldValue: '1123.00'
                },
                {
                    newValue: '123.00',
                    oldValue: '1234567890123456789012345678901234567890123456789012345678901234567890.00',
                },
                {
                    newValue: '123.00',
                    oldValue: 123.01
                },
                {
                    newValue: 123.00,
                    oldValue: '1123.00'
                },
                {
                    newValue: 123.00,
                    oldValue: 123.01
                }
            ], function(data) {
                it('should return down arrow class', function() {
                    var result = field._getArrowIconColorClass(data.newValue, data.oldValue);
                    expect(result).toEqual(' fa-arrow-down font-red');
                });
            }
        );
    });

    describe('_onCommitCollectionReset after _onWorksheetTotals', function() {
        var sandbox = sinon.sandbox.create(), renderSpy;
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'best_case',
                'datapoint',
                'list',
                fieldDef,
                moduleName,
                null,
                null,
                true
            );
            renderSpy = sandbox.spy(field, 'render');
        });

        afterEach(function() {
            sandbox.restore();
        });

        it('should not set total to 0 when collection is empty', function() {
            field._onWorksheetTotals({
                best_adjusted: '500.00'
            }, 'manager');
            field._onCommitCollectionReset(new Backbone.Collection());

            expect(renderSpy).toHaveBeenCalled(1);
            expect(field.total).toEqual('500.00');
        });
        it('should set initial_total when collection is not empty', function() {
            field._onWorksheetTotals({
                best_adjusted: '500.00'
            }, 'manager');
            field._onCommitCollectionReset(new Backbone.Collection([
                {best_case: '500.00'}
            ]));

            expect(renderSpy).toHaveBeenCalled(2);
            expect(field.total).toEqual('500.00');
            expect(field.initial_total).toEqual('500.00');
        });
    });

    describe('_onWorksheetCommit', function() {
       var sandbox = sinon.sandbox.create(), renderSpy;
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'best_case',
                'datapoint',
                'list',
                fieldDef,
                moduleName,
                null,
                null,
                true
            );
            renderSpy = sandbox.spy(field, 'render');
        });

        afterEach(function() {
            sandbox.restore();
        });

        it('should set total and initial total to be equal and arrow to be empty', function() {
            field._onWorksheetCommit('manager', {
                best_adjusted: '500.00'
            });
            expect(field.total).toEqual(field.initial_total);
            expect(field.arrow).toEqual('');
            expect(renderSpy).toHaveBeenCalled(1);
        });
    });
});
