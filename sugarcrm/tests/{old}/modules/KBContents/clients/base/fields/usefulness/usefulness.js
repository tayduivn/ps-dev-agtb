describe('modules.kbcontents.clients.base.fields.usefulness', function() {
    var sandbox, app, field,
        module = 'KBContents',
        fieldName = 'usefulness',
        fieldType = 'usefulness',
        model;

    beforeEach(function() {
        sandbox = sinon.sandbox.create();
        Handlebars.templates = {};
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'edit', module);
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'detail', module);
        SugarTest.testMetadata.set();
        
        app = SugarTest.app;
        app.data.declareModels();
        model = app.data.createBean(module);
        field = SugarTest.createField('base', fieldName, fieldType, 'detail', {}, module, model, null, true);
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
    });

    it('should have default vote values', function() {
        expect(field.model.get('useful')).toEqual(0);
        expect(field.model.get('notuseful')).toEqual(0);
    });

    it('should be able vote and set useful when useful button clicked', function() {
        var voteSpy = sinon.spy(field, 'vote'),
            urlRegExp = new RegExp('.*rest/v10/' + module + '/.*');
            server = sandbox.useFakeServer();
        server.respondWith(
            'PUT',
            urlRegExp,
            [200, {'Content-Type': 'application/json'}, JSON.stringify({
                'id': 'id',
                'usefulness_user_vote': '1'
            })]
        );
        field.render();
        field.$('[data-action="useful"]').click();
        server.respond();

        expect(voteSpy).toHaveBeenCalledWith(true);
        expect(field.votedUseful).toEqual(true);
        expect(field.votedNotUseful).toEqual(false);
    });

    it('should be able vote and set notuseful when notuseful button clicked', function() {
        var voteSpy = sandbox.spy(field, 'vote'),
            urlRegExp = new RegExp('.*rest/v10/' + module + '/.*');
            server = sandbox.useFakeServer();
        server.respondWith(
            'PUT',
            urlRegExp,
            [200, {'Content-Type': 'application/json'}, JSON.stringify({
                'id': 'id',
                'usefulness_user_vote': '-1'
            })]
        );
        field.render();
        field.$('[data-action="notuseful"]').click();
        server.respond();

        expect(voteSpy).toHaveBeenCalledWith(false);
        expect(field.votedUseful).toEqual(false);
        expect(field.votedNotUseful).toEqual(true);
    });
});
