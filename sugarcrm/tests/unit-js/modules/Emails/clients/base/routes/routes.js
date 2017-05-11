describe('Emails.Routes', function() {
    var app;
    var oldSync;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;

        app.routing.start();
        SugarTest.loadFile('../modules/Emails/clients/base/routes', 'routes', 'js', function(d) {
            app.events.off('router:init');
            eval(d);
            app.events.trigger('router:init');
        });

        oldSync = app.isSynched;
        app.isSynced = true;

        sandbox = sinon.sandbox.create();
        sandbox.stub(app.api, 'isAuthenticated').returns(true);
    });

    afterEach(function() {
        app.router.navigate('', {trigger: true});
        Backbone.history.stop();
        sandbox.restore();
        app.isSynched = oldSync;
    });

    describe('compose a new email', function() {
        it('should open the compose drawer when routing from another page in the app', function() {
            // Routing from layout.
            app.controller.context.set('layout', 'foo');
            sandbox.stub(app.utils, 'openEmailCreateDrawer');

            app.router.navigate('Emails/compose', {trigger: true});

            expect(app.utils.openEmailCreateDrawer).toHaveBeenCalledWith('compose-email');
        });

        it('should open the full page composer when routing from login', function() {
            // Routing from login.
            app.controller.context.set('layout', 'login');
            sandbox.stub(app.controller, 'loadView');

            app.router.navigate('Emails/compose', {trigger: true});

            expect(app.controller.loadView).toHaveBeenCalledOnce();
            expect(app.controller.loadView.firstCall.args[0].layout).toBe('compose-email');
            expect(app.controller.loadView.firstCall.args[0].action).toBe('create');
        });

        it('should open the full page composer when routing directly', function() {
            // Routing from outside the app.
            app.controller.context.unset('layout');
            sandbox.stub(app.controller, 'loadView');

            app.router.navigate('Emails/compose', {trigger: true});

            expect(app.controller.loadView).toHaveBeenCalledOnce();
            expect(app.controller.loadView.firstCall.args[0].layout).toBe('compose-email');
            expect(app.controller.loadView.firstCall.args[0].action).toBe('create');
        });
    });

    describe('editing a draft', function() {
        var model;

        beforeEach(function() {
            model = app.data.createBean('Emails');
            sandbox.stub(app.data, 'createBean');
            app.data.createBean.returns(model);
            sandbox.stub(model, 'fetch', function(options) {
                options.success(model);
            });
        });

        it('should open the compose drawer when routing from another page in the app', function() {
            model.set('state', 'Draft');

            // Routing from layout.
            app.controller.context.set('layout', 'foo');
            sandbox.stub(app.utils, 'openEmailCreateDrawer');

            app.router.navigate('Emails/123/compose', {trigger: true});

            expect(app.utils.openEmailCreateDrawer).toHaveBeenCalledWith('compose-email');
        });

        it('should open the full page composer when routing from login', function() {
            model.set('state', 'Draft');

            // Routing from login.
            app.controller.context.set('layout', 'login');
            sandbox.stub(app.controller, 'loadView');

            app.router.navigate('Emails/123/compose', {trigger: true});

            expect(app.controller.loadView).toHaveBeenCalledOnce();
            expect(app.controller.loadView.firstCall.args[0].layout).toBe('compose-email');
            expect(app.controller.loadView.firstCall.args[0].action).toBe('edit');
        });

        it('should open the full page composer when routing directly', function() {
            model.set('state', 'Draft');

            // Routing from outside the app.
            app.controller.context.unset('layout');
            sandbox.stub(app.controller, 'loadView');

            app.router.navigate('Emails/123/compose', {trigger: true});

            expect(app.controller.loadView).toHaveBeenCalledOnce();
            expect(app.controller.loadView.firstCall.args[0].layout).toBe('compose-email');
            expect(app.controller.loadView.firstCall.args[0].action).toBe('edit');
        });

        it('should open the record view if the email is not a draft', function() {
            model.set('state', 'Archived');
            sandbox.stub(app.router, 'record');

            app.router.navigate('Emails/123/compose', {trigger: true});

            expect(app.router.record).toHaveBeenCalled();
        });
    });
});
