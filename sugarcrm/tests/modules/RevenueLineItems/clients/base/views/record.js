//FILE SUGARCRM flav=pro ONLY
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
if (!(fixtures)) {
    var fixtures = {};
}
// Make this play nice if fixtures has already been defined for other tests
// so we dont overwrite data
if (!_.has(fixtures, 'metadata')) {
    fixtures.metadata = {};
}
fixtures.metadata.currencies = {
    "-99": {
        id: '-99',
        symbol: "$",
        conversion_rate: "1.0",
        iso4217: "USD"
    },
    //Because obviously everyone loves 1970's Jackson5 hits
    "abc123": {
        id: 'abc123',
        symbol: "€",
        conversion_rate: "0.9",
        iso4217: "EUR"
    }
}
describe("revenuelineitems_view_record", function() {
    var app, view, options;

    beforeEach(function() {
        options = {
            meta: {
                panels: [{
                    fields: [{
                        name: "commit_stage"
                    }]
                }]
            }
        };

        app = SugarTest.app;
        SugarTest.seedMetadata(true);
        app.user.setPreference('decimal_precision', 2);
        SugarTest.loadComponent('base', 'view', 'record');
        view = SugarTest.loadFile("../modules/RevenueLineItems/clients/base/views/record", "record", "js", function(d) { return eval(d); });
    });

    describe("initialization", function() {
        beforeEach(function() {
            sinon.stub(app.view, "invokeParent");

            sinon.stub(app.metadata, "getModule", function () {
                return {
                    is_setup: true,
                    buckets_dom: "commit_stage_binary_dom"
                }
            })
            sinon.stub(view, "_setupCommitStageField");

        });

        afterEach(function() {
            view._setupCommitStageField.restore();
            app.metadata.getModule.restore();
            app.view.invokeParent.restore();
        });

        it("should set up the commit_stage field for revenuelineitems", function () {
            view.initialize(options);
            expect(view._setupCommitStageField).toHaveBeenCalled();//With(options.meta.panels);
        });
    });

    describe("_setupCommitStageField method", function() {
        it("should remove the commit_stage field if forecasts is not setup", function() {
            sinon.stub(app.metadata, "getModule", function () {
                return {
                    is_setup: false
                }
            });
            view._setupCommitStageField(options.meta.panels);
            expect(options.meta.panels[0].fields).toEqual([]);
            app.metadata.getModule.restore();
        });

        it("should set the proper options on the commit_stage field if forecasts has been setup", function() {
            sinon.stub(app.metadata, "getModule", function () {
                return {
                    is_setup: true,
                    buckets_dom: "something_testable"
                }
            });
            view._setupCommitStageField(options.meta.panels);
            expect(options.meta.panels[0].fields[0].options).toEqual("something_testable");
            app.metadata.getModule.restore();
        });
    });

    describe("convertCurrencyFields", function() {
        beforeEach(function() {
            view.currencyFields = new Array('likely_case', 'best_case', 'worst_case');
            view.model = new Backbone.Model({likely_case: 25000, best_case: 30000, worst_case: 20000});
        });

        it("should convert fields from USD to our false Euro", function() {
            view.convertCurrencyFields("-99", "abc123");

            expect(view.model.get("likely_case")).toEqual(app.math.mul(app.math.div(25000,1.0),.9));
            expect(view.model.get("best_case")).toEqual(app.math.mul(app.math.div(30000,1.0),.9));
            expect(view.model.get("worst_case")).toEqual(app.math.mul(app.math.div(20000,1.0),.9));
        });

        it("should convert fields from Euro to our false USD", function() {
            view.convertCurrencyFields("abc123", "-99");

            expect(view.model.get("likely_case")).toEqual(app.math.mul(app.math.div(25000,.9), 1.0));
            expect(view.model.get("best_case")).toEqual(app.math.mul(app.math.div(30000,.9), 1.0));
            expect(view.model.get("worst_case")).toEqual(app.math.mul(app.math.div(20000,.9), 1.0));
        });

        it("should not convert anything", function() {
            view.convertCurrencyFields(jasmine.undefined, "abc123");

            expect(view.model.get("likely_case")).toEqual(25000);
            expect(view.model.get("best_case")).toEqual(30000);
            expect(view.model.get("worst_case")).toEqual(20000);
        });
    });

})
