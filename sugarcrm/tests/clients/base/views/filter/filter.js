describe("Filter View", function() {

    var layout, view, app;

    beforeEach(function() {
        layout = {trigger: function() {}, off: function() {}, on: function() {}};

        view = SugarTest.createView("base","Cases", "filter", null, null, false, layout);
        view.model = new Backbone.Model();
        view.collection = new Backbone.Collection(view.model);
        view.collection.fields = _.keys(fixtures.metadata.modules.Cases.fields);

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
