describe("Profile Actions", function() {

    var app, view, sinonSandbox, menuMeta;
    beforeEach(function() {
        app = SugarTest.app;
        var context = app.context.getContext();
        view = SugarTest.createView("base","Accounts", "profileactions", null, context);
        sinonSandbox = sinon.sandbox.create();
        menuMeta = [{
            acl_action: 'admin',
            label: 'LBL_ADMIN'
        }];
    });
    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        sinonSandbox.restore();
        Handlebars.templates = {};
        view = null;
        menuMeta = null;
    });

    it("should show admin link when acl of admin and developer", function() {
        var stubAdminAndDev = sinonSandbox.stub(app.acl, 'hasAccessToAny', function(a) {
            if (a === 'admin' || a === 'developer') {
                return true;
            } else {
                return false;
            }
        });
        var result = view.filterAvailableMenu(menuMeta);
        expect(stubAdminAndDev).toHaveBeenCalled();
        expect(result.length).toEqual(1);
    });
    it("should show admin link when acl of developer", function() {
        var stubDev = sinonSandbox.stub(app.acl, 'hasAccessToAny', function(a) {
            if (a === 'developer') {
                return true;
            } else {
                return false;
            }
        });
        var result = view.filterAvailableMenu(menuMeta);
        expect(stubDev).toHaveBeenCalled();
        expect(result.length).toEqual(1);
    });
    it("should NOT show admin link when acl is NOT of admin or developer", function() {
        var notAdminOrDev = sinonSandbox.stub(app.acl, 'hasAccessToAny', function(a) {
            return false;
        });
        var result = view.filterAvailableMenu(menuMeta);
        expect(notAdminOrDev).toHaveBeenCalled();
        expect(result.length).toEqual(0);
    });
});
