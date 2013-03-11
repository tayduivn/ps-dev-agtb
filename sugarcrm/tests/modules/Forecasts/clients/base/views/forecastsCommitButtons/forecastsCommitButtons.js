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

describe("forecasts_view_forecastsCommitButtons", function(){

    var app, view, metaStub;

    beforeEach(function() {
        app = SugarTest.app;
        metaStub = sinon.stub(app.user, 'getAcls', function() {
            return {
                'Forecasts': {
                    admin: 'yes'
                }
            }
        });
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsCommitButtons", "forecastsCommitButtons", "js", function(d) { return eval(d); });
    });

    afterEach(function() {
        metaStub.restore();
        view = null;
        app = null;
    });

    describe("test showCommitButton", function() {
        beforeEach(function() {
            testMethodStub = sinon.stub(app.user, "get", function() {
                return 'a_user_id';
            });
        });

        afterEach(function(){
            testMethodStub.restore();
        });

        describe("should show commit button", function() {
            it("is a user viewing their own forecast log", function() {
                expect(view.checkShowCommitButton('a_user_id')).toBeTruthy();
            });
        });

        describe("should not show commit button", function() {
            it("is a user not viewing their own forecast log", function() {
                expect(view.checkShowCommitButton('a_different_user_id')).toBeFalsy();
            });
        });
    });

    describe("test showConfigButton", function() {
        var testStub;

        beforeEach(function() {
            testStub = sinon.stub(app.view.View.prototype, "initialize");
            view.context = new Backbone.Model({
                currentUser : {
                    admin: "yes"
                }
            });
        });
        afterEach(function() {
            testStub.restore();
            view.context = null;
        });

        it("variable should be true an admin", function() {
            var options = {};
            view.initialize(options);
            expect(view.showConfigButton).toBeTruthy();
        });

        it("variable should be false for a non-admin", function(){
            // resetting metaStub for just this one test
            metaStub.restore();
            metaStub = sinon.stub(app.user, 'getAcls', function() {
                return {
                    'Forecasts': {
                        admin: 'no'
                    }
                }
            });
            var options = {};
            view.initialize(options);
            expect(view.showConfigButton).toBeFalsy();
        });
    });

    describe("test triggerExport", function(){
        var confirmStub, runExportStub;

        beforeEach(function(){
            view.$el = {
                find: function(){
                    return {
                        length : 1,

                        hasClass:function(){
                            return false;
                        }
                    }
                }
            };
            view.context = {
                get: function(){return "worksheet"}
            };

            confirmStub = sinon.stub(window, 'confirm', function(){return true});
            runExportStub = sinon.stub(view, 'runExport', function(){});
        });

        afterEach(function(){
            confirmStub.restore();
            runExportStub.restore();
            view.$el = {};
            view.context = {};
        });

        it("should have triggered confirm box, ok, and export", function(){
            view.triggerExport();
            expect(window.confirm).toHaveBeenCalled();
            expect(view.runExport).toHaveBeenCalled();
        });
    });

    describe("Forecasts commitButtons bindings ", function(){
        beforeEach(function(){
            view.context = {
                on: function(){}
            };

            // we need the view.layout to be defined since we listen for an event from there now
            view.layout = {
                on : function() {}
            };

            sinon.spy(view.context, "on");
            view.bindDataChange();
        });

        afterEach(function(){
            view.context.on.restore();
            delete view.context;
            view.context = {};
        });

        it("context.on should have been called with selectedUser", function(){
            expect(view.context.on).toHaveBeenCalledWith("change:selectedUser");
        });

        it("context.on should have been called with change:selectedTimePeriod", function(){
            expect(view.context.on).toHaveBeenCalledWith("change:selectedTimePeriod");
        });

        it("context.on should have been called with forecasts:worksheet:reloadCommitButton", function(){
            expect(view.context.on).toHaveBeenCalledWith("forecasts:worksheet:reloadCommitButton");
        });

        it("context.on should have been called with forecasts:worksheetManager:reloadCommitButton", function(){
            expect(view.context.on).toHaveBeenCalledWith("forecasts:worksheetManager:reloadCommitButton");
        });

        it("context.on should have been called with forecasts:commitButtons:triggerCommit", function(){
            expect(view.context.on).toHaveBeenCalledWith("forecasts:commitButtons:triggerCommit");
        });

        it("context.on should have been called with forecasts:commitButtons:enabled", function(){
            expect(view.context.on).toHaveBeenCalledWith("forecasts:commitButtons:enabled");
        });

        it("context.on should have been called with forecasts:commitButtons:disabled", function(){
            expect(view.context.on).toHaveBeenCalledWith("forecasts:commitButtons:disabled");
        });
    });
});

