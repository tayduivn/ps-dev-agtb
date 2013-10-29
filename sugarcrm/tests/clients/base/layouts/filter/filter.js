/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

describe("Base.Layout.Filter", function () {

    var app, layout, sinonSandbox;

    beforeEach(function () {
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();
    });

    afterEach(function () {
        sinonSandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        layout.dispose();
        layout.context = null;
        layout = null;
    });

    describe("filter layout", function () {
        var parentLayout
        beforeEach(function () {
            parentLayout = new Backbone.View();
            layout = SugarTest.createLayout('base', 'Accounts', 'filter', {last_state: {id: "filter"}}, false, false, {layout: parentLayout});
        });

        describe('events', function () {
            it('should call apply on filter:apply', function () {
                var stub = sinonSandbox.stub(layout, 'applyFilter');
                // clear previous events
                layout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                layout.trigger('filter:apply');
                expect(stub).toHaveBeenCalled();
            });

            it('should null the parent layout editing filter and trigger parent layout filter:create:close', function () {
                var spy = sinon.spy();
                parentLayout.on('filter:create:close', spy);

                layout.trigger('filter:create:close');
                expect(parentLayout.editingFilter).toEqual(null);
                expect(spy).toHaveBeenCalled();
            });
            it('should set the parent layout editing filter and trigger parent layout filter:create:open', function () {
                var spy = sinon.spy();
                parentLayout.on('filter:create:open', spy);
                var filtermodule = 'test';
                layout.trigger('filter:create:open', filtermodule);
                expect(parentLayout.editingFilter).toEqual(filtermodule);
                expect(spy).toHaveBeenCalled();
            });
            it('should trigger parent layout subpanel change on subpanel:change', function () {
                var spy = sinon.spy();
                parentLayout.on('subpanel:change', spy);
                layout.trigger('subpanel:change');
                expect(spy).toHaveBeenCalled();
            });
            it('should call initialize filter state on filter:get', function () {
                var stub = sinonSandbox.stub(layout, 'initializeFilterState');
                // clear previous events
                layout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                layout.trigger('filter:get');
                expect(stub).toHaveBeenCalled();
            });
            it('should trigger layout filter apply on parent layout filter:apply', function () {
                var stub = sinonSandbox.stub(layout, 'initializeFilterState');
                // clear previous events
                layout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                layout.trigger('filter:get');
                expect(stub).toHaveBeenCalled();
            });
            it('should call handleFilterPanelChange on parent layout filterpanel:change', function () {
                var stub = sinonSandbox.stub(layout, 'handleFilterPanelChange');
                // clear previous events
                layout.off();
                parentLayout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                parentLayout.trigger('filterpanel:change');
                expect(stub).toHaveBeenCalled();
            });
            describe('addFilter', function() {
                var lastStateStub, setLastFilterStub, clearFilterEditStateStub, layoutTriggerStub;
                beforeEach(function() {
                    lastStateStub = sinonSandbox.stub(app.user.lastState, 'set');
                    setLastFilterStub = sinonSandbox.stub(layout, 'setLastFilter');
                    clearFilterEditStateStub = sinonSandbox.stub(layout, 'clearFilterEditState');
                });
                it('should be called by parent layout', function() {
                    var addFilterStub = sinonSandbox.stub(layout, 'addFilter');
                    
                    // clear previous events
                    layout.off();
                    parentLayout.off();
                    // replace the original fn with the spy
                    layout.initialize(layout.options);

                    parentLayout.trigger('filter:add');
                    expect(addFilterStub).toHaveBeenCalled();
                });
                it('should add the filter, update saved filters, set last state, clear edit state and reinitialize"',
                    function() {
                        layoutTriggerStub = sinonSandbox.stub(layout.layout, 'trigger');
                        layout.addFilter(new Backbone.Model({id: 'new_filter'}));
                        expect(layout.filters.get('new_filter')).toBeDefined();
                        expect(lastStateStub).toHaveBeenCalled();
                        expect(clearFilterEditStateStub).toHaveBeenCalled();
                        expect(layoutTriggerStub).toHaveBeenCalled();
                        expect(layoutTriggerStub).toHaveBeenCalledWith('filter:reinitialize');
                    }
                );
            });
            it('should call removeFilter  on parent layout filter:remove', function () {
                var stub = sinonSandbox.stub(layout, 'removeFilter');
                // clear previous events
                layout.off();
                parentLayout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                parentLayout.trigger('filter:remove');
                expect(stub).toHaveBeenCalled();
            });
            it('should call initializeFilterState  on parent layout filter:remove', function () {
                var stub = sinonSandbox.stub(layout, 'initializeFilterState');
                // clear previous events
                layout.off();
                parentLayout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                parentLayout.trigger('filter:reinitialize');
                expect(stub).toHaveBeenCalled();
            });
        });

        it('should remove filters', function () {
            var model = new Backbone.Model({id: '123'});
            var stubCache = sinonSandbox.stub(app.user.lastState, 'set');
            layout.filters.add(model);
            parentLayout.off();
            var spy = sinon.spy();
            parentLayout.on('filter:reinitialize', spy);
            layout.removeFilter(model);
            // removed the model
            expect(_.contains(layout.filters.models, model)).toBeFalsy();
            // updated cache of filters
            expect(stubCache).toHaveBeenCalled();
            // triggered filter reinit
            expect(spy).toHaveBeenCalled();
        });

        it('should add filters', function () {
            var model = new Backbone.Model({id: '123'});
            var stubCache = sinonSandbox.stub(app.user.lastState, 'set');
            layout.filters.add(model);
            parentLayout.off();
            var spy = sinon.spy();
            parentLayout.on('filter:reinitialize', spy);
            layout.addFilter(model);
            // added the model
            expect(_.contains(layout.filters.models, model)).toBeTruthy();
            // updated cache of filters
            expect(stubCache).toHaveBeenCalled();
            // triggered filter reinit
            expect(spy).toHaveBeenCalled();
        });
        it('should handle filter panel change for regular modules', function () {
            var spy1 = sinon.spy();
            var spy2 = sinon.spy();
            layout.on("filter:render:module", spy1);
            layout.on("filter:change:module", spy2);
            var stubCache = sinonSandbox.stub(app.user.lastState, 'get');
            var stubData = sinonSandbox.stub(app.data, 'getRelatedModule');

            layout.handleFilterPanelChange('Accounts', true);

            sinon.assert.notCalled(stubCache);
            sinon.assert.notCalled(spy1);
            sinon.assert.notCalled(spy2);
        });
        it('should handle filter panel change for activity stream', function () {
            var spy1 = sinon.spy();
            var spy2 = sinon.spy();
            layout.on("filter:render:module", spy1);
            layout.on("filter:change:module", spy2);
            var stubCache = sinonSandbox.stub(app.user.lastState, 'get');
            var stubData = sinonSandbox.stub(app.data, 'getRelatedModule');

            layout.handleFilterPanelChange('activitystream', false);


            sinon.assert.notCalled(stubCache);
            sinon.assert.notCalled(stubData);
            expect(spy1).toHaveBeenCalled();
            expect(spy2.getCall(0).args[0]).toEqual('Activities');
        });
        it('should handle filter panel change for related records', function () {
            var spy1 = sinon.spy();
            var spy2 = sinon.spy();
            layout.layoutType = 'record';
            layout.on("filter:render:module", spy1);
            layout.on("filter:change:module", spy2);
            var stubCache = sinonSandbox.stub(app.user.lastState, 'get', function () {
                return 'Bugs';
            });
            var stubData = sinonSandbox.stub(app.data, 'getRelatedModule', function () {
                return 'Test';
            });

            layout.handleFilterPanelChange('Accounts', false);

            expect(spy1).toHaveBeenCalled();
            expect(spy2.getCall(0).args[1]).toEqual('Bugs');
            expect(spy2.getCall(0).args[0]).toEqual('Test');
        });
        describe('handleFilterChange', function() {
            var ctxt, lastEditState, model;
            var stubCache, triggerStub, layoutTriggerStub, retrieveFilterEditStateStub;
            beforeEach(function() {
                ctxt = new Backbone.Model({collection: {
                    resetPagination:function(){},
                    reset: function(){}
                }});

                stubCache = sinonSandbox.stub(app.user.lastState, 'set');
                sinonSandbox.stub(layout, 'getRelevantContextList', function () {
                    return [ctxt];
                });
                triggerStub = sinonSandbox.stub(layout, 'trigger');
                layoutTriggerStub = sinonSandbox.stub(layout.layout, 'trigger');

                lastEditState = undefined;
                retrieveFilterEditStateStub = sinonSandbox.stub(layout, 'retrieveFilterEditState', function() {
                    return lastEditState;
                });

                model = new Backbone.Model({id: '123', filter_definition: 'test'});
                layout.filters.add(model);
            });
            it('should save last filter into cache', function() {
                layout.handleFilterChange(model.get('id'), false);
                expect(stubCache).toHaveBeenCalled();
                expect(layoutTriggerStub).not.toHaveBeenCalled();
            });
            it('should not save last filter into cache', function() {
                layout.handleFilterChange(model.get('id'), true);
                expect(stubCache).not.toHaveBeenCalled();
                layout.handleFilterChange(undefined, false);
                expect(stubCache).not.toHaveBeenCalled();
            });
            describe('preserving last search', function() {
                it('should open the filter form if the edit state is available and validate', function() {
                    lastEditState = {name: 'test', filter_definition: [{'$favorite':''}]};
                    expect(model.toJSON()).not.toEqual(lastEditState);
                    layout.handleFilterChange(model.get('id'), false);
                    expect(triggerStub).toHaveBeenCalled();
                    expect(triggerStub).toHaveBeenCalledWith('filter:create:open');
                    expect(layoutTriggerStub).toHaveBeenCalled();
                    expect(layoutTriggerStub).toHaveBeenCalledWith('filter:create:validate');
                });
                it('should validate because the filter definition has changed', function() {
                    model.set({name: 'test'});
                    lastEditState = {id: '123', name: 'test', filter_definition: [{'$favorite':''}]};
                    expect(model.toJSON()).not.toEqual(lastEditState);
                    layout.handleFilterChange(model.get('id'), false);
                    expect(triggerStub).toHaveBeenCalled();
                    expect(triggerStub).toHaveBeenCalledWith('filter:create:open');
                    expect(layoutTriggerStub).toHaveBeenCalled();
                });
                it('should not validate if the filter definition has not changed', function() {
                    model.set({name: 'test', filter_definition: [{'$favorite':''}]});
                    lastEditState = {id: '123', name: 'test', filter_definition: [{'$favorite':''}]};
                    expect(model.toJSON()).toEqual(lastEditState);
                    layout.handleFilterChange(model.get('id'), false);
                    expect(triggerStub).toHaveBeenCalled();
                    expect(triggerStub).toHaveBeenCalledWith('filter:create:open');
                    expect(layoutTriggerStub).not.toHaveBeenCalled();
                });
                it('should not open the filter form if no edit state available', function() {
                    layout.handleFilterChange(model.get('id'), false);
                    expect(triggerStub).not.toHaveBeenCalledWith('filter:create:open');
                    expect(layoutTriggerStub).not.toHaveBeenCalled();
                    expect(layoutTriggerStub).not.toHaveBeenCalledWith('filter:create:validate');
                });
            });
            it('shoud determine if we need to clear the collection(s) and trigger quicksearch if yes', function() {
                layout.handleFilterChange(model.get('id'), false);
                expect(ctxt.get('collection').origFilterDef).toEqual(model.get('filter_definition'));
                expect(triggerStub).toHaveBeenCalled();
                expect(triggerStub).toHaveBeenCalledWith('filter:clear:quicksearch');
            });
            it('shoud determine if we need to clear the collection(s) and do nothing if no', function() {
                ctxt.get('collection').origFilterDef = model.get('filter_definition');
                layout.handleFilterChange(model.get('id'), false);
                expect(triggerStub).not.toHaveBeenCalled();
            });
        });

        it('should be able to apply a filter', function(){
            var ctxt = app.context.getContext();
            ctxt.set({
                module: 'Accounts',
                layout: 'filter'
            });
            ctxt._recordListFields = ['name', 'date_modified'];
            ctxt.prepare();
            var _oResetPagination = ctxt.get('collection').resetPagination;
            ctxt.get('collection').resetPagination = function() {};
            var stub = sinonSandbox.stub(ctxt,'loadData', function(options){options.success();});
            var resetLoadFlagSpy = sinon.spy(ctxt,'resetLoadFlag');
            var query = 'test query';
            var testFilterDef = {
              '$name': 'test'
            };
            var testFilterDef1 = {
                '$name': 'test1'
            };
            var spy = sinon.spy();

            sinonSandbox.stub(layout, 'getRelevantContextList', function () {
                return [ctxt];
            });
            sinonSandbox.stub(layout,'buildFilterDef',function(){return testFilterDef1;});

            app.events.on('preview:close', spy);

            layout.applyFilter(query,testFilterDef);

            expect(ctxt.get('collection').filterDef).toEqual(testFilterDef1);
            expect(ctxt.get('collection').origFilterDef).toEqual(testFilterDef);
            expect(ctxt.get('skipFetch')).toBeFalsy();
            expect(ctxt.get('fields')).toEqual(['name', 'date_modified']);
            expect(resetLoadFlagSpy).toHaveBeenCalled();
            expect(stub).toHaveBeenCalled();
            expect(spy).toHaveBeenCalled();
            ctxt.get('collection').resetPagination = _oResetPagination;
        });
        it('should be able to add or remove a clear icon depending on the quicksearch field', function() {
            sinonSandbox.stub(layout, 'getRelevantContextList', function() { return []; });
            layout.$el = $('<div></div>');
            layout.applyFilter('not empty');
            expect(layout.$('.add-on.icon-remove')[0]).not.toBeUndefined();
            layout.applyFilter('');
            expect(layout.$('.add-on.icon-remove')[0]).toBeUndefined();
        });
        it('should get relevant context lists for activities', function(){
            layout.showingActivities = true;
            var activityView = new Backbone.View();
            activityView.name = 'activitystream';
            var ctxt = app.context.getContext();
            ctxt.set({
                collection: new Backbone.Collection(),
                module: 'Accounts',
                layout: 'filter'
            });
            ctxt.prepare();
            activityView.context = ctxt;
            layout.layout._components = [activityView];
            var expectedList = [ctxt];
            sinon.mock(parentLayout,'getActivityContext', function(){return ctxt;});
            var resultList = layout.getRelevantContextList();
            _.each(expectedList, function(ctx){
                expect(_.contains(resultList, ctx)).toBeTruthy();
            });
        });
        it('should get relevant context lists for records layouts', function(){
            layout.showingActivities = false;
            layout.layoutType = 'records';
            var ctxt = app.context.getContext();
            ctxt.set({
                collection: new Backbone.Collection(),
                module: 'Accounts',
                layout: 'filter'
            });
            ctxt.prepare();
            var expectedList = [layout.context];
            var resultList = layout.getRelevantContextList();
            _.each(expectedList, function(ctx){
                expect(_.contains(resultList, ctx)).toBeTruthy();
            });
        });
        it('should get relevant context lists for any other views', function(){
            layout.showingActivities = false;
            layout.layoutType = 'list';
            var ctxt = app.context.getContext();
            ctxt.set({
                collection: new Backbone.Collection(),
                module: 'Accounts',
                layout: 'filter',
                link:'test1',
                isSubpanel:true,
                hidden: false
            });
            layout.context.children.push(ctxt);

            var ctxt1 = app.context.getContext();
            ctxt1.set({
                collection: new Backbone.Collection(),
                module: 'Accounts',
                layout: 'filter',
                link:'test1',
                isSubpanel:true,
                hidden: false
            });
            layout.context.children.push(ctxt1);

            var ctxtWithoutCollection = app.context.getContext();
            ctxtWithoutCollection.set({
                module: 'Accounts',
                layout: 'filter',
                link:'testNoCollection',
                isSubpanel:true,
                hidden: false
            });
            layout.context.children.push(ctxtWithoutCollection);

            var ctxtWithModelId = app.context.getContext();
            ctxtWithModelId.set({
                collection: new Backbone.Collection(),
                modelId: 'model_id',
                module: 'Accounts',
                layout: 'filter',
                link:'testModelId',
                isSubpanel:true,
                hidden: false
            });
            layout.context.children.push(ctxtWithModelId);

            var expectedList = [ctxt, ctxt1];
            var resultList = layout.getRelevantContextList();
            _.each(expectedList, function(ctx){
                expect(_.contains(resultList, ctx)).toBeTruthy();
            });
            expect(_.contains(resultList, ctxtWithoutCollection)).toBeFalsy();
        });
        it('should be able to build filter defs', function(){
            var searchTerm = 'test',
                ctxt = app.context.getContext();
            var odef = {};
            var result = [{
                'name': {
                    '$starts':'test'
                }
            }];
            ctxt.set({
                module: 'Accounts',
                layout: 'filter'
            });

            ctxt.prepare();

            sinonSandbox.stub(layout, 'getModuleQuickSearchFields', function() {
                return {'name': 'name'};
            });
            var builtDef = layout.buildFilterDef(odef,searchTerm,ctxt);
            expect(builtDef).toEqual(result);
        });
        it('should be able to build filter defs with multiple quick search fields', function(){
            var searchTerm = 'test',
                ctxt = app.context.getContext();
            var odef = {
                'test': {
                    '$test':'test'
                }
            };
            var result = [{
                '$and': [
                    {
                        'test': {
                            '$test':'test'
                        }
                    },
                    {
                        'name': {
                            '$starts':'test'
                        }
                    }
                ]
            }];
            ctxt.set({
                module: 'Accounts',
                layout: 'filter'
            });

            ctxt.prepare();

            sinonSandbox.stub(layout, 'getModuleQuickSearchFields', function() {
                return {'name': 'name'};
            });
            var builtDef = layout.buildFilterDef(odef,searchTerm,ctxt);
            expect(builtDef).toEqual(result);
        });
        it('should be able to append filter defs on filter build', function(){
            var searchTerm = 'test',
                ctxt = app.context.getContext();
            var odef = {};
            var result = [{
                '$or': [
                    {
                        'name': {
                            '$starts':'test'
                        }
                    },
                    {
                        'last_name': {
                            '$starts':'test'
                        }
                    }
                ]
            }];
            ctxt.set({
                module: 'Accounts',
                layout: 'filter'
            });

            ctxt.prepare();

            sinonSandbox.stub(layout, 'getModuleQuickSearchFields', function() {
                return {'name': 'name', 'last_name': 'last_name'};
            });
            var builtDef = layout.buildFilterDef(odef,searchTerm,ctxt);
            expect(builtDef).toEqual(result);
        });
        describe('initializeFilterState', function() {
            var lastStateFilter;
            var stubCache, nextCallStub;
            beforeEach(function() {
                lastStateFilter = undefined;
                stubCache = sinonSandbox.stub(app.user.lastState, 'get', function() {
                    return lastStateFilter;
                });
                nextCallStub = sinonSandbox.stub(layout, 'applyPreviousFilter');
                // Add the test filter to the filter collection
                layout.filters.add(new Backbone.Model({'id': 'testFilter'}));
            });
            it('should apply last filter and not specify link because not on record layout (ie subpanels)', function() {
                lastStateFilter = 'testFilter';

                var expected = ['Accounts', undefined, {'filter': 'testFilter'}];
                layout.initializeFilterState('Accounts');
                expect(nextCallStub.lastCall.args).toEqual(expected);
            });
            it('should apply subpanel link and filter when a subpanel filter is specified', function() {
                layout.layoutType = 'record';
                layout.showingActivities = false;
                lastStateFilter = 'testFilter';

                var expected = ['Accounts', 'testFilter', {'link': 'testFilter', 'filter': 'testFilter'}];
                layout.initializeFilterState('Accounts');
                expect(nextCallStub.lastCall.args).toEqual(expected);
            });
            it('should apply "all_modules" link and  "all_records" filter when last subpanel filter not specified',
                function() {
                    layout.layoutType = 'record';
                    layout.showingActivities = false;

                    var expected = ['Accounts', undefined, {'link': 'all_modules', 'filter': 'all_records'}];
                    layout.initializeFilterState('Accounts');
                    expect(nextCallStub.lastCall.args).toEqual(expected);
                }
            );
        });

        it('should be able to apply the previous filter when not showing activites', function(){
            var modName = 'Accounts';
            var linkName = undefined;
            var data = {'filter':'testFilter'};
            var expectedTriggerArgs = [modName,linkName,true];
            var spy = sinon.spy();
            var getFilterSpy = sinonSandbox.stub(layout,'getFilters');
            layout.off();
            layout.on('filter:change:module',spy);
            layout.applyPreviousFilter(modName, linkName, data);
            expect(getFilterSpy.getCall(0).args).toEqual([modName, data.filter]);
            expect(spy.getCall(0).args).toEqual(expectedTriggerArgs);

            data = {'link':'testLink','filter':'testFilter'};
            layout.applyPreviousFilter(modName, 'testLink', data);
            expectedTriggerArgs = [modName,'testLink',true];
            expect(getFilterSpy.getCall(1).args).toEqual([modName, data.filter]);
            expect(spy.getCall(1).args).toEqual(expectedTriggerArgs);
        });
        it('should get filters from cache', function(){
            var modName = 'Accounts';
            var defaultName = 'testDefault';
            var handleFilterRetrieveStub = sinonSandbox.stub(layout, 'handleFilterRetrieve');
            var baseController = app.view._getController({type:'layout',name:'filter'});
            baseController.loadedModules[modName] = true;
            sinonSandbox.stub(app.user.lastState, 'get', function () {
                return [{id:'1234'},{id:'123'}];
            });


            layout.getFilters(modName, defaultName);
            expect(layout.filters.models.length).toEqual(2);
            expect(handleFilterRetrieveStub.getCall(0).args).toEqual([modName,defaultName]);
        });
        it('should sort filters alphabetically per categories', function(){
            sinonSandbox.stub(app.lang, 'get', function(key) {
                var dictionnary = {
                    'LBL_MODULE_NAME': 'Leads',
                    'LBL_ASSIGNED_TO_ME': 'My {0}',
                    'LBL_FAVORITES': 'My Favorites'
                };
                return dictionnary[key];
            });
            layout.layout.currentModule = 'Accounts';

            layout.filters.add({id: 'random_id_1', name: 'Best Filter', editable: true});
            layout.filters.add({id: 'assigned_to_me', name: 'LBL_ASSIGNED_TO_ME', editable: false});

            // Sort results: `My Favorites`, `My Leads`, `Best Filter`, `First Filter`
            expect(layout.filters.pluck('id')).toEqual(['assigned_to_me', 'random_id_1']);

            layout.filters.add({id: 'random_id_2', name: 'First Filter', editable: true});

            // Sort results: `My Favorites`, `Best Filter`, `First Filter`
            expect(layout.filters.pluck('id')).toEqual(['assigned_to_me', 'random_id_1', 'random_id_2']);

            layout.filters.add({id: 'favorites', name: 'LBL_FAVORITES', editable: false});

            // Sort results: `My Favorites`, `My Leads`, `Best Filter`, `First Filter`
            expect(layout.filters.pluck('id')).toEqual(['favorites', 'assigned_to_me', 'random_id_1', 'random_id_2']);
        });
        it('should get filters from the server', function(){
            var modName = 'TestModule';
            var defaultName = 'testDefault';
            var handleFilterRetrieveStub = sinonSandbox.stub(layout, 'handleFilterRetrieve');
            sinonSandbox.stub(layout.filters,'fetch', function(options){options.success();});
            var baseController = app.view._getController({type:'layout',name:'filter'});

            layout.getFilters(modName, defaultName);
            expect(baseController.loadedModules[modName]).toBeTruthy();
            expect(handleFilterRetrieveStub.getCall(0).args).toEqual([modName,defaultName]);
        });
        describe('handleFilterRetrieve', function() {
            var lastFilter, moduleName = 'TestModule';
            var clearLastFilterStub, triggerStub, layoutTriggerStub;
            beforeEach(function() {
                var meta = {
                    basic: {
                        meta: {
                            filters: [
                                {
                                    id: 'favorites',
                                    filter_definition: [
                                        {'$favorites': ''}
                                    ]
                                },
                                {
                                    id: 'owner',
                                    filter_definition: [
                                        {'$owner': ''}
                                    ]
                                }
                            ],
                            'default_filter': 'owner'
                        }
                    }
                };
                lastFilter = undefined;
                sinonSandbox.stub(layout, 'setLastFilter', function(module, layout, id) {
                    lastFilter = id;
                });
                sinonSandbox.stub(layout, 'getLastFilter', function() {
                    return lastFilter;
                });
                sinonSandbox.stub(layout, 'getModuleFilterMeta', function() { return meta; });
                clearLastFilterStub = sinonSandbox.stub(layout, 'clearLastFilter');
                triggerStub = sinonSandbox.stub(layout, 'trigger');
                layoutTriggerStub = sinonSandbox.stub(layout.layout, 'trigger');
            });
            it('should retrieve the default filter from metadata if last filter and defaultId not specified', function() {
                layout.handleFilterRetrieve(moduleName);
                expect(clearLastFilterStub).toHaveBeenCalled();
                expect(triggerStub).toHaveBeenCalledWith('filter:change:filter', 'owner');
            });
            it('should ensure possible filters are in the filters collection', function() {
                layout.handleFilterRetrieve(moduleName, 'random_test');
                expect(clearLastFilterStub).toHaveBeenCalled();
                expect(triggerStub).toHaveBeenCalledWith('filter:change:filter', 'owner');
            });
            it('should retrieve the last filter if available in the filter collection', function() {
                lastFilter = 'my_filter';
                layout.filters.add({id: 'my_filter'});
                layout.handleFilterRetrieve(moduleName);
                expect(clearLastFilterStub).not.toHaveBeenCalled();
                expect(triggerStub).toHaveBeenCalledWith('filter:change:filter', 'my_filter');
            });
            it('should clear last filter if not found in the filter collection', function() {
                lastFilter = 'random_test';
                layout.handleFilterRetrieve(moduleName, 'random_test');
                expect(clearLastFilterStub).toHaveBeenCalled();
                expect(triggerStub).toHaveBeenCalledWith('filter:change:filter', 'owner');
            });
            it('should not clear last filter if filter id is equal to create', function() {
                lastFilter = 'create';
                layout.handleFilterRetrieve(moduleName);
                expect(clearLastFilterStub).not.toHaveBeenCalled();
                expect(triggerStub).toHaveBeenCalledWith('filter:change:filter', 'create');
            });
        });
        it('should handle filter retrieve', function(){
            sinonSandbox.stub(app.user.lastState, 'get', function () {
                return 'testID';
            });
            var modName = 'Accounts';
            var defaultId = 'defaultId';
            var spy1 = sinon.spy();
            var spy2 = sinon.spy();
            // clear other events from firing that we don't care about
            layout.off();
            layout.on('filter:render:filter', spy1);
            layout.on('filter:change:filter', spy2);
            layout.handleFilterRetrieve(modName, defaultId);

            expect(spy1).toHaveBeenCalled();
            expect(spy2).toHaveBeenCalled();
        });
        describe('canCreateFilter', function() {
            var hasAccess, metadata;
            beforeEach(function() {
                var ctxt = new Backbone.Model({collection: {}, module: 'Accounts'});
                sinonSandbox.stub(layout, 'getRelevantContextList', function() {
                    return [ctxt];
                });
                sinonSandbox.stub(app.acl, 'hasAccess', function() {
                    return hasAccess;
                });
                sinonSandbox.stub(app.metadata, 'getModule', function() {
                    return metadata;
                });
            });
            it('should return true because user has access and it is enabled in the metadata', function() {
                hasAccess = true;
                metadata = {
                    'filters': {
                        'f1': {
                            'meta': {
                                'create': true
                            }
                        }
                    }
                };
                expect(layout.canCreateFilter()).toBeTruthy();
            });
            it('should return false because it is set to false in the metadata', function() {
                hasAccess = true;
                metadata = {
                    'filters': {
                        'f1': {
                            'meta': {
                                'create': false
                            }
                        }
                    }
                };
                expect(layout.canCreateFilter()).toBeFalsy();
            });
            it('should return false because user has no access', function() {
                hasAccess = false;
                metadata = {
                    'filters': {
                        'f1': {
                            'meta': {
                                'create': true
                            }
                        }
                    }
                };
                expect(layout.canCreateFilter()).toBeFalsy();
            });
        });

        it('should get module filter meta', function () {
            var result = {'test': 'test'};
            sinonSandbox.stub(app.metadata, 'getModule', function () {
                return {
                    filters: result
                };
            });

            var meta = layout.getModuleFilterMeta('Accounts');
            expect(meta).toEqual(result);
        });
        it('should init filter state on render', function () {
            var initStub = sinonSandbox.stub(layout, 'initializeFilterState');
            layout._render();
            expect(initStub).toHaveBeenCalled();
        });
        it('should clear filters on unbind', function () {
            var oFilters = layout.filters;
            layout.filters = new Backbone.Collection();
            var spy = sinonSandbox.spy(layout.filters, 'off');

            layout.unbind();
            expect(layout.filters).toEqual(null);
            expect(spy).toHaveBeenCalled();
            // restore filters that we destroyed
            layout.filters = oFilters;
        });

        describe('last selected filter', function() {
            var expectedKey = 'Accounts:filter:last-TestModule-TestLayout',
                filterModule = 'TestModule',
                layoutName = 'TestLayout';
            var stubCache;

            it('should save filter id into cache', function(){
                var expectedValue = 'tvalue';
                stubCache = sinonSandbox.stub(app.user.lastState, 'set');
                layout.setLastFilter(filterModule, layoutName, 'tvalue');
                expect(stubCache).toHaveBeenCalled();
                expect(stubCache.getCall(0).args[0]).toEqual(expectedKey);
                expect(stubCache.getCall(0).args[1]).toEqual(expectedValue);
            });
            it('should get filter id from cache', function(){
                stubCache = sinonSandbox.stub(app.user.lastState, 'get');
                layout.getLastFilter(filterModule, layoutName);
                expect(stubCache).toHaveBeenCalled();
                expect(stubCache.getCall(0).args[0]).toEqual(expectedKey);
            });
            it('should clear filter id from cache', function(){
                stubCache = sinonSandbox.stub(app.user.lastState, 'remove');
                layout.clearLastFilter(filterModule, layoutName);
                expect(stubCache).toHaveBeenCalled();
                expect(stubCache.getCall(0).args[0]).toEqual(expectedKey);
            });
        });

        describe('last edit state filter', function() {
            var expectedKey = 'Accounts:filter:edit-TestModule-TestLayout';
            var stubCache;

            beforeEach(function() {
                layout.layout.currentModule = 'TestModule';
                layout.layoutType = 'TestLayout';
            });

            it('should save filter definition into cache', function(){
                var expectedValue = {'filter_definition':[{'account_type':{'$in':['Competitor']}}],'name':'Test Name'};
                stubCache = sinonSandbox.stub(app.user.lastState, 'set');
                var filter = {'filter_definition':[{'account_type':{'$in':['Competitor']}}],'name':'Test Name'};
                layout.saveFilterEditState(filter);
                expect(stubCache).toHaveBeenCalled();
                expect(stubCache.getCall(0).args[0]).toEqual(expectedKey);
                expect(stubCache.getCall(0).args[1]).toEqual(expectedValue);
            });
            it('should get filter definition from cache', function(){
                stubCache = sinonSandbox.stub(app.user.lastState, 'get');
                layout.retrieveFilterEditState();
                expect(stubCache).toHaveBeenCalled();
                expect(stubCache.getCall(0).args[0]).toEqual(expectedKey);
            });
            it('should clear filter definition from cache', function(){
                stubCache = sinonSandbox.stub(app.user.lastState, 'remove');
                layout.clearFilterEditState();
                expect(stubCache).toHaveBeenCalled();
                expect(stubCache.getCall(0).args[0]).toEqual(expectedKey);
            });
        });

        describe('filter:create:close', function() {
            var clearFilterEditStateStub, parentLayoutTriggerStub, clearLastFilterStub;
            beforeEach(function() {
                clearFilterEditStateStub = sinonSandbox.stub(layout, 'clearFilterEditState');
                parentLayoutTriggerStub = sinonSandbox.stub(layout.layout, 'trigger');
                clearLastFilterStub = sinonSandbox.stub(layout, 'clearLastFilter');
            });
            it('should clear filter edit state from cache', function() {
                layout.trigger('filter:create:close');
                expect(clearFilterEditStateStub).toHaveBeenCalled();
            });
            it('should reset "editingFilter" on parent layout', function() {
                layout.layout.editingFilter = 'filter_id';
                layout.trigger('filter:create:close');
                expect(layout.layout.editingFilter).toBeNull();
            });
            it('should trigger "filter:create:close" on parent layout', function() {
                layout.trigger('filter:create:close');
                expect(clearLastFilterStub).not.toHaveBeenCalled();
                expect(parentLayoutTriggerStub).toHaveBeenCalled();
                expect(parentLayoutTriggerStub).not.toHaveBeenCalledWith('filter:reinitialize');
                expect(parentLayoutTriggerStub).toHaveBeenCalledWith('filter:create:close');
            });
            it('should clear last filter and call "filter:reinitialize" when canceling filter creation', function() {
                sinonSandbox.stub(layout, 'getLastFilter', function() { return 'create'; });
                layout.trigger('filter:create:close');
                expect(clearLastFilterStub).toHaveBeenCalled();
                expect(parentLayoutTriggerStub).toHaveBeenCalled();
                expect(parentLayoutTriggerStub).toHaveBeenCalledWith('filter:reinitialize');
                expect(parentLayoutTriggerStub).toHaveBeenCalledWith('filter:create:close');
            });
        });
    });
});
