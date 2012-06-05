describe("Controller", function() {

    var app;

    describe("when a route is matched", function() {

        beforeEach(function() {
            app = SugarTest.app;
            SugarTest.seedMetadata();
            SugarTest.seedFakeServer();
        });

        it("should load the view properly", function() {
            var params = {
                    module: "Contacts",
                    layout: "list"
                };

            SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts.*/,
                [200, {  "Content-Type":"application/json"},
                    JSON.stringify(fixtures.api["rest/v10/contact"].GET.response)]);

            app.controller.loadView(params);
            SugarTest.server.respond();

            expect(app.controller.layout).toBeDefined();
            expect(app.controller.layout instanceof Backbone.View).toBeTruthy();
            expect(app.controller.context.get("collection")).toBeDefined();
            expect(app.controller.context.get("collection").models.length).toEqual(2);

        });

        it("should render addtional components", function() {
            var components = {login: {target: '#footer'}};
            app.controller.loadAdditionalComponents(components);
            app.controller.loadAdditionalComponents(components); // we should be able to call it multiple times safely
            expect(app.additionalComponents.login instanceof app.view.View).toBeTruthy();
            expect(app.additionalComponents.login.name).toEqual('login');
        });
    });
});
