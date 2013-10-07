describe('Tabbed Dashlet', function () {
    var moduleName = 'Home',
        app, view;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('tabbed-dashlet', {
            'tabs': [
                {
                    'module': 'Meetings',
                    'invitation_actions' : {
                        'name' : 'accept_status_users',
                        'type' : 'invitation-actions'
                    }
                }
            ]
        });

        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', moduleName, 'tabbed-dashlet');
        view.settings = new Backbone.Model();

        view._defaultSettings = {
            filter: 7,
            limit: 10,
            visibility: 'user'
        };
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.view.reset();
    });

    it('should retrieve its default settings', function() {

        view._initSettings();
        expect(view.settings.get('filter')).toEqual(view._defaultSettings.filter);
        expect(view.settings.get('limit')).toEqual(view._defaultSettings.limit);
        expect(view.settings.get('visibility')).toEqual(view._defaultSettings.visibility);
    });

    it('should override its default settings', function() {
        view.settings.set('filter', 12);

        view._initSettings();
        expect(view.settings.get('filter')).toEqual(12);
        expect(view.settings.get('limit')).toEqual(view._defaultSettings.limit);
        expect(view.settings.get('visibility')).toEqual(view._defaultSettings.visibility);
    });
});
