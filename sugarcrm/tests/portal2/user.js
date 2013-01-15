describe("Portal User extensions", function () {

    var app;

    beforeEach(function () {
        app = SUGAR.App;
        app.user.clear();
    });

    describe("app.user.isSupportPortalUser", function () {

        it("should be a portal user", function () {
            app.user.set('type', 'support_portal');
            expect(app.user.isSupportPortalUser()).toBeTruthy();
        });


        it("should be a portal user", function () {
            expect(app.user.isSupportPortalUser()).toBeFalsy();
        });
    });

});
