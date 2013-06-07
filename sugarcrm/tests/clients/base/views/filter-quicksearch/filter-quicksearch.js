describe("Filter Quick Search View", function () {

    var view, app, parentLayout;

    beforeEach(function () {
        parentLayout = new Backbone.View();
        view = SugarTest.createView("base", "Accounts", "filter-quicksearch", {}, false, false, parentLayout);
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

    it('should call clear input on filter:clear:quicksearch', function () {
        var stub = sinon.stub(view, 'clearInput', function () {
        });
        view.initialize(view.options);
        parentLayout.trigger('filter:clear:quicksearch');
        expect(stub).toHaveBeenCalled();
    });
    it('should trigger quick search change on throttle search', function () {
        var spy = sinon.spy();
        parentLayout.off();
        parentLayout.on('filter:change:quicksearch', spy);
        view.throttledSearch();
        expect(spy).toHaveBeenCalled();
    });
    it('should trigger filter:change:quicksearch on clearInput', function(){
        var spy = sinon.spy();
        parentLayout.off();
        parentLayout.on('filter:change:quicksearch', spy);
        view.clearInput();
        expect(spy).toHaveBeenCalled();
    });

});
