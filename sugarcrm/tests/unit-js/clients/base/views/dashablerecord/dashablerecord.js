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
    var getComponentStub;
    var getComponentStubListBottomReturn;
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
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'tabs');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'record');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'records');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'dashlet-config');

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
        getComponentStub = sinon.collection.stub(layout, 'getComponent');
        getComponentStub.withArgs('dashlet-toolbar').returns(dashletToolbar);
        getComponentStubListBottomReturn = {hide: function() {}, show: function() {}};
        getComponentStub.withArgs('list-bottom').returns(getComponentStubListBottomReturn);
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

        describe('pseudo dashlet', function() {
            describe('tab change', function() {
                it('should rerender when the tabs are changed', function() {
                    var renderStub = sinon.collection.stub(view, 'render');
                    view.meta.pseudo = true;
                    var tab = {
                        label: 'This Case',
                        link: 'Cases',
                        module: 'Cases',
                        relatedModule: 'This Case',
                        skipFetch: true
                    };

                    view.initDashlet('main');
                    view.meta.tabs = [tab];

                    expect(view.meta.tabs).toEqual([tab]);
                    expect(renderStub).toHaveBeenCalled();
                });
            });
        });

        describe('with a rowModel', function() {
            var rowModel;
            var newModel;

            beforeEach(function() {
                rowModel = app.data.createBean(moduleName, {id: 'an id'});
                view.context.parent.parent = new app.Context();
                view.context.parent.parent.set('rowModel', rowModel);
                newModel = {fetch: sinon.collection.stub()};
            });

            describe('with the base record', function() {
                it('should fetch and then re-render the full rowModel', function() {
                    sinon.collection.stub(app.data, 'createBean')
                        .withArgs(moduleName, {id: rowModel.get('id')})
                        .returns(newModel);
                    newModel.fetch.yieldsTo('success', newModel);
                    var renderNewModelStub = sinon.collection.stub(view, 'renderNewModel');

                    view.initDashlet('main');

                    expect(newModel.fetch).toHaveBeenCalledOnce();
                    expect(renderNewModelStub).toHaveBeenCalledWith(newModel);
                });
            });

            describe('with a non-base record', function() {
                it('should fetch the related model', function() {
                    var createRelatedBeanStub = sinon.collection.stub(app.data, 'createRelatedBean');
                    createRelatedBeanStub.withArgs(rowModel, 'related-account-id', 'accounts').returns(newModel);
                    view.settings.set('tabs', ['accounts']);
                    rowModel.set('accounts', {id: 'related-account-id'}, {silent: true});

                    view.initDashlet('main');

                    expect(newModel.fetch).toHaveBeenCalled();
                });
            });
        });
    });

    describe('bindDataChange', function() {
        it('should update the model, trigger dashlet:toolbar:change, and then re-render on change:model', function() {
            var switchModelStub = sinon.collection.stub(view, 'switchModel', function(model) { this.model = model; });
            var renderStub = sinon.collection.stub(view, 'render');
            var triggerStub = sinon.collection.stub(dashletToolbarContext, 'trigger');
            var model = app.data.createBean(moduleName);
            view.meta = {};
            view.bindDataChange();
            view.context.trigger('change:model', view.context, model);

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

        beforeEach(function() {
            var langStub = sinon.collection.stub(app.lang, 'get');
            langStub.withArgs('LBL_DASHLET_MODULE_UNAVAILABLE')
                .returns(unavailableModuleMsg);
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

        describe('main mode', function() {
            beforeEach(function() {
                view.moduleIsAvailable = true;
                view.model = app.data.createBean(view.module, {name: 'Test'});
                view.model.dataFetched = true;
                view.settings.set('module', moduleName);
                view.settings.set('tabs', [moduleName]);
                view.settings.set('activeTab', 0);
                view._initTabs();
            });

            it('should update the toolbar header', function() {
                view.initDashlet('main');
                var triggerStub = sinon.collection.stub(dashletToolbarContext, 'trigger');
                view.render();
                // we test the precise field list elsewhere, no need to do it again here
                expect(triggerStub).toHaveBeenCalledWith('dashlet:toolbar:change');
            });

            it('should hide the list-bottom view', function() {
                view.moduleIsAvailable = false;
                var _showHideListBottomStub = sinon.collection.stub(view, '_showHideListBottom');
                view.render();

                expect(_showHideListBottomStub).toHaveBeenCalledWith(null, true);
            });
        });
    });

    describe('_initTabs', function() {
        var hasAccessStub;
        beforeEach(function() {
            sinon.collection.stub(view, '_getTabsFromSettings').returns(null);
            hasAccessStub = sinon.collection.stub(app.acl, 'hasAccess');
            hasAccessStub.withArgs('view', 'Cases').returns(true);
            hasAccessStub.withArgs('view', 'Accounts').returns(false);
            hasAccessStub.withArgs('view', 'Contacts').returns(true);
        });

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
            view._baseModule = 'Cases';

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

        it('should not show any tabs if only one exists', function() {
            view.meta.tabs = [
                {
                    link: '',
                    module: 'Cases'
                }
            ];
            view._initTabs();
            expect(view.tabs).toEqual([]);
            expect(view.currentTab).not.toBeUndefined();
        });

        it('should set up the tab with template required properties', function() {
            view.meta.tabs = [
                {
                    link: '',
                    module: 'Cases'
                },
                {
                    link: 'Cases',
                    module: 'Cases'
                }
            ];

            view._initTabs();
            var tab = view.tabs[0];
            expect(tab.meta).not.toBeUndefined();
            expect(tab.model).toEqual(jasmine.any(app.Bean));
        });

        it('should set moduleIsAvailable based of ACLs', function() {
            // Has access
            view.meta.tabs = [
                {
                    module: 'Cases'
                }
            ];
            view._initTabs();
            expect(view.moduleIsAvailable).toBeTruthy();

            // No access to any tab
            view.meta.tabs = [
                {
                    module: 'Accounts'
                }
            ];
            view._initTabs();
            expect(view.moduleIsAvailable).toBeFalsy();

            // Access to at least 1 tab
            view.meta.tabs = [
                {
                    module: 'Cases'
                },
                {
                    module: 'Accounts'
                }
            ];
            view._initTabs();
            expect(view.moduleIsAvailable).toBeTruthy();
        });
    });

    describe('tabSwitcher', function() {
        var jDataStub;
        var loadDataForTabsStub;
        var tab1Collection;

        beforeEach(function() {
            jDataStub = sinon.collection.stub($.fn, 'data').returns(1);
            loadDataForTabsStub = sinon.collection.stub(view, '_loadDataForTabs');
            tab1Collection = app.data.createBeanCollection('Contacts');
            tab1Collection.dataFetched = false;
            tab1Collection.display_columns = {};
            view.tabs = [
                {},
                {
                    collection: tab1Collection
                }
            ];
        });

        it('should change tab', function() {
            view.tabSwitcher({currentTarget: '#tab1'});

            expect(view.settings.get('activeTab')).toEqual(1);
            expect(view.collection).toBe(tab1Collection);
            expect(loadDataForTabsStub).toHaveBeenCalledWith([view.tabs[1]]);
        });

        describe('config mode', function() {
            var renderStub;

            beforeEach(function() {
                renderStub = sinon.collection.stub(view, 'render');
                view.initDashlet('config');

            });

            it('should not render if the active tab is the one that was clicked', function() {
                view.activeConfigTab = 1;

                view.tabSwitcher({currentTarget: '#tab1'});

                expect(view.activeConfigTab).toEqual(1);
                expect(renderStub).not.toHaveBeenCalled();
            });

            it('should re-render if an inactive tab was clicked', function() {
                view.activeConfigTab = 0;

                view.tabSwitcher({currentTarget: '#tab1'});

                expect(view.activeConfigTab).toEqual(1);
                expect(renderStub).toHaveBeenCalled();
            });
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
            ];
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

    describe('getPaginationOptions', function() {
        it('should get the pagination options', function() {
            view.tabs = [
                {
                    limit: 5,
                    relate: 'accounts',
                    order_by: {direction: 'asc', field: 'name'},
                    include_child_items: false,
                    fields: ['name']
                }
            ];
            view.settings.set('activeTab', 0);

            var result = view.getPaginationOptions();

            expect(result).toEqual({
                limit: 5,
                relate: 'accounts',
                params: {
                    order_by: 'name:asc',
                    include_child_items: null
                },
                fields: ['name']
            });
        });
    });

    describe('_loadDataForTabs', function() {
        var asyncParallelStub;

        beforeEach(function() {
            asyncParallelStub = sinon.collection.stub(async, 'parallel');
        });

        it('should load data for tabs', function() {
            var collection = app.data.createBeanCollection('Contacts');
            var tabs = [
                {collection: app.data.createBeanCollection('Contacts')},
                {collection: app.data.createBeanCollection('Tasks')}
            ];
            view._loadDataForTabs(tabs);
            expect(asyncParallelStub.lastCall.args[0].length).toEqual(2);
        });

        it('should not do anything on the pseudo config dashlet', function() {
            view.meta.pseudo = true;

            view._loadDataForTabs([{}]);

            expect(asyncParallelStub).not.toHaveBeenCalled();
        });
    });

    describe('_getColumnsForTab', function() {
        it('should return display columns for tab', function() {
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

    describe('pseudo dashlet', function() {
        it('should be added on init in config mode', function() {
            var initComponentsStub = sinon.collection.stub(view.layout, 'initComponents');
            var dummyContext = {prepare: sinon.collection.stub()};
            sinon.collection.stub(view.context, 'getChildContext').returns(dummyContext);
            var dummyDashletConfiguration = {trigger: sinon.collection.stub()};
            var closestComponentStub = sinon.collection.stub(view, 'closestComponent');
            closestComponentStub.withArgs('dashletconfiguration')
                .returns(dummyDashletConfiguration);
            getComponentStub
                .withArgs('dashlet')
                .returns({context: {trigger: sinon.collection.stub()}});

            view.model = app.data.createBean(moduleName);
            view.initDashlet('config');
            view.settings.set('tabs', ['Cases', 'accounts']);

            view.layout.trigger('init');

            expect(dummyContext.prepare).toHaveBeenCalled();
            expect(initComponentsStub).toHaveBeenCalledWith(
                [{
                    layout: {
                        type: 'dashlet',
                        css_class: 'dashlets',
                        config: false,
                        preview: false,
                        module: 'Cases',
                        context: view.context,
                        components: [
                            {
                                name: view.name,
                                type: view.type,
                                context: dummyContext,
                                preview: true,
                                module: 'Cases',
                                custom_toolbar: 'no',
                                view: {
                                    module: 'Cases',
                                    name: view.name,
                                    type: view.type,
                                    preview: true,
                                    context: dummyContext,
                                    custom_toolbar: 'no',
                                    tabs: [
                                        {link: '', module: 'Cases'}, // This Case
                                        {link: 'accounts', module: 'Accounts'} // linked Account
                                        // FIXME: this will change when we support list view config
                                    ],
                                    pseudo: true,
                                }
                            }
                        ],
                        pseudo: true
                    }
                }],
                view.context
            );
        });
    });

    describe('dashletconfig:save event', function() {
        it('should only save a whitelisted list of settings', function() {
            var dummyDashletConfiguration = {trigger: sinon.collection.stub()};
            var closestComponentStub = sinon.collection.stub(view, 'closestComponent');
            closestComponentStub.withArgs('dashletconfiguration')
                .returns(dummyDashletConfiguration);
            view.settings.set('base_module', 'Cases');
            view.initDashlet('config');

            view.settings.set(
                {
                    activeTab: 1,
                    label: 'I am a record view dashlet',
                    tabs: ['Cases', 'accounts'],
                    I: 'should not be in the result'
                },
                {silent: true}
            );

            view.layout.triggerBefore('dashletconfig:save');

            expect(view.settings.get('activeTab')).toEqual(0); // resets to initial tab on config change
            expect(view.settings.get('base_module')).toEqual(moduleName);
            expect(view.settings.get('label')).toEqual('I am a record view dashlet');
            expect(view.settings.get('tabs')).toEqual([
                {
                    type: 'record',
                    label: 'TPL_DASHLET_RECORDVIEW_THIS_RECORD_TYPE',
                    module: 'Cases',
                    link: 'Cases'
                },
                {
                    type: 'record',
                    label: 'LBL_ACCOUNT',
                    module: 'Accounts',
                    link: 'accounts'
                }
            ]);
            expect(view.settings.has('I')).toBeFalsy();
        });
    });

    describe('renderNewModel', function() {
        it('should switch the model and then re-render', function() {
            var switchModelStub = sinon.collection.stub(view, 'switchModel');
            var renderStub = sinon.collection.stub(view, 'render');
            view.initDashlet('main');

            var newBean = app.data.createBean('Accounts');
            view.renderNewModel(newBean);

            expect(switchModelStub).toHaveBeenCalledWith(newBean);
            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('_updateViewToCurrentTab', function() {
        it('should set the view data', function()  {
            var model = app.data.createBean(moduleName);
            view._defaultBaseMeta = {oldMeta: 'somethingOld', panels: []};
            view.currentTab = {
                model: model,
                link: '',
                module: moduleName,
                meta: {panels: [{fields: []}]},
            };

            view._updateViewToCurrentTab();
            expect(view.model).toEqual(model);
            expect(view.module).toEqual(moduleName);
            expect(view.meta.oldMeta).toEqual('somethingOld');
            expect(view.meta.panels).toEqual([{fields: [], labels: true, grid: []}]);
            /*expect(view.meta).toEqual({
                oldMeta: 'somethingOld',
                panels: [{fields: [], labels: true, grid: []}]
            });*/
            expect(view.context.get('model')).toEqual(model);
        });
    });

    describe('_showHideListBottom', function() {
        it('should call show() when the tab is a list', function() {
            sinon.collection.spy(getComponentStubListBottomReturn, 'show');
            view._showHideListBottom({type: 'list'}, null);
            expect(getComponentStubListBottomReturn.show).toHaveBeenCalled();
        });

        it('should call hide() when the tab is a record', function() {
            sinon.collection.spy(getComponentStubListBottomReturn, 'hide');
            view._showHideListBottom({type: 'record'}, null);
            expect(getComponentStubListBottomReturn.hide).toHaveBeenCalled();
        });

        it('should call hide() when forceHide is true', function() {
            sinon.collection.spy(getComponentStubListBottomReturn, 'hide');
            view._showHideListBottom({}, true);
            expect(getComponentStubListBottomReturn.hide).toHaveBeenCalled();
        });
    });
});
