describe('Products.Base.Fields.QuoteDataEditablelistbutton', function() {
    var app;
    var field;
    var fieldDef;

    beforeEach(function() {
        app = SugarTest.app;
        fieldDef = {
            type: 'quote-data-editablelistbutton',
            label: 'testLbl',
            css_class: '',
            buttons: ['button1'],
            no_default_action: true
        };
        field = SugarTest.createField('base', 'quote-data-editablelistbutton', 'quote-data-editablelistbutton',
            'detail', fieldDef, 'ProductBundleNotes', null, null, true);

        sinon.collection.stub(field, '_super', function() {});
    });

    afterEach(function() {
        sinon.collection.restore();
        field.dispose();
        field = null;
    });

    describe('_render()', function() {
        var addClassStub;
        var removeClassStub;

        beforeEach(function() {
            addClassStub = sinon.collection.stub();
            removeClassStub = sinon.collection.stub();
            sinon.collection.stub(field, '$el', {
                closest: function() {
                    return {
                        addClass: addClassStub,
                        removeClass: removeClassStub
                    };
                },
                find: function() {}
            });
        });

        it('should add class higher if tplName is edit', function() {
            field.tplName = 'edit';
            field._render();
            expect(addClassStub).toHaveBeenCalled();
        });

        it('should remove class higher if tplName is not edit', function() {
            field.tplName = 'not-edit';
            field._render();
            expect(removeClassStub).toHaveBeenCalled();
        });
    });

    describe('_loadTemplate()', function() {
        var fieldTemplate;
        beforeEach(function() {
            fieldTemplate = function fieldTemplate() {};
            sinon.collection.stub(app.view.Field.prototype._loadTemplate, 'call', function(ctx) {
                ctx.template = fieldTemplate;
            });
        });

        it('should call Field.prototype._loadTemplate not super', function() {
            field.view.action = 'list';
            field.action = 'edit';
            field._loadTemplate();
            expect(app.view.Field.prototype._loadTemplate.call).toHaveBeenCalled();
        });

        it('should set template to empty when in edit mode', function() {
            field.view.action = 'list';
            field.action = 'edit';
            field._loadTemplate();
            expect(field.template).toBe(fieldTemplate);
        });

        it('should set template to empty if detail', function() {
            field.view.action = 'list';
            field.action = 'detail';
            field._loadTemplate();
            expect(field.template).toBe(app.template.empty);
        });
    });

    describe('cancelEdit()', function() {
        var lastCall;
        beforeEach(function() {
            field.model.module = 'TestModule';
            field.model.id = 'testId';

            sinon.collection.stub(field, 'setDisabled', function() {
                return false;
            });

            sinon.collection.stub(field.model, 'revertAttributes', function() {});
            field.view.clearValidationErrors = function() {};
            field.view.toggleRow = function() {};
            sinon.collection.stub(field.view, 'toggleRow', function() {});

            field.cancelEdit();
            lastCall = field.view.toggleRow.lastCall;
        });

        afterEach(function() {
            lastCall = null;
        });

        it('should call toggleRow with three params', function() {
            expect(lastCall.args.length).toBe(3);
        });

        it('should call toggleRow with first param module name', function() {
            expect(lastCall.args[0]).toBe('TestModule');
        });

        it('should call toggleRow with second param model id', function() {
            expect(lastCall.args[1]).toBe('testId');
        });

        it('should call toggleRow with third param false', function() {
            expect(lastCall.args[2]).toBeFalsy();
        });
    });
});
