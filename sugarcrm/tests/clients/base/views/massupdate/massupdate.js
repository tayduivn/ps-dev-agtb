describe("Base.View.Massupdate", function() {

    var view, app, layout;

    beforeEach(function() {
        app = SugarTest.app;
        var stub = sinon.stub(app.metadata, "getModule", function(){
            _.each(fixtures.metadata.modules.Contacts.fields, function(field){
                field.massupdate = true;
            });
            return fixtures.metadata.modules.Contacts;
        });
        layout = SugarTest.createLayout('base', 'Cases', 'list');
        view = SugarTest.createView("base", "Contacts", "massupdate", null, null, null, layout);
        stub.restore();
        view.model = new Backbone.Model();
    });


    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view.model = null;
        view = null;
        layout.dispose();
    });


    it("should generate its fields from metadata massupdate value", function() {
        var expected = view.meta.panels[0].fields.length,
            actual = (_.filter(fixtures.metadata.modules.Contacts.fields, function(field){
            return field.massupdate;
        })).length;

        expect(actual).toBe(expected);

    });

    it("should set the default option by the first available fields", function(){
        view.setDefault();

        var actual = view.defaultOption,
            expected = fixtures.metadata.modules.Contacts.fields[_.first(_.keys(fixtures.metadata.modules.Contacts.fields))];

        _.each(actual, function(value, key){
            expect(actual[key]).toBe(expected[key]);
        });
        //available options, exclusive from assigned field values
    });

    it("should set available fields out of assigned field values", function(){
        view.setDefault();
        var options = view.fieldOptions.length;

        view.addUpdateField();
        var expected = options - 1,
            actual = view.fieldOptions.length;

        expect(actual).toBe(expected);

        view.addUpdateField();
        view.addUpdateField();

        expected = expected - 2;
        actual = view.fieldOptions.length;

        expect(actual).toBe(expected);
    });

    it("should add, remove, and/or replace field values", function(){
        view.setDefault();
        var selectedOption = view.defaultOption;

        view.addUpdateField();
        var nextSelectedOption = view.defaultOption;
        expect(_.contains(view.fieldValues, selectedOption)).toBeTruthy();
        expect(_.contains(view.fieldOptions, selectedOption)).toBeFalsy();

        view.removeUpdateField(0);
        expect(_.contains(view.fieldValues, selectedOption)).toBeFalsy();
        expect(_.contains(view.fieldOptions, selectedOption)).toBeTruthy();
        expect(view.defaultOption).toBe(nextSelectedOption);

        view.replaceUpdateField(selectedOption, 0);
        expect(view.defaultOption).toBe(selectedOption);
    });
});
