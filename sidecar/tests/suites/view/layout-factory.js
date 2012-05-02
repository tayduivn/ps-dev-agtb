describe("View Manager", function() {
    var app, context;

    beforeEach(function() {
        SugarTest.seedApp(true);
        app = SugarTest.app;
        context = app.context.getContext();
        SugarTest.seedMetadata(true);
    });

    describe("should be able to create instances of Layout class which is", function() {

        it('base class', function () {
            expect(app.view.createLayout({
                name : "edit",
                module: "Contacts",
                context: context
            })).not.toBe(null);
        });

        it("creates layout with a custom controller", function () {
            var result = app.view.createLayout({
                name : "detailplus",
                module: "Contacts",
                context: context 
            });
            expect(result).toBeDefined();
            expect(result.customLayoutCallback).toBeDefined();
        });

        it("creates layout with a custom controller directly", function () {
            var result = app.view.createLayout({
                name : "tree",
                context: context,
                controller: "{customTreeLayoutHook: function(){return \"overridden\";}}",
                module: "Contacts"
            });
            expect(result).toBeDefined();
            expect(result.customTreeLayoutHook).toBeDefined();
            expect(result instanceof app.view.layouts.ContactsTreeLayout).toBeTruthy();
        });

    });

});
