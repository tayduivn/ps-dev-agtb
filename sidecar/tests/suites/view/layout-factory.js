describe("View Manager", function() {
    var app, context;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        context = app.context.getContext();
    });

    describe("should be able to create instances of Layout class which is", function() {

        it('base class', function () {
            expect(app.view.createLayout({
                name : "edit",
                module: "Contacts",
                context: context
            })).not.toBe(null);
        });

    });

});
