describe("Framework", function () {
    var app;

    afterEach(function() {
        SUGAR.App.destroy();
    });

    it("can create a new instance of the App", function() {
        app = SUGAR.App.getInstance({el: "body"});

        expect(app).toBeDefined();
    });
});