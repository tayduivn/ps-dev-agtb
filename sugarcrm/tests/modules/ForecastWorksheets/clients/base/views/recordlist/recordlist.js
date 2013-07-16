/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

describe("ForecastWorksheets.View.RecordList", function() {

    var app, view, layout, moduleName = 'ForecastWorksheets';

    beforeEach(function() {
        app = SUGAR.App;
        SugarTest.testMetadata.init();
        SugarTest.loadFile("../include/javascript/sugar7", "utils", "js", function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
        SugarTest.loadFile("../include/javascript/sugar7/plugins", "dirty-collection", "js", function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        app.user.set({'id': 'test_userid'});

        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'recordlist', moduleName);
        SugarTest.testMetadata.addViewDefinition("list", {
            "favorite": false,
            "selection": {
                "type": "multi",
                "actions": []
            },
            "rowactions": {
                "actions": []
            },
            "panels": [
                {
                    "name": "panel_header",
                    "header": true,
                    "fields": ["name", "likely_case", "best_case", "worst_case"]
                }
            ]
        }, "ForecastWorksheets");

        SugarTest.testMetadata.set();
        app.data.reset();

        app.data.declareModel(moduleName, SugarTest.app.metadata.getModule(moduleName));

        context = app.context.getContext();
        context.set({
            module: moduleName,
            'selectedUser': app.user.toJSON(),
            'selectedRanges': [],
            'selectedTimePeriod': 'test_timeperiod'
        });
        context.parent = undefined;
        context.prepare();

        view = SugarTest.createView("base", moduleName, "recordlist", null, context);
        layout = SugarTest.createLayout("base", moduleName, "list", null, null);
        view.layout = layout;

    });

    afterEach(function() {
        app.user.unset('id');
        view = null;
        app = null;
    });

    it("should have additional plugins defined", function() {
        expect(_.indexOf(view.plugins, 'cte-tabbing')).not.toEqual(-1);
        expect(_.indexOf(view.plugins, 'dirty-collection')).not.toEqual(-1);
    });

    describe('beforeRenderCallback', function() {
        describe('when layout hidden', function() {
            var layoutShowStub, layoutVisibleStub;
            beforeEach(function() {
                layoutShowStub = sinon.stub(view.layout, 'show', function() {
                });
                layoutVisibleStub = sinon.stub(view.layout, 'isVisible', function() {
                    return false;
                });
            });
            afterEach(function() {
                layoutShowStub.restore();
                layoutVisibleStub.restore();
            });

            it('should return true when user is not a manager and call show', function() {
                view.selectedUser.isManager = false;
                var ret = view.beforeRenderCallback();
                expect(ret).toBeTruthy();
                expect(layoutShowStub).toHaveBeenCalled();
            });
            it('should return true when user is manager and showOpps is true and call show', function() {
                view.selectedUser.isManager = true;
                view.selectedUser.showOpps = true;
                var ret = view.beforeRenderCallback();
                expect(ret).toBeTruthy();
                expect(layoutShowStub).toHaveBeenCalled();
            });
            it('should return false when user is manager and showOpps is false', function() {
                view.selectedUser.isManager = true;
                view.selectedUser.showOpps = false;
                var ret = view.beforeRenderCallback();
                expect(ret).toBeFalsy();
                expect(layoutShowStub).not.toHaveBeenCalled();
            });
        });

        describe('when layout visible', function() {
            var layoutHideStub, layoutVisibleStub;
            beforeEach(function() {
                layoutHideStub = sinon.stub(view.layout, 'hide', function() {
                });
                layoutVisibleStub = sinon.stub(view.layout, 'isVisible', function() {
                    return true;
                });
            });
            afterEach(function() {
                layoutHideStub.restore();
                layoutVisibleStub.restore();
            });

            it('should return false when user is manager and showOpps is false', function() {
                view.selectedUser.isManager = true;
                view.selectedUser.showOpps = false;
                var ret = view.beforeRenderCallback();
                expect(ret).toBeFalsy();
                expect(layoutHideStub).toHaveBeenCalled();
            });
        });
    });

    describe('renderCallback', function() {
        var layoutShowStub, layoutHideStub, layoutHideStub;
        beforeEach(function() {
            layoutShowStub = sinon.stub(view.layout, 'show', function() {
            });
            layoutHideStub = sinon.stub(view.layout, 'hide', function() {
            });
        });
        afterEach(function() {
            layoutShowStub.restore();
            layoutHideStub.restore();
            layoutVisibleStub.restore();
        });

        it('should run hide when user is a manager and show opps is false', function() {
            layoutVisibleStub = sinon.stub(view.layout, 'isVisible', function() {
                return true;
            });

            view.selectedUser.isManager = true;
            view.selectedUser.showOpps = false;
            view.renderCallback();

            expect(layoutShowStub).not.toHaveBeenCalled();
            expect(layoutHideStub).toHaveBeenCalled();
        });

        it('should run show when user is a manager and show opps is true', function() {
            layoutVisibleStub = sinon.stub(view.layout, 'isVisible', function() {
                return false;
            });

            tplViewStub = sinon.stub(app.template, 'getView', function(){
                return function() {};
            });

            view.selectedUser.isManager = true;
            view.selectedUser.showOpps = true;
            view.renderCallback();

            expect(layoutShowStub).toHaveBeenCalled();
            expect(layoutHideStub).not.toHaveBeenCalled();

            tplViewStub.restore();
        });
    });

    describe("parseFields should hide best and worst case", function() {
        beforeEach(function() {
            app.metadata.getModule('Forecasts', 'config').show_worksheet_best = 0;
            app.metadata.getModule('Forecasts', 'config').show_worksheet_worst = 0;
        });
        afterEach(function() {
            app.metadata.getModule('Forecasts', 'config').show_worksheet_best = 1;
            app.metadata.getModule('Forecasts', 'config').show_worksheet_worst = 1;
        });
        it("length of visible fields should equal 2", function() {
            fields = view.parseFields();
            expect(fields.visible.length).toEqual(2);
        })
    });

    describe("filteredCollection", function() {
        beforeEach(function() {
            // add some models
            var m1 = new Backbone.Model({'name': 'test1', 'commit_stage': 'include'});
            var m2 = new Backbone.Model({'name': 'test2', 'commit_stage': 'include'});
            var m3 = new Backbone.Model({'name': 'test3', 'commit_stage': 'exclude'});

            view.collection.add([m1, m2, m3]);
        });
        afterEach(function() {
            view.filters = [];
            view.collection.reset();
            view.filteredCollection.reset();
        });

        it("with no filters, filteredCollection should contain 3 records", function() {
            view.filterCollection();
            expect(view.filteredCollection.length).toEqual(3);
        });
        it("with include filter, filteredCollection should contain 2 records", function() {
            view.filters = ['include'];
            view.filterCollection();
            expect(view.filteredCollection.length).toEqual(2);
        });
        it("with exclude filter, filteredCollection should contain 1 records", function() {
            view.filters = ['exclude'];
            view.filterCollection();
            expect(view.filteredCollection.length).toEqual(1);
        });
        it("with 2 filters, filteredCollection should contain 3 records", function() {
            view.filters = ['include', 'exclude'];
            view.filterCollection();
            expect(view.filteredCollection.length).toEqual(3);
        });
    });

    describe("checkForDraftRows", function() {
        var layoutStub, ctxStub;
        beforeEach(function() {
            // add some models
            var m1 = new Backbone.Model({'name': 'test1', 'date_modified': '2013-05-14 16:20:15'});
            view.collection.add([m1]);

            // set that we can edit
            view.canEdit = true;
            layoutStub = sinon.stub(view.layout, 'isVisible', function() {
                return true;
            });

            context = app.context.getContext();
            context.set({
                module: 'Forecasts'
            });
            context.prepare();

            ctxStub = sinon.stub(context, 'trigger', function() {
            });

            view.context.parent = context;
        });
        afterEach(function() {
            view.collection.reset();
            layoutStub.restore();
            view.context.parent = undefined;
        });

        it("should not trigger event", function() {
            view.checkForDraftRows('2013-05-14 16:21:15');
            expect(ctxStub).not.toHaveBeenCalled();
        });

        it("should trigger event", function() {
            view.checkForDraftRows('2013-05-14 16:19:15');
            expect(ctxStub).toHaveBeenCalled();
        });

        it('should trigger when date is undefined and has rows', function() {
            view.checkForDraftRows(undefined);
            expect(ctxStub).toHaveBeenCalled();
        });

        it('should not trigger event when date is undefined and collection is empty', function() {
            view.collection.reset();
            view.checkForDraftRows(undefined);
            expect(ctxStub).not.toHaveBeenCalled();
        });

    });

    describe('updateSelectedUser', function() {
        var collectionFetchStub;
        beforeEach(function() {
            collectionFetchStub = sinon.stub(view.collection, 'fetch', function() {
            });
        });
        afterEach(function() {
            collectionFetchStub.restore()
            view.canEdit = false;
        });
        it("should change canEdit to be true", function() {
            view.updateSelectedUser({id: 'test_userid'});
            expect(view.canEdit).toBeTruthy();
        });
        it("should change canEdit to be false", function() {
            view.updateSelectedUser({id: 'test_user2'});
            expect(view.canEdit).toBeFalsy();
        });
        it("should call collection.fetch() isManager is False", function() {
            view.updateSelectedUser({id: 'test_user2', isManager: false});
            expect(collectionFetchStub).toHaveBeenCalled();
        });
        it("should call collection.fetch() with isManager is True and showOpps is True", function() {
            view.updateSelectedUser({id: 'test_userid', isManager: true, showOpps: true});
            expect(collectionFetchStub).toHaveBeenCalled();
        });
    });

    describe('updateTimeperiod', function() {
        var collectionFetchStub, layoutVisibleStub;

        beforeEach(function() {
            collectionFetchStub = sinon.stub(view.collection, 'fetch', function() {
            });
        });
        afterEach(function() {
            collectionFetchStub.restore()
            layoutVisibleStub.restore()
        });

        it('should update selectedTimePeriod and call collection.fetch when layout is visible', function() {
            layoutVisibleStub = sinon.stub(view.layout, 'isVisible', function() {
                return true;
            });
            view.updateSelectedTimeperiod({id: 'hello world'});

            expect(view.selectedTimeperiod).toEqual({id: 'hello world'});
            expect(collectionFetchStub).toHaveBeenCalled();
        });

        it('should update selectedTimePeriod and not call collection.fetch when layout is not visible', function() {
            layoutVisibleStub = sinon.stub(view.layout, 'isVisible', function() {
                return false;
            });
            view.updateSelectedTimeperiod({id: 'hello world'});

            expect(view.selectedTimeperiod).toEqual({id: 'hello world'});
            expect(collectionFetchStub).not.toHaveBeenCalled();
        })


    });

    describe('saveWorksheet', function() {
        var m, saveStub;
        beforeEach(function() {
            m = new Backbone.Model({'hello': 'world'});
            saveStub = sinon.stub(m, 'save', function() {
            });
            view.collection.add(m);
        });

        afterEach(function() {
            view.collection.reset();
            saveStub.restore();
            m = undefined;
        });

        it('should return zero with no dirty models', function() {
            expect(view.saveWorksheet()).toEqual(0);
        });

        it('should return 1 when one model is dirty', function() {
            m.set({'hello': 'jon1'});
            expect(view.saveWorksheet()).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
        });

        describe("Forecasts worksheet save dirty models with correct timeperiod after timeperiod changes", function() {
            var m, saveStub, safeFetchStub;
            beforeEach(function() {
                m = new Backbone.Model({'hello': 'world'});
                saveStub = sinon.stub(m, 'save', function() {
                });
                safeFetchStub = sinon.stub(view.collection, 'fetch', function() {
                });
                view.collection.add(m);
            });

            afterEach(function() {
                view.collection.reset();
                saveStub.restore();
                safeFetchStub.restore();
                m = undefined;
            });

            it('model should contain the old timeperiod id', function() {
                m.set({'hello': 'jon1'});
                view.updateSelectedTimeperiod('my_new_timeperiod');
                expect(view.saveWorksheet()).toEqual(1);
                expect(saveStub).toHaveBeenCalled();
                expect(safeFetchStub).toHaveBeenCalled();

                expect(m.get('timeperiod_id')).toEqual('test_timeperiod');
                expect(view.selectedTimeperiod).toEqual('my_new_timeperiod');
                expect(view.dirtyTimeperiod).toEqual(undefined);
            });
        });

        describe("dirty models with correct user_id after selected_user changes", function() {
            var m, saveStub, safeFetchStub;
            beforeEach(function() {
                m = new Backbone.Model({'hello': 'world'});
                saveStub = sinon.stub(m, 'save', function() {
                });
                safeFetchStub = sinon.stub(view.collection, 'fetch', function() {
                });
                view.collection.add(m);
            });

            afterEach(function() {
                saveStub.restore();
                safeFetchStub.restore();
                m = undefined;
            });

            it('model should contain the old userid', function() {
                m.set({'hello': 'jon1'});
                view.updateSelectedUser({'id': 'my_new_user_id'});
                expect(view.saveWorksheet()).toEqual(1);
                expect(saveStub).toHaveBeenCalled();

                expect(m.get('current_user')).toEqual('test_userid');
                expect(view.selectedUser.id).toEqual('my_new_user_id');
                expect(view.dirtyUser).toEqual(undefined);
            });
        });
    });

    describe('sync', function() {
        var stubs = [], options = {};
        beforeEach(function() {
            stubs.push(sinon.stub(app.api, 'buildURL', function() {}));
            stubs.push(sinon.stub(app.api, 'call', function() {}));
            stubs.push(sinon.stub(app.data, 'getSyncCallbacks', function() {}));
        });

        afterEach(function() {
            _.each(stubs, function(stub) {
                stub.restore();
            });
            options = {}
        });

        describe('timeperiod_id', function(){
            afterEach(function() {
               options = {}
            });

            it('should be set to view.selectedTimeperiod', function() {
                view.sync('read', view.collection, options);
                expect(_.isUndefined(options.params.timeperiod_id)).toBeFalsy();
                expect(options.params.timeperiod_id).toEqual(view.selectedTimeperiod);
            });
        });

        describe('user_id', function(){
            afterEach(function() {
               options = {}
            });

            it('should be set to view.selectedUser.id', function() {
                view.sync('read', view.collection, options);
                expect(_.isUndefined(options.params.user_id)).toBeFalsy();
                expect(options.params.user_id).toEqual(view.selectedUser.id);
            });
        });

        describe("orderBy should", function() {
            beforeEach(function() {
                options = {}
            });

            afterEach(function() {
                delete view.collection.orderBy;
                options = {}
            });

            it('not be set', function() {
                view.sync('read', view.collection, options);
                expect(_.isUndefined(options.params.order_by)).toBeTruthy();
            });

            it('be set', function() {
                view.collection.orderBy = {
                    'field' : 'best_case',
                    'direction' : '_desc',
                    'column_name' : 'best_case'
                }
                view.sync('read', view.collection, options);
                expect(_.isUndefined(options.params.order_by)).toBeFalsy();
                expect(options.params.order_by).toEqual('best_case:_desc');
            });

            it('should convert parent_name to name', function() {
                view.collection.orderBy = {
                    'field' : 'parent_name',
                    'direction' : '_desc',
                    'column_name' : 'parent_name'
                }
                view.sync('read', view.collection, options);
                expect(options.params.order_by).toEqual('name:_desc');
                expect(view.collection.orderBy.field).toEqual('parent_name');
            });
        });
    });

    describe('setRowActionButtonStates', function() {
        var fieldDef = {}, field, fieldStub, viewFields = [];
        beforeEach(function() {
            fieldDef = {
                'event': 'list:preview:fire',
                'type': 'button',
                'name': 'test_btn'
            }
            viewFields = view.fields;

            field = SugarTest.createField('base', 'rowaction', 'rowaction', 'list', fieldDef);
            fieldStub = sinon.stub(field, 'setDisabled', function() {});
            view.fields = [field];
        });

        afterEach(function() {
            view.fields = viewFields;
            fieldStub.restore();
            delete field;
        });

        it('should call field.setDisabled(false)', function() {
            field.model.set('parent_deleted', 0);
            view.setRowActionButtonStates();
            expect(fieldStub).toHaveBeenCalledWith(false);
        });

        it('should call field.setDisabled(true) ', function() {
            field.model.set('parent_deleted', 1);
            view.setRowActionButtonStates();
            expect(fieldStub).toHaveBeenCalledWith(true);
        });
    });
});
