describe("Layout Manager", function() {
    var app = SUGAR.App;

    beforeEach(function() {
        app.metadata.set(fixtures.metadata);
    });

    it('should create views', function () {
        expect(app.layout.get({
            view : "edit",
            module: "Contacts"
        })).not.toBe(null);
    });

    it('should create layouts', function () {
        expect(app.layout.get({
            layout : "edit",
            module: "Contacts"
        })).not.toBe(null);
    });

    it("should return a new instance of custom View class when the View has a custom controller", function () {
        var result = app.layout.get({
            view : "login",
            module: "Home"
        });

        expect(result).toBeDefined();
        expect(result.customCallback).toBeDefined();
        expect(SUGAR.App.layout.HomeLoginView).toBeDefined();
    });


});