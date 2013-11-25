describe('ForecastWorksheets.Base.Fields.Currency', function() {

    var app;
    var model;

    var moduleName;
    var metadata;

    beforeEach(function() {
        SugarTest.loadPlugin('ClickToEdit');
        SugarTest.loadComponent('base', 'field', 'currency');
        moduleName = 'ForecastWorksheets';
        metadata = {
            fields: {
                "amount": {
                    "name": "amount",
                    "vname": "LBL_AMOUNT",
                    "type": "currency",
                    "dbType": "currency",
                    "comment": "Unconverted amount of the opportunity",
                    "importable": "required",
                    "duplicate_merge": "1",
                    "required": true,
                    "options": "numeric_range_search_dom",
                    "enable_range_search": true,
                    "validation": {
                        "type": "range",
                        "min": 0
                    }
                },
                "currency_id": {
                    "name": "currency_id",
                    "type": "id",
                    "group": "currency_id",
                    "vname": "LBL_CURRENCY",
                    "function": {
                        "name": "getCurrencyDropDown",
                        "returns": "html"
                    },
                    "reportable": false,
                    "comment": "Currency used for display purposes"
                },
                "base_rate": {
                    "name": "base_rate",
                    "vname": "LBL_CURRENCY_RATE",
                    "type": "double",
                    "required": true
                }
            },
            views: [],
            layouts: [],
            _hash: "d7e699e7cf748d05ac311b0165e7591a"
        };

        app = SugarTest.app;

        app.data.declareModel(moduleName, metadata);
        app.user.setPreference('currency_id', '-99');

        model = app.data.createBean(moduleName, {
            amount: 123456789.12,
            currency_id: '-99',
            base_rate: 1
        });
        model.isCopy = function() {
            return (model.isCopied === true);
        };

        sinon.stub(app.metadata, 'getCurrency', function() {
            return {
                'conversion_rate': '0.900'
            }
        });
        sinon.stub(_, 'defer', function() {
        });
    });

    afterEach(function() {
        delete app.plugins.plugins['field']['ClickToEdit'];
        delete app.plugins.plugins['view']['CteTabbing'];
        app.cache.cutAll();
        app.view.reset();
        _.defer.restore();
        Handlebars.templates = {};
        model = null;

        app.metadata.getCurrency.restore();

        moduleName = null;
        metadata = null;
    });

    describe('ClickToEdit Plugin', function() {
        var field;
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'amount',
                'currency',
                'detail',
                {
                    related_fields: ['currency_id', 'base_rate'],
                    currency_field: 'currency_id',
                    base_rate_field: 'base_rate'
                },
                moduleName,
                model,
                undefined,
                true
            );
            field.action = 'detail'
            sinon.stub(field, 'render', function() {
            });
            sinon.stub(field, 'setCurrencyValue', function() {
            });
        });

        afterEach(function() {
            field.render.restore();
            field.setCurrencyValue.restore();
            field = null;
        });


        it('should have ClickToEdit Plugin registered', function() {
            expect(field.plugins).toContain('ClickToEdit');
        });


        describe('ClickToEdit fieldValueChanged', function() {
            var sandbox = sinon.sandbox.create();
            beforeEach(function() {
                field.value = '1.000000';
            });
            afterEach(function() {
                field.value = undefined;
                sandbox.restore();
            });

            it('should return true when adding 1', function() {
                sandbox.stub(field.$el, 'find', function() {
                    return {
                        val: function() {
                            return '+1.000000';
                        }
                    }
                });
                expect(field.fieldValueChanged(field)).toBeTruthy();
            });

            it('should return true when subtracting 1', function() {
                sandbox.stub(field.$el, 'find', function() {
                    return {
                        val: function() {
                            return '-1.000000';
                        }
                    }
                });
                expect(field.fieldValueChanged(field)).toBeTruthy();
            });

            it('should return true when adding a percent', function() {
                sandbox.stub(field.$el, 'find', function() {
                    return {
                        val: function() {
                            return '+1%';
                        }
                    }
                });
                expect(field.fieldValueChanged(field)).toBeTruthy();
            });

            it('should return true when subtracting a percent', function() {
                sandbox.stub(field.$el, 'find', function() {
                    return {
                        val: function() {
                            return '-1%';
                        }
                    }
                });
                expect(field.fieldValueChanged(field)).toBeTruthy();
            });

            it('should return false when values are the same', function() {
                sandbox.stub(field.$el, 'find', function() {
                    return {
                        val: function() {
                            return '1.000000';
                        }
                    }
                });
                expect(field.fieldValueChanged(field)).toBeFalsy();
            });
        });

    });
});
