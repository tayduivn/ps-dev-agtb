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
describe('Base.View.ActiveSubscriptions', function() {
    var app;
    var context;
    var layout;
    var layoutName = 'dashlet';
    var moduleName = 'Accounts';
    var view;
    var viewName = 'active-subscriptions';

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
                fields: [
                    'name',
                    'quantity'
                ],
            }
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
            var accountModel = app.data.createBean('Accounts');
            accountModel.set('_module', 'Accounts');

            var parentContext = app.context.getContext();
            parentContext.set({
                module: 'Accounts',
                rowModel: accountModel,
            });

            var mainContext = app.context.getContext();
            mainContext.set({module: 'Accounts'});
            mainContext.parent = parentContext;
            view.context = mainContext;
            view.collection = {
                off: $.noop
            };
            view._getBaseModel();
            expect(view.baseModel).toEqual(accountModel);
        });
    });

    describe('_initCollection', function() {
        it('should not take action when base module or model does not exist', function() {
            var beanCollectionStub = sinon.collection.stub(app.data, 'createBeanCollection').returns({
                'off': $.noop
            });
            view.baseModule = 'Accounts';
            view.baseModel = undefined;

            view._initCollection();
            expect(beanCollectionStub).not.toHaveBeenCalled();

        });

        it('should create new bean collection', function() {
            var beanCollectionStub = sinon.collection.stub(app.data, 'createBeanCollection').returns({
                'off': $.noop
            });
            var today = app.date().formatServer(true);
            view.baseModule = 'Accounts';
            view.baseModel = app.data.createBean('Accounts', {'id': 'my_id'});
            view.meta = {
                'fields': ['name']
            };
            view.opportunitiesWithRevenueLineItems = true;
            app.config.maxRecordFetchSize = 1000;
            view._initCollection();
            expect(beanCollectionStub.lastCall.args[0]).toEqual('RevenueLineItems');
            expect(beanCollectionStub.lastCall.args[2].fields).toEqual(['name']);
            expect(beanCollectionStub.lastCall.args[2].filter[0].account_id.$equals).toEqual('my_id');
            expect(beanCollectionStub.lastCall.args[2].filter[1]['opportunities.sales_status'].$equals).
            toEqual('Closed Won');
            expect(beanCollectionStub.lastCall.args[2].filter[2].sales_stage.$equals).toEqual('Closed Won');
            expect(beanCollectionStub.lastCall.args[2].filter[3].service_duration_value.$gt).toEqual(0);
            expect(beanCollectionStub.lastCall.args[2].filter[4].service_start_date.$lte).toEqual(today);
            expect(beanCollectionStub.lastCall.args[2].filter[5].service_end_date.$gte).toEqual(today);
            expect(beanCollectionStub.lastCall.args[2].limit).toEqual(app.config.maxRecordFetchSize);
            expect(beanCollectionStub.lastCall.args[2].params.order_by).toEqual('service_start_date');
        });

        it('calls _daysDifferenceCalculator', function() {
            sinon.collection.stub(app.date.fn, 'formatUser', function() {
                return {split: function() {
                        return (['2019-10-21']);
                    }};
            });
            var model = app.data.createBean('Accounts', {
                service_start_date: '2019-10-21',
                service_end_date: '2020-10-20',
                get: sinon.collection.stub(),
                set: function() {},
            });

            view.baseModule = 'Accounts';
            view.baseModel = model;
            view.collection = {
                models: [model],
                'off': $.noop
            };
            view._daysDifferenceCalculator();
            expect(view.collection.models[0].get('endDate')).toEqual('2019-10-21');
            expect(view.collection.models[0].get('startDate')).toEqual('2019-10-21');
        });

        it('calls _caseComparator', function() {
            sinon.collection.stub(app.date.fn, 'formatUser', function() {
                return {split: function() {
                        return (['2019-10-21']);
                    }};
            });
            var model = app.data.createBean('Accounts', {
                service_start_date: '2019-10-21',
                service_end_date: '2020-10-20',
                get: sinon.collection.stub(),
                set: function() {},
            });

            view.baseModule = 'Accounts';
            view.baseModel = model;
            view.collection = {
                models: [model],
                'off': $.noop
            };
            view._caseComparator();
            expect(view.overallSubscriptionEndDate).toEqual(18555);
            expect(view.overallSubscriptionStartDate).toEqual(18190);
        });

    });

    describe('loadData', function() {
        it('should fetch collection', function() {
            var fetchStub = sinon.collection.stub();
            view.collection = {
                fetch: fetchStub,
                off: $.noop
            };
            view.opportunitiesWithRevenueLineItems = true;
            view.loadData();
            expect(fetchStub).toHaveBeenCalled();
        });
    });
});
