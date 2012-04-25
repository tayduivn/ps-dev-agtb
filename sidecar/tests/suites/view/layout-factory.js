describe("View Manager", function() {
    var app;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
    });

    describe("should be able to create instances of Layout class which is", function() {

        it('base class', function () {
            expect(app.view.createLayout({
                name : "edit",
                module: "Contacts"
            })).not.toBe(null);
        });

    });

});
