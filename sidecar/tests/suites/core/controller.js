describe("Controller", function() {
describe("when a route is matched", function() {
    var app, server;

    beforeEach(function() {
        app = SugarTest.app;
        server = sinon.fakeServer.create();
    });

    afterEach(function() {
        if (server && server.restore) server.restore();
    });

    it("should load the view properly", function() {
            var params = {
                module: "Contacts",
                layout: "list"
            };

            var mock = sinon.mock(app.controller.$el);
            var expection = mock.expects("html");

            server.respondWith("GET", /\/rest\/v10\/Contacts/,
                [200, {  "Content-Type":"application/json"},
                    JSON.stringify(fixtures.api["rest/v10/contact"].GET.response)]);


            app.controller.loadView(params);
            server.respond();

            expect(app.controller.layout).toBeDefined();
            expect(app.controller.layout instanceof Backbone.View).toBeTruthy();
            expect(app.controller.context.get().collection).toBeDefined();
            expect(app.controller.context.get().collection.models.length).toEqual(2);

            mock.verify();
        });
    });
});
