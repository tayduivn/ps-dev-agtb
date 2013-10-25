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
            view.layout.filters.add(new Backbone.Model({id: 'all_records', name: 'ALL_RECORDS' }));
            view.layout.filters.add(new Backbone.Model({id: 'test_id', name: 'TEST' }));
        });

        it('should return filter list with translated labels', function() {
            sinonSandbox.stub(view.layout, 'canCreateFilter', function() { return false; });
            expected = [{ id: 'all_records', text: app.lang.get('ALL_RECORDS')},
                        { id: 'test_id', text: app.lang.get('TEST')}];
            filterList = view.getFilterList();
            expect(filterList).toEqual(expected);
        });

        it('should return filter list (including create) with translated labels', function() {
            sinonSandbox.stub(view.layout, 'canCreateFilter', function() { return true; });
            expected = [{ id: 'all_records', text: app.lang.get('ALL_RECORDS')},
                        { id: 'test_id', text: app.lang.get('TEST')},
                        { id: 'create', text: app.lang.get('LBL_FILTER_CREATE_NEW')}];
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

        it('should formatSelection for selected module', function() {
            var expected = {label: app.lang.get("LBL_FILTER"), enabled: view.filterDropdownEnabled },
                html;

            //Template replacement
            view._select2formatSelectionTemplate = function(val) { return val; };

            html = view.formatSelection({id: 'test', text: 'TEST'});

            expect(html).toEqual(expected);
        });

        it('should formatResult for selected module', function() {
            var expected = 'TEST',
                html;

            //Template replacement
            view._select2formatResultTemplate = function(val) { return val; };
            html = view.formatResult({id: 'test', text: 'TEST'});

            expect(html).toEqual(expected);
        });
    });
});
