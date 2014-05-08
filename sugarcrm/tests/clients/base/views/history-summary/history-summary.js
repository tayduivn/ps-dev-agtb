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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
describe('Base.View.HistorySummary', function() {
    var app,
        view,
        sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();

        sandbox.stub(app.template, 'getView', function() {
            return function() {
                return '';
            };
        });

        var context = app.context.getContext(),
            meta = {
                template: 'history-summary'
            };

        view = SugarTest.createView('base', null, 'history-summary', meta, context, false, null, true);

        sandbox.stub(view.collection, 'fetch', function() {});
    });

    afterEach(function() {
        app = null;
        sandbox.restore();
        view = null;
    });

    describe('setActivityModulesToFetch()', function() {
        it('should populate activityModules with defaults', function() {
            view.setActivityModulesToFetch();
            expect(view.activityModules).toEqual(view.allActivityModules);
        });
    });

    describe('loadData()', function() {
        it('should call collection.fetch', function() {
            view.loadData({});
            expect(view.collection.fetch).toHaveBeenCalled();
        });
    });

    describe('_setOrderBy()', function() {
        var orderBy,
            loadDataStub,
            opts;

        beforeEach(function() {
            orderBy = {
                direction: 'asc',
                field: 'name'
            };
            view.orderBy = orderBy;
            opts = {};
        });

        afterEach(function() {
            loadDataStub.restore();
            view.orderBy = null;
            opts = null;
        });

        it('should set options.orderBy properly', function() {
            loadDataStub = sinon.stub(view, 'loadData', function(options) {
                return options;
            });
            view._setOrderBy(opts);
            expect(opts.orderBy).toBeDefined();
            expect(opts.orderBy).toEqual(orderBy);
        });

        it('should set options.orderBy & call loadData with proper orderBy', function() {
            view._setOrderBy(opts);
            expect(view.collection.fetch).toHaveBeenCalledWith({
                orderBy: orderBy
            });
        });
    });

    describe('_renderField()', function() {
        var field,
            superStub,
            testModule;

        beforeEach(function() {
            field = {
                name: '',
                model: new Backbone.Model()
            };
            superStub = sinon.stub(view, '_super', function() {});
            testModule = 'TestModule';
        });

        afterEach(function() {
            field = null;
            superStub.restore();
            testModule = null;
        });

        it('should set field.model.module when fieldName=="name"', function() {
            field.name = 'name';
            field.model.set({
                _module: testModule
            });

            view._renderField(field);
            expect(field.model.module).toEqual(testModule);
        });

        it('should set module in field.model when fieldName=="module"', function() {
            field.name = 'module';
            field.model.set({
                moduleNameSingular: testModule
            });

            view._renderField(field);
            expect(field.model.get('module')).toEqual(testModule);
        });

        describe('should set related_contact and related_contact_id when fieldName=="related_contact"', function() {
            it('when fieldModule=="Emails"', function() {
                field.name = 'related_contact';
                field.model.set({
                    _module: 'Emails'
                });

                view._renderField(field);
                expect(field.model.get('related_contact')).toEqual('');
                expect(field.model.get('related_contact_id')).toEqual('');
            });

            it('when fieldModule is any other module', function() {
                field.name = 'related_contact';
                field.model.set({
                    _module: 'Notes',
                    contact_name: 'TestContactName',
                    contact_id: 'TestContactId'
                });

                view._renderField(field);
                expect(field.model.get('related_contact')).toEqual('TestContactName');
                expect(field.model.get('related_contact_id')).toEqual('TestContactId');
            });
        });

        it('should set module in field.model when fieldName=="status" and the module is "Emails"', function() {
            sinon.stub(app.lang, 'getAppListStrings', function() {
                return {
                    testStatus: 'Test Status'
                }
            });

            field.name = 'status';
            field.model.set({
                _module: 'Emails',
                status: 'testStatus'
            });
            view._renderField(field);

            expect(field.model.get('status')).toEqual('Test Status');

            app.lang.getAppListStrings.restore();
        });

        it('should set field.model.module when field id=="previewBtn"', function() {
            field.name = 'name';
            field.def = {
                id: 'previewBtn'
            };
            field.model.set({
                _module: testModule
            });

            view._renderField(field);
            expect(field.model.module).toEqual(testModule);
        });

        it('should call _super() ', function() {
            field.def = {};
            view._renderField(field);
            expect(superStub).toHaveBeenCalled();
        });
    });
});
