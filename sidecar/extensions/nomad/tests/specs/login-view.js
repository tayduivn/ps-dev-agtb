describe("Login View", function() {
    var app;

    beforeEach(function() {
        app = SugarTest.app;
    });

    it("should be able to normalize server URL", function() {
        var f = app.view.views.LoginView.normalizeUrl;

        expect(f("example.com/sugar")).toEqual("http://example.com/sugar/rest/v10");
        expect(f("example.com/sugar/")).toEqual("http://example.com/sugar/rest/v10");
        expect(f("http://example.com/sugar")).toEqual("http://example.com/sugar/rest/v10");
        expect(f("http://example.com/sugar/")).toEqual("http://example.com/sugar/rest/v10");

        app.config.useHttps = true;

        expect(f("example.com/sugar")).toEqual("https://example.com/sugar/rest/v10");
        expect(f("example.com/sugar/")).toEqual("https://example.com/sugar/rest/v10");
        expect(f("http://example.com/sugar")).toEqual("https://example.com/sugar/rest/v10");
        expect(f("http://example.com/sugar/")).toEqual("https://example.com/sugar/rest/v10");
    });
});