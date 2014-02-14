describe('Filter Actions View', function() {

    var view, app, parentLayout;

    beforeEach(function() {
        parentLayout = new Backbone.View();
        view = SugarTest.createView('base', 'Accounts', 'filter-actions', {}, false, false, parentLayout);
        view.layout = parentLayout;
        view.initialize(view.options);
        app = SUGAR.App;
    });

    afterEach(function() {
        sinon.collection.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
    });

    it('should call set filter name on filter:create:open', function() {
        var name = 'test';
        view.model.set({'name': name});
        var viewSetFilterStub = sinon.collection.stub(view, 'setFilterName');
        parentLayout.trigger('filter:create:open', view.model);
        expect(viewSetFilterStub).toHaveBeenCalled();
        expect(viewSetFilterStub.getCall(0).args).toEqual([name]);
    });

    it('should call toggleSave on filter:toggle:savestate', function() {
        var stub = sinon.collection.stub(view, 'toggleSave');
        view.initialize(view.options);
        parentLayout.trigger('filter:toggle:savestate');
        expect(stub).toHaveBeenCalled();
    });

    it('should call setFilterName on filter:set:name', function() {
        var stub = sinon.collection.stub(view, 'setFilterName');
        view.initialize(view.options);
        parentLayout.trigger('filter:set:name');
        expect(stub).toHaveBeenCalled();
    });

    it('should trigger save', function() {
        var spy = sinon.spy();
        parentLayout.off();
        parentLayout.on('filter:create:save', spy);
        view.triggerSave();
        expect(spy).toHaveBeenCalled();
    });

    it('should trigger delete', function() {
        var spy = sinon.spy();
        parentLayout.off();
        parentLayout.on('filter:create:delete', spy);
        view.triggerDelete();
        expect(spy).toHaveBeenCalled();
    });

    describe('filterNameChanged', function() {
        var layoutTriggerStub, component, saveFilterEditStateStub;

        beforeEach(function() {
            layoutTriggerStub = sinon.collection.stub(view.layout, 'trigger');
            component = {
                saveFilterEditState: $.noop
            };
            view.layout.getComponent = function() {
                return component;
            };
            saveFilterEditStateStub = sinon.collection.stub(component, 'saveFilterEditState');
        });

        it('should trigger validate', function() {
            view.filterNameChanged();
            expect(layoutTriggerStub).toHaveBeenCalled();
            expect(layoutTriggerStub).toHaveBeenCalledWith('filter:toggle:savestate');
        });

        it('should save edit state when filter definition is valid', function() {
            view.filterNameChanged();
            expect(saveFilterEditStateStub).toHaveBeenCalled();
        });
    });

    describe('triggerClose', function() {
        var component, filterLayoutTriggerStub, layoutTriggerStub;

        beforeEach(function() {
            component = {
                trigger: $.noop,
                buildFilterDef: function() {
                    return [
                        {$favorite: ''}
                    ];
                }
            };
            view.layout.getComponent = function() {
                return component;
            };
            filterLayoutTriggerStub = sinon.collection.stub(component, 'trigger');
            layoutTriggerStub = sinon.collection.stub(view.layout, 'trigger');
        });

        it('should trigger "filter:create:close" on the filter layout', function() {
            view.layout.getComponent = function() {
                return component;
            };
            view.layout.editingFilter = new Backbone.Model({id: 'my_filter'});
            view.triggerClose();
            expect(filterLayoutTriggerStub).toHaveBeenCalled();
            expect(filterLayoutTriggerStub).toHaveBeenCalledWith('filter:create:close', true, 'my_filter');
        });

        it('should trigger "filter:apply" on the filter layout to cancel changes in filter definition', function() {
            view.layout.editingFilter = new Backbone.Model({id: 'my_filter', filter_definition: [
                {$owner: ''}
            ]});
            view.triggerClose();
            expect(layoutTriggerStub).toHaveBeenCalled();
            expect(layoutTriggerStub).toHaveBeenCalledWith('filter:apply', null, [
                {$owner: ''}
            ]);
            expect(filterLayoutTriggerStub).toHaveBeenCalled();
            expect(filterLayoutTriggerStub).toHaveBeenCalledWith('filter:create:close', true, 'my_filter');
        });
    });
});
