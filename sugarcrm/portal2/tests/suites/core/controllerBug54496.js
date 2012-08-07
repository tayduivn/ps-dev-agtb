describe("bug 54496", function () {
    describe("controller", function () {
        it("should not call logout if app status equals offline and not authenticated", function () {
            var app = SugarTest.app;
            var params = {
                    module: "Contacts",
                    layout: "list"
                };
            app.config.appStatus = 'offline';
            var logoutSpy = sinon.spy(app, 'logout');

            app.controller.loadView(params);

            expect(logoutSpy).not.toHaveBeenCalled();
            app.config.appStatus = 'online';
        });
    });
});