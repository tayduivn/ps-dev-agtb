describe("Portal Signup View", function() {

    var view, app;

    beforeEach(function() {
        view = SugarTest.createView("portal","Signup", "signup");
        view.context = new Backbone.Model();
        view.model = view.context.attributes.model = new Backbone.Model();
        app = SUGAR.App;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe("signup", function() {

        it("should toggle state field", function() {
            var $countries = $('<select>').attr('name', 'country');
            $('<option></option>').attr('value', '').appendTo($countries);
            $('<option></option>').attr('value', 'USA').appendTo($countries);
            $('<option></option>').attr('value', 'France').appendTo($countries);

            var $states = $('<select>').attr('name', 'state');
            $('<option></option>').attr('value', '').appendTo($states);
            $('<option></option>').attr('value', 'California').appendTo($states);
            $('<option></option>').attr('value', 'New York').appendTo($states);

            $('<div></div>').append($countries).appendTo(view.$el);
            $('<div></div>').append($states).appendTo(view.$el);

            view.stateField = view.$('select[name=state]');
            view.countryField = view.$('select[name=country]');

            view.toggleStateField();
            expect(view.$('select[name=state]').parent().css('display')).toEqual('none');

            view.countryField.val('USA');
            view.toggleStateField();
            expect(view.$('select[name=state]').parent().css('display')).not.toEqual('none');
        });
    });
});
