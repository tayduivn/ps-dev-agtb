describe("Controller", function() {

    describe("when a route is matched", function() {

        beforeEach(function() {
            SugarTest.seedMetadata();
            SugarTest.seedFakeServer();
        });

        it("should load the view properly", function() {
            var app = SugarTest.app,
                params = {
                    module: "Contacts",
                    layout: "list"
                },
                mock = sinon.mock(app.controller.$el),
                expection = mock.expects("html");

            SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts.*/,
                [200, {  "Content-Type":"application/json"},
                    JSON.stringify(fixtures.api["rest/v10/contact"].GET.response)]);

            app.controller.loadView(params);
            SugarTest.server.respond();

            expect(app.controller.layout).toBeDefined();
            expect(app.controller.layout instanceof Backbone.View).toBeTruthy();
            expect(app.controller.context.get().collection).toBeDefined();
            expect(app.controller.context.get().collection.models.length).toEqual(2);

            mock.verify();
        });
    });
});
