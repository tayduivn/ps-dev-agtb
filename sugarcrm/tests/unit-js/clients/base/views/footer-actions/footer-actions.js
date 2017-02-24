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
describe('Base.View.FooterActions', function () {
    var view,
        sandbox,
        app = SUGAR.App;

    beforeEach(function () {
        sandbox = sinon.sandbox.create();
        sandbox.stub(app.shortcuts, 'registerGlobal');
        // doWhen needs to be stubed out so it doesn't continue to run
        // and possibly fail
        sandbox.stub(app.utils, 'doWhen');
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('footer-actions', 'view', 'base');
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', 'Contacts', 'footer-actions');

    });

    afterEach(function () {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        sandbox.restore();
    });

    describe('Shortcuts button', function() {
        it('should display if shortcuts are enabled and if user is authenticated', function() {
            sandbox.stub(app.api, 'isAuthenticated', function() {
                return true;
            });
            sandbox.stub(app.shortcuts, 'isEnabled', function() {
                return true;
            });

            view.render();

            expect(view.$('[data-action=shortcuts]').length).not.toBe(0);
        });

        it('should not display if shortcuts are disabled', function() {
            sandbox.stub(app.api, 'isAuthenticated', function() {
                return true;
            });
            sandbox.stub(app.shortcuts, 'isEnabled', function() {
                return false;
            });

            view.render();

            expect(view.$('[data-action=shortcuts]').length).toBe(0);
        });

        it('should not display if user is not authenticated', function() {
            sandbox.stub(app.api, 'isAuthenticated', function() {
                return false;
            });
            sandbox.stub(app.shortcuts, 'isEnabled', function() {
                return true;
            });

            view.render();

            expect(view.$('[data-action=shortcuts]').length).toBe(0);
        });
    });
});
