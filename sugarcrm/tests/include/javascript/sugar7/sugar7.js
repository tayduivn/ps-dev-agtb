describe('Sugar7.Routes', function() {
    var app, router, navigateStub, appController, appUserLastState;

    beforeEach(function() {
        app = SugarTest.app;
        navigateStub = sinon.stub(app.Router.prototype, 'navigate');
        appController = app.controller;
        app.controller = {
            loadView: sinon.stub()
        };
        appUserLastState = app.user.lastState;
        app.user.lastState = {
            buildKey: sinon.stub(),
            get: sinon.stub(),
            set: sinon.stub()
        };

        SugarTest.loadFile("../include/javascript", "sugar7", "js", function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
        app.routing.start();
        router = app.router;
    });

    afterEach(function() {
        app.router = null;
        app.controller = appController;
        app.user.lastState = appUserLastState;
        navigateStub.restore();
    });

    describe("Home.Routing", function() {
        var mockKey = 'foo:key',
            homeOptions = {
                dashboard: 'dashboard',
                activities: 'activities'
            };

        beforeEach(function () {
            app.user.lastState.buildKey.returns(mockKey);
        });

        it("should load dashboard layout if last visited Home is dashboard", function() {
            var route = _.find(app.router.customRoutes, function(route) {return (route.name==='home')});
            app.user.lastState.get.returns(homeOptions.dashboard);
            route.callback();
            expect(app.controller.loadView.calledWith({module: 'Home', layout: 'records'})).toBe(true);
        });

        it("should navigate to activities route if last visited Home is activity stream", function() {
            var route = _.find(app.router.customRoutes, function(route) {return (route.name==='home')});
            app.user.lastState.get.returns(homeOptions.activities);
            route.callback();
            expect(navigateStub.calledWith('#activities', {trigger: true})).toBe(true);
        });

        it("should set last visited Home to activity stream when routing to activity stream", function() {
            var route = _.find(app.router.customRoutes, function(route) {return (route.name==='activities')});
            route.callback();
            expect(app.user.lastState.set.calledWith(mockKey, homeOptions.activities));
        });

        it("should set last visited Home to dashboard when routing to a dashboard", function() {
            var route = _.find(app.router.customRoutes, function(route) {return (route.name==='homeRecord')}),
                routerRecordStub = sinon.stub(app.router, 'record');
            route.callback();
            expect(app.user.lastState.set.calledWith(mockKey, homeOptions.dashboard));
            routerRecordStub.restore();
        });
    });
});
