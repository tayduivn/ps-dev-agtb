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

    var app, layout;

    beforeEach(function () {
        app = SugarTest.app;
    });

    afterEach(function () {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        layout.dispose();
        layout.context = null;
        layout = null;
    });

    describe("filter layout", function () {
        var parentLayout
        beforeEach(function () {
            parentLayout = new Backbone.View();
            layout = SugarTest.createLayout('base', 'Accounts', 'filter', {}, false, false, {layout: parentLayout});
        });

        describe('events', function () {
            it('should call apply on filter:apply', function () {
                var stub = sinon.stub(layout, 'applyFilter');
                // clear previous events
                layout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                layout.trigger('filter:apply');
                expect(stub).toHaveBeenCalled();
                stub.restore();
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
                var stub = sinon.stub(layout, 'initializeFilterState');
                // clear previous events
                layout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                layout.trigger('filter:get');
                expect(stub).toHaveBeenCalled();
                stub.restore();
            });
            it('should trigger layout filter apply on parent layout filter:apply', function () {
                var stub = sinon.stub(layout, 'initializeFilterState');
                // clear previous events
                layout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                layout.trigger('filter:get');
                expect(stub).toHaveBeenCalled();
                stub.restore();
            });
            it('should call handleFilterPanelChange on parent layout filterpanel:change', function () {
                var stub = sinon.stub(layout, 'handleFilterPanelChange');
                // clear previous events
                layout.off();
                parentLayout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                parentLayout.trigger('filterpanel:change');
                expect(stub).toHaveBeenCalled();
                stub.restore();
            });
            it('should call addFilter  on parent layout filter:add', function () {
                var stub = sinon.stub(layout, 'addFilter', function () {
                });
                // clear previous events
                layout.off();
                parentLayout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                parentLayout.trigger('filter:add');
                expect(stub).toHaveBeenCalled();
                stub.restore();
            });
            it('should call removeFilter  on parent layout filter:remove', function () {
                var stub = sinon.stub(layout, 'removeFilter');
                // clear previous events
                layout.off();
                parentLayout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                parentLayout.trigger('filter:remove');
                expect(stub).toHaveBeenCalled();
                stub.restore();
            });
            it('should call initializeFilterState  on parent layout filter:remove', function () {
                var stub = sinon.stub(layout, 'initializeFilterState');
                // clear previous events
                layout.off();
                parentLayout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                parentLayout.trigger('filter:reinitialize');
                expect(stub).toHaveBeenCalled();
                stub.restore();
            });
        });

        it('should remove filters', function () {
            var model = new Backbone.Model({id: '123'});
            var stubCache = sinon.stub(app.cache, 'set');
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
            stubCache.restore();
        });

        it('should add filters', function () {
            var model = new Backbone.Model({id: '123'});
            var stubCache = sinon.stub(app.cache, 'set');
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
            stubCache.restore();
        });
        it('should handle filter panel change for regular modules', function () {
            var spy1 = sinon.spy();
            var spy2 = sinon.spy();
            layout.on("filter:render:module", spy1);
            layout.on("filter:change:module", spy2);
            var stubCache = sinon.stub(app.cache, 'get');
            var stubData = sinon.stub(app.data, 'getRelatedModule');

            layout.handleFilterPanelChange('Accounts', true);


            sinon.assert.notCalled(stubCache);
            sinon.assert.notCalled(spy1);
            sinon.assert.notCalled(spy2);
            stubCache.restore();
            stubData.restore();
        });
        it('should handle filter panel change for activity stream', function () {
            var spy1 = sinon.spy();
            var spy2 = sinon.spy();
            layout.on("filter:render:module", spy1);
            layout.on("filter:change:module", spy2);
            var stubCache = sinon.stub(app.cache, 'get');
            var stubData = sinon.stub(app.data, 'getRelatedModule');

            layout.handleFilterPanelChange('activitystream', false);


            sinon.assert.notCalled(stubCache);
            sinon.assert.notCalled(stubData);
            expect(spy1).toHaveBeenCalled();
            expect(spy2.getCall(0).args[0]).toEqual('Activities');

            stubCache.restore();
            stubData.restore();
        });
        it('should handle filter panel change for related records', function () {
            var spy1 = sinon.spy();
            var spy2 = sinon.spy();
            layout.layoutType = 'record';
            layout.on("filter:render:module", spy1);
            layout.on("filter:change:module", spy2);
            var stubCache = sinon.stub(app.cache, 'get', function () {
                return 'Bugs'
            });
            var stubData = sinon.stub(app.data, 'getRelatedModule', function () {
                return 'Test'
            });

            layout.handleFilterPanelChange('Accounts', false);

            expect(spy1).toHaveBeenCalled();
            expect(spy2.getCall(0).args[1]).toEqual('Bugs');
            expect(spy2.getCall(0).args[0]).toEqual('Test');

            stubCache.restore();
            stubData.restore();
        });
        it('should handle filter change', function () {
            var spy = sinon.spy();
            var model = new Backbone.Model({id: '123', filter_definition: 'test'});
            var ctxt = new Backbone.Model({collection: {
                resetPagination:function(){},
                reset: function(){}
            }});
            var contextListStub = sinon.stub(layout, 'getRelevantContextList', function () {
                return [ctxt];
            });
            layout.on('filter:clear:quicksearch', spy);
            layout.filters.add(model);
            var stubCache = sinon.stub(app.cache, 'set');
            layout.handleFilterChange(model.get('id'), false);
            expect(stubCache).toHaveBeenCalled();
            expect(ctxt.get('collection').origFilterDef).toEqual(model.get('filter_definition'));
            expect(spy).toHaveBeenCalled();
            stubCache.restore();
        });
        it('should be able to apply a filter', function(){
            var ctxt = app.context.getContext();
            ctxt.set({
                module: 'Accounts',
                layout: 'filter'
            });
            ctxt.prepare();
            var stub = sinon.stub(ctxt,'loadData', function(options){options.success();});
            var resetLoadFlagSpy = sinon.spy(ctxt,'resetLoadFlag');
            var query = 'test query';
            var testFilterDef = {
              '$name': 'test'
            };
            var testFilterDef1 = {
                '$name': 'test1'
            };
            var spy = sinon.spy();

            var contextListStub = sinon.stub(layout, 'getRelevantContextList', function () {
                return [ctxt];
            });
            var getFilterStub = sinon.stub(layout,'buildFilterDef',function(){return testFilterDef1});

            app.events.on('preview:close', spy);

            layout.applyFilter(query,testFilterDef);

            expect(ctxt.get('collection').filterDef).toEqual(testFilterDef1);
            expect(ctxt.get('collection').origFilterDef).toEqual(testFilterDef);
            expect(ctxt.get('skipFetch')).toBeFalsy();
            expect(resetLoadFlagSpy).toHaveBeenCalled();
            expect(stub).toHaveBeenCalled();
            expect(spy).toHaveBeenCalled();

        });
        it('should get relevant context lists for activities', function(){
            layout.showingActivities = true;
            var ctxt = app.context.getContext();
            ctxt.set({
                module: 'Accounts',
                layout: 'filter'
            });
            ctxt.prepare();
            var expectedList = [ctxt];
            sinon.mock(parentLayout,'getActivityContext', function(){return ctxt;});

            var resultList = layout.getRelevantContextList;
            _.each(expectedList, function(ctx){
                expect(_.contains(ctx,resultList));
            });
        });
        it('should get relevant context lists for record layouts', function(){
            layout.showingActivities = false;
            layout.layoutType = 'records';
            var ctxt = app.context.getContext();
            ctxt.set({
                module: 'Accounts',
                layout: 'filter'
            });
            ctxt.prepare();
            var expectedList = [ctxt, layout.context];
            var resultList = layout.getRelevantContextList;
            _.each(expectedList, function(ctx){
                expect(_.contains(ctx,resultList));
            });
        });
        it('should get relevant context lists for any other views', function(){
            layout.showingActivities = false;
            layout.layoutType = 'list';
            var ctxt = app.context.getContext();
            ctxt.set({
                module: 'Accounts',
                layout: 'filter',
                link:'test1',
                hidden: false
            });

            layout.context.children.push(ctxt);
            var ctxt1 = app.context.getContext();
            ctxt1.set({
                module: 'Accounts',
                layout: 'filter',
                link:'test1',
                hidden: false
            });

            layout.context.children.push(ctxt1);
            var expectedList = [ctxt, ctxt1];
            var resultList = layout.getRelevantContextList;
            _.each(expectedList, function(ctx){
                expect(_.contains(ctx,resultList));
            });
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

            sinon.stub(layout,'getModuleQuickSearchFields',function(){return {'name':'name'}});
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

            sinon.stub(layout,'getModuleQuickSearchFields',function(){return {'name':'name'}});
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

            sinon.stub(layout,'getModuleQuickSearchFields',function(){return {'name':'name','last_name':'last_name'}});
            var builtDef = layout.buildFilterDef(odef,searchTerm,ctxt);
            expect(builtDef).toEqual(result);
        });
        it('should init filter state', function(){
            var testFilter = new Backbone.Model({'id': 'testFilter'});
            layout.filters.add(testFilter);
            var stubCache = sinon.stub(app.cache, 'get', function () {
                return 'testFilter';
            });
            var nextCallStub = sinon.stub(layout,'applyPreviousFilter');
            var expected = ['Accounts',undefined,{'filter':'testFilter'}];

            layout.initializeFilterState('Accounts');
            expect(nextCallStub.getCall(0).args).toEqual(expected);
            layout.layoutType = 'record';
            layout.showingActivities = false;
            expected = ['Accounts',undefined,{'link':'testFilter','filter':'testFilter'}];
            layout.initializeFilterState('Accounts');
            expect(nextCallStub.getCall(1).args).toEqual(expected);
            stubCache.restore();
            var stubCache = sinon.stub(app.cache, 'get');
            expected = ['Accounts',undefined,{'link':'all_modules','filter':'all_records'}];
            layout.initializeFilterState('Accounts');
            expect(nextCallStub.getCall(2).args).toEqual(expected);
            stubCache.restore();
        });
        it('should be able to apply the previous filter when not showing activites', function(){
            var modName = 'Accounts';
            var linkName = undefined;
            var data = {'filter':'testFilter'};
            var expectedTriggerArgs = [modName,linkName,true];
            var spy = sinon.spy();
            var getFilterSpy = sinon.stub(layout,'getFilters');
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
            var handleFilterRetrieveStub = sinon.stub(layout, 'handleFilterRetrieve');
            var baseController = app.view._getController({type:'layout',name:'filter'});
            baseController.loadedModules[modName] = true;
            var stubCache = sinon.stub(app.cache, 'get', function () {
                return [{id:'1234'},{id:'123'}];
            });


            layout.getFilters(modName, defaultName);
            expect(layout.filters.models.length).toEqual(2);
            expect(handleFilterRetrieveStub.getCall(0).args).toEqual([modName,defaultName]);
            stubCache.restore();
        });
        it('should get filters from the server', function(){
            var modName = 'TestModule';
            var defaultName = 'testDefault';
            var handleFilterRetrieveStub = sinon.stub(layout, 'handleFilterRetrieve');
            var filterFetchStub = sinon.stub(layout.filters,'fetch', function(options){options.success();});
            var baseController = app.view._getController({type:'layout',name:'filter'});

            layout.getFilters(modName, defaultName);
            expect(baseController.loadedModules[modName]).toBeTruthy();
            expect(handleFilterRetrieveStub.getCall(0).args).toEqual([modName,defaultName]);
        });
        it('should handle filter retrieve', function(){
            var stubCache = sinon.stub(app.cache, 'get', function () {
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
            stubCache.restore();
        });
        it('should be able to check can create', function(){
            var ctxt = new Backbone.Model({collection: {}, module:'Accounts'});
            var contextListStub = sinon.stub(layout, 'getRelevantContextList', function () {
                return [ctxt];
            });
            var aclStub =sinon.stub(app.acl,'hasAccess', function(){
                return true;
            });
            var metaStub = sinon.stub(app.metadata, 'getModule', function(){
                    return {
                        'filters': {
                            'f1': {
                                'meta': {
                                    'create': true
                                }
                            }
                        }
                    };
            });

            expect(layout.canCreateFilter()).toBeTruthy();
            metaStub.restore();
            aclStub.restore();
            var aclStub =sinon.stub(app.acl,'hasAccess', function(){
                return true;
            });
            var metaStub = sinon.stub(app.metadata, 'getModule', function(){
                return {
                    'filters': {
                        'f1': {
                            'meta': {
                                'create': false
                            }
                        }
                    }
                };
            });

            expect(layout.canCreateFilter()).toBeFalsy();
            metaStub.restore();
            aclStub.restore();
            var aclStub =sinon.stub(app.acl,'hasAccess', function(){
                return false;
            });
            var metaStub = sinon.stub(app.metadata, 'getModule', function(){
                return {
                    'filters': {
                        'f1': {
                            'meta': {
                                'create': true
                            }
                        }
                    }
                };
            });

            expect(layout.canCreateFilter()).toBeFalsy();
            metaStub.restore();
            aclStub.restore();
        });
        it('should get module filter meta', function () {
            var result = {'test': 'test'};
            var metadataStub = sinon.stub(app.metadata, 'getModule', function () {
                return {
                    filters: result
                };
            });

            var meta = layout.getModuleFilterMeta('Accounts');
            expect(meta).toEqual(result);
            metadataStub.restore();
        });
        it('should get the highest priority field for search', function () {
            var moduleFilterMetaStub = sinon.stub(layout, 'getModuleFilterMeta', function () {
                return {
                    'meta1': {
                        'meta': {
                            'quicksearch_field': 'test1',
                            'quicksearch_priority': 0
                        }
                    },
                    'meta2': {
                        'meta': {
                            'quicksearch_field': 'test2',
                            'quicksearch_priority': 3
                        }
                    },
                    'meta3': {
                        'meta': {
                            'quicksearch_field': 'test3',
                            'quicksearch_priority': 2
                        }
                    }
                }
            });

            var field = layout.getModuleQuickSearchFields('Accounts');

            expect(field).toEqual('test2');
            moduleFilterMetaStub.restore();
        });
        it('should init filter state on render', function () {
            var initStub = sinon.stub(layout, 'initializeFilterState');

            layout._render();

            expect(initStub).toHaveBeenCalled();
        });
        it('should clear filters on unbind', function () {
            var oFilters = layout.filters;
            layout.filters = new Backbone.Collection();
            var spy = sinon.spy(layout.filters, 'off');

            layout.unbind();
            expect(layout.filters).toEqual(null);
            expect(spy).toHaveBeenCalled();
            // restore filters that we destroyed
            layout.filters = oFilters;
        });
    });
});
