describe("Filter View", function() {

    var layout, view, app, module = "Cases", prevEvents;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition("records", {}, "Filters");
        SugarTest.loadHandlebarsTemplate('filter', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'filter');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
        prevEvents = app.events;
        app.events = {trigger: function() {}, off: function() {}, on: function() {}};

        layout = {trigger: function() {}, off: function() {}, on: function() {}};
        view = SugarTest.createView("base", module, "filter", null, null, false, layout, false);
    });


    afterEach(function() {
        app.events = prevEvents;
        app.cache.cutAll();
        app.view.reset();
        view = null;
        layout = null;
    });


    describe("unit tests", function() {
        describe("addFilters", function() {
            it("should add a model to the filter collection", function() {
                var filter = app.data.createBean("Filters", {id: "guidguidguid"});
                // Stub out setFilter.
                var stub = sinon.stub(view, "setFilter");
                view.addFilter(filter);
                expect(view.filters.get("guidguidguid")).toBeTruthy();
                stub.restore();
            });
        });

        describe("setFilter", function() {
            it("should set a filter as the currently chosen filter", function() {
                var id = "guidguidguid",
                    filter = app.data.createBean("Filters", {id: id}),
                    stub = function() { return this; },
                    updateFilterListStub = sinon.stub(view, "updateFilterList");
                view.customFilterNode = {select2: stub, trigger: stub};
                // We're not testing addFilter, so we add it manually.
                view.filters.add(filter);

                view.setFilter(id);
                expect(view.currentFilter).toEqual(id);
                expect(updateFilterListStub).toHaveBeenCalledOnce();
            });
        });

        describe("getFilters", function() {
            var apiSpy, jQStub;

            beforeEach(function() {
                apiSpy = sinon.spy(app.api, "call");
                jQStub = sinon.stub($, "ajax");
            });

            afterEach(function() {
                apiSpy.restore();
                jQStub.restore();
            });

            it("fetches the list of filters for the current module", function() {
                view.getFilters();
                expect(apiSpy).toHaveBeenCalledOnce();
                expect(apiSpy).toHaveBeenCalledWithMatch("filter", /.*Filters\/filter.*/);
            });
        });

        describe("getPreviouslyUsedFilter", function() {
            var apiSpy, handleFilterSelectionStub;

            beforeEach(function() {
                apiSpy = sinon.spy(app.api, "call");
                handleFilterSelectionStub = sinon.stub(view, "handleFilterSelection");
                SugarTest.seedFakeServer();
            });

            afterEach(function() {
                apiSpy.restore();
                handleFilterSelectionStub.restore();
            });

            it("should call the correct URL", function() {
                view.getPreviouslyUsedFilter();
                expect(apiSpy).toHaveBeenCalledOnce();
                expect(apiSpy).toHaveBeenCalledWithMatch("read", /.*Filters\/Cases\/used/);
            });

            it("should add the previously used filter to the filter collection", function() {
                var filters = [{id: "foobar", filter_definition: {}}],
                    spy = sinon.spy(view.filters, "add");

                SugarTest.server.respondWith("GET", new RegExp(".*\/Filters\/Cases\/used"), [
                    200,
                    {"Content-Type": "application/json"},
                    JSON.stringify(filters)
                ]);

                view.getPreviouslyUsedFilter();
                SugarTest.server.respond();
                expect(spy).toHaveBeenCalledOnce();
                expect(spy).toHaveBeenCalledWith(filters);
                spy.reset();
            });

            it("should not add to the filter collection if a previously used filter is not returned", function() {
                var filters = [],
                    spy = sinon.spy(view.filters, "add");

                SugarTest.seedFakeServer();
                SugarTest.server.respondWith("GET", new RegExp(".*\/Filters\/Cases\/used"), [
                    200,
                    {"Content-Type": "application/json"},
                    JSON.stringify(filters)
                ]);
                view.getPreviouslyUsedFilter();
                SugarTest.server.respond();
                expect(spy).not.toHaveBeenCalled();
                spy.reset();
            });
        });

        describe("render", function() {
            var protoRenderStub, aclStub;

            beforeEach(function() {
                protoRenderStub = sinon.stub(app.view.Component.prototype, 'render');
                aclStub = sinon.stub(app.acl, "hasAccess");
            });

            afterEach(function() {
                protoRenderStub.restore();
                aclStub.restore();
            });

            it("should render if we have ACL access", function() {
                aclStub.returns(true);
                view.render();
                expect(protoRenderStub).toHaveBeenCalledOnce();
            });

            it("should not render if we don't have ACL access", function() {
                aclStub.returns(false);
                view.render();
                expect(protoRenderStub).not.toHaveBeenCalled();
            });
        });

        describe("updateFilterList", function() {
            var dollarStub, langStub, select2Spy;

            beforeEach(function() {
                var stub = function() { return this; };
                select2Spy = sinon.spy();
                langStub = sinon.stub(app.lang, 'get');
                dollarStub = sinon.stub(view, '$');
                dollarStub.returns({select2: select2Spy, trigger: stub, on: stub, off: stub});
            });

            afterEach(function() {
                dollarStub.restore();
                langStub.restore();
            });

            it("should populate the filter list with at least 'All Records' and 'Create'", function() {
                var firstArg;
                view.updateFilterList();
                firstArg = select2Spy.getCall(0).args[0];
                expect(firstArg.data.length).toEqual(2);
                expect(firstArg.data[0].id).toEqual("all_records");
                expect(firstArg.data[1].id).toEqual("create");
            });

            it("should add a filter if one exists", function() {
                var attrs = {id: "guidguidguid", name: "namenamename"},
                    firstArg, model = app.data.createBean("Filters", attrs);
                view.filters.add(model);
                view.updateFilterList();
                firstArg = select2Spy.getCall(0).args[0];
                expect(firstArg.data.length).toEqual(3);
                expect(firstArg.data[1].id).toEqual("guidguidguid");
            });

            it("should translate strings before inserting them into the list", function() {
                var firstArg;
                langStub.returns("foo");
                view.updateFilterList();
                firstArg = select2Spy.getCall(0).args[0];
                _.each(firstArg.data, function(v) {
                    expect(v.text).toEqual("foo");
                });
            });
        });

        describe("openPanel", function() {
            it("should have triggered filter:create:new", function() {
                var triggerSpy = sinon.spy(view.layout, "trigger");
                view.openPanel();
                expect(triggerSpy).toHaveBeenCalledWith("filter:create:new");
            });
        });

        describe("filterDataSetAndSearch", function() {
            xit("", function() {

            });
        });

        describe("bindDataChange", function() {
            it("should bind an event when filter collection is reset", function() {
                var stub = sinon.stub(view, 'updateFilterList');
                view.filters.reset();
                expect(stub).toHaveBeenCalledOnce();
                stub.restore();
            });
        });

        describe("_dispose", function() {
            it("should unregister on disposed (be dispose-safe)", function() {
                var renderStub = sinon.stub(view, 'render'),
                    disposeStub= sinon.stub(app.view.Component.prototype, '_dispose');

                view.filters = new Backbone.View();
                view.bindDataChange();
                view._dispose();
                // Trigger unregistered event and assert render not called
                view.filters.trigger("reset");
                expect(renderStub).not.toHaveBeenCalled();
                disposeStub.restore();
            });
        });
    });

    xdescribe("integration tests", function() {
        describe("records view (list)", function() {
            it("should set the module pill to the module name and disable it", function() {
                var viewSpy = sinon.spy(view, "updateModuleList"), select2;
                view.render();
                select2 = view.moduleFilterNode.data("select2");
                expect(viewSpy.calledOnce).toBeTruthy();
                expect(select2.enabled).toBeFalsy();
                expect(view.moduleFilterNode.val()).toEqual(module);
            });

            it("should get previously used filters for the module when rendering", function() {
                var mock = sinon.mock(view);
                mock.expects("getPreviouslyUsedFilter").once();
                view.render();
                mock.restore();
            });


            xit("should fetch the list of filtered records if a previously used filter exists", function() {
                var filters = [{id: "foobar", filter_definition: {"filter": [{ "$owner": "" }]}}],
                    spy = sinon.spy(app.events, "trigger");

                SugarTest.seedFakeServer();
                SugarTest.server.respondWith("GET", new RegExp(".*\/rest\/v10\/Filters\/Cases\/used"), [
                    200,
                    {"Content-Type": "application/json"},
                    JSON.stringify(filters)
                ]);

                view.render();
                expect(spy).toHaveBeenCalledWith("list:filter:fire", filters[0].filter_definition);

                SugarTest.server.respond();
            });

            xit("should fetch the list of all records if a previously used filter does not exist", function() {
                var filters = [],
                    spy = sinon.spy(app.events, "trigger");

                SugarTest.seedFakeServer();
                SugarTest.server.respondWith("GET", new RegExp(".*\/rest\/v10\/Filters\/Cases\/used"), [
                    200,
                    {"Content-Type": "application/json"},
                    JSON.stringify(filters)
                ]);

                view.render();
                expect(spy).toHaveBeenCalledWith("list:filter:fire", undefined);
            });

            xit("should get the list of filters for the given module", function() {

            });

            xit("should filter the list once a filter is chosen", function() {

            });

            xit("should set the previously used filter once a filter is chosen", function() {

            });

            xit("should clear the previously used filter once 'All Records' is chosen", function() {

            });
        });

        describe("record view (subpanel)", function() {

        });
    });

});
