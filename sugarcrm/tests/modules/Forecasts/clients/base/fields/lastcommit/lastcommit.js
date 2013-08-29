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

describe("Forecasts.Base.Field.Lastcommit", function() {
    var app, field, model, fieldDef = {}, moduleName = 'Forecasts';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadFile("../include/javascript/sugar7", "utils", "js", function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        sinon.stub(app.utils, 'getColumnVisFromKeyMap', function() {
            return true;
        });

        model = new Backbone.Model({
            'best_case': 100,
            'worst_case': 200
        });

        SugarTest.loadComponent('base', 'field', 'base');
        field = SugarTest.createField("base", "best_case", 'lastcommit', 'list', fieldDef, moduleName, model, null, true);
    });

    afterEach(function() {
        app.utils.getColumnVisFromKeyMap.restore();
        field = null;
        fieldDef = null;
        app = null;
    });
    describe('processDataPoints', function() {
        beforeEach(function() {
            field.points = ['best_case', 'worst_case'];
            sinon.stub(app.template, 'getField', function() {
                return function() {
                    return 'no_access';
                };
            });
        });

        afterEach(function() {
            field.points = [];
            app.template.getField.restore();
        });

        it('both point should have values', function() {
            var points = field.processDataPoints(model);
            expect(points[0].value).not.toBeUndefined();
            expect(points[0].error).toBeUndefined();
            expect(points[1].value).not.toBeUndefined();
            expect(points[1].error).toBeUndefined();
        });

        it('best_case should have error', function() {
            sinon.stub(app.user, 'getAcls', function() {
                return {
                    'ForecastWorksheets': {
                        'fields': {
                            'best_case': {
                                'read': 'no',
                                'write': 'no',
                                'create': 'no'
                            }
                        }
                    }
                };
            });
            var points = field.processDataPoints(model);
            // 0 is best_case
            expect(points[0].value).toBeUndefined();
            expect(points[0].error).not.toBeUndefined();
            expect(points[0].error).toEqual('no_access');
            // 1 is likely_case
            expect(points[1].value).not.toBeUndefined();
            expect(points[1].error).toBeUndefined();
            app.user.getAcls.restore();
        });
    });
});
