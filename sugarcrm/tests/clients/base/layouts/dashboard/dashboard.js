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

describe("Base.Layout.Dashboard", function(){

    var app, layout;

    beforeEach(function() {
        app = SugarTest.app;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        layout.dispose();
        layout.context = null;
        layout = null;
    });

    describe("Home Dashboard", function() {

        beforeEach(function() {
            layout = SugarTest.createLayout("base", "Home", "dashboard");
        });

        it("should initialize dashboard model and collection", function() {
            var model = layout.context.get("model");
            expect(model.apiModule).toBe("Dashboards");
            var syncStuff = sinon.stub(app.api, 'records');
            layout.loadData();
            var expectedApiUrl = "Dashboards";
            expect(syncStuff).toHaveBeenCalledWith("read", expectedApiUrl);
            syncStuff.restore();


            syncStuff = sinon.stub(app.api, 'records');
            model.set("foo", "Blah");
            expectedApiUrl = "Dashboards";
            model.save();
            expect(syncStuff).toHaveBeenCalledWith("create", expectedApiUrl, {view: "", foo: "Blah"});
            syncStuff.restore();

            syncStuff = sinon.stub(app.api, 'records');
            model.set("id", "fake-id-value");
            expectedApiUrl = "Dashboards";
            model.save();
            expect(syncStuff).toHaveBeenCalledWith("update", expectedApiUrl);
            syncStuff.restore();
        });
    });

    describe("Module Dashboard", function() {
        var context, parentLayout, parentModule;
        beforeEach(function() {
            parentModule = "Tasks";
            context = app.context.getContext({
                module: parentModule,
                layout: "records"
            }),
            parentLayout = app.view.createLayout({
                name : "records",
                type: "records",
                module: "Accounts",
                context : context
            });
            layout = SugarTest.createLayout("base", "Home", "dashboard", null, parentLayout.context.getChildContext({
                module: "Home"
            }));
            parentLayout.addComponent(layout);
        });

        it("should initialize dashboard model and collection", function() {
            var model = layout.context.get("model"),
                expectedApiUrl;
            expect(model.apiModule).toBe("Dashboards");
            expect(model.dashboardModule).toBe(parentModule);
            var syncStuff = sinon.stub(app.api, 'records');
            layout.loadData();

            expectedApiUrl = "Dashboards/" + parentModule;
            expect(syncStuff).toHaveBeenCalledWith("read", expectedApiUrl);
            syncStuff.restore();

            syncStuff = sinon.stub(app.api, 'records');
            model.set("foo", "Blah");
            expectedApiUrl = "Dashboards/" + parentModule;
            model.save();
            expect(syncStuff).toHaveBeenCalledWith("create", expectedApiUrl, {view: "records", foo: "Blah"});
            syncStuff.restore();

            syncStuff = sinon.stub(app.api, 'records');
            model.set("id", "fake-id-value");
            expectedApiUrl = "Dashboards";
            model.save();
            expect(syncStuff).toHaveBeenCalledWith("update", expectedApiUrl);
            syncStuff.restore();
        });

        it("should navigate RHS panel without replacing document URL", function() {

            var syncStuff, expectedApiUrl;
            syncStuff = sinon.stub(app.api, 'records');
            layout.navigateLayout('new-fake-id-value');
            expectedApiUrl = "Dashboards";
            expect(syncStuff).toHaveBeenCalledWith("read", expectedApiUrl, {view: 'records', id: 'new-fake-id-value'});

            syncStuff.restore();
        });

        afterEach(function() {
            context.clear();
            parentLayout.dispose();
            parentLayout = null;
            parentModule = null;
        });
    });
});
