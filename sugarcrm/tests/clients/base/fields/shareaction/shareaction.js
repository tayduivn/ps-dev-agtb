describe('Base.Field.Shareaction', function() {

    var app, field, view, moduleName = 'Contacts';

    beforeEach(function() {
        app = SugarTest.app;
        app.drawer = { open: $.noop };

        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'shareaction');
        field = SugarTest.createField('base', 'shareaction', 'shareaction', 'edit', {
            'type': 'shareaction',
            'name': 'share',
            'acl_action': 'view'
        }, moduleName);
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
            shareWithMailToStub = sinon.collection.stub(field, '_shareWithMailTo'),
            drawerOpenStub = sinon.collection.stub(app.drawer, 'open'),
            prefStub = sinon.collection.stub(app.user, 'getPreference');

        // FIXME this should send boolean value (need to fix user prefs).
        prefStub.withArgs('use_sugar_email_client').returns('true');

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
        expect(shareWithMailToStub).not.toHaveBeenCalled();
    });

    it('should share using "mailto" when user preference is not set of set to false', function() {

        var shareWithMailToStub = sinon.collection.stub(field, '_shareWithMailTo'),
            shareWithSugarEmailClientStub = sinon.collection.stub(field, '_shareWithSugarEmailClient'),
            prefStub = sinon.collection.stub(app.user, 'getPreference');

        // FIXME this should send boolean value (need to fix user prefs).
        prefStub.withArgs('use_sugar_email_client').returns('false');

        field.share();

        expect(shareWithMailToStub).toHaveBeenCalled();
        expect(shareWithSugarEmailClientStub).not.toHaveBeenCalled();

        prefStub.withArgs('use_sugar_email_client').returns();

        field.share();

        expect(shareWithMailToStub).toHaveBeenCalled();
        expect(shareWithSugarEmailClientStub).not.toHaveBeenCalled();
    });
});
