describe("Controller", function() {

    afterEach(function() {
        SUGAR.App.destroy();
    });

    it("should exist within the framework", function() {
        expect(SUGAR.App.controller).toBeDefined();
    });

    describe("when a route is matched", function() {
        it("should be able to load a view based on the given route information", function() {
            SUGAR.App.controller.init({});
            SUGAR.App.controller.loadView();

            expect().toBeTruthy();
        });

        it("should set the context", function() {

        });

        it("should fetch the needed data from the data manager", function() {

        });
    });
});