describe("Filter View", function() {

    var layout, view, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition("records", {}, "Filters");
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        layout = {trigger: function() {}, off: function() {}, on: function() {}};
        view = SugarTest.createView("base","Cases", "filter", null, null, false, layout);

        app = SUGAR.App;
    });


    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        view = null;
        layout = null;
    });


    describe("openPanel", function() {
        it("should have triggered filter:create:new", function() {
            var triggerSpy = sinon.spy(view.layout, "trigger");
            view.openPanel();
            expect(triggerSpy).toHaveBeenCalledWith("filter:create:new");
        });
    });

});
