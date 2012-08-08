describe("sugarviews", function() {
    var view, app;
    beforeEach(function() {
        view = SugarTest.createView("base","Cases", "filter");
        view.model = new Backbone.Model();
        view.collection = new Backbone.Collection(view.model);
        view.collection.fields = _.keys(fixtures.metadata.modules.Cases.fields);
        app = SUGAR.App;
    });

    describe("filter", function() {
        it("should return a set of search fields for a given module", function() {
            var stub = sinon.stub(app.metadata, "getModule", function(){
                return fixtures.metadata.modules.Cases
            });
            expect(view.getSearchFields()).toEqual([fixtures.metadata.modules.Cases.fields.name.vname]);
            stub.restore();
        });
    });
});