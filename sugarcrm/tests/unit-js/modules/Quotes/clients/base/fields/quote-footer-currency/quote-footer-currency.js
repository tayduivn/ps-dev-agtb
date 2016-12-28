describe('Quotes.Base.Fields.QuoteFooterCurrency', function() {
    var app;
    var layout;
    var view;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'record');

        var def = {
            name: 'shipping',
            type: 'quote-footer-currency'
        };

        layout = SugarTest.createLayout('base', 'Quotes', 'record', {});
        view = SugarTest.createView('base', 'Quotes', 'record', null, null, true, layout);
        field = SugarTest.createField({
            name: 'shipping',
            type: 'quote-footer-currency',
            viewName: 'detail',
            fieldDef: def,
            module: 'Quotes',
            model: view.model,
            loadFromModule: true
        });
        sinon.collection.stub(field, '_super');
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        layout.dispose();
        field.dispose();
        view = null;
        layout = null;
        app = null;
    });

    describe('bindDomChange', function() {
        it('should attach _onModelChanged to the change event', function() {
            var el = {
                on: sinon.collection.stub()
            };

            sinon.collection.stub(field.$el, 'find', function() {
                return el;
            });

            field.bindDomChange();

            expect(el.on).toHaveBeenCalledWith('change');
        });
    });

    describe('_onModelChanged', function() {
        it('should set the value from the event and try to validate', function() {
            var evt = {
                currentTarget: {
                    value: 'foo'
                }
            };

            sinon.collection.stub(field.model, 'set', function() {});
            sinon.collection.stub(field.model, 'doValidate', function() {});

            field._onModelChanged(evt);

            expect(field.model.set).toHaveBeenCalledWith(field.name, evt.currentTarget.value);
            expect(field.model.doValidate).toHaveBeenCalledWith(field.name);
        });
    });

    describe('_validationComplete', function() {
        beforeEach(function() {
            sinon.collection.stub(app.alert, 'dismiss', function() {});
            sinon.collection.stub(field.model, 'save', function() {});
            sinon.collection.stub(field.context, 'isCreate', function() {
                return false;
            });
        });

        it('should dismiss the error alert if valid', function() {
            field._validationComplete(true);
            expect(app.alert.dismiss).toHaveBeenCalledWith('invalid-data');
        });

        it('should call model.save if not in create mode', function() {
            field._validationComplete(true);
            expect(field.model.save).toHaveBeenCalled();
        });
    });

    describe('_doValidateIsNumeric', function() {
        var callback = sinon.collection.stub();
        var errors = [];

        beforeEach(function() {
            sinon.collection.stub(app.lang, 'get', function() {
                return 'foo';
            });
        });

        it('should call the callback without errors ', function() {
            sinon.collection.stub(field.model, 'get', function() {
                return 1;
            });

            field._doValidateIsNumeric([], [], callback);
            expect(callback).toHaveBeenCalledWith(null, [], errors);
        });

        it('should call the callback without errors ', function() {
            sinon.collection.stub(field.model, 'get', function() {
                return 'foo';
            });

            errors[field.name] = 'foo';
            field._doValidateIsNumeric([], [], callback);
            expect(callback).toHaveBeenCalledWith(null, [], errors);
        });
    });

    describe('format', function() {
        it('should call app.utils.formatNumberLocale ', function() {
            sinon.collection.stub(app.utils, 'formatNumberLocale', function() {

            });

            field.format(42);
            expect(app.utils.formatNumberLocale).toHaveBeenCalledWith(42);
        });
    });
});
