//FILE SUGARCRM flav=ent ONLY
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
describe('Base.View.ActivityTimeline', function() {
    var app;
    var context;
    var layout;
    var layoutName = 'dashlet';
    var moduleName = 'Cases';
    var view;
    var viewName = 'activity-timeline';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'preview');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'layout', 'dashlet');
        SugarTest.loadComponent('base', 'layout', 'dashboard');

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition(
            viewName,
            {
                activity_modules: [
                    {
                        module: 'Calls',
                        fields: [
                            'name',
                            'status',
                        ],
                    },
                    {
                        module: 'Emails',
                        fields: [
                            'name',
                            'date_sent',
                        ],
                    },
                ],
            },
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
        view = null;
        layout = null;
        dashboard = null;
    });

    describe('_getBaseModel', function() {
        it('should get model from parent context', function() {
            var caseModel = app.data.createBean('Cases');
            caseModel.set('_module', 'Cases');

            var parentContext = app.context.getContext();
            parentContext.set({
                module: 'Cases',
                rowModel: caseModel,
            });

            var mainContext = app.context.getContext();
            mainContext.set({module: 'Cases'});
            mainContext.parent = parentContext;

            var model = view._getBaseModel({
                context: mainContext,
            });

            expect(model).toEqual(caseModel);
        });
    });

    describe('_setActivityModulesAndFields', function() {
        it('should set activityModules and moduleFieldNames', function() {
            expect(view.activityModules).toEqual(['Calls', 'Emails']);
            expect(view.moduleFieldNames).toEqual({
                Calls: ['name', 'status'],
                Emails: ['name','date_sent'],
            });
        });
    });

    describe('_render', function() {
        it('should hide the OOTB dashlet config icon', function() {
            var hideDashletConfigStub = sinon.collection.stub(view, '_hideDashletConfig');

            view._render();
            expect(hideDashletConfigStub).toHaveBeenCalled();
        });
    });

    describe('_getModuleFieldMeta', function() {
        var getViewMetaStub;

        beforeEach(function() {
            getViewMetaStub = sinon.collection.stub(app.metadata, 'getView');
        });

        it('should get field from preview meta', function() {
            getViewMetaStub.withArgs('Calls', 'preview').returns({
                panels: [
                    {
                        fields: [
                            {name: 'name'},
                            {name: 'status'},
                        ]
                    }
                ]
            });

            getViewMetaStub.withArgs('Emails', 'preview').returns({
                panels: [
                    {
                        fields: [
                             {name: 'name'},
                        ]
                    },
                    {
                        fields: [
                            {name: 'date_sent'},
                            {name: 'leave_me_alone'},
                        ],
                    }
                ]
            });

            var fieldMeta = view._getModuleFieldMeta();
            expect(fieldMeta.Calls.panels[0].fields.length).toBe(2);
            expect(fieldMeta.Emails.panels[0].fields.length).toBe(2);
        });

        it('should get field from record meta when preview meta not available', function() {
            getViewMetaStub.withArgs('Calls', 'preview').returns(undefined);
            getViewMetaStub.withArgs('Calls', 'record').returns({
                panels: [
                    {
                        fields: [
                            {name: 'name'},
                        ]
                    },
                    {
                        fields: [
                            {name: 'status'},
                            {name: 'leave_me_alone'},
                        ],
                    }
                ]
            });

            getViewMetaStub.withArgs('Emails', 'preview').returns(undefined);
            getViewMetaStub.withArgs('Emails', 'record').returns({
                panels: [
                    {
                        fields: [
                             {name: 'name'},
                        ]
                    },
                ]
            });

            var fieldMeta = view._getModuleFieldMeta();
            expect(fieldMeta.Calls.panels[0].fields.length).toBe(2);
            expect(fieldMeta.Emails.panels[0].fields.length).toBe(1);
        });

        it('should return empty fields when neither preview nor record meta is available', function() {
            getViewMetaStub.withArgs('Calls', 'preview').returns(undefined);
            getViewMetaStub.withArgs('Calls', 'record').returns(undefined);

            getViewMetaStub.withArgs('Emails', 'preview').returns(undefined);
            getViewMetaStub.withArgs('Emails', 'record').returns(undefined);

            var fieldMeta = view._getModuleFieldMeta();
            expect(fieldMeta.Calls.panels[0].fields.length).toBe(0);
            expect(fieldMeta.Emails.panels[0].fields.length).toBe(0);
        });
    });

    describe('_initCollection', function() {
        var mixedBeanCollectionStub;

        beforeEach(function() {
            mixedBeanCollectionStub = sinon.collection.stub(app.MixedBeanCollection, 'extend').returns(
                function MockCollectionConstructor() {
                    this.collection = 'fake_collection';
                }
            );
        });

        it('should not take action when any of base module, record or activity modules do not exists', function() {
            view.baseModule = 'Cases';
            view.baseRecord = undefined;
            view.activityModules = ['Calls'];

            view._initCollection();
            expect(mixedBeanCollectionStub).not.toHaveBeenCalled();
        });

        it('should create new mixed bean collection', function() {
            view.baseModule = 'Cases';
            view.baseRecord = app.data.createBean('Cases');
            view.activityModules = ['Calls', 'Emails'];

            view._initCollection();
            expect(view.relatedCollection.collection).toEqual('fake_collection');
        });
    });

    describe('loadData', function() {
        it('should fetch collection', function() {
            var fetchStub = sinon.collection.stub();
            view.relatedCollection = {
                fetch: fetchStub,
            };

            view.loadData();
            expect(fetchStub).toHaveBeenCalled();
        });
    });

    describe('fetchModels', function() {
        it('should fetch models', function() {
            var fetchStub = sinon.collection.stub();
            view.relatedCollection = {
                fetch: fetchStub,
            };

            view.fetchModels();
            expect(fetchStub).toHaveBeenCalled();
        });

        it('should not fetch models when all models had been fetched', function() {
            var fetchStub = sinon.collection.stub();
            view.relatedCollection = {
                fetch: fetchStub,
            };
            view.fetchCompleted = true;

            view.fetchModels();
            expect(fetchStub).not.toHaveBeenCalled();
        });
    });

    describe('_setIconClass', function() {
        it('should set icon class base on model type', function() {
            var moduleIcons = [
                {name: 'Calls', icon: 'fa-phone'},
                {name: 'Emails', icon: 'fa-envelope'},
                {name: 'Meetings', icon: 'fa-calendar'},
                {name: 'Notes', icon: 'fa-file-text'},
            ];

            view.models = _.map(moduleIcons, function(module) {
                return app.data.createBean(module.name, {moduleName: module.name});
            });
            view._setIconClass();

            _.each(view.models, function(model, ind) {
                expect(model.get('icon_class')).toEqual(moduleIcons[ind].icon);
            });
        });
    });

    describe('_patchFieldsToModel', function() {
        it('should set fieldMeta to model based on its module type', function() {
            var fieldsMeta = [
                {name: 'Calls', meta: 'mock_call_meta'},
                {name: 'Emails', meta: 'mock_email_meta'},
                {name: 'Meetings', meta: 'mock_meeting_meta'},
                {name: 'Notes', meta: 'mock_note_meta'},
            ];

            var previewMeta = {};
            _.each(fieldsMeta, function(metaObj) {
                previewMeta[metaObj.name] = metaObj.meta;
            });

            view.meta = {preview: previewMeta};

            _.each(fieldsMeta, function(metaObj) {
                var model = app.data.createBean(metaObj.name, {moduleName: metaObj.name});
                view._patchFieldsToModel(model);
                expect(model.get('fieldsMeta')).toEqual(metaObj.meta);
            });
        });
    });
});
