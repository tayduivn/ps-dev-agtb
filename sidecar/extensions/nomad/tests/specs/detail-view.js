describe ("Detail View", function () {
    var app, context, view;

    beforeEach(function() {
        app = SugarTest.app;
        app.metadata.set(nomad_fixtures);
    });

    function initModuleSummaryView (moduleName) {
        app.data.declareModel(moduleName, nomad_fixtures.modules[moduleName]);
        var model = app.data.createBean(moduleName);

        context = app.context.getContext();
        context.set({"model": model, "module": moduleName});

        view = app.view.createView({
            name: "detail",
            context: context
        });
    }

    it("should be able to build a list of relationship links", function() {
        initModuleSummaryView("Accounts");

        var links = app.nomad.getLinks(view.model);
        expect(links.length).toEqual(6);

        var names = _.pluck(links, "name"); //map link names
        expect(_.include(names, "cases")).toBeTruthy();
        expect(_.include(names, "tasks")).toBeTruthy();
        expect(_.include(names, "meetings")).toBeTruthy();
        expect(_.include(names, "calls")).toBeTruthy();
        expect(_.include(names, "leads")).toBeTruthy();
        expect(_.include(names, "opportunities")).toBeTruthy();
    });

    it("should be able to filter fields of needed types and check them", function() {
        initModuleSummaryView("Accounts");

        expect(!!view.phoneFields.length).toEqual(true);
        expect(view.isDataDefined(view.phoneFields)).toEqual(true);

        expect(!!view.emailFields.length).toEqual(true);
        expect(view.isDataDefined(view.emailFields)).toEqual(true);

        expect(!!view.urlFields.length).toEqual(true);
        expect(view.isDataDefined(view.urlFields)).toEqual(true);

        expect(view.addressFieldsGroups).toBeTruthy();
        expect(!!(_.keys(view.addressFieldsGroups).length)).toEqual(true);
        expect(view.isDataDefined(view.addressFieldsGroups)).toEqual(true);

        initModuleSummaryView("Contacts");

        expect(!!view.urlFields.length).toEqual(false);
        expect(view.isDataDefined(view.urlFields)).toEqual(false);

        initModuleSummaryView("Meetings");

        expect(!!view.phoneFields.length || !!view.emailFields.length || !!view.urlFields.length || !!(_.keys(view.addressFieldsGroups).length)).toEqual(false);
    });

    it("should return addresses array in correct form", function() {
        initModuleSummaryView("Accounts");

        var addresses = view.getAddresses();
        expect(addresses.length).toEqual(1);

        _.each(addresses, function(address) {
            expect(typeof address === "object").toEqual(true);

            var value = _.values(address);
            expect(value.length).toEqual(1);

            value = value[0];
            expect(typeof value === "object").toEqual(true);

            var fields = _.values(value);
            expect(fields.length).toEqual(5);
        });

    });
});