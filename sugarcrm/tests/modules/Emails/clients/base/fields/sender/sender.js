describe("Emails.Field.Sender", function() {

    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        var def = {
            'name':'email_config',
            'id_name' : 'email_config_id',
            'label':'LBL_FROM',
            'type':'sender',
            'css_class':'inherit-width',
            'endpoint':{
                'module' : 'EmailTemplates',
                'action' : 'listTemplates'
            }
        };

        SugarTest.loadComponent("base", "field", "sender", "Emails");

        var view = new app.view.View({ name: 'edit', context: null }),
            context = app.context.getContext(),
            model = model || new Backbone.Model();

        if (def) {
            model.fields = {};
            model.fields[def.name] = def;
        }

        field = app.view.createField({
            def: def,
            view: view,
            context: context,
            module:'Emails',
            model: model
        });

        field.model = new Backbone.Model({email_config_id: "111-222-33333", email_config: "blob"});

        //used as mock for select2 library
        if (!$.fn.select2) {
            $.fn.select2 = function(options) {
                var obj = {
                    on : function() {
                        return obj;
                    }
                };
                return obj;
            };
        }
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field.model = null;
        field = null;
    });

    it("should set value correctly", function() {
        var populateValues = sinon.spy(field, 'populateValues'),
            expectedId = "c0e9fb1f-52e8-bca1-20e1-51094f123246",
            expectedValue = "System-generated password email",
            results = [
                {"id":expectedId, "display":expectedValue},
                {"id":"c9680513-d932-8ee6-ebbe-51094ff7384c", "display":"Forgot Password email"}
            ];

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/EmailTemplates\/listTemplates.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(results)]);

        field.render();
        SugarTest.server.respond();

        expect(populateValues.calledOnce).toBeTruthy();

        //Setting values through method for Mocking of onchange event from select2
        field.setValue({id: expectedId, value: expectedValue});
        var actualId = field.model.get(field.def.id_name),
            actualName = field.model.get(field.def.name);
        expect(actualId).toEqual(expectedId);
        expect(actualName).toEqual(expectedValue);

        populateValues.restore();
    });
});
