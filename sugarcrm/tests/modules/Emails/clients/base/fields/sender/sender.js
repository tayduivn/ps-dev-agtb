describe("Emails.Field.Sender", function() {
    var app, field, drawer;

    beforeEach(function() {
        var def;

        app = SugarTest.app;
        def = {
            endpoint:  {
                module: 'OutboundEmailConfiguration',
                action: 'list'
            }
        };
        field = SugarTest.createField("base", "email_config", "sender", "edit", def, 'Emails', null, null, true);

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

        drawer = SugarTest.app.drawer;
        SugarTest.app.drawer = {
            close: function(){}
        };
    });

    afterEach(function() {
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        SugarTest.app.drawer = drawer;
        SugarTest.testMetadata.dispose();
    });

    it("should call custom endpoint on render when tplName is 'edit'", function() {
        var populateValues = sinon.spy(field, "populateValues");

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*rest\/v10\/OutboundEmailConfiguration\/list.*/,
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
        SugarTest.server.respondWith("GET", /.*rest\/v10\/OutboundEmailConfiguration\/list.*/,
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
        SugarTest.server.respondWith("GET", /.*rest\/v10\/OutboundEmailConfiguration\/list.*/,
            [200, {"Content-Type": "application/json"},
            JSON.stringify(results)]);

        field.model.unset("email_config", {silent: true});
        field._render();
        SugarTest.server.respond();

        expect(field.model.get("email_config")).toBe(results[0].id);
    });
});
