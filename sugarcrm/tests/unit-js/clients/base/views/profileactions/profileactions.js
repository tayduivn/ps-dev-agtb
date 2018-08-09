/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
describe("Profile Actions", function() {

    var app, view, sinonSandbox, menuMeta;
    beforeEach(function() {
        var context;
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('profileactions', 'view', 'base');
        SugarTest.testMetadata.set();
        context = app.context.getContext();
        view = SugarTest.createView("base","Accounts", "profileactions", null, context);
        sinonSandbox = sinon.sandbox.create();
        menuMeta = [{
            acl_action: 'admin',
            label: 'LBL_ADMIN'
        },{
            acl_action: 'not_admin',
            acl_module: 'Accounts'
        }];
    });
    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        sinonSandbox.restore();
        Handlebars.templates = {};
        view.dispose();
        view = null;
        menuMeta = null;
    });

    it('should show admin link together with normal link when user is an admin', function() {
        var stubAdmin = sinonSandbox.stub(app.acl, 'hasAccess', function(action, module) {
            if (action === 'admin' && module === 'Administration') {
                return true;
            }
            if (module === 'Accounts') {
                return true;
            }
            return false;
        });
        var result = view.filterAvailableMenu(menuMeta);
        expect(stubAdmin).toHaveBeenCalled();
        expect(result.length).toEqual(2);
    });

    it('should show admin link together with normal link when user is a developer', function() {
        var stubDev = sinonSandbox.stub(app.acl, 'hasAccessToAny', function(action) {
            return action === 'developer';
        });
        var result = view.filterAvailableMenu(menuMeta);
        expect(stubDev).toHaveBeenCalled();
        expect(result.length).toEqual(2);
    });

    it('should NOT show admin link when user is NOT an admin or a developer', function() {
        var notDev = sinonSandbox.stub(app.acl, 'hasAccessToAny', function(action) {
            return false;
        });
        var notAdmin = sinonSandbox.stub(app.acl, 'hasAccess', function(action, module) {
            return action === 'admin';
        });
        var result = view.filterAvailableMenu(menuMeta);
        expect(notDev).toHaveBeenCalled();
        expect(notAdmin).toHaveBeenCalled();
        expect(result.length).toEqual(1);
    });
});
