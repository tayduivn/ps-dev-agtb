describe('Quotes.Routes', function() {
    var app;
    var loadViewStub;
    var buildKeyStub;
    var getStub;
    var setStub;

    beforeEach(function() {
        app = SugarTest.app;
        app.controller.loadAdditionalComponents(app.config.additionalComponents);
        loadViewStub = sinon.collection.stub(app.controller, 'loadView');
        buildKeyStub = sinon.collection.stub(app.user.lastState, 'buildKey');
        getStub = sinon.collection.stub(app.user.lastState, 'get');
        setStub = sinon.collection.stub(app.user.lastState, 'set');
        sinon.sandbox.stub(app.api, 'isAuthenticated').returns(true);

        SugarTest.loadFile('../modules/Quotes/clients/base/routes', 'routes', 'js', function(d) {
            app.events.off('router:init');
            eval(d);
            app.events.trigger('router:init');
        });

        app.routing.start();

    });

    afterEach(function() {
        sinon.collection.restore();
    });

    it('should load the create view in bwc mode', function() {
        var options = {
                layout: 'bwc',
                url: 'index.php?module=Quotes&action=EditView&return_module=Quotes'
            };

        app.router.navigate('Quotes/create', {trigger: true});
        expect(app.controller.loadView).toHaveBeenCalledWith(options);

    });

});
