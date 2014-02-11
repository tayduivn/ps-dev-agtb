/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

describe("Base.Layout.Dashboard", function(){

    var app, layout;

    beforeEach(function() {
        app = SugarTest.app;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        layout.dispose();
        layout.context = null;
        layout = null;
    });

    describe("Home Dashboard", function() {

        var sandbox = sinon.sandbox.create();

        beforeEach(function() {
            layout = SugarTest.createLayout("base", "Home", "dashboard");
        });

        afterEach(function() {
            sandbox.restore();
        });

        it('should navigate to bwc dashboard', function() {
            layout.collection.models.push(layout.context.get("model"));
            sandbox.stub(layout, 'getLastStateKey', function() {
                return 'Home:last-visit:Home.';
            });
            sandbox.stub(app.user.lastState, 'get', function() {
                return '#bwc/index.php?module=Home&action=bwc_dashboard'
            });
            navSpy = sandbox.spy(app.router, 'navigate');

            layout.setDefaultDashboard();
            expect(navSpy).toHaveBeenCalledWith('#bwc/index.php?module=Home&action=bwc_dashboard', {trigger: true});
        });

        afterEach(function() {
            sandbox.restore();
        });

        it('should show help dashboard', function() {
            var collection = new Backbone.Collection();
            collection.add(new Backbone.Model({'dashboard_type' : 'help-dashboard', id: 'help-dash'}));
            collection.add(new Backbone.Model({'dashboard_type' : 'dashboard', id: 'normal-dash'}));

            sandbox.stub(app, 'navigate', function(context, id) {
            });

            layout.showHelpDashboard(collection);
            expect(app.navigate).toHaveBeenCalledWith(layout.context, collection.models[0]);
        });

        it('should hide help dashboard when another dashboard is present', function() {
            var collection = new Backbone.Collection();
            collection.add(new Backbone.Model({'dashboard_type' : 'help-dashboard', id: 'help-dash'}));
            collection.add(new Backbone.Model({'dashboard_type' : 'dashboard', id: 'normal-dash'}));

            sandbox.stub(app, 'navigate', function(context, id) {
            });

            layout.hideHelpDashboard(collection);
            expect(app.navigate).toHaveBeenCalledWith(layout.context, collection.models[1]);
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
            expect(syncStuff).toHaveBeenCalledWith("create", expectedApiUrl, {view_name: "", foo: "Blah"});
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
        var context, parentLayout, parentModule, sandbox = sinon.sandbox.create();;
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

        afterEach(function() {
            sandbox.restore();
        });

        it('should show help dashboard', function() {
            var collection = new Backbone.Collection();
            collection.add(new Backbone.Model({'dashboard_type' : 'help-dashboard', id: 'help-dash'}));
            collection.add(new Backbone.Model({'dashboard_type' : 'dashboard', id: 'normal-dash'}));

            sandbox.stub(layout, 'navigateLayout', function(id) {
            });

            layout.showHelpDashboard(collection);
            expect(layout.navigateLayout).toHaveBeenCalledWith('help-dash');
        });

        it('should hide help dashboard when another dashboard is present', function() {
            var collection = new Backbone.Collection();
            collection.add(new Backbone.Model({'dashboard_type' : 'help-dashboard', id: 'help-dash'}));
            collection.add(new Backbone.Model({'dashboard_type' : 'dashboard', id: 'normal-dash'}));

            sandbox.stub(layout, 'navigateLayout', function(id) {
            });

            layout.hideHelpDashboard(collection);
            expect(layout.navigateLayout).toHaveBeenCalledWith('normal-dash');
        });

        it('should hide the help dashboard and display list', function() {
            var collection = new Backbone.Collection();
            collection.add(new Backbone.Model({'dashboard_type' : 'help-dashboard', id: 'help-dash'}));

            sandbox.stub(layout, 'navigateLayout', function(id) {
            });

            layout.hideHelpDashboard(collection);
            expect(layout.navigateLayout).toHaveBeenCalledWith('list');
        });

        it("should initialize dashboard model and collection", function() {
            var model = layout.context.get("model"),
                expectedApiUrl;
            expect(model.apiModule).toBe("Dashboards");
            expect(model.dashboardModule).toBe(parentModule);
            sinon.collection.stub(layout.context.parent, 'isDataFetched', function() { return true; });
            var syncStub = sinon.stub(app.api, 'records');
            layout.loadData();

            expectedApiUrl = "Dashboards/" + parentModule;
            expect(syncStub).toHaveBeenCalledWith("read", expectedApiUrl);
            syncStub.restore();

            syncStub = sinon.stub(app.api, 'records');
            model.set("foo", "Blah");
            expectedApiUrl = "Dashboards/" + parentModule;
            model.save();
            expect(syncStub).toHaveBeenCalledWith("create", expectedApiUrl, {view_name: "records", foo: "Blah"});
            syncStub.restore();

            syncStub = sinon.stub(app.api, 'records');
            model.set("id", "fake-id-value");
            expectedApiUrl = "Dashboards";
            model.save();
            expect(syncStub).toHaveBeenCalledWith("update", expectedApiUrl);
            syncStub.restore();
        });

        it("should navigate RHS panel without replacing document URL", function() {
            var syncStub, expectedApiUrl;
            sinon.collection.stub(layout.context.parent, 'isDataFetched', function() { return true; });
            syncStub = sinon.stub(app.api, 'records');
            layout.navigateLayout('new-fake-id-value');
            expectedApiUrl = "Dashboards";
            expect(syncStub).toHaveBeenCalledWith("read", expectedApiUrl, {view_name: 'records', id: 'new-fake-id-value'});

            syncStub.restore();
        });

        afterEach(function() {
            sinon.collection.restore();
            context.clear();
            parentLayout.dispose();
            parentLayout = null;
            parentModule = null;
        });
    });
});
