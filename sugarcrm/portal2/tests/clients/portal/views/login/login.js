//FILE SUGARCRM flav=ent ONLY
describe('PortalLoginView', function() {
    var app;
    var view;
    var viewName = 'login';

    beforeEach(function() {
        //Load base components before portal components
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('portal', 'view', viewName);

        app = SUGAR.App;
        view = SugarTest.createView('portal', '', viewName);
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        view = null;
        sinon.collection.restore();
    });

    describe('signup', function() {
        it('should properly route to sign up page', function() {
            app.router = app.router || {navigate: _.noop};
            sinon.collection.stub(app.router, 'navigate');
            view.signup();
            expect(app.router.navigate).toHaveBeenCalledWith('#signup', {trigger: true});
        });
    });
});
