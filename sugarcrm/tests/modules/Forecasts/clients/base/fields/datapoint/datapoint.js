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
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
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
            sandbox = sinon.sandbox.create();
            field = SugarTest.createField("base", "best_case", 'datapoint', 'list', fieldDef, moduleName, null, null, true);
        });

        afterEach(function() {
            sandbox.restore();
        });

        it('should return empty string', function() {
            sandbox.stub(app.math, 'isDifferentWithPrecision', function() {
                return false;
            });
            expect(field._getArrowIconColorClass(100.00, 100.00)).toEqual('');
        });

        it('should return up arrow class', function() {
            sandbox.stub(app.math, 'isDifferentWithPrecision', function() {
                return true;
            });
            expect(field._getArrowIconColorClass(100.10, 100.00)).toEqual(' icon-arrow-up font-green');
        });

        it('should return down arrow class', function() {
            sandbox.stub(app.math, 'isDifferentWithPrecision', function() {
                return true;
            });
            expect(field._getArrowIconColorClass(99.90, 100.00)).toEqual(' icon-arrow-down font-red');
        });
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
