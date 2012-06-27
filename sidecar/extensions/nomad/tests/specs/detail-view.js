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
        switch (moduleName) {
            case "Contacts":
                model.set({
                    account_id: "daa33fbe-21ab-5366-c885-4fcf4d9c3cac",
                    account_name: "B.C. Investing International",
                    assigned_user_id: "seed_max_id",
                    assigned_user_name: "Max Jensen",
                    date_modified: "06/06/2012 15:31",
                    deleted: "0",
                    do_not_call: "0",
                    email1: "phone20@example.co.uk",
                    first_name: "Sophia",
                    id: "f1962cc3-acf3-976f-6b56-4fcf4d1a0f89",
                    last_name: "Zwick",
                    name: "Sophia Zwick",
                    phone_mobile: "(238) 406-4147",
                    phone_work: "(634) 144-1097",
                    portal_active: "0",
                    primary_address_city: "Persistance",
                    primary_address_country: "USA",
                    primary_address_postalcode: "47402",
                    primary_address_state: "CA",
                    primary_address_street: "48920 San Carlos Ave",
                    team_id: "West",
                    team_name: "West",
                    title: "Mgr Operations"
                });
                break;
        }
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

        expect(view.phoneFields.length).toBeGreaterThan(0);
        expect(view.isDataDefined(view.phoneFields)).toBeTruthy();

        expect(view.emailFields.length).toBeGreaterThan(0);
        expect(view.isDataDefined(view.emailFields)).toBeTruthy();

        expect(view.urlFields.length).toBeGreaterThan(0);
        expect(view.isDataDefined(view.urlFields)).toBeTruthy();

        expect(view.addressFieldsGroups).toBeTruthy();
        expect(_.keys(view.addressFieldsGroups).length).toBeGreaterThan(0);
        expect(view.isDataDefined(view.addressFieldsGroups)).toBeTruthy();

        initModuleSummaryView("Contacts");

        expect(view.urlFields.length).toBeFalsy();
        expect(view.isDataDefined(view.urlFields)).toBeFalsy();

        initModuleSummaryView("Meetings");

        expect(view.phoneFields.length || view.emailFields.length || view.urlFields.length || _.keys(view.addressFieldsGroups).length).toBeFalsy();
    });

    it("should return correct fields data array", function() {
        initModuleSummaryView("Contacts");

        var phones = view.getFieldsDataArray(view.phoneFields),
            urls = view.getFieldsDataArray(view.urlFields),
            emails = view.getFieldsDataArray(view.emailFields);

        expect(phones.length).toEqual(2);
        _.each(phones, function(phone) {
            expect(typeof phone === "object").toBeTruthy();

            var value = _.values(phone);
            expect(value.length).toEqual(1);
            expect(typeof value[0] === "string").toBeTruthy();
        });

        expect(urls.length).toEqual(0);
        expect(emails.length).toEqual(1);
    });

    it("should return correct addresses array", function() {
        initModuleSummaryView("Accounts");

        var addresses = view.getAddresses();
        expect(addresses.length).toEqual(1);

        _.each(addresses, function(address) {
            expect(typeof address === "object").toBeTruthy();

            var value = _.values(address);
            expect(value.length).toEqual(1);

            value = value[0];
            expect(typeof value === "object").toBeTruthy();

            var fields = _.values(value);
            expect(fields.length).toEqual(5);
        });
    });
});