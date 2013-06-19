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

describe("forecasts_view_forecastsConfigWorksheetColumns", function(){
    var app, view;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsConfigWorksheetColumns", "forecastsConfigWorksheetColumns", "js", function(d) { return eval(d); });
    });

    afterEach(function() {
        view = null;
        app = null;
    });

    describe("addOption()", function() {
        var testFieldObj0,
            testFieldObj1,
            testFieldObj2,
            initObjects = [];

        beforeEach(function() {
            testFieldObj0= {
                id: 'test_field0',
                text: 'Test Field0',
                index: 0
            };
            testFieldObj1 = {
                id: 'test_field1',
                text: 'Test Field1',
                index: 1
            };
            testFieldObj2 = {
                id: 'test_field2',
                text: 'Test Field2',
                index: 2
            };
        });

        afterEach(function() {
            testFieldObj0 = null;
            testFieldObj1 = null;
            testFieldObj2 = null;
        });

        describe("option already exists", function() {
            beforeEach(function() {
                initObjects = [testFieldObj0, testFieldObj1, testFieldObj2];
                view.allOptions = initObjects;
                view.selectedOptions = initObjects;
                view.addOption(testFieldObj1);
            });

            it("should not add already existing option", function() {
                expect(view.allOptions).toContain(testFieldObj1);
                expect(view.selectedOptions).toContain(testFieldObj1);
            });
            it("should not be added again to the array", function() {
                expect(view.allOptions.length).toEqual(3);
                expect(view.selectedOptions.length).toEqual(3);
            });
        });

        describe("option does not exist", function() {
            beforeEach(function() {
                initObjects = [testFieldObj0, testFieldObj2];
                view.allOptions = initObjects;
                view.selectedOptions = initObjects;
                view.addOption(testFieldObj1);
            });

            it("should add option since it doesn't exist", function() {
                expect(view.allOptions).toContain(testFieldObj1);
                expect(view.selectedOptions).toContain(testFieldObj1);
            });
            it("should add option at correct array index", function() {
                expect(view.allOptions[testFieldObj1.index]).toEqual(testFieldObj1);
                expect(view.selectedOptions[testFieldObj1.index]).toEqual(testFieldObj1);
            });
        });
    });

    describe("removeOption()", function() {
        var testFieldObj0,
            testFieldObj1,
            testFieldObj2,
            initObjects = [];

        beforeEach(function() {
            testFieldObj0= {
                id: 'test_field0',
                text: 'Test Field0',
                index: 0
            };
            testFieldObj1 = {
                id: 'test_field1',
                text: 'Test Field1',
                index: 1
            };
            testFieldObj2 = {
                id: 'test_field2',
                text: 'Test Field2',
                index: 2
            };
        });

        afterEach(function() {
            testFieldObj0 = null;
            testFieldObj1 = null;
            testFieldObj2 = null;
        });

        describe("option already exists, remove it", function() {
            beforeEach(function() {
                initObjects = [testFieldObj0, testFieldObj1, testFieldObj2];
                view.allOptions = initObjects;
                view.selectedOptions = initObjects;
                view.removeOption(testFieldObj1);
            });

            it("should remove existing option", function() {
                expect(view.allOptions).not.toContain(testFieldObj1);
                expect(view.selectedOptions).not.toContain(testFieldObj1);
            });
        });

        describe("option does not exist, nothing should happen", function() {
            beforeEach(function() {
                initObjects = [testFieldObj0, testFieldObj2];
                view.allOptions = initObjects;
                view.selectedOptions = initObjects;
                view.removeOption(testFieldObj1);
            });

            it("should still not contain removed object", function() {
                expect(view.allOptions).not.toContain(testFieldObj1);
                expect(view.selectedOptions).not.toContain(testFieldObj1);
            });
            it("should not affect arrays", function() {
                expect(view.allOptions.length).toEqual(2);
                expect(view.selectedOptions.length).toEqual(2);
            });
        });
    });
});
