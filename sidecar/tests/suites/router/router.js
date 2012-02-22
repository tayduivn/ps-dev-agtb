describe("Router", function() {
    var app, mock,
        controller = {
            loadView: function(args) {}
        };

    it("should call the controller to load the default view", function() {
        var mock = sinon.mock(controller);
        mock.expects("loadView").once();

        // Initialize the router
        SUGAR.App.router.init({controller: controller});
        SUGAR.App.router.start();
        expect(mock.verify()).toBeTruthy();

    });
});