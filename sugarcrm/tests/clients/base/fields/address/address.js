describe("Address", function() {

    var app, view, context;

    beforeEach(function() {
        app = SugarTest.app;
        //app.metadata.set(meta);
        context = app.context.getContext();
        view = new app.view.View({ name: "test", context: context });
        console.log(app);
        if (!app.view.fields.AddressField)
        {

            $.ajax("../clients/base/fields/address/address.js", {
                async : false,
                success : function(o) {
                    console.log(o);
                    app.view.declareComponent("field", "address", null, o, null, true);
                }
            });
        } else {
            console.log(app);
        }
    });

    afterEach(function() {
        app.cache.cutAll();
        delete Handlebars.templates;
    });

    it("should exist", function() {
       expect(app.view.fields.AddressField).toBeDefined();
    });
});

