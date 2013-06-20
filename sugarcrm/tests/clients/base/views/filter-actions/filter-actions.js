describe("Filter Actions View", function () {

    var view, app, parentLayout;

    beforeEach(function () {
        parentLayout = new Backbone.View();
        view = SugarTest.createView("base", "Accounts", "filter-actions",{},false,false,parentLayout);
        view.layout = parentLayout;
        view.initialize(view.options);
        app = SUGAR.App;
    });

    afterEach(function () {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    it('should call set filter name on filter:create:open', function () {
        var name = 'test';
        view.model.set({'name': name});
        var viewSetFilterStub = sinon.stub(view, 'setFilterName');
        parentLayout.trigger('filter:create:open', view.model);
        expect(viewSetFilterStub).toHaveBeenCalled();
        expect(viewSetFilterStub.getCall(0).args).toEqual([name]);
    });
    it('should call toggleRowState on filter:create:rowsValid', function() {
       var stub = sinon.stub(view,'toggleRowState', function(){});
        view.initialize(view.options);
       parentLayout.trigger('filter:create:rowsValid');
       expect(stub).toHaveBeenCalled();
    });
    it('should call setFilterName on filter:set:name', function() {
        var stub = sinon.stub(view,'setFilterName', function(){});
        view.initialize(view.options);
        parentLayout.trigger('filter:set:name');
        expect(stub).toHaveBeenCalled();
    });
    it('should trigger save', function(){
       var spy = sinon.spy();
        parentLayout.off();
        parentLayout.on('filter:create:save', spy);
        view.triggerSave();
        expect(spy).toHaveBeenCalled();
    });
    it('should trigger delete', function(){
        var spy = sinon.spy();
        parentLayout.off();
        parentLayout.on('filter:create:delete', spy);
        view.triggerDelete();
        expect(spy).toHaveBeenCalled();
    });

});
