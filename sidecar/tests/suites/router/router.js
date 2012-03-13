describe("Router", function() {
    var app, mock,
        controller = {
            loadView: function(args) {
            }
        };

    it("should call the controller to load the default view", function() {
        var mock = sinon.mock(controller);
        mock.expects("loadView").once();

        // Initialize the router
        SUGAR.App.router.initialize({controller: controller});
        SUGAR.App.router.start();
        expect(mock.verify()).toBeTruthy();

    });

    it("should build a route given a model", function(){
        var route,
            model = new Backbone.Model(),
            action = "edit";

        model.set("id", "1234");
        model.module = "Contacts";

        SUGAR.App.router.initialize({controller: controller});

        route = SUGAR.App.router.buildRoute(model.module, model.id, action);

        expect(route).toEqual("Contacts/1234/edit");
    });

    it("should build a route given a context", function(){
        var route,
            context = { get: function() { return "Contacts"; }},
            action = "create";

        SUGAR.App.router.initialize({controller: controller});

        route = SUGAR.App.router.buildRoute(context, null, action,{});

        expect(route).toEqual("Contacts/create");
    });

    // TODO: This test has been disabled, as the paramters don't work properly. Need to add supporting routes
    xit("should add params to a route if given in options ", function(){
        var route,
            context = {},
            options = {
                module: "Contacts",
                params: [
                    {name: "first", value: "Rick"},
                    {name: "last", value: "Astley"},
                    {name: "job", value: "Rock Star"}
                ]
            },
            action = "create";

        SUGAR.App.router.initialize({controller: controller});
        route = SUGAR.App.router.buildRoute(context, action, {}, options);

        expect(route).toEqual("Contacts/create?first=Rick&last=Astley&job=Rock+Star");
    });
});