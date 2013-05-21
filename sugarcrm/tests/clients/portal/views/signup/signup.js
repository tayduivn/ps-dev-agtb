//FILE SUGARCRM flav=ent ONLY
describe("Portal Signup View", function() {

    var view, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('signup', {
            "panels": [
                {
                    "fields": [
                        {
                            "name": "first_name"
                        },
                        {
                            "name": "last_name"
                        }
                    ]
                }
            ]
        });
        SugarTest.testMetadata.set();
        view = SugarTest.createView("portal","Signup", "signup");
        app = SUGAR.App;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe("Declare Sign Up Bean", function() {

        it("should have declared a Bean with the fields metadata", function() {
            expect(view.model.fields).toBeDefined();
            expect(_.size(view.model.fields)).toBeGreaterThan(0);
            expect(_.size(view.model.fields.first_name)).toBeDefined();
            expect(_.size(view.model.fields.last_name)).toBeDefined();
        });
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
