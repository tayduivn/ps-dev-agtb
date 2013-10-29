//FILE SUGARCRM flav=pro ONLY
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

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
});
