describe('Home.Routes', function() {
    var app, loadViewStub, buildKeyStub, getStub, setStub;

    beforeEach(function() {
        app = SugarTest.app;
        app.controller.loadAdditionalComponents(app.config.additionalComponents);
        // FIXME: SC-4677, load additionalComponents in tests
        // "Before Route Show Wizard Check" dependency
        loadViewStub = sinon.collection.stub(app.controller, 'loadView');
        buildKeyStub = sinon.collection.stub(app.user.lastState, 'buildKey');
        getStub = sinon.collection.stub(app.user.lastState, 'get');
        setStub = sinon.collection.stub(app.user.lastState, 'set');

        SugarTest.loadFile('../modules/Home/clients/base/routes', 'routes', 'js', function(d) {
            eval(d);
            app.routing.start();
        });
    });

    afterEach(function() {
        sinon.collection.restore();
        app.routing.stop();
        app.events.off('router:init');
    });

    describe('Routes', function() {
        var mockKey = 'foo:key';
        var oldIsSynced;

        beforeEach(function() {
            oldIsSynced = app.isSynced;
            app.isSynced = true;
            sinon.collection.stub(app.router, 'index');
            sinon.collection.stub(app.router, 'hasAccessToModule').returns(true);
            sinon.collection.stub(app.api, 'isAuthenticated').returns(true);
            sinon.collection.stub(app, 'sync');
            buildKeyStub.returns(mockKey);
        });

        afterEach(function() {
            app.isSynced = oldIsSynced;
            app.router.navigate('', {trigger: true});
            Backbone.history.stop();
        });

        describe('Activities', function() {
            it('should set last visited Home to activity stream when routing to activity stream', function() {
                app.router.navigate('activities', {trigger: true});

                expect(setStub).toHaveBeenCalledWith(mockKey, 'activities');
                expect(loadViewStub).toHaveBeenCalledWith({
                    layout: 'activities',
                    module: 'Activities'
                });
            });
        });

        describe('homeRecord', function() {
            var recordStub;

            beforeEach(function() {
                recordStub = sinon.collection.stub(app.router, 'record');
            });

            it('should set last visited Home to dashboard when routing to a dashboard', function() {
                app.router.navigate('Home/test_ID', {trigger: true});

                expect(setStub).toHaveBeenCalledWith(mockKey, 'dashboard');
                expect(recordStub).toHaveBeenCalledWith('Home', 'test_ID');
            });
        });

        describe('Home', function() {
            var redirectStub,
                listStub;

            beforeEach(function() {
                redirectStub = sinon.collection.stub(app.router, 'redirect');
                listStub = sinon.collection.stub(app.router, 'list');
            });

            using('homeOptions', [
                {
                    value: 'dashboard',
                    redirectCalled: false,
                    listRouteCalled: true
                },
                {
                    value: 'activities',
                    redirectCalled: true,
                    listRouteCalled: false
                }
            ], function(option) {
                it('should navigate to the appropriate route according to the lastState', function() {
                    getStub.returns(option.value);
                    app.router.navigate('Home', {trigger: true});

                    expect(redirectStub.calledWith('#activities')).toBe(option.redirectCalled);
                    expect(listStub.called).toBe(option.listRouteCalled);
                });
            });
        });
    });
});
