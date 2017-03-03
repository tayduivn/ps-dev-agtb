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
describe('Emails.Field.Name', function() {
    var app;
    var field;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('name', 'field', 'base', 'list', 'Emails');
        SugarTest.testMetadata.set();

        field = SugarTest.createField('base', 'name', 'name', 'list', {}, 'Emails', null, null, true);
        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete field.model;
        field = null;
        SugarTest.testMetadata.dispose();
        sandbox.restore();
        Handlebars.templates = {};
    });

    describe('render', function() {
        it('should show a paperclip if attachments exist', function() {
            field.model.set('total_attachments', 3);
            field.render();

            expect(field.$('.email-has-attachments .fa-paperclip').length).toBe(1);
        });

        it('should not show a paperclip if attachments do not exist', function() {
            field.model.set('total_attachments', 0);
            field.render();

            expect(field.$('.email-has-attachments .fa-paperclip').length).toBe(0);
        });
    });

    describe('format()', function() {
        var result;

        it('should return with no subject if value is empty', function() {
            sandbox.stub(app.lang, 'get', function() { return 'LBL_NO_SUBJECT'; });
            result = field.format('');

            expect(result).toBe('LBL_NO_SUBJECT');
        });

        it('should return the value if value is not empty', function() {
            result = field.format('testValue');

            expect(result).toBe('testValue');
        });
    });

    describe('buildHref()', function() {
        using('different email states and email client settings',
            [
                ['Draft', true, 'Emails/drafts'],
                ['Draft', false, 'Emails'],
                ['Archived', true, 'Emails'],
                ['Archived', false, 'Emails']
            ],
            function(state, useSugarClient, expected) {
                it('should return the correct href', function() {
                    var oldAppRouter = app.router;

                    field.model.set('state', state);
                    sandbox
                        .stub(field, '_useSugarEmailClient')
                        .returns(useSugarClient);

                    app.router = {
                        buildRoute: sandbox.stub()
                    };

                    field.buildHref();
                    expect(app.router.buildRoute).toHaveBeenCalledWith(expected);
                    app.router = oldAppRouter;
                });
            }
        );
    });
});
