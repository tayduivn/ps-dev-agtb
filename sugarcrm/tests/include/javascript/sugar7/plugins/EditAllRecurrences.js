describe("Plugins.EditAllRecurrences", function() {
    var moduleName = 'Meetings',
        view,
        pluginsBefore,
        app,
        sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();

        view = SugarTest.createView('base', moduleName, 'record');
        pluginsBefore = view.plugins;
        view.plugins = ['EditAllRecurrences'];
        SugarTest.loadPlugin('EditAllRecurrences');
        SugarTest.app.plugins.attach(view, 'view');
        view.trigger('init');
        sandbox = sinon.sandbox.create();
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

    it("should turn on all recurrence mode when all_recurrences:edit event is fired from parent", function() {
        view.model.set('repeat_parent_id', '');
        view.allRecurrencesMode = false;
        view.context.trigger('all_recurrences:edit');
        expect(view.allRecurrencesMode).toEqual(true);
    });

    it("should redirect to parent when all_recurrences:edit event is fired from child", function() {
        var repeatParentId = '123',
            navigateStub = sandbox.stub(app.router, 'navigate');

        sandbox.stub(app.alert, 'show', function(alertName, options) {
            options.onConfirm();
        });
        view.model.set('repeat_parent_id', repeatParentId);
        view.allRecurrencesMode = false;
        view.context.trigger('all_recurrences:edit');
        expect(navigateStub.callCount).toEqual(1);
        expect(navigateStub.lastCall.args[0]).toEqual('#Meetings/123/edit/all-recurrences');
    });

    it("should turn off all recurrence mode on initial render", function() {
        view.allRecurrencesMode = true;
        view.render();
        expect(view.allRecurrencesMode).toEqual(false);
    });

    it("should turn go into edit all recurrence mode when rendering with all_recurrences in the context and edit button initialized", function() {
        var button = SugarTest.createField('base', 'button', 'button');
        view.context.set('all_recurrences', true);
        view.buttons.edit_recurrence_button = button;
        view.render();
        expect(view.allRecurrencesMode).toEqual(true);
        //clear out all_recurrences so next render will not do this
        expect(view.context.get('all_recurrences')).toBeUndefined();
        button.dispose();
    });

    it("should turn off all recurrence mode when the cancel button is clicked", function() {
        view.allRecurrencesMode = true;
        view.cancelClicked();
        expect(view.allRecurrencesMode).toEqual(false);
    });

    it("should add all_recurrences flag to save options when in all recurrence mode", function() {
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

    it("should prevent toggling out of all recurrence mode when there is no recurrence", function() {
        view.allRecurrencesMode = true;
        view.model.set('repeat_type', '');
        view.toggleAllRecurrencesMode(false);
        expect(view.allRecurrencesMode).toEqual(true);
    });

    it("should update noEditFields when toggling all recurrence mode", function() {
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
});
