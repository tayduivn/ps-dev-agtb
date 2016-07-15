/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
describe('NotificationCenter.Layout.ConfigDrawerContent', function() {
    var app, layout, sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        var context = app.context.getContext();
        context.set({
            model: app.data.createBean('NotificationCenter', {configMode: 'global'})
        });
        var meta = {
            components: []
        };

        layout = SugarTest.createLayout('base', 'NotificationCenter', 'config-drawer-content', meta, context, true);
        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        sandbox.restore();
        layout = null;
    });

    it('should call _createViews() before it is rendered', function() {
        var method = sandbox.spy(layout, '_createViews');
        layout.triggerBefore('render');
        expect(method).toHaveBeenCalled();
    });

    describe('_createViews()', function() {
        using('methods',
            ['removeComponent', 'addComponent', 'createComponentFromDef'],
            function(method) {
                it('should not call any method in case of empty model', function() {
                    var spied = sandbox.spy(layout, method);
                    layout._createViews();
                    expect(spied).not.toHaveBeenCalled();
                });
            });

        it('should have only config-carriers view component when model is empty', function() {
            layout._createViews();
            expect(layout._components.length).toBe(1);
        });

        it('should have 3 config-view components when model has emitters data', function() {
            layout.model.set('config', {
                'fooEmitter': {},
                'barEmitter': {}
            });
            layout._createViews();
            expect(layout._components.length).toBe(3);
        });
    });

    describe('selectPanel()', function() {
        using('panel names',
            [[{name: 'foo'}, 'foo'], ['bar', 'bar'], [{}, {}]],
            function(panelName, name) {
                it('should extract panel\'s name from given def correctly', function() {
                    var superMethod = sandbox.spy(layout, '_super');
                    layout.selectPanel(panelName);
                    expect(superMethod).toHaveBeenCalledWith('selectPanel', [name]);
                });
            });
    });

    describe('_switchHowToData()', function() {
        var module;
        var suffix;

        beforeEach(function() {
            layout.currentHowToData = {};
            module = layout.module;
            layout.model.set('configMode', 'user');
            suffix = '-' + app.utils.generateUUID();
        });

        it('should set currentHowToData properly for Carriers', function() {
            layout._switchHowToData('config-carriers' + suffix);
            expect(layout.currentHowToData.title).toEqual(
                app.lang.get('LBL_CARRIER_DELIVERY_OPTION_TITLE_USER', module)
            );
            expect(layout.currentHowToData.text).toEqual(app.lang.get('LBL_CARRIER_DELIVERY_OPTION_HELP_USER', module));
        });

        it('should set currentHowToData properly for Application Emitter', function() {
            layout._switchHowToData('config-ApplicationEmitter' + suffix);
            expect(layout.currentHowToData.title).toEqual(app.lang.get('LBL_APPLICATION_EMITTER_TITLE_USER', module));
            expect(layout.currentHowToData.text).toEqual(app.lang.get('LBL_APPLICATION_EMITTER_HELP_USER', module));
        });

        it('should set currentHowToData properly for Bean Emitter', function() {
            layout._switchHowToData('config-BeanEmitter' + suffix);
            expect(layout.currentHowToData.title).toEqual(app.lang.get('LBL_BEAN_EMITTER_TITLE_USER', module));
            expect(layout.currentHowToData.text).toEqual(app.lang.get('LBL_BEAN_EMITTER_HELP_USER', module));
        });

        using('config-module views and module names',
            [['config-Accounts', 'Accounts'], ['config-', '']],
            function(viewName, moduleName) {
                it('should extract module name from view name correctly', function() {
                    var appLangGet = sandbox.spy(app.lang, 'get');
                    layout._switchHowToData(viewName + suffix);
                    expect(appLangGet).toHaveBeenCalledWith('LBL_EMITTER_TITLE_USER', moduleName);
                    expect(appLangGet).toHaveBeenCalledWith('LBL_EMITTER_HELP_USER', moduleName);
                });
            });
    });
});
