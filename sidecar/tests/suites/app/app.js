describe("Framework", function() {
    var app, mock;

    beforeEach(function() {
        app = SUGAR.App.init({el: "body"});
    });

    afterEach(function() {
        SUGAR.App.destroy();
    });

    it("can create a new instance of the App", function() {
        expect(app).toBeDefined();
    });

    it("can augment itself with new modules", function() {
        var module = {
            init: function() {}
        }

        mock = sinon.mock(module);
        mock.expects("init").once();

        app.augment("test", module, true);
        expect(mock.verify()).toBeTruthy();
    });
});