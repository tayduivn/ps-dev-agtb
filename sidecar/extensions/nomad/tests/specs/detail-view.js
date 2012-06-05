describe ("Detail View", function () {
    var app, context;

    beforeEach(function() {
        //SugarTest.seedMetadata(false);
        app = SugarTest.app;
        app.metadata.set(nomad_fixtures);
        app.data.declareModel("Opportunities", nomad_fixtures.modules.Opportunities);
        var model = app.data.createBean("Opportunities");

        context = app.context.getContext();
        context.set({"model": model, "module": "Opportunities"});
    });

    it("should be able to build a list of relationship links", function() {
        var view = app.view.createView({
            name: "detail",
            context: context
        });

        var links = SUGAR.App.nomad.getLinks(view.model);

        expect(links.length).toEqual(2);
    });
});