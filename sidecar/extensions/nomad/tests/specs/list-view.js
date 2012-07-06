describe("List View", function() {
    var app, context, view;

    beforeEach(function() {
        app = SugarTest.app;
        app.metadata.set(nomad_fixtures);
    });

    function initRelatesContactListView() {
        app.data.declareModel("Opportunities", nomad_fixtures.modules["Opportunities"]);
        var opportunity = app.data.createBean("Opportunities");

        app.data.declareModel("Contacts", nomad_fixtures.modules["Contacts"]);
        var collection = app.data.createRelatedCollection(opportunity, "contacts");

        var model = app.data.createRelatedBean(opportunity, null, "contacts", {
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
            opportunity_role: "Influencer",
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
        collection.add(model);

        context = app.context.getContext();
        context.set({collection: collection, module: "Contacts", link: "contacts", parentModule: "Opportunities"});

        view = app.view.createView({
            name: "list",
            meta: nomad_fixtures.modules["Contacts"].views.list.meta,
            context: context
        });

    }

    it("should display relationship field data", function() {
        initRelatesContactListView();

        view._render();
        expect(view.$el.find(".items > article > div > a").next().html()).toEqual("Influencer");
    });
});