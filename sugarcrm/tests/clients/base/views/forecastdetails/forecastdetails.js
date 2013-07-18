/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

describe("Base.View.Forecastdetails", function() {
    var app, view, cfg, result, sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        app.user.setPreference('decimal_precision', 2);

        sandbox = sinon.sandbox.create();
        sandbox.stub(app.metadata, 'getModule', function() {
            return {
                is_setup: 1
            }
        });
        sandbox.stub(app.utils, 'checkForecastConfig', function() {
            return true;
        });
        sandbox.stub(app.user, 'getAcls', function() {
            return {
                'Forecasts': {
                    admin: true
                }
            };
        });

        var context = app.context.getContext();
        context.set({
            module: 'Forecasts',
            model: new Backbone.Model()
        });
        context.parent = new Backbone.Model();
        context.parent.set('selectedUser', {id: 'test_user', is_manager: false});
        context.parent.set('selectedTimePeriod', 'test_timeperiod');
        context.parent.set('module', 'Forecasts');

        var meta = {
            config: false
        }
        view = SugarTest.createView('base', 'Forecasts', 'forecastdetails', meta, context, false, null, true);
    });

    afterEach(function() {
        sandbox.restore();
        cfg = null;
        result = null;
    });
    
    describe("setUpShowDetailsDataSet()", function() {
        beforeEach(function() {
            sinon.stub(app.metadata, 'getStrings', function() {
                return {
                    forecasts_options_dataset: {
                        best: "Best",
                        likely: "Likely",
                        worst: "Worst"
                    }
                }
            })
        });

        afterEach(function() {
            app.metadata.getStrings.restore();
        });

        describe("should return the proper object based on Forecasts config settings", function() {
            it("all show_worksheet_ settings false, detailsDataSet should be empty", function() {
                cfg = {
                    show_worksheet_best: false,
                    show_worksheet_likely: false,
                    show_worksheet_worst: false
                };
                result = view.setUpShowDetailsDataSet(cfg);
                expect(result).toEqual({});
            });
            it("one show_worksheet_ setting true, detailsDataSet should have one item in it", function() {
                cfg = {
                    show_worksheet_best: false,
                    show_worksheet_likely: true,
                    show_worksheet_worst: false
                };
                result = view.setUpShowDetailsDataSet(cfg);
                expect(result).toEqual({
                    likely : 'Likely'
                });
            });
            it("two show_worksheet_ setting true, detailsDataSet should have two items in it", function() {
                cfg = {
                    show_worksheet_best: true,
                    show_worksheet_likely: true,
                    show_worksheet_worst: false
                };
                result = view.setUpShowDetailsDataSet(cfg);
                expect(result).toEqual({
                    best: "Best",
                    likely: "Likely"
                });
            });
            it("three show_worksheet_ setting true, detailsDataSet should have three items in it", function() {
                cfg = {
                    show_worksheet_best: true,
                    show_worksheet_likely: true,
                    show_worksheet_worst: true
                };
                result = view.setUpShowDetailsDataSet(cfg);
                expect(result).toEqual({
                    best: "Best",
                    likely: "Likely",
                    worst: "Worst"
                });
            });
        });
    });

    describe("resetModel()", function() {
        beforeEach(function() {
            view.model = new Backbone.Model();
        });

        afterEach(function() {
            view.forecastConfig = null
        });

        it("should set show_details_likely from cfg settings", function() {
            view.forecastConfig = {
                show_worksheet_best: false,
                show_worksheet_likely: true,
                show_worksheet_worst: false
            };
            view.resetModel();
            expect(view.model.get('show_details_likely')).toBeTruthy();
        });

        it("should set show_details_best from cfg settings", function() {
            view.forecastConfig = {
                show_worksheet_best: true,
                show_worksheet_likely: false,
                show_worksheet_worst: false
            };
            view.resetModel();
            expect(view.model.get('show_details_best')).toBeTruthy();
        });

        it("should set show_details_worst from cfg settings", function() {
            view.forecastConfig = {
                show_worksheet_best: false,
                show_worksheet_likely: false,
                show_worksheet_worst: true
            };
            view.resetModel();
            expect(view.model.get('show_details_worst')).toBeTruthy();
        });

        it("should set isForecastSetup from view.isForecastSetup setting", function() {
            view.forecastConfig = {
                show_worksheet_best: false,
                show_worksheet_likely: true,
                show_worksheet_worst: false
            };
            view.isForecastSetup = true;
            view.resetModel();
            expect(view.model.get('isForecastSetup')).toBeTruthy();
        });

        it("should set isForecastAdmin from view.isForecastAdmin setting", function() {
            view.forecastConfig = {
                show_worksheet_best: false,
                show_worksheet_likely: true,
                show_worksheet_worst: false
            };
            view.isForecastAdmin = true;
            view.resetModel();
            expect(view.model.get('isForecastAdmin')).toBeTruthy();
        });
    });

    describe("getProjectedURL()", function() {
        var expectedTimePeriodId,
            expectedSelectedUserId,
            expectedModule,
            expectedEndpoint;

        beforeEach(function() {
            expectedTimePeriodId = 'yayTime';
            expectedSelectedUserId = 'yayUser';
            expectedModule = 'yayModule';
            expectedEndpoint = 'progressManager';

            view.shouldRollup = true;
            view.model = new Backbone.Model({
                selectedTimePeriod: expectedTimePeriodId
            });
            view.module = expectedModule;
            view.selectedUser = {
                id: expectedSelectedUserId
            };

        });

        afterEach(function() {
            expectedTimePeriodId = null;
            expectedSelectedUserId = null;
            expectedModule = null;
            expectedEndpoint = null;

            view.shouldRollup = null;
            view.model = null;
            view.module = null;
            view.selectedUser = null;
        });

        describe("url fragments in proper order for API call - manager", function() {
            beforeEach(function() {
                // get the url
                result = view.getProjectedURL();

                // split off the ?module=Module param at the end
                result = result.split('?')[0];

                // split by / to get individual url fragments
                result = result.split('/');
            });

            it("timeperiod id fragment", function() {
                expect(result[result.length - 3]).toBe(expectedTimePeriodId);
            });

            it("manager endpoint fragment", function() {
                expect(result[result.length - 2]).toBe(expectedEndpoint);
            });

            it("user id fragment", function() {
                expect(result[result.length - 1]).toBe(expectedSelectedUserId);
            });
        });

        describe("url fragments in proper order for API call - rep", function() {
            beforeEach(function() {
                // change expectedEndpoint for this one test
                expectedEndpoint = 'progressRep';

                view.shouldRollup = false;

                // get the url
                result = view.getProjectedURL();

                // split off the ?module=Module param at the end
                result = result.split('?')[0];

                // split by / to get individual url fragments
                result = result.split('/');
            });

            // Already tested other fragments, this is the only thing that changes
            it("rep endpoint fragment", function() {
                expect(result[result.length - 2]).toBe(expectedEndpoint);
            });
        });
    });

    describe("bindDataChange()", function() {
        var loadDataStub;
        beforeEach(function() {
            loadDataStub = sinon.stub(view, 'loadData', function() {});
            view.forecastConfig = {
                show_worksheet_best: false,
                show_worksheet_likely: true,
                show_worksheet_worst: false
            };
            view.model = new Backbone.Model();

        });

        afterEach(function() {
            loadDataStub.restore();
            view.forecastConfig = null;
        });

        it("loadData() should be called on this.model change:selectedTimePeriod events", function() {
            view.currentModule = 'Home';

            view.resetModel();
            view.bindDataChange();

            // just setting any property on the model
            view.model.set({selectedTimePeriod: 'abc'});
            expect(loadDataStub).toHaveBeenCalled();
        });

        it("loadData() should be called on this.model change:selectedUser events", function() {
            view.currentModule = 'Forecasts';
            view.context = app.context.getContext();
            view.context.parent = new Backbone.Model({selectedTimePeriod: '1'});

            view.resetModel();
            view.bindDataChange();

            // just setting any property on the model
            view.context.parent.set({selectedUser: 'abc'});
            expect(loadDataStub).toHaveBeenCalled();
        });
    });

    describe("loadData()", function() {
        var getUrlStub, getInitStub;
        beforeEach(function() {
            getUrlStub = sinon.stub(view, 'getProjectedURL', function() {});
            getInitStub = sinon.stub(view, 'getInitData', function() {});
            view.forecastConfig = {
                show_worksheet_best: false,
                show_worksheet_likely: true,
                show_worksheet_worst: false
            };
            view.model = new Backbone.Model();
        });

        afterEach(function() {
            getUrlStub.restore();
            getInitStub.restore();
        });

        it("getInitData should only be called once", function() {
            view.resetModel();
            view.initDataLoaded = true;
            view.loadData();
            expect(getInitStub).not.toHaveBeenCalled();
        });

        it("app.api.call() should not be called for the progress endpoint if selectedTimePeriod is not set", function() {
            view.resetModel();
            view.loadData();
            expect(getUrlStub).not.toHaveBeenCalled();
        });
    });

    describe("onDataChange()", function() {
        var dataFromServer;
        beforeEach(function() {
            dataFromServer = {};
            view.forecastConfig = {
                show_worksheet_best: false,
                show_worksheet_likely: true,
                show_worksheet_worst: false
            };
            view.model = new Backbone.Model();
            view.serverData = new Backbone.Model();
            view.likelyTotal = -1;
            view.bestTotal = -1;
            view.worstTotal = -1;
            view.isForecastSetup = true;
            view.isForecastAdmin = false;
            view.detailsMsgTpl = Handlebars.template;
        });

        afterEach(function() {
            dataFromServer = null;
            view.likelyTotal = null;
            view.bestTotal = null;
            view.worstTotal = null;
            view.isForecastSetup = null;
            view.isForecastAdmin = null;
            view.serverData = null;
        });

        describe("Manager Test -- shouldRollup is true", function() {
            beforeEach(function() {
                view.shouldRollup = true;
                view.currentModule = 'Forecasts';
                dataFromServer = {
                    best_adjusted: 1250.50,
                    best_case: 1000.00,
                    closed_amount: 0,
                    likely_adjusted: 650.50,
                    likely_case: 500.00,
                    opportunities: 4,
                    pipeline_revenue: 3000.00,
                    quota_amount: 5000.00,
                    timeperiod_id: "64ff8224-c6c9-04aa-7869-518ba6db2ea2",
                    user_id: "seed_jim_id",
                    worst_adjusted: 250.50,
                    worst_case: 200.00
                };
            });

            afterEach(function() {
                view.shouldRollup = null;
            });

            it("likelyTotal set properly by likely_adjusted", function() {
                view.resetModel();
                view.calculateData(view.mapAllTheThings(dataFromServer, false));
                expect(view.likelyTotal).toBe(dataFromServer.likely_adjusted);
            });

            it("bestTotal set properly by best_adjusted", function() {
                view.resetModel();
                view.calculateData(view.mapAllTheThings(dataFromServer, false));
                expect(view.bestTotal).toBe(dataFromServer.best_adjusted);
            });

            it("worstTotal set properly by worst_adjusted", function() {
                view.resetModel();
                view.calculateData(view.mapAllTheThings(dataFromServer, false));
                expect(view.worstTotal).toBe(dataFromServer.worst_adjusted);
            });

            it("opportunities set properly by opportunities", function() {
                view.resetModel();
                view.calculateData(view.mapAllTheThings(dataFromServer, false));
                expect(view.model.get('opportunities')).toBe(dataFromServer.opportunities);
            });

            it("closed_amount set properly by closed_amount", function() {
                view.resetModel();
                view.calculateData(view.mapAllTheThings(dataFromServer, false));
                expect(view.model.get('closed_amount')).toBe(dataFromServer.closed_amount);
            });
        });

        describe("Rep Test -- shouldRollup is false", function() {
            beforeEach(function() {
                view.shouldRollup = false;
                dataFromServer = {
                    amount: 500.00,
                    best_case: 1000.00,
                    includedClosedAmount: 250.00,
                    includedClosedCount: 4,
                    included_opp_count: 12,
                    lost_amount: 250.00,
                    lost_count: 1,
                    overall_amount: 1000.00,
                    overall_best: 900.00,
                    overall_worst: 200.00,
                    quota_amount: 5000.00,
                    timeperiod_id: "64ff8224-c6c9-04aa-7869-518ba6db2ea2",
                    total_opp_count: 10,
                    user_id: "seed_max_id",
                    won_amount: 300.00,
                    won_count: 5,
                    worst_case: 200.00
                };
            });

            afterEach(function() {
                view.shouldRollup = null;
            });

            it("likelyTotal set properly by amount", function() {
                view.resetModel();
                view.calculateData(view.mapAllTheThings(dataFromServer, false));
                expect(view.likelyTotal).toBe(dataFromServer.amount);
            });

            it("bestTotal set properly by best_case", function() {
                view.resetModel();
                view.calculateData(view.mapAllTheThings(dataFromServer, false));
                expect(view.bestTotal).toBe(dataFromServer.best_case);
            });

            it("worstTotal set properly by worst_case", function() {
                view.resetModel();
                view.calculateData(view.mapAllTheThings(dataFromServer, false));
                expect(view.worstTotal).toBe(dataFromServer.worst_case);
            });

            it("closed_amount set properly by won_amount", function() {
                view.resetModel();
                view.calculateData(view.mapAllTheThings(dataFromServer, false));
                expect(view.model.get('closed_amount')).toBe(dataFromServer.won_amount);
            });
        });
    });

    describe("getAbsDifference()", function() {
        var appCurrencyStub, caseVal, stageVal;
        beforeEach(function() {
            appCurrencyStub = sinon.stub(app.currency, 'formatAmountLocale', function(amt) { return '$' + amt; });
        });

        afterEach(function() {
            appCurrencyStub.restore();
            caseVal = null;
            stageVal = null;
        });

        it("negative values should return positive", function() {
            caseVal = 100;
            stageVal = 50;

            result = view.getAbsDifference(caseVal, stageVal);

            expect(result).toBe('$50');
        });

        it("positive values should return positive", function() {
            caseVal = 50;
            stageVal = 100;

            result = view.getAbsDifference(caseVal, stageVal);

            expect(result).toBe('$50');
        });
    });

    describe("getPercent()", function() {
        var caseVal, stageVal;
        beforeEach(function() {
        });

        afterEach(function() {
            caseVal = null;
            stageVal = null;
        });

        it("should return zero", function() {
            caseVal = 0;
            stageVal = 50;

            result = view.getPercent(caseVal, stageVal);

            expect(result).toBe('0%');
        });

        it("should return a whole number percent -- ratio less than 1", function() {
            caseVal = 50;
            stageVal = 100;

            result = view.getPercent(caseVal, stageVal);

            expect(result).toBe('50%');
        });

        it("should return a whole number percent -- ratio greater than 1", function() {
            caseVal = 150;
            stageVal = 100;

            result = view.getPercent(caseVal, stageVal);

            expect(result).toBe('150%');
        });
    });

    describe("isManagerView()", function() {
        it("selectedUser is a manager and showOpps is undefined", function() {
            view.selectedUser.isManager = true;
            view.currentModule = "Forecasts";

            result = view.isManagerView();

            expect(result).toBeTruthy();
        });

        it("selectedUser is a manager and showOpps is true", function() {
            view.selectedUser.isManager = true;
            view.selectedUser.showOpps = true;
            view.currentModule = "Forecasts";

            result = view.isManagerView();

            expect(result).toBeFalsy();
        });

        it("selectedUser is a manager and showOpps is false", function() {
            view.selectedUser.isManager = true;
            view.selectedUser.showOpps = false;
            view.currentModule = "Forecasts";

            result = view.isManagerView();

            expect(result).toBeTruthy();
        });

        it("selectedUser is a manager and but we aren't in Forecasts", function() {
            view.selectedUser.isManager = true;
            view.selectedUser.showOpps = false;
            view.currentModule = "Home";

            result = view.isManagerView();

            expect(result).toBeFalsy();
        });

        it("selectedUser is not a manager and showOpps is undefined", function() {
            view.selectedUser.isManager = false;
            view.currentModule = "Forecasts";
            result = view.isManagerView();

            expect(result).toBeFalsy();
        });
    });

    describe("mapAllTheThings()", function() {
        var fromModel, data;
        beforeEach(function() {
            data = {
                worst_case: 2,
                amount: 4,
                likely_case:5,
                best_case: 6,
                worst_adjusted: 22,
                likely_adjusted: 24,
                best_adjusted: 26
            }
        });

        afterEach(function() {
            view.shouldRollup = null;
            view.currentModule = null;
        });

        describe("with this.shouldRollup = true", function() {
            beforeEach(function() {
                view.shouldRollup = true;
            });

            describe("Updating from model change, fromModel = true", function() {
                beforeEach(function() {
                    fromModel = true;
                });

                it("should return the right likely value", function() {
                    result = view.mapAllTheThings(data, fromModel);
                    expect(result.likely).toEqual(data.likely_adjusted);
                });

            });

            describe("Updating from endpoint/server data, fromModel = false", function() {
                beforeEach(function() {
                    fromModel = false;
                });

                it("should return the right likely value", function() {
                    result = view.mapAllTheThings(data, fromModel);
                    expect(result.likely).toEqual(data.likely_adjusted);
                });
            });
        });

        describe("with this.shouldRollup = false", function() {
            beforeEach(function() {
                view.shouldRollup = false;
            });

            describe("Updating from model change, fromModel = true", function() {
                beforeEach(function() {
                    fromModel = true;
                });

                it("should return the right likely value", function() {
                    result = view.mapAllTheThings(data, fromModel);
                    expect(result.likely).toEqual(data.likely_case);
                });

            });

            describe("Updating from endpoint/server data, fromModel = false", function() {
                beforeEach(function() {
                    fromModel = false;
                });

                it("should return the right likely value", function() {
                    result = view.mapAllTheThings(data, fromModel);
                    expect(result.likely).toEqual(data.amount);
                });
            });
        });
    });

    describe("getDetailsForCase()", function() {
        var caseStr, caseValue, stageValue, closedAmt;
        beforeEach(function() {
            sinon.stub(app.lang, 'get', function(key) {
                return key;
            });
        });

        afterEach(function() {
            app.lang.get.restore();
            caseStr = null;
            caseValue = null;
            stageValue = null;
            closedAmt = null;
        });

        describe("when there is no data", function() {
            beforeEach(function() {
                caseStr = 'likely';
                caseValue = 0;
                stageValue = 0;
                closedAmt = 0;
            });

            it("should return the 'No Data' message for amount", function() {
                result = view.getDetailsForCase(caseStr, caseValue, stageValue, closedAmt);
                expect(result.amount).toBe('LBL_FORECAST_DETAILS_NO_DATA');
            });
        });

        describe("when likely has met quota", function() {
            beforeEach(function() {
                caseStr = 'likely';
                caseValue = 10;
                stageValue = 10;
                closedAmt = 0;
            });

            it("should return the 'Meeting Quota' message for amount", function() {
                result = view.getDetailsForCase(caseStr, caseValue, stageValue, closedAmt);
                expect(result.shortOrExceed).toBe('LBL_FORECAST_DETAILS_MEETING_QUOTA');
            });
        });

        describe("when likely is under quota", function() {
            beforeEach(function() {
                caseStr = 'likely';
                caseValue = 10;
                stageValue = 100;
                closedAmt = 50;
            });

            it("should return correct amount", function() {
                result = view.getDetailsForCase(caseStr, caseValue, stageValue, closedAmt);
                expect(result.amount).toBe('$10.00');
            });

            it("should return correct shortOrExceed", function() {
                result = view.getDetailsForCase(caseStr, caseValue, stageValue, closedAmt);
                expect(result.shortOrExceed).toBe('LBL_FORECAST_DETAILS_SHORT');
            });

            it("should return correct percent", function() {
                result = view.getDetailsForCase(caseStr, caseValue, stageValue, closedAmt);
                expect(result.percent).toBe('40%');
            });

            it("should return correct deficitAmount", function() {
                result = view.getDetailsForCase(caseStr, caseValue, stageValue, closedAmt);
                expect(result.deficitAmount).toBe('($40.00)');
            });

        });

        describe("when likely is over quota", function() {
            beforeEach(function() {
                caseStr = 'likely';
                caseValue = 115;
                stageValue = 100;
                closedAmt = 50;
            });

            it("should return correct amount", function() {
                result = view.getDetailsForCase(caseStr, caseValue, stageValue, closedAmt);
                expect(result.amount).toBe('$115.00');
            });

            it("should return correct shortOrExceed", function() {
                result = view.getDetailsForCase(caseStr, caseValue, stageValue, closedAmt);
                expect(result.shortOrExceed).toBe('LBL_FORECAST_DETAILS_EXCEED');
            });

            it("should return correct percent", function() {
                result = view.getDetailsForCase(caseStr, caseValue, stageValue, closedAmt);
                expect(result.percent).toBe('65%');
            });

            it("should return correct deficitAmount", function() {
                result = view.getDetailsForCase(caseStr, caseValue, stageValue, closedAmt);
                expect(result.deficitAmount).toBe('($65.00)');
            });
        });
    });
});
