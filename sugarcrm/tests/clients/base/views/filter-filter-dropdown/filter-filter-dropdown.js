describe("BaseFilterFilterDropdownView", function () {
    var view, layout, app, sinonSandbox;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'filter-filter-dropdown');
        SugarTest.testMetadata.set();
        layout = SugarTest.createLayout('base', "Cases", "filter", {}, null, null, { layout: new Backbone.View() });
        view = SugarTest.createView("base", "Cases", "filter-filter-dropdown", null, null, null, layout);
        view.layout = layout;
        app = SUGAR.App;
        sinonSandbox = sinon.sandbox.create();
    });

    afterEach(function () {
        sinonSandbox.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
    });

    describe('handleChange callback of filter:change:module', function() {
        var layoutStub, panelOpenStub, filter;

        beforeEach(function() {
            layoutStub = sinonSandbox.stub(view.layout, 'trigger');
            panelOpenStub = sinonSandbox.stub(view.layout, 'createPanelIsOpen', function() {
                return !!_.reduce(layoutStub.args, function(memo, args){
                    if (!_.isArray(args)) return memo;
                    if (args[0] === 'filter:create:open') return 1;
                    if (args[0] === 'filter:create:close') return 0;
                    return memo;
                }, 1);
            });
            view.filterNode = $('');
            view.layout.filters = new Backbone.Collection();
            filter = new Backbone.Model({id: 'test_id', editable: false });
            view.layout.filters.add(filter);
        });

        it('should open the filter form because id equals to create', function() {
            view.handleChange('create');

            expect(layoutStub).toHaveBeenCalled();
            expect(layoutStub.firstCall.args[0]).toEqual('filter:create:open');
        });

        it('should close the filter form because filter is not editable', function() {
            view.handleChange('test_id');

            expect(layoutStub).toHaveBeenCalled();
            expect(layoutStub.firstCall.args[0]).toEqual('filter:create:close');
            expect(layoutStub.secondCall).toBeNull();
        });

        it('should open and populate the filter form because filter is editable', function() {
            filter.set('editable', true);
            view.handleChange('test_id');

            expect(layoutStub).toHaveBeenCalled();
            expect(layoutStub.firstCall.args[1]).toEqual(filter);
            expect(layoutStub.secondCall).toBeNull();
        });

        it('should retrieve and populate last edit state', function() {
            filter.set('editable', true);

            var editState = new Backbone.Model({ id: 'test_id', filter_definition: [{'my_filter': {'is': 'cool'}}]});

            view.layout.retrieveFilterEditState = $.noop;
            sinonSandbox.stub(view.layout, 'retrieveFilterEditState', function() {
                return editState.toJSON();
            });

            view.handleChange('test_id');
            expect(layoutStub).toHaveBeenCalled();
            expect(layoutStub.firstCall.args[1].toJSON()).not.toEqual(filter.toJSON());
            expect(layoutStub.firstCall.args[1].toJSON()).toEqual(editState.toJSON());
            expect(layoutStub.secondCall).toBeNull();
        });
    });

    describe('handleModuleChange', function(){

        it('should disable filter dropdown when All Records is selected', function(){
            sinonSandbox.stub(app.metadata, 'getModule', function() { return {isBwcEnabled: false}; });
            expect(view.filterDropdownEnabled).toBeTruthy();
            view.layout.trigger("filter:change:module", "ALL_RECORDS", "all_modules");
            expect(view.filterDropdownEnabled).toBeFalsy();
        });

        it('should disable filter dropdown when a BWC module is selected', function(){
            sinonSandbox.stub(app.metadata, 'getModule', function() { return {isBwcEnabled: true}; });
            expect(view.filterDropdownEnabled).toBeTruthy();
            view.layout.trigger("filter:change:module", "TEST_MODULE", "test_id");
            expect(view.filterDropdownEnabled).toBeFalsy();
        });

    });

    describe('filterList', function() {

        var expected, filterList;

        beforeEach(function() {
            view.layout.filters = new Backbone.Collection();
            view.layout.filters.add(new Backbone.Model({id: 'all_records', name: 'ALL_RECORDS', editable: false }));
            view.layout.filters.add(new Backbone.Model({id: 'test_id', name: 'TEST' }));
            view.layout.filters.add(new Backbone.Model({id: 'test_id_2', name: 'TEST_2' }));
        });

        it('should return filter list with translated labels', function() {
            sinonSandbox.stub(view.layout, 'canCreateFilter', function() { return false; });
            expected = [
                { id: 'all_records', text: app.lang.get('ALL_RECORDS')},
                { id: 'test_id', text: app.lang.get('TEST'), firstUserFilter: true},
                { id: 'test_id_2', text: app.lang.get('TEST_2')}
            ];
            filterList = view.getFilterList();
            expect(filterList).toEqual(expected);
        });

        it('should return filter list (including create) with translated labels', function() {
            sinonSandbox.stub(view.layout, 'canCreateFilter', function() { return true; });
            expected = [
                { id: 'create', text: app.lang.get('LBL_FILTER_CREATE_NEW')},
                { id: 'all_records', text: app.lang.get('ALL_RECORDS')},
                { id: 'test_id', text: app.lang.get('TEST'), firstUserFilter: true},
                { id: 'test_id_2', text: app.lang.get('TEST_2')}
            ];
            filterList = view.getFilterList();
            expect(filterList).toEqual(expected);
        });
    });

    describe('select2 options', function() {

        describe('initSelection', function() {

            var $input, callback;

            beforeEach(function() {
                $input = $('<input type="text">');
                callback = sinon.stub();
                view.layout.filters = new Backbone.Collection();
                view.layout.filters.add(new Backbone.Model({id: 'test_id', name: 'TEST' }));
            });


            it('should recognize when selected filter is create', function() {
                var $input = $('<input type="text">').val('create'),
                    callback = sinon.stub(),
                    expected = {id: "create", text: app.lang.get("LBL_FILTER_CREATE_NEW")};

                view.initSelection($input, callback);

                expect(callback).toHaveBeenCalled();
                expect(callback.lastCall.args[0]).toEqual(expected);
            });

            it('should get selected filter', function() {
                var $input = $('<input type="text">').val('test_id'),
                    callback = sinon.stub(),
                    expected = { id: 'test_id', text: app.lang.get('TEST')};

                view.initSelection($input, callback);

                expect(callback).toHaveBeenCalled();
                expect(callback.lastCall.args[0]).toEqual(expected);
            });

            it('should get all_records filter with the module label', function() {
                view.layout.filters.add(new Backbone.Model({id: 'all_records', name: 'ALL_RECORDS' }));
                var $input = $('<input type="text">').val('all_records'),
                    callback = sinon.stub(),
                    expected = { id: 'all_records', text: app.lang.get('ALL_RECORDS')};

                view.initSelection($input, callback);

                expect(callback).toHaveBeenCalled();
                expect(callback.lastCall.args[0]).toEqual(expected);
            });

            it('should get all_records filter with basic label', function() {
                var $input = $('<input type="text">').val('all_records'),
                    callback = sinon.stub(),
                    expected = { id: 'all_records', text: app.lang.get('LBL_FILTER_ALL_RECORDS')};

                view.initSelection($input, callback);

                expect(callback).toHaveBeenCalled();
                expect(callback.lastCall.args[0]).toEqual(expected);
            });

        });

        describe('formatSelection', function() {
            beforeEach(function() {
                //Template replacement
                view._select2formatSelectionTemplate = function(val) { return val; };
            });
            it('should format the filter dropdown on left', function() {
                var expected = {label: app.lang.get("LBL_FILTER"), enabled: view.filterDropdownEnabled };

                expect(view.formatSelection({id: 'test', text: 'TEST'})).toEqual(expected);
            });
            it('should format the selected filter on right', function() {
                var jQuerySpys = {
                    html: sinon.spy(),
                    show: sinon.spy(),
                    hide: sinon.spy()
                };
                sinonSandbox.stub(view, '$', function() {
                    return jQuerySpys;
                });
                view.formatSelection({id: 'test', text: 'TEST'});
                expect(jQuerySpys.html).toHaveBeenCalled();
                expect(jQuerySpys.show).toHaveBeenCalled();
                expect(jQuerySpys.hide).not.toHaveBeenCalled();
            });
            it('should hide the close button if the selected filter is "all_records"', function() {
                var jQuerySpys = {
                    html: sinon.spy(),
                    show: sinon.spy(),
                    hide: sinon.spy()
                };
                sinonSandbox.stub(view, '$', function() {
                    return jQuerySpys;
                });
                view.formatSelection({id: 'all_records', text: 'TEST'});
                expect(jQuerySpys.html).toHaveBeenCalled();
                expect(jQuerySpys.show).not.toHaveBeenCalled();
                expect(jQuerySpys.hide).toHaveBeenCalled();
            });
        });

        it('should formatResult for selected filter', function() {
            sinonSandbox.stub(layout, 'getLastFilter', function() { return 'last_filter'; });
            //Template replacement
            view._select2formatResultTemplate = function(val) { return val; };

            expect(view.formatResult({id: 'test', text: 'TEST'}))
                .toEqual({id: 'test', text: 'TEST', icon: undefined});

            expect(view.formatResult({id: 'create', text: 'Create'}))
                .toEqual({id: 'create', text: 'Create', icon: 'icon-plus'});

            expect(view.formatResult({id: 'last_filter', text: 'Last selected filter'}))
                .toEqual({id: 'last_filter', text: 'Last selected filter', icon: 'icon-ok'});
        });

        it('should formatResultCssClass (add css class to visually add borders and separate categories)', function() {
            sinonSandbox.stub(layout, 'getLastFilter', function() { return 'last_filter'; });
            //Template replacement
            view._select2formatResultTemplate = function(val) { return val; };

            expect(view.formatResultCssClass({id: 'test', text: 'TEST'}))
                .toBeUndefined();

            expect(view.formatResultCssClass({id: 'create', text: 'Create'}))
                .toEqual('select2-result-border-bottom');

            expect(view.formatResultCssClass({id: 'test', text: 'TEST', firstUserFilter: true}))
                .toEqual('select2-result-border-top');
        });
    });

    describe('handleClearFilter', function() {
        it('should stop propagation, clear last filter and trigger "filter:reinitialize"', function() {
            view.filterNode = $('');
            var evt = {
                'stopPropagation': sinon.spy()
            };
            var clearLastFilterStub = sinonSandbox.stub(layout, 'clearLastFilter');
            var triggerStub = sinonSandbox.stub(layout, 'trigger');
            view.handleClearFilter(evt);
            expect(evt.stopPropagation).toHaveBeenCalled();
            expect(clearLastFilterStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalledWith('filter:change:filter', 'all_records');
        });
    });
});
