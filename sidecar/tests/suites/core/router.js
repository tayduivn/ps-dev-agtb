describe("Router", function() {
    var app, router, defaultModule;

    beforeEach(function() {
        app = SugarTest.app;
        router = app.router;
        defaultModule = app.config.defaultModule;
    });

    afterEach(function() {
        app.config.defaultModule = defaultModule;
    });

    it("should call the controller to load a view for the default route", function() {
        var mock = sinon.mock(app.controller);
        mock.expects("loadView").once();

        router.start();
        expect(mock.verify()).toBeTruthy();
    });

    it("should build a route given a model", function(){
        var route,
            model = new Backbone.Model(),
            action = "edit";

        model.set("id", "1234");
        model.module = "Contacts";

        route = router.buildRoute(model.module, model.id, action);

        expect(route).toEqual("Contacts/1234/edit");
    });

    it("should build a route given a context", function(){
        var route,
            context = { get: function() { return "Contacts"; }},
            action = "create";

        route = router.buildRoute(context, null, action, {});

        expect(route).toEqual("Contacts/create");
    });

    // TODO: Move to portal tests. It's handled in portal.js routing.before.
    xit("should handle index route with default module", function() {
        app.config.defaultModule = "Cases";
        var mock = sinon.mock(app.controller);
        mock.expects("loadView").once().withArgs({
            module: 'Cases',
            layout: 'list'
        });

        router.index();
        expect(mock.verify()).toBeTruthy();
    });

    it("should handle index route with unspecified default module", function() {
        app.config.defaultModule = null;
        var mock = sinon.mock(app.controller);
        mock.expects("loadView").once().withArgs({
            module: 'Home',
            layout: 'home'
        });

        router.index();
        expect(mock.verify()).toBeTruthy();
    });

    it("should handle arbitrary layout route", function() {
        var mock = sinon.mock(app.controller);
        mock.expects("loadView").once().withArgs({
            module:'Cases',
            layout:'list'
        });

        router.layout('Cases', 'list');
        expect(mock.verify()).toBeTruthy();
    });

    it("should handle create route", function() {
        var mock = sinon.mock(app.controller);
        mock.expects("loadView").once().withArgs({
            module: 'Cases',
            create: true,
            layout: 'edit'
        });

        router.create('Cases');
        expect(mock.verify()).toBeTruthy();
    });

    it("should handle record route", function() {
        var mock = sinon.mock(app.controller);
        mock.expects("loadView").once().withArgs({
            module: 'Cases',
            modelId: 123,
            action: 'edit',
            layout: 'edit'
        });

        router.record('Cases', 123, 'edit');
        expect(mock.verify()).toBeTruthy();
    });

    it("should handle login route", function() {
        var mock = sinon.mock(app.controller);
        mock.expects("loadView").once().withArgs({
            module:'Login',
            layout:'login',
            create: true
        });

        router.login();
        expect(mock.verify()).toBeTruthy();
    });

    it("should handle logout route", function() {
        var mock = sinon.mock(app.api);
        mock.expects("logout").once();

        router.logout();
        expect(mock.verify()).toBeTruthy();
    });

    it("should reject a secure route if the user is not authenticated", function() {
        sinon.stub(app.api, "isAuthenticated", function() { return false; });
        var beforeRouting = app.routing.before("index");
        expect(beforeRouting).toBeFalsy();
    });

    it("should reject a secure route if the app is not synced", function() {
        app.isSynced = false;
        var beforeRouting = app.routing.before("index");
        expect(beforeRouting).toBeFalsy();
    });

    it("should always accept an unsecure route", function() {
        var beforeRouting = app.routing.before("signup");
        expect(beforeRouting).toBeTruthy();
    });

    it("should call a route handler and routing.after if routing.before returns true", function() {
        sinon.stub(app.routing, "before", function() { return true; });
        var stub = sinon.stub(app.routing, "after");
        var stub2 = sinon.stub(app.router, "index");

        app.router._routeHandler(app.router.index);
        expect(stub).toHaveBeenCalled();
        expect(stub2).toHaveBeenCalled();
        app.routing.before.restore();
        app.routing.after.restore();
        app.router.index.restore();
    });

    it("should not call a route handler and routing.after if routing.before returns false", function() {
        sinon.stub(app.routing, "before", function() { return false; });
        var spy = sinon.spy(app.routing, "after");
        var spy2 = sinon.spy(app.router, "index");

        app.router._routeHandler(app.router.index);
        expect(spy).not.toHaveBeenCalled();
        expect(spy2).not.toHaveBeenCalled();
        app.routing.before.restore();
        app.routing.after.restore();
        app.router.index.restore();
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

        route = router.buildRoute(context, action, {}, options);

        expect(route).toEqual("Contacts/create?first=Rick&last=Astley&job=Rock+Star");
    });

});
