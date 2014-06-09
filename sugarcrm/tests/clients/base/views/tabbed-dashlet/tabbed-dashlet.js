/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */
describe('Base.View.TabbedDashlet', function() {
    var moduleName = 'Accounts',
        viewName = 'tabbed-dashlet',
        layoutName = 'tabbed-layout',
        app, view, layout;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(layoutName, 'layout', 'base');
        SugarTest.loadComponent('base', 'layout', layoutName);
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.testMetadata.addViewDefinition(
            viewName,
            {
                'tabs': [
                    {
                        'module': 'Meetings',
                        'invitation_actions': {
                            'name': 'accept_status_users',
                            'type': 'invitation-actions'
                        }
                    }
                ],
                'panels': [
                    {
                        'name': 'panel_body',
                        'columns': 1,
                        'placeholders': true,
                        'fields': [
                            {name: 'visibility', type: 'base', label: 'visibility'}
                        ]
                    }
                ]
            },
            moduleName
        );

        SugarTest.testMetadata.set();
        app.data.declareModels();
        SugarTest.loadPlugin('Dashlet');

        layout = SugarTest.createLayout('base', moduleName, layoutName);
        view = SugarTest.createView('base', moduleName, viewName, null, null, null, layout);
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
        delete app.plugins.plugins['view']['Dashlet'];
        view = null;
        layout = null;
        app = null;
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

    describe('Visibility toggle.', function() {
        it('Should set state when visibility is toggled.', function() {
            view._initVisibility();
            var event = $.Event('click');
            var element = event.currentTarget = $('<input/>', {value: 'test'});
            element.appendTo(view.$el);
            var setStateStub = sinon.collection.stub(app.user.lastState, 'set');
            //Prevent actual calls to load data (makes an XHR request)
            sinon.collection.stub(layout, 'loadData');

            view.visibilitySwitcher(event);
            expect(setStateStub.calledOnce).toBe(true);
            // Shouldn't be toggled twice with same value.
            view.visibilitySwitcher(event);
            expect(setStateStub.calledTwice).toBe(false);
        });
    });
});
