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
describe('Base.View.Dashablerecord', function() {
    var app;
    var context;
    var dashboard;
    var dashletToolbar;
    var dashletToolbarContext;
    var layout;
    var layoutName = 'dashlet';
    var moduleName = 'Cases'; // important: visible is true in metadata.json for Cases, so we can select it as a module
    var recordMeta = {
        panels: [
            {
                name: 'panel_header',
                fields: [
                    {
                        name: 'picture',
                        type: 'avatar',
                        size: 'large',
                    },
                    {
                        name: 'name',
                    },
                    {
                        name: 'favorite',
                        type: 'favorite',
                    },
                    {
                        name: 'follow',
                        type: 'follow',
                    }
                ],
                header: true,
            },
            {
                name: 'businesscard',
                fields: [
                    {
                        name: 'case_number',
                        readonly: true,
                    }
                ],
            }
        ]
    };
    var view;
    var viewName = 'dashablerecord';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'layout', 'dashlet');
        SugarTest.loadComponent('base', 'layout', 'dashboard');

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'dashablerecord');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'businesscard');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'headerpane');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'noaccess');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'pick-a-record');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'tabs');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'record');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'records');

        SugarTest.testMetadata.addViewDefinition(
            'record',
            recordMeta,
            moduleName
        );
        SugarTest.testMetadata.set();
        app.data.declareModels();
        SugarTest.loadPlugin('Dashlet');

        context = app.context.getContext();
        context.set({
            module: moduleName,
            layout: layoutName
        });
        context.parent = app.data.createBean('Home');
        context.parent.set('dashboard_module', moduleName);
        context.prepare();

        dashboard = app.view.createLayout({
            name: 'dashboard',
            type: 'dashboard',
            context: context.parent,
        });

        layout = app.view.createLayout({
            name: layoutName,
            context: context,
            meta: {index: 0},
            layout: dashboard,
        });

        view = SugarTest.createView(
            'base',
            moduleName,
            viewName,
            {module: moduleName},
            context,
            false,
            layout
        );

        dashletToolbarContext = new app.Context();
        dashletToolbar = {context: dashletToolbarContext, editClicked: sinon.collection.stub()};
        sinon.collection.stub(layout, 'getComponent')
            .withArgs('dashlet-toolbar')
            .returns(dashletToolbar);
    });

    afterEach(function() {
        sinon.collection.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        dashboard.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
        layout = null;
        dashboard = null;
    });

    describe('Plugins', function() {
        it('should have most record view and list view plugins', function() {
            expect(view.plugins.sort()).toEqual([
                'Dashlet',
                'Editable',
                'ErrorDecoration',
                'GridBuilder',
                'Pagination',
                'SugarLogic',
                'ToggleMoreLess',
            ]);
        });
    });

    describe('initDashlet', function() {
        it('should update the module on settings change if in configuration mode', function() {
            view.initDashlet('config');
            view.settings.trigger('change:module', app.data.createBean('Home'), 'Cases');
            expect(view.dashModel.get('module')).toEqual('Cases');
        });
    });

    describe('bindDataChange', function() {
        it('should update the model, trigger dashlet:toolbar:change, and then re-render on change:model', function() {
            var switchModelStub = sinon.collection.stub(view, 'switchModel', function(model) { this.model = model; });
            var renderStub = sinon.collection.stub(view, 'render');
            var triggerStub = sinon.collection.stub(dashletToolbarContext, 'trigger');
            var model = app.data.createBean(moduleName);

            view.bindDataChange();
            view.context.trigger('change:model', model);

            expect(switchModelStub).toHaveBeenCalledWith(model);
            expect(triggerStub).toHaveBeenCalledWith(
                'dashlet:toolbar:change',
                [
                    {
                        name: 'picture',
                        type: 'avatar',
                        size: 'button', // test that we shrank the avatar for the smaller dashlet toolbar
                        height: 28,
                        width: 28, // default the size
                    },
                    {
                        name: 'name',
                        type: 'name',
                        label: 'LBL_SUBJECT',
                    },
                ],
                model
            );
            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('editClicked', function() {
        it('should proxy to the dashlet toolbar', function() {
            view.editClicked();
            expect(dashletToolbar.editClicked).toHaveBeenCalled();
        });
    });

    describe('switchModel', function() {
        it('should abort any ongoing fetches, stop listening to the old model, and change to the new one', function() {
            var oldModel = view.model;
            var abortFetchRequestStub = sinon.collection.stub(oldModel, 'abortFetchRequest');
            var stopListeningStub = sinon.collection.stub(view, 'stopListening');

            var newModel = app.data.createBean(moduleName, {id: '12345'});
            view.switchModel(newModel);

            expect(abortFetchRequestStub).toHaveBeenCalledOnce();
            expect(stopListeningStub).toHaveBeenCalledWith(oldModel);
            expect(view.model).toBe(newModel);
        });
    });

    describe('rendering', function() {
        var unavailableModuleMsg = 'This module is not available';
        var pickARecordMsg = 'Pick a record';

        beforeEach(function() {
            var langStub = sinon.collection.stub(app.lang, 'get');
            langStub.withArgs('LBL_DASHLET_MODULE_UNAVAILABLE')
                .returns(unavailableModuleMsg);
            langStub.withArgs('LBL_DASHLET_PICK_A_RECORD')
                .returns(pickARecordMsg);
        });

        it('should show the no access template if the module is not available', function() {
            view.moduleIsAvailable = false;

            view.render();

            expect(view.$('.block-footer').text().trim()).toEqual(unavailableModuleMsg);
        });

        it('should show the no access template if there is no model to show', function() {
            view.moduleIsAvailable = true;
            delete view.model;

            view.render();

            expect(view.$('.block-footer').text().trim()).toEqual(unavailableModuleMsg);
        });

        it('should update the toolbar header if the dashlet is in main mode', function() {
            view.moduleIsAvailable = true;
            view.model = app.data.createBean(view.module, {name: 'Test'});
            view.model.dataFetched = true;
            view.settings.set('module', moduleName);
            view.initDashlet('main');
            var triggerStub = sinon.collection.stub(dashletToolbarContext, 'trigger');

            view.render();

            // we test the precise field list elsewhere, no need to do it again here
            expect(triggerStub).toHaveBeenCalledWith('dashlet:toolbar:change');
        });

        it('should show the pick-a-record template if we are showing the wrong module', function() {
            view.initDashlet('main');
            view.model = app.data.createBean('Bugs');

            view.render();

            expect(view.$('.block-footer').text().trim()).toEqual(pickARecordMsg);
        });
    });

    describe('_initTabs', function() {
        it('should create tabs from metadata', function() {
            var createCollectionStub = sinon.collection.stub(view, '_createCollection').returns({
                link: {name: 'contacts'}
            });
            var getColumnsStub = sinon.collection.stub(view, '_getColumnsForTab').returns('columns');
            view.meta.tabs = [
                {
                    link: '',
                    module: 'Cases',
                },
                {
                    link: 'contacts',
                    module: 'Contacts',
                    fields: [
                        'name',
                        'date_entered',
                    ],
                    order_by: {
                        field: 'date_entered',
                        direction: 'desc',
                    },
                    limit: 10,
                }
            ];
            view._initTabs();
            expect(view.tabs.length).toEqual(2);
            expect(view.tabs[0].module).toBe('Cases');
            expect(view.tabs[0].link).toBe('');
            expect(view.tabs[0].collection).not.toBeDefined();
            expect(view.tabs[1].module).toBe('Contacts');
            expect(view.tabs[1].link).toBe('contacts');
            expect(view.tabs[1].relate).toBe(true);
            expect(view.tabs[1].collection).toBeDefined();
            expect(view.tabs[1].collection.display_columns).toBeDefined();
        });
    });

    describe('tabSwitcher', function() {
        it('should change tab', function() {
            var jDataStub = sinon.collection.stub($.fn, 'data').returns(1);
            var loadDataForTabsStub = sinon.collection.stub(view, '_loadDataForTabs');
            var tab1Collection = app.data.createBeanCollection('Contacts');
            tab1Collection.dataFetched = false;
            tab1Collection.display_columns = {};
            view.tabs = [
                {},
                {
                    collection: tab1Collection
                }
            ],
            view.tabSwitcher({currentTarget: '#tab1'});
            expect(view.settings.get('activeTab')).toEqual(1);
            expect(view.collection).toBe(tab1Collection);
            expect(loadDataForTabsStub).toHaveBeenCalledWith([view.tabs[1]]);
        });
    });

    describe('setOrderBy', function() {
        it('should change order by', function() {
            var jFindStub = sinon.collection.stub($.fn, 'find').returns({length: 0});
            var jDataStub = sinon.collection.stub($.fn, 'data').returns('name');
            var loadDataForTabsStub = sinon.collection.stub(view, '_loadDataForTabs');
            var accessStub = sinon.collection.stub(app.acl, 'hasAccess').returns(true);
            var collection = app.data.createBeanCollection('Contacts');
            var orderBy = {field: 'date_entered', direction: 'asc'};
            view.tabs = [
                {
                    collection: collection,
                    order_by: orderBy
                }
            ],
            view.settings.set('activeTab', 0);
            view.setOrderBy({currentTarget: '#tab0'});
            expect(collection.orderBy).toEqual({field: 'name', direction: 'desc'});
            expect(loadDataForTabsStub).toHaveBeenCalledWith([view.tabs[0]]);
        });
    });

    describe('_createCollection', function() {
        it('should create collection for tab', function() {
            var getModuleStub = sinon.collection.stub(app.metadata, 'getModule').returns({
                fields: {contacts: {type: 'link'}}
            });
            var createCollectionStub = sinon.collection.stub(app.data, 'createBeanCollection');
            var tab = {link: 'contacts', module: 'Contacts'};
            view._createCollection(tab);
            expect(createCollectionStub.lastCall.args[0]).toEqual('Contacts');
            expect(createCollectionStub.lastCall.args[2].link.name).toEqual('contacts');
        });
    });

    describe('_loadDataForTabs', function() {
        it('should load data for tabs', function() {
            var asyncParallelStub = sinon.collection.stub(async, 'parallel');
            var collection = app.data.createBeanCollection('Contacts');
            var tabs = [
                {collection: app.data.createBeanCollection('Contacts')},
                {collection: app.data.createBeanCollection('Tasks')}
            ];
            view._loadDataForTabs(tabs);
            expect(asyncParallelStub.lastCall.args[0].length).toEqual(2);
        });
    });

    describe('_getColumnsForTab', function() {
        it('should return dislay columns for tab', function() {
            var getModuleStub = sinon.collection.stub(app.metadata, 'getModule').returns({
                fields: {subject: {}}
            });
            var fieldMetaStub = sinon.collection.stub(view, '_getFieldMetaForTab').returns(
                [{name: 'subject'}]
            );
            var tab = {fields: ['subject'], module: 'Tasks'};
            expect(view._getColumnsForTab(tab)).toEqual([{name: 'subject', sortable: true}]);
        });
    });
});
