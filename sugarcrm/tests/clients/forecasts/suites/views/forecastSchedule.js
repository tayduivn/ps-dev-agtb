//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

describe("The expected opportunities view tests", function(){

    var app, view, context, dm, metadata;

    beforeEach(function() {
        app = SugarTest.app;
        dm = app.data;
        metadata = fixtures.metadata;
        metadata.modules.ForecastSchedule = {
            fields: {
                   id: {
                       name: "id",
                       vname: "LB_FS_KEY",
                       type: "id",
                       required: true,
                       reportable: false,
                       comment: "Unique identifier"
                   },
                   timeperiod_id: {
                       name: "timeperiod_id",
                       vname: "LBL_FS_TIMEPERIOD_ID",
                       type: "id",
                       reportable: false,
                       comment: "ID of the associated time period for this forecast schedule"
                   },
                   user_id: {
                       name: "user_id",
                       vname: "LBL_FS_USER_ID",
                       type: "id",
                       reportable: false,
                       comment: "User to which this forecast schedule pertains"
                   },
                   cascade_hierarchy: {
                       name: "cascade_hierarchy",
                       vname: "LBL_FS_CASCADE",
                       type: "bool",
                       comment: "Flag indicating if a forecast for a manager is propagated to his reports"
                   },
                   forecast_start_date: {
                       name: "forecast_start_date",
                       vname: "LBL_FS_FORECAST_START_DATE",
                       type: "date",
                       comment: "Starting date for this forecast"
                   },
                   status: {
                       name: "status",
                       vname: "LBL_FS_STATUS",
                       type: "enum",
                       len: 100,
                       options: "forecast_schedule_status_dom",
                       comment: "Status of this forecast"
                   },
                   created_by: {
                       name: "created_by",
                       vname: "LBL_FS_CREATED_BY",
                       type: "varchar",
                       len: "36",
                       comment: "User name who created record"
                   },
                   date_entered: {
                       name: "date_entered",
                       vname: "LBL_FS_DATE_ENTERED",
                       type: "datetime",
                       comment: "Date record created"
                   },
                   date_modified: {
                       name: "date_modified",
                       vname: "LBL_FS_DATE_MODIFIED",
                       type: "datetime",
                       comment: "Date record modified"
                   },
                   deleted: {
                       name: "deleted",
                       vname: "LBL_FS_DELETED",
                       type: "bool",
                       reportable: false,
                       comment: "Record deletion indicator"
                   },
                   expected_best_case: {
                       name: "expected_best_case",
                       vname: "LBL_EXPECTED_BEST_CASE",
                       dbType: "decimal",
                       type: "currency",
                       len: "26,6"
                   },
                   expected_likely_case: {
                       name: "expected_likely_case",
                       vname: "LBL_EXPECTED_LIKELY_CASE",
                       dbType: "decimal",
                       type: "currency",
                       len: "26,6"
                   },
                   expected_worst_case: {
                       name: "expected_worst_case",
                       vname: "LBL_EXPECTED_WORST_CASE",
                       dbType: "decimal",
                       type: "currency",
                       len: "26,6"
                   },
                   expected_amount: {
                       name: "expected_amount",
                       vname: "LBL_EXPECTED_AMOUNT",
                       dbType: "decimal",
                       type: "currency",
                       len: "26,6"
                   },
                   include_expected: {
                       name: "include_expected",
                       vname: "LBL_INCLUDE_EXPECTED",
                       type: "bool",
                       default: "0"
                   },
                   expected_commit_stage : {
                       name: "expected_commit_stage",
                       type: "enum",
                       options: "commit_stage_dom",
                       label: "LBL_FORECAST",
                       default: 0,
                       enabled: 0
                   }                
            },
            views: [ ],
            layouts: [ ],
            _hash: "af8d404a4f9961ad14409e92b755a0e6"
        };
        dm.reset();
        view = SugarTest.loadFile("../modules/Forecasts/metadata/base/views", "forecastSchedule", "js", function(d) { return eval(d); });
    });

    afterEach(function() {
        app.events.off("data:sync:start data:sync:end");
    });

    describe("test change:include_expected updates the model", function() {

        it("should update the model on change:include_expected", function() {
            var moduleName = "ForecastSchedule", bean, collection;
            dm.declareModel(moduleName, metadata.modules[moduleName]);
            forecastSchedule = dm.createBean(moduleName, {  id: "xyz", include_expected : 0, expected_commit_stage : 50, expected_amount : 100, expected_best_case : 100, expected_likely_case : 100 });
            forecastSchedule.hasChanged = function (attr) { return true; };
            forecastSchedule.save = function() { this.set('include_expected', '1'); };

            var collection = new Backbone.Collection([forecastSchedule]);
            view._collection = collection;

            context = { forecasts : {
                            forecastschedule : collection,
                            on : function() {}
                      }
            };

            view.context = context;
            view.bindDataChange();
            forecastSchedule.set('include_expected', "1");
            view._collection.trigger("change:include_expected");
            expect(forecastSchedule.get("include_expected")).toEqual("1");
        });
    });


    describe("test change:expected_commit_stage updates the model", function() {

        it("should update the model on change:expected_commit_stage", function() {
            var moduleName = "ForecastSchedule", bean, collection;
            dm.declareModel(moduleName, metadata.modules[moduleName]);
            forecastSchedule = dm.createBean(moduleName, {  id: "xyz", include_expected : 0, expected_commit_stage : 50, expected_amount : 100, expected_best_case : 100, expected_likely_case : 100 });
            forecastSchedule.hasChanged = function (attr) { return true; };
            forecastSchedule.save = function() { this.set('expected_commit_stage', '100'); };

            var collection = new Backbone.Collection([forecastSchedule]);
            view._collection = collection;

            context = { forecasts : {
                            forecastschedule : collection,
                            on : function() {}
                      }
            };

            view.context = context;
            view.bindDataChange();
            forecastSchedule.set('expected_commit_stage', "100");
            view._collection.trigger("change:expected_commit_stage");
            expect(forecastSchedule.get("expected_commit_stage")).toEqual("100");
            expect(forecastSchedule.get("include_expected")).toEqual("1");
        });
    });


    describe("forecast column", function() {
        beforeEach(function(){
            field = [
                {
                    name: 'include_expected',
                    enabled: true
                },
                {
                    name: 'expected_commit_stage',
                    enabled: true
                }
            ]
        });

        it("should be the 'include_expected' field if show_buckets is false", function() {
            app.config.show_buckets = false;
            var unused = view._setForecastColumn(field);
            expect(unused).toEqual(field[1]);
            expect(field[0].enabled).toBeTruthy();
            expect(field[1].enabled).toBeFalsy();
        });

        it("should be the 'expected_commit_stage' field if show_buckets is true", function() {
            app.config.show_buckets = true;
            var unused = view._setForecastColumn(field);
            expect(unused).toEqual(field[0]);
            expect(field[0].enabled).toBeFalsy();
            expect(field[1].enabled).toBeTruthy();
        });

    });

});
