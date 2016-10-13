describe('ProductBundles.Base.Fields.QuoteDataEditablelistbutton', function() {
    var app;
    var field;
    var fieldDef;
    var fieldType = 'quote-data-editablelistbutton';
    var fieldModule = 'ProductBundles';

    beforeEach(function() {
        app = SugarTest.app;
        fieldDef = {
            type: fieldType,
            label: 'testLbl',
            css_class: '',
            buttons: ['button1'],
            no_default_action: true
        };

        field = SugarTest.createField('base', fieldType, fieldType, 'detail',
            fieldDef, fieldModule, null, null, true);

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
            sinon.collection.stub(app.template, 'getField', function() {
                return fieldTemplate;
            });
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
        var lastCallCtxTrigger;
        beforeEach(function() {
            field.model.module = 'TestModule';
            field.model.id = 'testId';

            sinon.collection.stub(field, 'setDisabled', function() {
                return false;
            });

            sinon.collection.stub(field.model, 'revertAttributes', function() {});
            field.view.clearValidationErrors = function() {};
            field.view.toggleRow = function() {};
            field.view.model = new Backbone.Model({
                id: 'viewId1'
            });
            sinon.collection.stub(field.view, 'toggleRow', function() {});
            field.view.layout = {
                trigger: $.noop
            };
            sinon.collection.stub(field.view.layout, 'trigger', function() {});
            field.view.name = 'fieldViewName';
        });

        afterEach(function() {
            lastCall = null;
            field.view.layout.trigger.restore();
            delete field.view.layout.trigger;
            delete field.view.layout;
        });

        describe('when not create or new header row', function() {
            beforeEach(function() {
                field.cancelEdit();
                lastCall = field.view.toggleRow.lastCall;
                lastCallCtxTrigger = field.view.layout.trigger.lastCall;
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

            it('should trigger editablelist:viewName:cancel on view layout', function() {
                expect(lastCallCtxTrigger.args[0]).toBe('editablelist:' + field.view.name + ':cancel');
            });
        });

        describe('when in create or new header row', function() {
            it('should trigger editablelist:viewName:create:cancel on view layout', function() {
                field.view.isCreateView = true;
                field.cancelEdit();
                lastCallCtxTrigger = field.view.layout.trigger.lastCall;

                expect(lastCallCtxTrigger.args[0]).toBe('editablelist:' + field.view.name + ':create:cancel');
            });

            it('should trigger editablelist:viewName:create:cancel on view layout', function() {
                field.view.isCreateView = false;
                sinon.collection.stub(field.model, 'getSynced', function() {
                    return {
                        _justSaved: true
                    };
                });
                field.cancelEdit();
                lastCallCtxTrigger = field.view.layout.trigger.lastCall;

                expect(lastCallCtxTrigger.args[0]).toBe('editablelist:' + field.view.name + ':create:cancel');
            });
        });
    });

    describe('_save()', function() {
        beforeEach(function() {
            field.view.layout = {
                trigger: $.noop
            };
            sinon.collection.stub(field.view.layout, 'trigger', function() {});

            field.view.model = app.data.createBean('ProductBundles');
            sinon.collection.stub(field, '_saveRowModel', function() {});
        });

        afterEach(function() {
            field.view.layout.trigger.restore();
            delete field.view.layout.trigger;
            delete field.view.layout;
        });

        it('should trigger editablelist:viewName:saving on the view.layout', function() {
            field._save();
            expect(field.view.layout.trigger).toHaveBeenCalled();
        });

        it('should call _saveRowModel', function() {
            field._save();
            expect(field._saveRowModel).toHaveBeenCalled();
        });
    });

    describe('_saveRowModel()', function() {
        beforeEach(function() {
            sinon.collection.stub(field.model, 'save', function() {});
            field.getCustomSaveOptions = function() {};
            field.model.id = 'fieldId1';
            field.model.set('id', 'fieldId1');
        });

        it('should unset id if model._notSaved is true', function() {
            field.model.set('_notSaved', true);
            field._saveRowModel();
            expect(field.model.id).toBeUndefined();
        });

        it('should unset id on model if model._notSaved is true', function() {
            field.model.set('_notSaved', true);
            field._saveRowModel();
            expect(field.model.get('id')).toBeUndefined();
        });

        it('should not unset id if model._notSaved is false', function() {
            field._saveRowModel();
            expect(field.model.id).toBe('fieldId1');
            expect(field.model.get('id')).toBe('fieldId1');
        });
    });
});
