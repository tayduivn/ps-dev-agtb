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
            var value;
            var $countries = $('<input type="hidden">').attr('name', 'country');

            var $states = $('<input type="hidden">').attr('name', 'state');

            $('<div></div>').append($countries).appendTo(view.$el);
            $('<div></div>').append($states).appendTo(view.$el);

            view.stateField = view.$('input[name=state]');
            view.countryField = view.$('input[name=country]');
            var stubFn = function(arg1, arg2){
                if(arg1 === 'val'){
                    if(_.isUndefined(arg2)){
                        return value;
                    } else {
                        value = arg2;
                    }
                }
            };
            var stateSelect2Stub = sinon.stub(view.stateField, "select2", stubFn);
            var countrySelect2Stub = sinon.stub(view.countryField, "select2", stubFn);
            value = 'NOT_USA';
            view.toggleStateField();
            expect(view.$('input[name=state]').parent().css('display')).toEqual('none');
            value = 'USA';
            view.toggleStateField();
            expect(view.$('input[name=state]').parent().css('display')).not.toEqual('none');

            stateSelect2Stub.restore();
            countrySelect2Stub.restore();
        });
    });
});
