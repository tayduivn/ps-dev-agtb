describe("Framework", function () {
    var app, app2;

    afterEach(function() {
        app = null;
        app2 = null;
    });

    it("can create a new instance of the App", function() {
        app = new SUGAR.App();

        expect(app).toBeDefined();
    });

    it("can create multiple instances of the App", function() {
        app = new SUGAR.App();
        app2 = new SUGAR.App();

        expect(app).toBeDefined();
        expect(app2).toBeDefined();
        expect(app).not.toEqual(app2);
        expect(app.appId).not.toEqual(app2.appId);
    });
});