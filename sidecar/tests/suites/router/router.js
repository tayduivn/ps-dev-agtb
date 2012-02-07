describe("Router", function() {
    var app, mock,
        controller = {
            loadView: function(args) {}
        };

    beforeEach(function() {
        app = SUGAR.App.getInstance({el: "body"});
    });

    afterEach(function() {
        SUGAR.App.destroy();
    });

    it("is initialized", function() {
        expect(app.router).toBeDefined();
    });

    it("calls controller to load the default view", function() {
        var mock = sinon.mock(controller);
        mock.expects("loadView").once();

        // Override controller with a sinon mock
        app.router.controller = controller;

        app.router.index();
        expect(mock.verify()).toBeTruthy();
    });
});