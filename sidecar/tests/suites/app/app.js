describe("Framework", function() {
    describe("when an instance is requested", function() {
        var app;

        it("should return a new instance if none exists", function() {
            app = SUGAR.App.init({el: "body"});
            expect(app).toBeTruthy();
        });

        it("should return an existing instance", function() {
            var app2 = SUGAR.App.init({el: "body"});
            expect(app2).toEqual(app);
        });

        SUGAR.App.destroy();
    });

    describe("when augmented", function() {
        var app = SUGAR.App.init({el: "body"}),
            mock;
        it("should register a module with itself", function() {
            var module = {
                init: function() {}
            }

            mock = sinon.mock(module);
            mock.expects("init").once();

            app.augment("test", module, true);
            expect(mock.verify()).toBeTruthy();
        });

        SUGAR.App.destroy();
    });
});