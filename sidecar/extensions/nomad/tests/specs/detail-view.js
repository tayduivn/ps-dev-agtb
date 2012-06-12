describe ("Detail View", function () {
    var app, context, view;

    beforeEach(function() {
        //SugarTest.seedMetadata(false);
        app = SugarTest.app;

        app.metadata.set(nomad_fixtures);
        app.data.declareModel("Accounts", nomad_fixtures.modules.Accounts);
        var model = app.data.createBean("Accounts");

        context = app.context.getContext();
        context.set({"model": model, "module": "Accounts"});

        view = app.view.createView({
            name: "detail",
            context: context
        });
    });

    it("should be able to build a list of relationship links", function() {
        var links = app.nomad.getLinks(view.model);

        expect(links.length).toEqual(5);

        var names = _.pluck(links, "name"); //map
        expect(_.include(names, "cases")).toEqual(true);
        expect(_.include(names, "tasks")).toEqual(true);
        expect(_.include(names, "meetings")).toEqual(true);
        expect(_.include(names, "calls")).toEqual(true);
        expect(_.include(names, "leads")).toEqual(true);
    });

    it("should be able to filter fields of needed types and check them", function() {
        expect(!!view.phoneFields.length).toEqual(true);
        expect(view.isDataDefined(view.phoneFields)).toEqual(true);

        expect(!!view.emailFields.length).toEqual(true);
        expect(view.isDataDefined(view.emailFields)).toEqual(true);

        expect(!!view.urlFields.length).toEqual(true);
        expect(view.isDataDefined(view.urlFields)).toEqual(true);

        expect(!!view.addressFields.length).toEqual(true);
        expect(view.isDataDefined(view.addressFields)).toEqual(true);
    });
});