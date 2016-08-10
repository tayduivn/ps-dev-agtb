describe('Emails.Views.Record', function() {
    var app;
    var view;
    var sandbox;

    beforeEach(function() {
        var context;
        var viewName = 'record';
        var moduleName = 'Emails';

        app = SugarTest.app;
        app.drawer = {on: $.noop, off: $.noop, getHeight: $.noop, close: $.noop, reset: $.noop};

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');

        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        context = app.context.getContext();
        context.set({
            module: moduleName,
            create: true
        });
        context.prepare();

        view = SugarTest.createView('base', moduleName, viewName, null, context, true);

        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        sandbox.restore();
    });

    describe('Delete Confirmation', function() {
        it('should display (no subject) as the record name on delete confirmation', function() {
            var name = view._getNameForMessage(view.model);
            expect(name).toBe('LBL_NO_SUBJECT');
        });

        it('should display the record name when not empty on delete confirmation', function() {
            var recordName = 'Test Record';

            view.model.set('name', recordName);
            var name = view._getNameForMessage(view.model);
            expect(name).toBe(recordName);
        });
    });

    describe('alert the user that the email is a draft', function() {
        beforeEach(function() {
            sandbox.stub(app.alert, 'show');
        });

        it('should alert the user when state changes to draft on the model', function() {
            view.model.set('state', view.STATE_DRAFT);
            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should alert the user when the model is synced and state becomes draft', function() {
            sandbox.stub(view.model, 'sync', function(method, model, options) {
                options.success({state: view.STATE_DRAFT});
            });
            view.model.save();
            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should alert the user when the model starts with state equal to draft', function() {
            var context = app.context.getContext();

            context.set({
                module: 'Emails',
                create: true
            });
            context.prepare();
            context.get('model').set('state', view.STATE_DRAFT);

            view = SugarTest.createView('base', 'Emails', 'record', null, context, true);

            expect(app.alert.show).toHaveBeenCalled();
        });
    });
});
