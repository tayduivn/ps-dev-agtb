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
describe("The forecastCommitLog view", function(){
    var app, view, formatAmountLocaleStub, createHistoryLogStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile("../sidecar/src/utils", "currency", "js", function(d) { return eval(d); });
        SugarTest.loadFile("../sidecar/src/utils", "date", "js", function(d) { return eval(d); });
        SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "ForecastsUtils", "js", function(d) { return eval(d); });


        context = app.context.getContext();
        context.set({"selectedUser": {"id": 'test_user'}});
        context.set({"timeperiod_id": {"id": 'timeperiod_id'}});
        context.set({"collection": new Backbone.Collection()});


        app.initData = {};
        app.defaultSelections = {
            timeperiod_id: {},
            group_by: {},
            selectedUser: {}
        };

        var layout = {
            getComponent : function(){
                return {}
            }
        };

        view = SugarTest.createView("base", "Forecasts", "forecastsCommitLog", null, context, true, layout);
    });

    describe("test buildForecastsCommitted function", function() {
        beforeEach(function(){
            var model1 = new Backbone.Model({date_entered:"2012-12-05T11:14:25-04:00", best_case : 100, likely_case : 90, base_rate : 1 }),
                model2 = new Backbone.Model({date_entered:"2012-10-05T11:14:25-04:00", best_case : 110, likely_case : 100, base_rate : 1 }),
                model3 = new Backbone.Model({date_entered:"2012-11-05T11:14:25-04:00", best_case : 120, likely_case : 110, base_rate : 1 });
            view.collection = new Backbone.Collection([model1, model2, model3]);

            formatAmountLocaleStub = sinon.stub(app.currency, "formatAmountLocale", function(value) {
                return value;
            });

            createHistoryLogStub = sinon.stub(app.utils, "createHistoryLog", function(model, previousModel) {
                return "createHistoryLog";
            });
        });

        afterEach(function() {
            formatAmountLocaleStub.restore();
            createHistoryLogStub.restore();
            // empty out the collection
            view.collection = new Backbone.Collection([]);
        });

        it("should have an empty historyLog", function() {
            view.collection = new Backbone.Collection([]);

            view.buildForecastsCommitted();
            expect(_.isEmpty(view.historyLog)).toBeTruthy();
        });

        it("should create one historyLog entry", function() {
            var model1 = new Backbone.Model({date_entered:"2012-12-05T11:14:25-04:00", best_case : 100, likely_case : 90, base_rate : 1 });
            view.collection = new Backbone.Collection([model1]);

            view.buildForecastsCommitted();
            expect(view.historyLog.length == 1).toBeTruthy();
        });

        it("should create three historyLog entries", function() {
            view.buildForecastsCommitted();
            expect(view.historyLog.length == 3).toBeTruthy();
        });

        describe("previousDateEntered should", function(){
            var userStub;

            beforeEach(function(){
                userStub = sinon.stub(app.user, "get", function(type){
                   switch(type) {
                       case "datepref":
                           return "m/d/Y";
                           break;
                       case "timepref":
                           return "h:m:s";
                           break;
                   }
                });
            });

            afterEach(function(){
                userStub.restore();
            });

            it("be set", function(){
                view.buildForecastsCommitted();
                expect(_.isEmpty(view.previousDateEntered)).toBeFalsy()
            });

            it("be in the user format", function(){
                view.buildForecastsCommitted();
                var testDate = new Date('2012-12-05T11:14:25-04:00'),
                    dateFormatted = app.date.format(testDate,app.user.getPreference('datepref') + ' ' +  app.user.getPreference('timepref'));
                expect(view.previousDateEntered).toEqual(dateFormatted);
            });
        })
    });
    
    describe("Forecasts Commit Log Bindings ", function(){
        beforeEach(function(){
            sinon.stub(view.collection, "on");
            view.bindDataChange();
        });
        
        afterEach(function(){
            view.collection.on.restore();
        });
        
        //bindDataChange redefines this.collection to be context.committed
        it("collection.on should have been called with 'reset change'", function(){
            expect(view.collection.on).toHaveBeenCalledWith("reset change");
        });               
    });
    
    describe("Forecasts Commit Log Reset (resetCommittedLog)", function(){
        beforeEach(function(){
            view.context = {
                forecasts:{
                    on: function(event, fcn){},
                    committed:{
                        on:  function(event, fcn){}
                    }
                }
            };
            
            view.buildForecastsCommitted();
        });
        
        afterEach(function(){
            delete view.context;
            delete view.collection;
            view.context = {};
            view.collection = {};
        });
        
        it("bestCase should equal ''", function(){
            expect(view.bestCase).toEqual("");
        });
        it("likelyCase should equal ''", function(){
            expect(view.likelyCase).toEqual("");
        });
        it("worstCase should equal ''", function(){
            expect(view.worstCase).toEqual("");
        });
        it("previousBestCase should equal ''", function(){
            expect(view.previousBestCase).toEqual("");
        });
        it("previousLikelyCase should equal ''", function(){
            expect(view.previousLikelyCase).toEqual("");
        });
        it("previousWorstCase should equal ''", function(){
            expect(view.previousWorstCase).toEqual("");
        });
        it("showHistoryLog should equal ''", function(){
            expect(view.showHistoryLog).toBeFalsy();
        });
        it("previousDateEntered should equal ''", function(){
            expect(view.previousDateEntered).toEqual("");
        });
    });

    describe("dispose safe", function() {
        it("should not render if disposed", function() {
            var renderStub = sinon.stub(view, 'render');

            view.buildForecastsCommitted();
            expect(renderStub).toHaveBeenCalled();
            renderStub.reset();

            view.disposed = true;
            view.buildForecastsCommitted();
            expect(renderStub).not.toHaveBeenCalled();
        });
    });
});
