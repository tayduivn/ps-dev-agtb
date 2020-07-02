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
describe('Base.View.OmnichannelButton', function() {
    var view;
    var sandbox;
    var app = SUGAR.App;

    beforeEach(function() {
        sandbox = sinon.sandbox.create();
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('omnichannel-button', 'view', 'base');
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', 'Contacts', 'omnichannel-button');
        app.config.awsConnectInstanceName = 'instance1';
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        sandbox.restore();
    });

    describe('_renderHTML()', function() {
        it('should display if user has serve license', function() {
            sandbox.stub(app.api, 'isAuthenticated', function() {
                return true;
            });
            sandbox.stub(app.user, 'get', function() {
                return ['SUGAR_SERVE'];
            });

            view.render();

            expect(view.$('[data-action=omnichannel]').length).not.toBe(0);
        });

        it('should not display if user has no serve license', function() {
            sandbox.stub(app.api, 'isAuthenticated', function() {
                return true;
            });
            sandbox.stub(app.user, 'get', function() {
                return ['SUGAR_SELL'];
            });

            view.render();

            expect(view.$('[data-action=omnichannel]').length).toBe(0);
        });

        it('should not display if user is not authenticated', function() {
            sandbox.stub(app.api, 'isAuthenticated', function() {
                return false;
            });

            view.render();

            expect(view.$('[data-action=omnichannel]').length).toBe(0);
        });

        it('should not display if aws is not configured', function() {
            sandbox.stub(app.api, 'isAuthenticated', function() {
                return true;
            });
            sandbox.stub(app.user, 'get', function() {
                return ['SUGAR_SERVE'];
            });
            app.config.awsConnectInstanceName = '';
            view.render();

            expect(view.$('[data-action=omnichannel]').length).toBe(0);
        });
    });

    describe('setStatus()', function() {
        beforeEach(function() {
            sandbox.stub(app.api, 'isAuthenticated', function() {
                return true;
            });
            sandbox.stub(app.user, 'get', function() {
                return ['SUGAR_SERVE'];
            });
            view.render();
        });

        it('should change status to active-session', function() {
            view.setStatus('active-session');
            expect(view.$('.btn').attr('class')).toBe('btn active-session');
            expect(view.status).toBe('active-session');
        });

        it('should change status to logged-out', function() {
            view.setStatus('logged-out');
            expect(view.$('.btn').attr('class')).toBe('btn logged-out');
            expect(view.status).toBe('logged-out');
        });

        it('should change status to logged-in', function() {
            view.setStatus('logged-in');
            expect(view.$('.btn').attr('class')).toBe('btn logged-in');
            expect(view.status).toBe('logged-in');
        });
    });
});
