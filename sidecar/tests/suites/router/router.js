describe("Router", function() {
    var app, mock,
        controller = {
            loadView: function(args) {}
        };

    afterEach(function() {
        SUGAR.App.destroy();
    });

    it("should be set and initialized within the framework", function() {
        expect(SUGAR.App.router).toBeTruthy();
    });

    it("should call the controller to load the default view", function() {
        var mock = sinon.mock(controller);
        mock.expects("loadView").once();

        // Initialize the router
        SUGAR.App.router.init({controller: controller});
        expect(mock.verify()).toBeTruthy();
    });
});