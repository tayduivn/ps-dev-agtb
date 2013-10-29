describe('Base.Field.Shareaction', function() {

    var app, field, view, moduleName = 'Contacts';

    beforeEach(function() {
        app = SugarTest.app;
        app.drawer = { open: $.noop };

        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'shareaction');

    });

    afterEach(function() {
        app.drawer = undefined;
        sinon.collection.restore();
        field.dispose();
        field = null;
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    it('should share using email compose action when user preference is true', function() {
        var model, createBeanStub,
            drawerOpenStub = sinon.collection.stub(app.drawer, 'open'),
            prefStub = sinon.collection.stub(app.user, 'getPreference');

        prefStub.withArgs('use_sugar_email_client').returns('true');

        field = SugarTest.createField('base', 'shareaction', 'shareaction', 'edit', {
            'type': 'shareaction',
            'name': 'share',
            'acl_action': 'view'
        }, moduleName);

        model = app.data.createBean('Emails', {
            subject: 'Subject for this jasmine test',
            html_body: '<p>HTML body for this jasmine test</p> <br>'
        });

        createBeanStub = sinon.collection.stub(app.data, 'createBean', function() {
            return model;
        });

        sinon.collection.stub(field, 'shareTplSubject', function() {
            return 'Subject for this jasmine test';
        });
        sinon.collection.stub(field, 'shareTplBody', function() {
            return 'Body for this jasmine test';
        });
        sinon.collection.stub(field, 'shareTplBodyHtml', function() {
            return '<p>HTML body for this jasmine test</p> <br>';
        });

        field.share();

        expect(createBeanStub).toHaveBeenCalledWith('Emails', {
            subject: 'Subject for this jasmine test',
            html_body: '<p>HTML body for this jasmine test</p> <br>'
        });
        expect(drawerOpenStub).toHaveBeenCalledWith({
            layout: 'compose',
            context: {
                create: true,
                module: 'Emails',
                model: model
            }
        });

        expect(field.options.def.href).toBeUndefined();

        drawerOpenStub.restore();
        prefStub.restore();
    });

    it('should share using "mailto" when user preference is set to false', function() {
        var context, model, view,
            prefStub = sinon.collection.stub(app.user, 'getPreference'),
            mailToText = 'MailTo',

            shareWithMailToMock = sinon.collection.stub(
                app.view.fields.BaseShareactionField.prototype, '_shareWithMailTo',
                function() {
                    return mailToText;
                }
            );

        prefStub.withArgs('use_sugar_email_client').returns('false');

        context = app.context.getContext();
        context.set({
            module: moduleName
        });
        context.prepare();

        view = new app.view.View({ name: 'edit', context: context });

        model = new Backbone.Model();
        model.fields = {};

        field = app.view.createField({
            context: context,
            view: view,
            model: model,
            def: { name: 'share', type: 'shareaction', events: {} },
            module: moduleName
        });
        expect(field.options.def.href).toEqual(mailToText);

        shareWithMailToMock.restore();
    });

    it('should share using "mailto" when user preference is not set', function () {
        var context, model, view,
            prefStub = sinon.collection.stub(app.user, 'getPreference'),
            mailToText = 'MailTo',
            shareWithMailToMock = sinon.collection.stub(
                app.view.fields.BaseShareactionField.prototype, '_shareWithMailTo',
                function () {
                    return mailToText;
                }
            );

        prefStub.withArgs('use_sugar_email_client').returns('false');

        context = app.context.getContext();
        context.set({
            module: moduleName
        });
        context.prepare();

        view = new app.view.View({ name: 'edit', context: context });

        model = new Backbone.Model();
        model.fields = {};

        field = app.view.createField({
            context: context,
            view: view,
            model: model,
            def: { name: 'share', type: 'shareaction', events: {} },
            module: moduleName
        });
        expect(field.options.def.href).toEqual(mailToText);

        shareWithMailToMock.restore();
    });

});
