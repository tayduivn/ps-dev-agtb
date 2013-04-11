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

describe("forecasts_field_commitStage", function() {
    var field, fieldDef, context, model;

    beforeEach(function() {
        fieldDef = {
            "name": "commit_stage",
            "type": "commitStage",
            "options": "commit_stage_dom"
        };
        app = SugarTest.app;
        app.user.id = "tester";
        sinon.stub(app.lang, "getAppListStrings", function() {
            return {test: "test"};
        });
        context = app.context.getContext();
    });

    afterEach(function() {
        app.lang.getAppListStrings.restore();
        app.user.id = null;
        app = null;
    });

    describe("when buckets are set to show_binary", function() {
        beforeEach(function() {
            sinon.stub(app.metadata, "getModule", function() {
                return {
                    sales_stage_won: ["Closed Won"],
                    sales_stage_lost: ["Closed Lost"],
                    forecast_ranges: "show_binary"
                };
            });
        });

        afterEach(function() {
            app.metadata.getModule.restore();
            delete field.context;
        });

        describe("when it is your sheet and the sales_stage is open", function() {
            beforeEach(function() {
                context.get = function() {
                    return {id: "tester"};
                };
                model = new Backbone.Model({sales_stage: "Open"});
                field = SugarTest.createField("../modules/Forecasts/clients/base", "commitStage", "commitStage", "detail", fieldDef, "Forecasts", model, context);
            });

            it("should have def.view set to bool", function() {
                expect(field.def.view).toBe("bool");
            });

            it("should have a format function defined", function() {
                expect(field.format).toBeDefined();
            });

            it("should have an unformat function defined", function() {
                expect(field.unformat).toBeDefined();
            });

            it("should have disabled = false", function() {
                expect(field.disabled).toBeFalsy();
            });
        });

        describe("when it is your sheet and the sales_stage is closed", function() {
            beforeEach(function() {
                context.get = function() {
                    return {id: "tester"};
                };

                model = new Backbone.Model({sales_stage: "Closed Lost"});
                field = SugarTest.createField("../modules/Forecasts/clients/base", "commitStage", "commitStage", "detail", fieldDef, "Forecasts", model, context);
            });

            it("should have def.view set to bool", function() {
                expect(field.def.view).toBe("bool");
            });

            it("should have a format function defined", function() {
                expect(field.format).toBeDefined();
            });

            it("should have an unformat function defined", function() {
                expect(field.unformat).toBeDefined();
            });

            it("should have disabled = true", function() {
                expect(field.disabled).toBeTruthy();
            });
        });


        describe("when it is not your sheet and sales_stage is open", function() {
            beforeEach(function() {
                context.get = function() {
                    return {id: "tester2"};
                };

                model = new Backbone.Model({sales_stage: "Open"});
                field = SugarTest.createField("../modules/Forecasts/clients/base", "commitStage", "commitStage", "detail", fieldDef, "Forecasts", model, context);
            });
            it("should have def.view set to bool", function() {
                expect(field.def.view).toBe("bool");
            });

            it("should have a format function defined", function() {
                expect(field.format).toBeDefined();
            });

            it("should have an unformat function defined", function() {
                expect(field.unformat).toBeDefined();
            });

            it("should have disabled = true", function() {
                expect(field.disabled).toBeTruthy();
            });
        });

        describe("when it is not your sheet and sales stage is closed", function() {
            beforeEach(function() {
                context.get = function() {
                    return {id: "tester2"};
                };

                model = new Backbone.Model({sales_stage: "Closed Lost"});
                field = SugarTest.createField("../modules/Forecasts/clients/base", "commitStage", "commitStage", "detail", fieldDef, "Forecasts", model, context);
            });
            it("should have def.view set to bool", function() {
                expect(field.def.view).toBe("bool");
            });

            it("should have a format function defined", function() {
                expect(field.format).toBeDefined();
            });

            it("should have an unformat function defined", function() {
                expect(field.unformat).toBeDefined();
            });

            it("should have disabled = true", function() {
                expect(field.disabled).toBeTruthy();
            });
        });
    });

    describe("when buckets are set to show_custom_buckets", function() {
        beforeEach(function() {
            sinon.stub(app.metadata, "getModule", function() {
                return {
                    sales_stage_won: ["Closed Won"],
                    sales_stage_lost: ["Closed Lost"],
                    forecast_ranges: "show_custom_buckets",
                    buckets_dom: "commit_stage_custom_dom"
                };
            });
        });

        afterEach(function() {
            app.metadata.getModule.restore();
        });

        it("field should set this.def.options to commit_stage_custom_dom", function() {
            context.get = function() {
                return {id: "tester"};
            };

            model = new Backbone.Model({sales_stage: "Open"});
            field = SugarTest.createField("../modules/Forecasts/clients/base", "commitStage", "commitStage", "detail", fieldDef, "Forecasts", model, context);

            expect(field.def.options).toEqual('commit_stage_custom_dom');
        });
    });

    describe("when buckets are set to show_buckets", function() {
        beforeEach(function() {
            sinon.stub(app.metadata, "getModule", function() {
                return {
                    sales_stage_won: ["Closed Won"],
                    sales_stage_lost: ["Closed Lost"],
                    forecast_ranges: "show_buckets",
                    buckets_dom: "commit_stage_dom"
                };
            });
        });

        afterEach(function() {
            app.metadata.getModule.restore();
        });

        describe("when it is your sheet and sales_stage is Open", function() {
            beforeEach(function() {
                context.get = function() {
                    return {id: "tester"};
                };

                model = new Backbone.Model({sales_stage: "Open"});
                field = SugarTest.createField("../modules/Forecasts/clients/base", "commitStage", "commitStage", "detail", fieldDef, "Forecasts", model, context);
            });

            afterEach(function() {

            });

            it("should have def.options set to commit_stage_dom", function() {
                expect(field.def.options).toBe("commit_stage_dom");
            });

            it("should have def.view set to default", function() {
                expect(field.def.view).toBe("default");
            });

            it("should have called createBuckets", function() {
                expect($.data(document.body, "commitStageBuckets")).toBeTruthy();
            });

            //this should be called, but since things aren't stubbed out, it returns
            //undefined to replace the "" that was set up at the beginning of the test.
            it("should have called getLanguageValue", function() {
                expect(field.langValue).toBeUndefined();
            });

            it("should have field.showCteIcon defined", function() {
                expect(field.showCteIcon).toBeDefined();
            });

            it("should have field.hideCteIcon defined", function() {
                expect(field.hideCteIcon).toBeDefined();
            });

            it("should have disabled = false", function() {
                expect(field.disabled).toBeFalsy();
            });
        });

        describe("when it is your sheet and sales_stage is closed", function() {
            beforeEach(function() {
                context.get = function() {
                    return {id: "tester"};
                };

                model = new Backbone.Model({sales_stage: "Closed Lost"});
                field = SugarTest.createField("../modules/Forecasts/clients/base", "commitStage", "commitStage", "detail", fieldDef, "Forecasts", model, context);
            });

            afterEach(function() {

            });

            it("should have def.view set to default", function() {
                expect(field.def.view).toBe("default");
            });

            //this should be called, but since things aren't stubbed out, it returns
            //undefined to replace the "" that was set up at the beginning of the test.
            it("should have called getLanguageValue", function() {
                expect(field.langValue).toBeUndefined();
            });

            it("should have disabled = true", function() {
                expect(field.disabled).toBeTruthy();
            });
        });

        describe("when it is not your sheet and sales_stage is open", function() {
            beforeEach(function() {
                context.get = function() {
                    return {id: "tester2"};
                };

                model = new Backbone.Model({sales_stage: "Open"});
                field = SugarTest.createField("../modules/Forecasts/clients/base", "commitStage", "commitStage", "detail", fieldDef, "Forecasts", model, context);
            });

            afterEach(function() {

            });

            it("should have def.view set to default", function() {
                expect(field.def.view).toBe("default");
            });

            //this should be called, but since things aren't stubbed out, it returns
            //undefined to replace the "" that was set up at the beginning of the test.
            it("should have called getLanguageValue", function() {
                expect(field.langValue).toBeUndefined();
            });

            it("should have disabled = true", function() {
                expect(field.disabled).toBeTruthy();
            });
        });

        describe("when it is not your sheet and sales_stage is closed", function() {
            beforeEach(function() {
                context.get = function() {
                    return {id: "tester2"};
                };

                model = new Backbone.Model({sales_stage: "Closed Lost"});
                field = SugarTest.createField("../modules/Forecasts/clients/base", "commitStage", "commitStage", "detail", fieldDef, "Forecasts", model, context);
            });

            afterEach(function() {

            });

            it("should have def.view set to default", function() {
                expect(field.def.view).toBe("default");
            });

            //this should be called, but since things aren't stubbed out, it returns
            //undefined to replace the "" that was set up at the beginning of the test.
            it("should have called getLanguageValue", function() {
                expect(field.langValue).toBeUndefined();
            });

            it("should have disabled = true", function() {
                expect(field.disabled).toBeTruthy();
            });
        });

        describe("dispose safe", function() {
            beforeEach(function() {
                context.get = function() {
                    return {id: "tester"};
                };

                model = new Backbone.Model({sales_stage: "Closed Lost"});
                field = SugarTest.createField("../modules/Forecasts/clients/base", "commitStage", "commitStage", "detail", fieldDef, "Forecasts", model, context);
            });

            afterEach(function() {

            });

            it("should not render if disposed", function() {
                var renderStub = sinon.stub(field, 'render');

                field.resetBucket();
                expect(renderStub).toHaveBeenCalled();
                renderStub.reset();

                field.disposed = true;
                field.resetBucket();
                expect(renderStub).not.toHaveBeenCalled();

            });
        });
    });
});
