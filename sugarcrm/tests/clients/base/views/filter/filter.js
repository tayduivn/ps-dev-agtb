describe("Filter View", function() {

    var layout, view, app, previousFilterStub, collectionFetchStub;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition("records", {}, "Filters");
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        SugarTest.loadComponent('base', 'view', 'filter');
        app = SugarTest.app;

        layout = {trigger: function() {}, off: function() {}, on: function() {}};
        previousFilterStub = sinon.stub(app.view.views.FilterView.prototype, "getPreviouslyUsedFilter",
            function(){
                this.currentFilter = "all_records";
                //this.filterDataSetAndSearch();
            });
        collectionFetchStub = sinon.stub(Backbone.Collection.prototype, "fetch",
            function(a, callback){
                if (callback && _.isFunction(callback.success))
                    callback.success();
            });
        view = SugarTest.createView("base","Cases", "filter", null, null, false, layout, false);


    });


    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        view = null;
        layout = null;
        previousFilterStub.restore();
        collectionFetchStub.restore();
    });


    describe("openPanel", function() {
        it("should have triggered filter:create:new", function() {
            var triggerSpy = sinon.spy(view.layout, "trigger");
            view.openPanel();
            expect(triggerSpy).toHaveBeenCalledWith("filter:create:new");
        });
    });

    describe("dispose safe", function() {
        it("should unregister on disposed", function() {
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
