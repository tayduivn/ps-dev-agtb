describe("Emails.Field.Sender", function() {
    var app, field, drawer, ajaxStub;

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
        ajaxStub = sinon.stub($, 'ajax', $.noop);
    });

    afterEach(function() {
        ajaxStub.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete field.model;
        field = null;
        SugarTest.app.drawer = drawer;
        SugarTest.testMetadata.dispose();
    });

    it("should call custom endpoint on render when tplName is 'edit'", function() {
        var url, regex, apiCallStub;
        url = "rest/v10/OutboundEmailConfiguration/list";
        regex = new RegExp(".*"+url);
        apiCallStub = sinon.stub(app.api, 'call');
        field.options.viewName = "edit";
        field._render();
        expect(apiCallStub.calledOnce).toBeTruthy();
        expect(apiCallStub.args[0][0]).toEqual("GET");
        expect(apiCallStub.args[0][1]).toMatch(/.*rest\/v10\/OutboundEmailConfiguration\/list/);
        apiCallStub.restore();
    });

    it("should not call custom endpoint on render when tplName is not 'edit'", function() {
        var apiCallStub, populateValues;
        populateValues = sinon.spy(field, "populateValues");
        apiCallStub = sinon.stub(app.api, 'call');

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*rest\/v10\/OutboundEmailConfiguration\/list.*/,
            [200, {"Content-Type": "application/json"}, ""]);

        field.options.viewName = "foo";
        field._render();
        SugarTest.server.respond();
        expect(populateValues.calledOnce).toBeFalsy();

        populateValues.restore();
        apiCallStub.restore();
    });

    it("should set the default value if custom endpoint returns data and the model does not yet have a value", function() {
        var results = [
                {id: "abcd", display: "Configuration A", type: "system", default: true},
                {id: "efgh", display: "Configuration B", type: "user", default: false}
            ];
        field.disposed = false;
        field.model.unset("email_config", {silent: true});
        field.populateValues(results);
        expect(field.model.get("email_config")).toBe(results[0].id);
    });
});
