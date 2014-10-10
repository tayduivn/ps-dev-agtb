describe('Plugins.EditAllRecurrences', function() {
    var moduleName = 'Meetings',
        view,
        pluginsBefore,
        app,
        sandbox,
        navigateStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();
        sandbox = sinon.sandbox.create();

        navigateStub = sandbox.stub(app.router, 'navigate');
        view = SugarTest.createView('base', moduleName, 'record');
        view.model.set('repeat_type', 'Daily');
        pluginsBefore = view.plugins;
        view.plugins = ['EditAllRecurrences'];
        SugarTest.loadPlugin('EditAllRecurrences');
        SugarTest.app.plugins.attach(view, 'view');

        view.trigger('init');
    });

    afterEach(function() {
        sandbox.restore();
        view.plugins = pluginsBefore;
        view.dispose();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        app.cache.cutAll();
        view = null;
    });

    it('should turn on all recurrence mode when all_recurrences:edit event is fired from parent', function() {
        view.model.set('repeat_parent_id', '');
        view.allRecurrencesMode = false;
        view.context.trigger('all_recurrences:edit');
        expect(view.allRecurrencesMode).toEqual(true);
    });

    it('should redirect to parent when all_recurrences:edit event is fired from child', function() {
        var repeatParentId = '123';

        view.model.set('repeat_parent_id', repeatParentId);
        view.allRecurrencesMode = false;
        view.context.trigger('all_recurrences:edit');
        expect(navigateStub.callCount).toEqual(1);
        expect(navigateStub.lastCall.args[0]).toEqual('#Meetings/123/edit/all-recurrences');
    });

    it('should init all recurrence mode to false if not coming from all_recurrence route', function() {
        view.allRecurrencesMode = true;
        view.trigger('init');
        expect(view.allRecurrencesMode).toEqual(false);
    });

    it('should go into edit all recurrence mode when all_recurrences from a route', function() {
        view.allRecurrencesMode = undefined;
        view.context.set('all_recurrences', true);
        view.trigger('init');
        expect(view.allRecurrencesMode).toEqual(true);
        // all_recurrences should be cleared out too
        expect(view.context.get('all_recurrences')).toBeUndefined();
    });

    it('should turn off all recurrence mode when the cancel button is clicked', function() {
        view.allRecurrencesMode = true;
        view.cancelClicked();
        expect(view.allRecurrencesMode).toEqual(false);
    });

    it('should add all_recurrences flag to save options when in all recurrence mode', function() {
        var actual,
            expected = {
                params: {
                    all_recurrences: true
                }
            };
        view.allRecurrencesMode = true;
        actual = view.getCustomSaveOptions();
        expect(actual).toEqual(expected);
    });

    it('should prevent toggling out of all recurrence mode when repeat_type is blank', function() {
        view.allRecurrencesMode = true;
        view.model.set('repeat_type', '');
        view.toggleAllRecurrencesMode(false);
        expect(view.allRecurrencesMode).toEqual(true);
    });

    it('should force into all recurrence mode when repeat_type is blank on sync', function() {
        view.allRecurrencesMode = false;
        view.model.set('repeat_type', '');
        view.model.trigger('sync');
        expect(view.allRecurrencesMode).toEqual(true);
    });

    it('should update noEditFields when toggling all recurrence mode', function() {
        view.toggleAllRecurrencesMode(true);
        expect(view.noEditFields).toEqual([]);
        view.toggleAllRecurrencesMode(false);
        expect(view.noEditFields).toEqual([
            'repeat_type',
            'recurrence',
            'repeat_interval',
            'repeat_dow',
            'repeat_until',
            'repeat_count'
        ]);
    });

    it('should show a /edit/all_recurrences route when editing all recurrences', function() {
        view.allRecurrencesMode = true;
        view.model.id = 'foo_id';
        view.editClicked();
        expect(navigateStub).toHaveBeenCalledWith('Meetings/foo_id/edit/all_recurrences', {trigger: false});
    });
});
