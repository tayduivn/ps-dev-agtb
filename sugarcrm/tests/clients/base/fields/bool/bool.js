describe('Base.Field.Bool', function() {
    var app;
    beforeEach(function() {
        app = SugarTest.app;
    });

    afterEach(function() {
        app.cache.cutAll();
        sinon.collection.restore();
        Handlebars.Templates = {};
    });

    describe('format & unformat', function() {
        var field;
        beforeEach(function() {
            field = SugarTest.createField('base', 'bool', 'bool', 'detail');
        });

        afterEach(function() {
            field.dispose();
        });

        using('valid values',
            [['0', false], ['1', true], [false, false], [true, true]],
            function(value, result) {
                it('should format the value', function() {
                    expect(field.format(value)).toEqual(result);
                });
            });

        using('valid values',
            [['0', false], ['1', true], [false, false], [true, true]],
            function(value, result) {
                it('should unformat the value', function() {
                    expect(field.unformat(value)).toEqual(result);
                });
        });
    });

    describe('bindDomChange', function() {
        var field;
        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.loadHandlebarsTemplate('bool', 'field', 'base', 'edit');
            SugarTest.testMetadata.set();
            field = SugarTest.createField('base', 'bool', 'bool', 'edit');
        });

        afterEach(function() {
            field.dispose();
            SugarTest.testMetadata.dispose();
        });

        it('should update the model on value change', function() {
            field.render();
            var modelSpy = sinon.collection.spy(field.model, 'set');
            field.$(field.fieldTag).attr('checked', true).trigger('change');
            expect(modelSpy).toHaveBeenCalledWith('bool', true);
            field.$(field.fieldTag).attr('checked', false).trigger('change');
            expect(modelSpy).toHaveBeenCalledWith('bool', false);
        });
    });

    describe('render', function() {
        describe('render detail', function() {
            var field;
            beforeEach(function() {
                SugarTest.testMetadata.init();
                SugarTest.loadHandlebarsTemplate('bool', 'field', 'base', 'detail');
                SugarTest.testMetadata.set();
                field = SugarTest.createField('base', 'bool', 'bool', 'detail');
            });

            afterEach(function() {
                field.dispose();
                SugarTest.testMetadata.dispose();
            });

            it('should render as a disabled checkbox and toggle according to the value', function() {
                field.def.default = false;
                field.render();
                expect(field.$(field.fieldTag)).toHaveAttr('disabled');
                expect(field.$(field.fieldTag)).not.toHaveAttr('checked');
                field.model.set(field.name, true);
                expect(field.$(field.fieldTag)).toHaveAttr('checked');
            });
        });

        describe('render edit', function() {
            var field;
            beforeEach(function() {
                SugarTest.testMetadata.init();
                SugarTest.loadHandlebarsTemplate('bool', 'field', 'base', 'edit');
                SugarTest.testMetadata.set();
                field = SugarTest.createField('base', 'bool', 'bool', 'edit');
            });

            afterEach(function() {
                field.dispose();
                SugarTest.testMetadata.dispose();
            });

            it('should render with default values in def', function() {
                field.def.text = 'text';
                field.def.tabindex = 1;
                field.render();
                expect(field.$('label')).toExist();
                expect(field.$(field.fieldTag)).toHaveAttr('tabindex');
            });

            it('should render with no default values in def', function() {
                field.render();
                expect(field.$('label')).not.toExist();
            });

        });

        describe('render dropdown', function() {
            var field;
            beforeEach(function() {
                SugarTest.testMetadata.init();
                SugarTest.loadHandlebarsTemplate('bool', 'field', 'base', 'dropdown');
                SugarTest.testMetadata.set();
                field = SugarTest.createField('base', 'bool', 'bool', 'massupdate');
            });

            afterEach(function() {
                field.dispose();
                SugarTest.testMetadata.dispose();
            });

            it('should call select2 with required params on massupdate', function() {
                var jqueryStubReturnObj = {
                        'select2': sinon.collection.spy(),
                        'on': $.noop,
                        'off': $.noop
                    },
                    jqueryStub = sinon.collection.stub(field, '$', function(val) {
                        return jqueryStubReturnObj;
                    });
                field.render();
                expect(jqueryStub).toHaveBeenCalledWith(field.select2fieldTag);
                expect(jqueryStubReturnObj.select2).toHaveBeenCalledWith({'minimumResultsForSearch': -1});
            });

            it('should fall back to the dropdown template if attempting to render the massupdate template', function() {
                var getFieldSpy = sinon.collection.spy(app.template, 'getField');
                field.render();
                expect(getFieldSpy).toHaveBeenCalledWith('bool', 'massupdate', undefined, 'dropdown');
            });
        });

        describe('render disabled', function() {
            var field;
            beforeEach(function() {
                SugarTest.testMetadata.init();
                SugarTest.loadHandlebarsTemplate('bool', 'field', 'base', 'edit');
                SugarTest.testMetadata.set();
                field = SugarTest.createField('base', 'bool', 'bool', 'disabled');
            });

            afterEach(function() {
                field.dispose();
                SugarTest.testMetadata.dispose();
            });

            it('should render disabled when the action is disabled', function() {
                field.render();
                expect(field.action).toEqual('disabled');
                expect(field.$(field.fieldTag)).toHaveAttr('disabled');
            });
        });
    });
});
