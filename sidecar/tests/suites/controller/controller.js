describe("Controller", function() {

    afterEach(function() {
        SUGAR.App.destroy();
    });

    it("exists", function() {
        expect(SUGAR.App.controller).toBeDefined();
    });

    it("can load a view", function() {
        SUGAR.App.controller.init({});
        SUGAR.App.controller.loadView();

        expect().toBeTruthy();
    });
});