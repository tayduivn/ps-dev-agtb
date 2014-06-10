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

describe("Base.Layout.Filterpanel", function(){

    var app, layout, getModuleStub, activityStreamEnabled = true;

    beforeEach(function() {
        app = SugarTest.app;
        getModuleStub = sinon.stub(app.metadata, 'getModule', function(module) {
            return {activityStreamEnabled: activityStreamEnabled};
        });
    });

    afterEach(function() {
        getModuleStub.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        layout.dispose();
        layout.context = null;
        layout = null;
    });

    describe("Filter Panel", function() {
        var oLastState;
        beforeEach(function() {
            oLastState = app.user.lastState;
            app.user.lastState = {
                key: function(){},
                get: function(){},
                set: function(){},
                register: function(){}
            };
            layout = SugarTest.createLayout("base", "Accounts", "filterpanel", {});
        });
        afterEach(function () {
            app.user.lastState = oLastState;
        });

        describe('initialize', function() {

            it('should trigger filterpanel:change:module', function() {
                var spy = sinon.spy();
                layout.off();
                layout.on('filterpanel:change:module', spy);

                layout.initialize(layout.options);
                expect(spy).toHaveBeenCalled();
            });

            it('should initialize the filterOptions object', function() {
                // Verify the filter options fallback chain.
                layout.options.meta = layout.options.meta || {};
                layout.options.meta.filter_options = {
                    stickiness: false,
                    show_actions: false
                };
                layout.context.set('filterOptions', {
                    show_actions: true
                });

                layout.initialize(layout.options);

                expect(layout.context.get('filterOptions').stickiness).toEqual(false);
                expect(layout.context.get('filterOptions').show_actions).toEqual(true);
            });
        });


        it("should trigger `filter:reinitialize` on render", function() {
            var spy = sinon.spy();
            layout.on('filter:reinitialize', spy);
            layout.render();
            expect(spy).toHaveBeenCalled();
        });

        describe('events', function(){
           it('should update current module and link on filter change', function(){
               layout.trigger('filterpanel:change:module','test','testLink');
               expect(layout.currentModule).toEqual('test');
               expect(layout.currentLink).toEqual('testLink');
           });
        });

        describe('applying last filter when a change happens on list view', function() {
            var collection, origFilterDef, triggerStub;
            beforeEach(function() {
                collection = new Backbone.Collection();
                triggerStub = sinon.stub(layout, 'trigger');
                //Fake quicksearch field
                var $input = $('<input>').addClass('search-name').val('query test');
                $('<div>').addClass('search').append($input[0]).appendTo(layout.$el);
            });
            afterEach(function() {
                triggerStub.restore();
            });
            it('should trigger filtering if no condition', function() {
                //Fake original filter def
                origFilterDef = [{field1: { $equals: 'value1'}}];
                collection.origFilterDef = origFilterDef;

                //Call the method
                layout.applyLastFilter(collection);

                expect(triggerStub).toHaveBeenCalled();
                expect(triggerStub).toHaveBeenCalledWith('filter:apply', 'query test', origFilterDef);
            });
            it('should trigger filtering because the filter contains $favorites (1)', function() {
                //Fake original filter def
                origFilterDef = [{field1: { $equals: 'value1'}}, {$favorite: ''}];
                collection.origFilterDef = origFilterDef;

                //Call the method
                layout.applyLastFilter(collection, 'favorite');

                expect(triggerStub).toHaveBeenCalled();
                expect(triggerStub).toHaveBeenCalledWith('filter:apply', 'query test', origFilterDef);
            });
            it('should trigger filtering because the filter contains $favorites (1)', function() {
                //Fake original filter def
                origFilterDef = {$favorite: ''};
                collection.origFilterDef = origFilterDef;

                //Call the method
                layout.applyLastFilter(collection, 'favorite');

                expect(triggerStub).toHaveBeenCalled();
                expect(triggerStub).toHaveBeenCalledWith('filter:apply', 'query test', origFilterDef);
            });
            it('should not trigger filtering because the filter does not contain $favorite', function() {
                //Fake original filter def
                origFilterDef = [{field1: { $equals: 'value1'}}, {field2: { $starts: 'value2'}}];
                collection.origFilterDef = origFilterDef;

                //Call the method
                layout.applyLastFilter(collection, 'favorite');

                expect(triggerStub).not.toHaveBeenCalled();
            });
        });
    });

    describe('disableActivityStreamToggle', function(){
        it('should set activity stream toggle to inactive when activity stream not enabled', function(){
            var meta = {'availableToggles': [
                {'name': 'list', 'icon': 'icon-table', 'label': 'LBL_LISTVIEW'},
                {'name': 'activitystream', 'icon': 'icon-th-list', 'label': 'LBL_ACTIVITY_STREAM'}
            ], 'components': [
                {'layout': 'filter', 'targetEl': '.filter', 'position': 'prepend'},
                {'view': 'filter-actions', 'targetEl': '.filter-options'},
                {'view': 'filter-rows', 'targetEl': '.filter-options'},
                {'layout': 'activitystream', 'context': {'module': 'Activities'}},
                {'layout': 'list'}
            ]};
            activityStreamEnabled = false;

            layout = SugarTest.createLayout('base', 'Accounts', 'filterpanel', meta);

            var activityStreamToggle = _.find(layout.meta.availableToggles, function(toggle) {
                return toggle.name === 'activitystream';
            })

            expect(activityStreamToggle.disabled).toEqual(true);
            expect(activityStreamToggle.label).toEqual('LBL_ACTIVITY_STREAM_DISABLED');

            activityStreamEnabled = true;
        });

    });
});
