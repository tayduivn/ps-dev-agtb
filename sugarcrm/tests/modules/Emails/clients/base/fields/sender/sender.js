describe("Emails.Field.Sender", function() {
    var app, field;

    beforeEach(function() {
        app = SugarTest.app;

        var def = {
            name:      'email_config',
            label:     'LBL_FROM',
            type:      'sender',
            css_class: 'inherit-width',
            endpoint:  {
                module: 'OutboundEmailConfiguration',
                action: 'list'
            }
        };

        SugarTest.loadComponent("base", "field", "sender", "Emails");

        var view    = new app.view.View({name: 'edit', context: null}),
            context = app.context.getContext(),
            model   = new Backbone.Model();

        model.fields           = {};
        model.fields[def.name] = def;

        field = app.view.createField({
            def:     def,
            view:    view,
            context: context,
            module:  'Emails',
            model:   model
        });

        //used as mock for select2 library
        if (!$.fn.select2) {
            $.fn.select2 = function(options) {
                var obj = {
                    on: function() {
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
        delete field.model;
        delete field;
    });

    it("should call custom endpoint on render when tplName is 'edit'", function() {
        var populateValues = sinon.spy(field, "populateValues");

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/OutboundEmailConfiguration\/list.*/,
            [200, {"Content-Type": "application/json"}, ""]);

        field.options.viewName = "edit";
        field._render();
        SugarTest.server.respond();
        expect(populateValues.calledOnce).toBeTruthy();

        populateValues.restore();
    });

    it("should not call custom endpoint on render when tplName is not 'edit'", function() {
        var populateValues = sinon.spy(field, "populateValues");

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/OutboundEmailConfiguration\/list.*/,
            [200, {"Content-Type": "application/json"}, ""]);

        field.options.viewName = "foo";
        field._render();
        SugarTest.server.respond();
        expect(populateValues.calledOnce).toBeFalsy();

        populateValues.restore();
    });

    it("should set the default value if custom endpoint returns data and the model does not yet have a value", function() {
        var results = [
                {id: "abcd", display: "Configuration A", type: "system", default: true},
                {id: "efgh", display: "Configuration B", type: "user", default: false}
            ];

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/OutboundEmailConfiguration\/list.*/,
            [200, {"Content-Type": "application/json"},
            JSON.stringify(results)]);

        field.model.unset("email_config", {silent: true});
        field._render();
        SugarTest.server.respond();

        expect(field.model.get("email_config")).toBe(results[0].id);
    });
});
