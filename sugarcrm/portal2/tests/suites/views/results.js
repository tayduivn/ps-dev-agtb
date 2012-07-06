describe("Results View", function() {
    var app, spyContextGet, context, ResultsView, view;

    beforeEach(function() {
        var controller;
        //SugarTest.app.config.env = "dev"; // so I can see app.data ;=)
        controller = SugarTest.loadFile('../../../../../sugarcrm/clients/base/views/results', 'results', 'js', function(d){ return d;});
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        context        = app.context.getContext();
        context.set('query', 'fubar');
        spyContextGet = sinon.spy(context, 'get');
        ResultsView = app.view.declareComponent('view', 'Results', null, controller);
        view = new ResultsView({context: context});
    });
    afterEach(function() {
        context.get.restore();
    });
    it("should initialize with custom meta", function() {
        expect(view.meta).toBeDefined();
    });
    it("should get last query from context", function() {
        // Prevent http request from going out
        var stub = sinon.stub(jQuery, 'ajax');
        view.render();
        stub.restore();
        expect(spyContextGet).toHaveBeenCalled();
        expect(spyContextGet.lastCall.args[0]).toEqual("query");
    });
    it("should call update collection and render subnav if search api returns records", function() {
        var stubFireSearch, stubUpdateCollection, stubRenderSubnav;

        stubFireSearch = sinon.stub(view, "fireSearchRequest", function(cb) {
            cb({next_offset: 1,
                    records: [{
                        id: "824ac1ce-8ef2-1c42-7d5e-4fc193416db2",
                        name: "System not responding"}]});
        });
        stubUpdateCollection = sinon.stub(view, 'updateCollection', function(d){});
        stubRenderSubnav     = sinon.stub(view, 'renderSubnav', function(){});

        view.render();
        expect(stubRenderSubnav).toHaveBeenCalled();
        expect(stubUpdateCollection).toHaveBeenCalled();
        expect(stubUpdateCollection.args[0][0].records[0].name).toEqual("System not responding");
    });
    it("should NOT call update collection but should call render subnav if search api returns no data", function() {
        var stubFireSearch, stubUpdateCollection, stubRenderSubnav;
        stubFireSearch = sinon.stub(view, "fireSearchRequest", function(cb) {
            cb({next_offset: -1, records: null});
        });
        stubUpdateCollection = sinon.stub(view, 'updateCollection', function(d){});
        stubRenderSubnav     = sinon.stub(view, 'renderSubnav', function(){});

        view.render();
        expect(stubRenderSubnav).toHaveBeenCalled();
        expect(stubRenderSubnav.args[0][0]).toMatch(/No.?results.*/i);
        expect(stubUpdateCollection).not.toHaveBeenCalled();
    });

    it("should use context's collection's next_offset when showMoreResults called", function() {
        var stubFireSearch, stubCtxGet, stubUpdateCollection, stubRenderSubnav, stubDismiss, stubShow, lContext, lView;

        // Create context with get that returns stubbed collection w/next_offset property
        lContext     = app.context.getContext();
        lContext.get = function() {};
        stubCtxGet   = sinon.stub(lContext, 'get', function() { return {next_offset:99};});
        lView        = new app.view.views.ResultsView({context: lContext});

        stubFireSearch = sinon.stub(lView, "fireSearchRequest", function(cb) {
            cb({});
        });
        stubUpdateCollection = sinon.stub(lView, 'updateCollection', function(d){});
        stubRenderSubnav     = sinon.stub(lView, 'renderSubnav', function(){});
        stubDismiss          = sinon.stub(app.alert, 'dismiss', function(){});
        stubShow             = sinon.stub(app.alert, 'show', function(){});

        lView.showMoreResults();
        expect(stubFireSearch.args[0][1]).toEqual(99);
    });
});
