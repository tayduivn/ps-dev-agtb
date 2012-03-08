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
        var route = "";
        var model = new Backbone.Model();
        var action = "edit";

        model.set("id","1234");
        model.module="Contacts";
        SUGAR.App.router.initialize({controller: controller});
        route = SUGAR.App.router.buildRoute({},action,model,{});

        expect(route).toEqual("Contacts/1234/edit");
    });

    it("should build a route given a context", function(){
        var route = "";
        var context = {module:"Contacts"};
        var action = "create";

        SUGAR.App.router.initialize({controller: controller});
        route = SUGAR.App.router.buildRoute(context,action,{},{});
        expect(route).toEqual("Contacts/create");
    });

    it("should build a route given module via options", function(){
        var route = "";
        var context = {};
        var options = {module:"Contacts"};
        var action = "create";

        SUGAR.App.router.initialize({controller: controller});
        route = SUGAR.App.router.buildRoute(context,action,{},options);
        expect(route).toEqual("Contacts/create");
    });

    it("should add params to a route if given in options ", function(){
        var route = "";
        var context = {};
        var options = {
            module:"Contacts",
            params:[{name:"first",value:"Rick"},
            {name:"last",value:"Astley"},
            {name:"job",value:"Rock Star"}]
            };
        var action = "create";

        SUGAR.App.router.initialize({controller: controller});
        route = SUGAR.App.router.buildRoute(context,action,{},options);
        
        expect(route).toEqual("Contacts/create?first=Rick&last=Astley&job=Rock+Star");
    });
});