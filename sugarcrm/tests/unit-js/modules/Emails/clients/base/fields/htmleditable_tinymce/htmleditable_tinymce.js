describe('Emails.Field.Htmleditable_tinymce', function() {
    var field;
    var sandbox;
    var tinymce;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'htmleditable_tinymce');
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
    });

    describe('readonly view for preview pane', function() {
        beforeEach(function() {
            var $textarea = $('<iframe class="htmleditable" frameborder="0"></iframe>');
            sandbox = sinon.sandbox.create();
            field = SugarTest.createField(
                'base',
                'html_email',
                'htmleditable_tinymce',
                'preview',
                {},
                'Emails',
                null,
                null,
                true
            );
            sandbox.stub(field, '_getHtmlEditableField', function() {
                return $textarea;
            });
            sandbox.stub(field, 'destroyTinyMCEEditor', $.noop());
        });

        afterEach(function() {
            field.dispose();
            sandbox.restore();
        });

        it('should set iframe height when contentHeight is set', function() {
            var editor = field._getHtmlEditableField();
            var cssHeight;

            // Content height is padded to 25px more than it is set to allow for scrollbar padding
            sandbox.stub(field, '_getContentHeight', function() {
                return 200;
            });

            field.render();

            cssHeight = editor.css('height');
            expect(cssHeight).toEqual('225px');
        });

        it('should set iframe to max height when contentHeight is greater than 400', function() {
            var editor = field._getHtmlEditableField();
            var cssHeight;

            sandbox.stub(field, '_getContentHeight', function() {
                return 550;
            });

            field.render();

            cssHeight = editor.css('height');
            expect(cssHeight).toEqual('400px');
        });

        it('should not set iframe height when template is not preview', function() {
            var cssHeight;
            var newField = SugarTest.createField(
                'base',
                'html_email',
                'htmleditable_tinymce',
                'detail',
                {},
                'Emails',
                null,
                null,
                true
            );
            var editor;

            sandbox.stub(newField, '_getHtmlEditableField', function() {
                return $('<iframe class="htmleditable" frameborder="0"></iframe>');
            });
            sandbox.stub(newField, 'destroyTinyMCEEditor', $.noop());

            editor = newField._getHtmlEditableField();

            newField.render();

            cssHeight = editor.css('height');
            expect(cssHeight).toEqual('0px');

            newField.dispose();
        });
    });
});
