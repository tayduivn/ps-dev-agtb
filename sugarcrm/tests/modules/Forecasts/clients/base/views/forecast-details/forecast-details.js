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

describe("forecast-details", function() {
    var app, view, cfg, result;

    beforeEach(function() {
        app = SugarTest.app;
        app.user.setPreference('decimal_precision', 2);
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecast-details", "forecast-details", "js", function(d) {return eval(d); });
    });

    afterEach(function() {
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

        it("should return url with module url param added", function() {
            result = view.getProjectedURL();
            // from url, split off the ? url param part module=Module
            // then split that by the = and take the right-hand side
            result = result.split('?')[1].split('=')[1];

            expect(result).toBe(expectedModule);
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
            view.resetModel();
            view.bindDataChange();

            // just setting any property on the model
            view.model.set({selectedTimePeriod: 'abc'});
            expect(loadDataStub).toHaveBeenCalled();
        });

        it("loadData() should be called on this.model change:selectedUser events", function() {
            view.resetModel();
            view.bindDataChange();

            // just setting any property on the model
            view.model.set({selectedUser: 'abc'});
            expect(loadDataStub).toHaveBeenCalled();
        });
    });

    describe("loadData()", function() {
        var apiCallStub;
        beforeEach(function() {
            apiCallStub = sinon.stub(app.api, 'call', function() {});
            view.forecastConfig = {
                show_worksheet_best: false,
                show_worksheet_likely: true,
                show_worksheet_worst: false
            };
            view.model = new Backbone.Model();
        });

        afterEach(function() {
            apiCallStub.restore();
        });

        it("app.api.call() should not be called if selectedTimePeriod is not set", function() {
            view.resetModel();
            view.loadData();
            expect(apiCallStub).not.toHaveBeenCalled();
        });
    });

    describe("handleNewDataFromServer()", function() {
        var dataFromServer;
        beforeEach(function() {
            dataFromServer = {};
            view.forecastConfig = {
                show_worksheet_best: false,
                show_worksheet_likely: true,
                show_worksheet_worst: false
            };
            view.model = new Backbone.Model();
            view.likelyTotal = -1;
            view.bestTotal = -1;
            view.worstTotal = -1;
            view.isForecastSetup = true;
            view.isForecastAdmin = false;
        });

        afterEach(function() {
            dataFromServer = null;
            view.likelyTotal = null;
            view.bestTotal = null;
            view.worstTotal = null;
            view.isForecastSetup = null;
            view.isForecastAdmin = null;
        });

        describe("Manager Test -- shouldRollup is true", function() {
            beforeEach(function() {
                view.shouldRollup = true;
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
                view.handleNewDataFromServer(dataFromServer);
                expect(view.likelyTotal).toBe(dataFromServer.likely_adjusted);
            });

            it("bestTotal set properly by best_adjusted", function() {
                view.resetModel();
                view.handleNewDataFromServer(dataFromServer);
                expect(view.bestTotal).toBe(dataFromServer.best_adjusted);
            });

            it("worstTotal set properly by worst_adjusted", function() {
                view.resetModel();
                view.handleNewDataFromServer(dataFromServer);
                expect(view.worstTotal).toBe(dataFromServer.worst_adjusted);
            });

            it("opportunities set properly by opportunities", function() {
                view.resetModel();
                view.handleNewDataFromServer(dataFromServer);
                expect(view.model.get('opportunities')).toBe(dataFromServer.opportunities);
            });

            it("closed_amount set properly by closed_amount", function() {
                view.resetModel();
                view.handleNewDataFromServer(dataFromServer);
                expect(view.model.get('closed_amount')).toBe(dataFromServer.closed_amount);
            });

            it("revenue set properly from pipeline_revenue", function() {
                view.resetModel();
                view.handleNewDataFromServer(dataFromServer);
                expect(view.model.get('revenue')).toBe(dataFromServer.pipeline_revenue);
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
                view.handleNewDataFromServer(dataFromServer);
                expect(view.likelyTotal).toBe(dataFromServer.amount);
            });

            it("bestTotal set properly by best_case", function() {
                view.resetModel();
                view.handleNewDataFromServer(dataFromServer);
                expect(view.bestTotal).toBe(dataFromServer.best_case);
            });

            it("worstTotal set properly by worst_case", function() {
                view.resetModel();
                view.handleNewDataFromServer(dataFromServer);
                expect(view.worstTotal).toBe(dataFromServer.worst_case);
            });

            it("closed_amount set properly by won_amount", function() {
                view.resetModel();
                view.handleNewDataFromServer(dataFromServer);
                expect(view.model.get('closed_amount')).toBe(dataFromServer.won_amount);
            });

            it("opportunities set properly - My data", function() {
                // Set user values
                app.user.set({id: 'itsMe'});
                view.selectedUser.id = 'itsMe';

                var opps = dataFromServer.total_opp_count - (dataFromServer.lost_count + dataFromServer.won_count);

                view.resetModel();
                view.handleNewDataFromServer(dataFromServer);
                expect(view.model.get('opportunities')).toBe(opps);
            });

            it("opportunities set properly - Another user's data", function() {
                // Set user values
                app.user.set({id: 'itsMe'});
                view.selectedUser.id = 'itsNotMe';

                var opps = dataFromServer.included_opp_count - dataFromServer.includedClosedCount;

                view.resetModel();
                view.handleNewDataFromServer(dataFromServer);
                expect(view.model.get('opportunities')).toBe(opps);
            });

            it("revenue set properly - My data", function() {
                // Set user values
                app.user.set({id: 'itsMe'});
                view.selectedUser.id = 'itsMe';

                var revenue = dataFromServer.overall_amount - (dataFromServer.lost_amount + dataFromServer.won_amount);

                view.resetModel();
                view.handleNewDataFromServer(dataFromServer);
                expect(view.model.get('revenue')).toBe(revenue);
            });

            it("revenue set properly - Another user's data", function() {
                // Set user values
                app.user.set({id: 'itsMe'});
                view.selectedUser.id = 'itsNotMe';

                var revenue = dataFromServer.amount - dataFromServer.includedClosedAmount;

                view.resetModel();
                view.handleNewDataFromServer(dataFromServer);
                expect(view.model.get('revenue')).toBe(revenue);
            });
        });

    });

    describe("getRowLabel()", function() {
        var appLangStub, caseVal, stageVal, caseStr, stageStr;
        beforeEach(function() {
            appLangStub = sinon.stub(app.lang, 'get', function(str) { return str; });
        });

        afterEach(function() {
            appLangStub.restore();
            caseVal = null;
            stageVal = null;
            caseStr = null;
            stageStr = null;
        });

        it("should say ABOVE and FROM", function() {
            caseVal = 100;
            stageVal = 50;
            caseStr = 'LIKELY';
            stageStr = 'CLOSED';
            result = view.getRowLabel(caseStr, stageStr, caseVal, stageVal);

            expect(result).toBe('LBL_DISTANCE_ABOVE_LIKELY_FROM_CLOSED');
        });

        it("should say LEFT and TO", function() {
            caseVal = 50;
            stageVal = 100;
            caseStr = 'LIKELY';
            stageStr = 'QUOTA';
            result = view.getRowLabel(caseStr, stageStr, caseVal, stageVal);

            expect(result).toBe('LBL_DISTANCE_LEFT_LIKELY_TO_QUOTA');
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

    describe("calculatePipelineSize()", function() {
        var likelyTotal, revenue;
        beforeEach(function() {
        });

        afterEach(function() {
            likelyTotal = null;
            revenue = null;
        });

        it("should return zero since likelyTotal is 0", function() {
            likelyTotal = 0;
            revenue = 50;

            result = view.calculatePipelineSize(likelyTotal, revenue);

            expect(result).toEqual(0);
        });

        it("should return an integer as the numbers divide evenly", function() {
            likelyTotal = 50;
            revenue = 100;

            result = view.calculatePipelineSize(likelyTotal, revenue);

            expect(result).toEqual(2);
        });

        it("should return a decimal truncated to 1 place", function() {
            likelyTotal = 168;
            revenue = 100;

            result = view.calculatePipelineSize(likelyTotal, revenue);

            expect(result).toEqual(0.6);
        });
    });

    describe("isManagerView()", function() {
        it("selectedUser is a manager and showOpps is undefined", function() {
            view.selectedUser.is_manager = true;

            result = view.isManagerView();

            expect(result).toBeTruthy();
        });

        it("selectedUser is a manager and showOpps is true", function() {
            view.selectedUser.is_manager = true;
            view.selectedUser.showOpps = true;

            result = view.isManagerView();

            expect(result).toBeFalsy();
        });

        it("selectedUser is a manager and showOpps is false", function() {
            view.selectedUser.is_manager = true;
            view.selectedUser.showOpps = false;

            result = view.isManagerView();

            expect(result).toBeTruthy();
        });

        it("selectedUser is not a manager and showOpps is undefined", function() {
            view.selectedUser.is_manager = false;

            result = view.isManagerView();

            expect(result).toBeFalsy();
        });
    });
});
- 
- 
