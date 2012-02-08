describe("Router", function() {
    var app, mock,
        controller = {
            loadView: function(args) {}
        };

    afterEach(function() {
        SUGAR.App.destroy();
    });

    it("is initialized", function() {
        expect(SUGAR.App.router).toBeDefined();
    });

    it("calls controller to load the default view", function() {
        var mock = sinon.mock(controller);
        mock.expects("loadView").once();

        // Initialize the router
        SUGAR.App.router.init({controller: controller});
        expect(mock.verify()).toBeTruthy();
    });
});