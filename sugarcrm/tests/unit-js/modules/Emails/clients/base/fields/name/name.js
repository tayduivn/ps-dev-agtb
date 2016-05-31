describe('Emails.Field.Name', function() {
    var app;
    var field;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;

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
    });

    describe('initialize()', function() {
        var attachments;
        beforeEach(function() {
            attachments = {
                records: []
            };
        });

        afterEach(function() {
            attachments = null;
        });

        it('should set hasAttachments to true if attachments exist', function() {
            attachments.records.push('recordId1');
            field.initialize({
                model: new Backbone.Model({
                    attachments: attachments
                })
            });

            expect(field.hasAttachments).toBeTruthy();
        });

        it('should set hasAttachments to false if attachments do not exist', function() {
            field.initialize({
                model: new Backbone.Model({
                    attachments: attachments
                })
            });
            expect(field.hasAttachments).toBeFalsy();
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
