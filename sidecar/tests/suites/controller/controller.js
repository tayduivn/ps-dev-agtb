describe("Controller", function() {

    var controller = SUGAR.App.controller;

    afterEach(function() {
        SUGAR.App.destroy();
    });

    it("should exist within the framework", function() {
        expect(controller).toBeDefined();
    });

    describe("when a route is matched", function() {
        var params = {
            module: "main",
            url: ""
        };

        it("should fetch the needed data from the data manager", function() {
            expect(controller.data).not.toBeEqual(_.empty())

        });

        it("should set the context", function() {
            expect(controller.context).toBeTruthy();
        });

        it("should load the appropriate layout", function() {
            expect(controller.currentView.name).toBe("tbd");
        });

        it("should render the appropriate layout to the specified root div", function() {

        });
    });
});