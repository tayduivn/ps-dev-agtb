/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

describe("Base.Layout.Dashboard", function() {

    var app, layout, apiStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'layout', 'default');
        apiStub = sinon.collection.stub(app.api, 'records', function(method, module, data, params, callbacks, options) {
           callbacks.success();
           callbacks.complete();
        });
        app.routing.start();
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        sinon.collection.restore();
        layout.dispose();
        layout.context = null;
        layout = null;
        app.routing.stop();
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
            var navSpy = sandbox.stub(app.router, 'navigate', function() {
            });

            layout.setDefaultDashboard();
            expect(navSpy).toHaveBeenCalledWith('#bwc/index.php?module=Home&action=bwc_dashboard', {trigger: true});
        });

        afterEach(function() {
            sandbox.restore();
        });

        it('should show help dashboard', function() {
            var collection = new Backbone.Collection();
            collection.add(new Backbone.Model({'dashboard_type': 'help-dashboard', id: 'help-dash'}));
            collection.add(new Backbone.Model({'dashboard_type': 'dashboard', id: 'normal-dash'}));

            sandbox.stub(app, 'navigate', function(context, id) {
            });

            layout.showHelpDashboard(collection);
            expect(app.navigate).toHaveBeenCalledWith(layout.context, collection.models[0]);
        });

        it('should hide help dashboard when another dashboard is present', function() {
            var collection = new Backbone.Collection();
            collection.add(new Backbone.Model({'dashboard_type': 'help-dashboard', id: 'help-dash'}));
            collection.add(new Backbone.Model({'dashboard_type': 'dashboard', id: 'normal-dash'}));

            sandbox.stub(app, 'navigate', function(context, id) {
            });

            layout.hideHelpDashboard(collection);
            expect(app.navigate).toHaveBeenCalledWith(layout.context, collection.models[1]);
        });

        it("should initialize dashboard model and collection", function() {
            var model = layout.context.get("model");
            expect(model.apiModule).toBe("Dashboards");
            layout.loadData();
            var expectedApiUrl = "Dashboards";
            expect(apiStub).toHaveBeenCalledWith('read', expectedApiUrl);
            apiStub.reset();

            model.set("foo", "Blah");
            expectedApiUrl = "Dashboards";
            model.save();
            expect(apiStub).toHaveBeenCalledWith('create', expectedApiUrl, {view_name: '', foo: 'Blah'});
            apiStub.reset();

            model.set("id", "fake-id-value");
            expectedApiUrl = "Dashboards";
            model.save();
            expect(apiStub).toHaveBeenCalledWith('update', expectedApiUrl);
        });
    });

    describe("Module Dashboard", function() {
        var context, parentLayout, parentModule, sandbox = sinon.sandbox.create();
        beforeEach(function() {
            parentModule = "Tasks";
            context = app.context.getContext({
                module: parentModule,
                layout: "records"
            }),
                parentLayout = app.view.createLayout({
                    name: "records",
                    type: "records",
                    module: "Accounts",
                    context: context
                });
            layout = SugarTest.createLayout("base", "Home", "dashboard", null, parentLayout.context.getChildContext({
                module: "Home"
            }));
            parentLayout.addComponent(layout);
        });

        afterEach(function() {
            sandbox.restore();
        });

        describe('openHelpDashboard', function() {
            var closestLayout, def;
            beforeEach(function() {
                layout.dashboardVisibleState = 'open';
                def = {
                    'components': [
                        {'layout': {'span': 4}},
                        {'layout': {'span': 8}}
                    ]
                };
                closestLayout = SugarTest.createLayout('base', null, 'default', def, null);
                sandbox.stub(layout, 'closestComponent', function() {
                    return closestLayout;
                });
                sandbox.stub(closestLayout, 'toggleSidePane');
                sandbox.stub(layout, 'navigateLayout', function(id) {
                });
                sandbox.stub(layout.collection, 'fetch');
            });

            afterEach(function() {
                layout.dashboardVisibleState = 'open';
                sandbox.restore();
            });

            it('will not toggle sidebar if already open', function() {
                layout.openHelpDashboard();
                expect(layout.closestComponent).not.toHaveBeenCalled();
                expect(closestLayout.toggleSidePane).not.toHaveBeenCalled();
            });

            it('will open sidebar if closed', function() {
                layout.dashboardVisibleState = 'close';
                layout.openHelpDashboard();
                expect(layout.closestComponent).toHaveBeenCalled();
                expect(closestLayout.toggleSidePane).toHaveBeenCalled();
            });

            it('will not call fetch when help is visible', function() {
                sandbox.stub(layout, 'isHelpDashboard').returns(true);
                layout.openHelpDashboard();
                expect(layout.collection.fetch).not.toHaveBeenCalled();
            });

            it('will call fetch help is not visible', function() {
                sandbox.stub(layout, 'isHelpDashboard').returns(false);
                layout.openHelpDashboard();
                expect(layout.collection.fetch).toHaveBeenCalled();
            });

            it('will only call "showHelpDashboard" if we are in the search results page', function() {
                sandbox.stub(layout, 'isHelpDashboard').returns(false);
                sandbox.stub(layout, 'isSearchContext').returns(true);
                sandbox.stub(layout, 'showHelpDashboard');
                layout.openHelpDashboard();

                expect(layout.showHelpDashboard).toHaveBeenCalled();
                expect(layout.collection.fetch).not.toHaveBeenCalled();
            });
        });

        describe('closeHelpDashboard', function() {
            beforeEach(function() {
                sandbox.stub(layout.collection, 'fetch');
                sandbox.stub(layout, 'hideHelpDashboard');
            });

            afterEach(function() {
                sandbox.restore();
            });

            it('will call "hideHelpDashboard" if we are in the search page', function() {
                sandbox.stub(layout, 'isHelpDashboard').returns(true);
                sandbox.stub(layout, 'isSearchContext').returns(true);
                layout.closeHelpDashboard();
                expect(layout.hideHelpDashboard).toHaveBeenCalled();
            });

            it('will call fetch when help is visible', function() {
                sandbox.stub(layout, 'isHelpDashboard').returns(true);

                layout.closeHelpDashboard();
                expect(layout.collection.fetch).toHaveBeenCalled();
            });

            it('will not call fetch help is not visible', function() {
                sandbox.stub(layout, 'isHelpDashboard').returns(false);

                layout.closeHelpDashboard();
                expect(layout.collection.fetch).not.toHaveBeenCalled();
            });
        });

        describe('showHelpDashboard', function() {
            var collection;
            beforeEach(function() {
                sandbox.stub(layout, 'navigateLayout');
                sandbox.stub(app, 'navigate');
                sandbox.spy(app.events, 'trigger');
                collection = new SUGAR.App.BeanCollection([
                    {'dashboard_type': 'help-dashboard', id: 'help-dash'},
                    {'dashboard_type': 'dashboard', id: 'normal-dash'}
                ]);
            });

            afterEach(function() {
                delete collection;
                sandbox.restore();
            });

            it('will call navigateLayout and trigger "app:help:shown"', function() {
                layout.showHelpDashboard(collection);
                expect(layout.navigateLayout).toHaveBeenCalledWith('help-dash');
                expect(app.events.trigger).toHaveBeenCalledWith('app:help:shown');
            });

            describe('when context does not have parent', function() {
                var parentContext;
                beforeEach(function() {
                    parentContext = layout.context.parent;
                    layout.context.parent = null;
                });

                afterEach(function() {
                    layout.context.parent = parentContext;
                });

                it('will not call app.navigate when module is not Home', function() {
                    layout.module = 'TestModule';
                    layout.showHelpDashboard(collection);
                    expect(app.navigate).not.toHaveBeenCalled();
                });

                it('will call app.navigate when module is Home', function() {
                    layout.module = 'Home';
                    layout.showHelpDashboard(collection);
                    expect(app.navigate).toHaveBeenCalled();
                });
            });
        });

        describe('hideHelpDashboard', function() {
            beforeEach(function() {
                sandbox.stub(layout, 'navigateLayout', function(id) {
                });
            });

            afterEach(function() {
                sandbox.restore();
            });

            it('will hide help dashboard when another dashboard is present', function() {
                var collection = new Backbone.Collection();
                collection.add(new Backbone.Model({'dashboard_type': 'help-dashboard', id: 'help-dash'}));
                collection.add(new Backbone.Model({'dashboard_type': 'dashboard', id: 'normal-dash'}));

                layout.hideHelpDashboard(collection);
                expect(layout.navigateLayout).toHaveBeenCalledWith('normal-dash');
            });

            it('will hide the help dashboard and display list', function() {
                var collection = new Backbone.Collection();
                collection.add(new Backbone.Model({'dashboard_type': 'help-dashboard', id: 'help-dash'}));

                layout.hideHelpDashboard(collection);
                expect(layout.navigateLayout).toHaveBeenCalledWith('list');
            });
        });

        describe('isHelpDashboard', function() {
            var ogType;
            beforeEach(function() {
                ogType = layout.model.get('dashboard_type');
            });

            afterEach(function() {
                layout.model.set('dashboard_type', ogType, {silent: true});
            });

            it('will return true', function() {
                layout.model.set('dashboard_type', 'help-dashboard', {silent: true});
                expect(layout.isHelpDashboard()).toBeTruthy();
            });

            it('will return false', function() {
                layout.model.set('dashboard_type', 'dashboard', {silent: true});
                expect(layout.isHelpDashboard()).toBeFalsy();
            });
        });

        describe('layout.model.sync event', function() {
            beforeEach(function() {
                sandbox.stub(app.events, 'trigger');
            });

            afterEach(function() {
                sandbox.restore();
            });

            describe('when on home module and help-dashboard visible', function() {
                var _renderCalled = false;
                beforeEach(function() {
                    layout.module = 'Home';
                    sandbox.stub(layout, 'isHelpDashboard').returns(true);

                    sandbox.stub(layout, 'getComponent', function() {
                        return {
                            getComponent: function() {
                                return {
                                    meta: {
                                        buttons: [],
                                        last_state: {}
                                    },
                                    render: function() {
                                        _renderCalled = true;
                                    }
                                };
                            }
                        };
                    });

                    sandbox.stub(app.metadata, 'getView', function() {
                        return {
                            buttons: [],
                            last_state: {}
                        };
                    });
                });

                it('should re-render the header pane', function() {
                    layout.model.trigger('sync');
                    expect(_renderCalled).toBeTruthy();
                });
            });

            describe('when sidebar is open', function() {
                var isHelpStub;
                beforeEach(function() {
                    layout.dashboardVisibleState = 'open';
                });

                afterEach(function() {
                    if (isHelpStub) {
                        isHelpStub.restore();
                    }
                });

                it('will trigger event when dashboard is help', function() {
                    isHelpStub = sandbox.stub(layout, 'isHelpDashboard').returns(true);
                    layout.model.trigger('sync');

                    expect(isHelpStub).toHaveBeenCalled();
                });

                it('will not trigger event when dashboard is not help', function() {
                    isHelpStub = sandbox.stub(layout, 'isHelpDashboard').returns(false);
                    layout.model.trigger('sync');

                    expect(isHelpStub).toHaveBeenCalled();
                    expect(app.events.trigger).not.toHaveBeenCalled();
                });
            });

            describe('when sidebar is closed', function() {
                var isHelpStub;
                beforeEach(function() {
                    layout.dashboardVisibleState = 'close';
                });

                afterEach(function() {
                    if (isHelpStub) {
                        isHelpStub.restore();
                    }
                });

                it('will not trigger event when dashboard is help', function() {
                    isHelpStub = sandbox.stub(layout, 'isHelpDashboard').returns(true);
                    layout.model.trigger('sync');

                    expect(isHelpStub).not.toHaveBeenCalled();
                    expect(app.events.trigger).not.toHaveBeenCalled();
                });

                it('will not trigger event when dashboard is not help', function() {
                    isHelpStub = sandbox.stub(layout, 'isHelpDashboard').returns(false);
                    layout.model.trigger('sync');

                    expect(isHelpStub).not.toHaveBeenCalled();
                    expect(app.events.trigger).not.toHaveBeenCalled();
                });
            });
        });

        it("should initialize dashboard model and collection", function() {
            var model = layout.context.get("model"),
                expectedApiUrl;
            expect(model.apiModule).toBe("Dashboards");
            expect(model.dashboardModule).toBe(parentModule);
            sinon.collection.stub(layout.context.parent, 'isDataFetched').returns(true);
            layout.loadData();

            expectedApiUrl = "Dashboards/" + parentModule;
            expect(apiStub).toHaveBeenCalledWith('read', expectedApiUrl);
            apiStub.reset();

            model.set("foo", "Blah");
            expectedApiUrl = "Dashboards/" + parentModule;
            model.save();
            expect(apiStub).toHaveBeenCalledWith("create", expectedApiUrl, {view_name: "records", foo: "Blah"});
            apiStub.reset();

            model.set("id", "fake-id-value");
            expectedApiUrl = "Dashboards";
            model.save();
            expect(apiStub).toHaveBeenCalledWith("update", expectedApiUrl);
        });

        it("should navigate RHS panel without replacing document URL", function() {
            sinon.collection.stub(layout.context.parent, 'isDataFetched').returns(true);
            layout.navigateLayout('new-fake-id-value');
            var expectedApiUrl = "Dashboards";
            expect(apiStub).toHaveBeenCalledWith("read", expectedApiUrl, {view_name: 'records', id: 'new-fake-id-value'});
        });

        afterEach(function() {
            context.clear();
            parentLayout.dispose();
            parentLayout = null;
            parentModule = null;
        });
    });

    describe('Custom Error Handlers', function() {
        var redirectStub, fragmentStub;

        beforeEach(function() {
            layout = SugarTest.createLayout('base', 'Home', 'dashboard');
            redirectStub = sinon.collection.stub(app.router, 'redirect');
            fragmentStub = sinon.collection.stub(Backbone.history, 'getFragment');
        });

        using('different routes', [
            {
                route: 'Home/test',
                redirectCalled: true
            },
            {
                route: 'test',
                redirectCalled: false
            }
        ], function(value) {
            it('should redirect depending on the route when handleNotFoundError is invoked', function() {
                fragmentStub.returns(value.route);
                layout.error.handleNotFoundError();
                expect(redirectStub.called).toBe(value.redirectCalled);
            });
        });

        it('should return false when handleValidationError is invoked', function() {
            var result = layout.error.handleValidationError();
            expect(result).toBe(false);
        });
    });

    describe('navigateLayout', function() {
        var _componentDef, parentModule, parentLayout, context;
        beforeEach(function() {

            parentModule = 'Tasks';
            context = app.context.getContext({
                module: parentModule,
                layout: 'records'
            });
            parentLayout = app.view.createLayout({
                name: 'records',
                type: 'records',
                module: 'Accounts',
                context: context
            });
            layout = SugarTest.createLayout('base', 'Home', 'dashboard', null,
                    parentLayout.context.getChildContext({
                        module: 'Home'
                    })
                );
            sinon.collection.stub(layout, 'dispose');
            parentLayout.addComponent(layout);
            sinon.collection.stub(layout.layout, 'render');
            sinon.collection.stub(layout.layout, '_addComponentsFromDef', function(def) {
                _componentDef = def;
            });
        });

        afterEach(function() {
            parentLayout.dispose();
        });

        it('will set type to dashboard when undefined', function() {
            layout.navigateLayout('hello-world');
            expect(_componentDef[0].layout.components[0].view).toEqual('dashboard-headerpane');
        });

        it('will set type to dashboard when not equal to dashboard or help-dashboard', function() {
            layout.navigateLayout('hello-world', 'test-dashboard');
            expect(_componentDef[0].layout.components[0].view).toEqual('dashboard-headerpane');
        });

        it('will set headerpane to "help-dashboard-headerpane" when type is "help-dashboard"', function() {
            sinon.collection.stub(app.metadata, 'getView').returns(true);
            layout.navigateLayout('hello-world', 'help-dashboard');
            expect(_componentDef[0].layout.components[0].view).toEqual('help-dashboard-headerpane');
        });

        it('will set headerpane to "search-help-dashboard-headerpane" when type is "help-dashboard" and we are in the search results page.', function() {
            sinon.collection.stub(app.metadata, 'getView').returns(true);
            sinon.collection.stub(layout, 'isSearchContext').returns(true);

            layout.navigateLayout('hello-world', 'help-dashboard');
            expect(_componentDef[0].layout.components[0].view).toEqual('search-help-dashboard-headerpane');
        });
    });

    describe('initComponents', function() {
        beforeEach(function() {
            layout = SugarTest.createLayout('base', 'Home', 'dashboard');
            sinon.collection.spy(layout.model, 'trigger');
            sinon.collection.stub(layout, 'isSearchContext').returns(true);
            sinon.collection.stub(layout, '_super');
        });

        it('should trigger "change:metadata" on the model if we are in the search results page', function() {
            layout.initComponents('hello-world');

            expect(layout.model.trigger).toHaveBeenCalledWith('change:metadata');
        });
    });

    describe('loadData in search results page', function() {
        beforeEach(function() {
            layout = SugarTest.createLayout('base', 'Home', 'dashboard');
            sinon.collection.spy(layout.model, 'trigger');
            sinon.collection.stub(layout, 'isSearchContext').returns(true);
            sinon.collection.stub(layout, '_getInitialDashboardMetadata');
            sinon.collection.stub(layout, 'navigateLayout');
        });

        it('should return if the model already has the metadata property', function() {
            layout.model.set('metadata', '');
            layout.loadData();
            expect(layout.navigateLayout).not.toHaveBeenCalled();
        });

        it('should set "skipFetch" and "currentDashboardIndex" in the context, and call "navigateLayout"', function() {
            layout.loadData();
            expect(layout.navigateLayout).toHaveBeenCalled();
            expect(layout.context.get('skipFetch')).toBe(true);
            expect(layout.context.get('currentDashboardIndex')).toEqual(2);
        });
    });
});
