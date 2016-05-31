describe('Emails.Routes', function() {
    var app, oldSync;
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

        sinon.sandbox.stub(app.api, 'isAuthenticated').returns(true);
    });

    afterEach(function() {
        app.router.navigate('', {trigger: true});
        Backbone.history.stop();
        sinon.sandbox.restore();
        app.isSynched = oldSync;
    });

    describe('Routes', function() {
        describe('Email Draft', function() {
            var model;

            beforeEach(function() {
                model = app.data.createBean('Emails', {id: '123'});
                sinon.sandbox.stub(app.data, 'createBean');
                app.data.createBean.returns(model);
                sinon.sandbox.stub(model, 'fetch', function(options) {
                    options.success(model);
                });
            });

            it('should open create drawer when routing from another page in the app', function() {
                model.set('state', 'Draft');

                //routing from layout
                app.controller.context.set('layout', 'foo');

                app.drawer = app.drawer || {open: $.noop};
                sinon.sandbox.stub(app.drawer, 'open');

                app.router.navigate('Emails/drafts/' + model.id, {trigger: true});
                expect(app.drawer.open).toHaveBeenCalled();
            });

            it('should open full page create when routing from login', function() {
                model.set('state', 'Draft');

                //routing from login
                app.controller.context.set('layout', 'login');

                sinon.sandbox.stub(app.controller, 'loadView');

                app.router.navigate('Emails/drafts/' + model.id, {trigger: true});
                expect(app.controller.loadView).toHaveBeenCalled();
            });

            it('should open full page create when routing directly', function() {
                model.set('state', 'Draft');

                //routing from outside the app
                app.controller.context.unset('layout');

                sinon.sandbox.stub(app.controller, 'loadView');

                app.router.navigate('Emails/drafts/' + model.id, {trigger: true});
                expect(app.controller.loadView).toHaveBeenCalled();
            });

            it('should open record view if email is not a draft', function() {
                model.set('state', 'Archived');

                sinon.sandbox.stub(app.router, 'record');

                app.router.navigate('Emails/drafts/' + model.id, {trigger: true});
                expect(app.router.record).toHaveBeenCalled();
            });
        });
    });
});
