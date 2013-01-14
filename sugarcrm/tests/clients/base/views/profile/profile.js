describe("Detail View (Profile)", function() {

    var detailview, view, app;

    beforeEach(function() {
        app = SUGAR.App;
        app.metadata.set(fixtures.metadata, false, true);
        detailview = SugarTest.createView("base","Contacts", "detail");
        view = SugarTest.createView("base","Contacts", "profile");
        view.model = new Backbone.Model();
        view.model.fields = app.metadata.getModule("Contacts").fields;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe("full name formatting", function() {

        it("has first name, last name and salutation", function () {
            view.model.set({
                first_name: "Joe",
                last_name: "Plumber",
                salutation: "Mr."
            });
            expect(view.getFullName()).toBe("Mr. Joe Plumber");
        });

        it("has name, full name and salutation", function () {
            view.model.set({
                name: "Who",
                full_name: "Who",
                salutation: "Dr."
            });
            expect(view.getFullName()).toBe("Dr. Who");
        });

        it("has first name and last name", function () {
            view.model.set({
                first_name: "Mister",
                last_name: "Boots"
            });
            expect(view.getFullName()).toBe("Mister Boots");

            view.model.set({
                first_name: "Mister",
                last_name: "Boots",
                salutation: ""
            });
            expect(view.getFullName()).toBe("Mister Boots");

        });
    });


    describe("bindDataChange", function() {

        it("it should update subnav model", function () {
            view.context = new Backbone.Model();
            view.context.set('subnavModel', new Backbone.Model());
            view.bindDataChange();
            view.model.set('test', 'test');
            expect(view.context.has('subnavModel')).toBeTruthy();
            expect(view.context.get('subnavModel').toJSON()).toEqual({
                'title':view.getFullName(),
                'meta':view.meta
            });
        });
    });
});
