describe("BaseFilterRowsView", function() {
    var view, layout, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadComponent('base', 'view', 'filter-rows');
        SugarTest.testMetadata.set();
        layout = SugarTest.createLayout('base', "Cases", "filter", {}, null, null, { layout: new Backbone.View() });
        view = SugarTest.createView("base", "Cases", "filter-rows", null, null, null, layout);
        view.layout = layout;
        app = SUGAR.App;
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe('handleFilterChange', function() {
        it('should return undefined if there is no module metadata', function() {
            var metadataStub = sinon.stub(app.metadata, 'getModule', function() {
                return;
            });
            view.handleFilterChange('test');
            expect(view.fieldList).toBeUndefined();
            metadataStub.restore();
        });
    });

    describe('openForm', function() {
        var renderStub, addRowStub, populateFilterStub,
            filterModel;
        beforeEach(function() {
            renderStub = sinon.stub(view, 'render');
            addRowStub = sinon.stub(view, 'addRow');
            populateFilterStub = sinon.stub(view, 'populateFilter');
            filterModel = new Backbone.Model();
        });
        afterEach(function() {
            renderStub.restore();
            addRowStub.restore();
            populateFilterStub.restore();
        });
        it('should render the view and add a row', function() {
            view.openForm(filterModel);
            expect(renderStub).toHaveBeenCalled();
            expect(addRowStub).toHaveBeenCalled();
            expect(populateFilterStub).not.toHaveBeenCalled();
        });
        it('should populate filter', function() {
            filterModel.set('filter_definition', { /* ... */ });
            view.openForm(filterModel);
            expect(renderStub).not.toHaveBeenCalled();
            expect(addRowStub).not.toHaveBeenCalled();
            expect(populateFilterStub).toHaveBeenCalled();
        });
    });

    describe('saveFilter', function() {
        it('should trigger events', function() {
            var triggerStub = sinon.stub(view.layout, 'trigger');
            view.layout.editingFilter = new Backbone.Model();
            var syncStub = sinon.stub(view.layout.editingFilter, 'sync', function(method, model, options) {
                if (options.success) options.success(model, {}, options);
            });
            view.saveFilter();
            expect(triggerStub).toHaveBeenCalledWith('filter:add', view.layout.editingFilter);
            expect(triggerStub).toHaveBeenCalledWith('filter:create:rowsValid', false);
            expect(triggerStub).toHaveBeenCalledWith('filter:create:close');
            syncStub.restore();
            triggerStub.restore();
        });
    });

    describe('deleteFilter', function() {
        it('should trigger events', function() {
            var triggerStub = sinon.stub(view.layout, 'trigger');
            view.layout.editingFilter = new Backbone.Model();
            view.deleteFilter();
            expect(triggerStub).toHaveBeenCalledWith('filter:remove', view.layout.editingFilter);
            expect(triggerStub).toHaveBeenCalledWith('filter:create:close');
            triggerStub.restore();
        });
    });

    describe('getFilterableFields', function() {
        it('should return the list of filterable fields with fields definition', function() {
            var metadataStub = sinon.stub(app.metadata, 'getModule', function() {
                var moduleMeta = {
                    fields: {
                        name: {
                            name: 'name',
                            type: 'varchar',
                            len: 100
                        },
                        date_modified: {
                            name: 'date_modified',
                            options: 'date_range_search_dom',
                            type: 'datetime',
                            vname: 'LBL_DATE_MODIFIED'
                        },
                        number: {
                            name: 'number',
                            type: 'varchar',
                            len: 100,
                            readonly: true
                        }
                    },
                    filters: {
                        'default': {
                            meta: {
                                default_filter: 'all_records',
                                fields: {
                                    account_name_related: {
                                        dbFields: ['accounts.name'],
                                        type: 'text',
                                        vname: 'LBL_ACCOUNT_NAME'
                                    },
                                    date_modified: {},
                                    number: {}
                                },
                                filters: [
                                    {
                                        'id': 'test_filter',
                                        'name': 'Test Filter',
                                        'filter_definition': {
                                            '$starts': 'Test'
                                        }
                                    }
                                ]
                            }
                        }
                    }
                };
                return moduleMeta;
            });
            var fields = view.getFilterableFields('Cases');
            var expected = {
                account_name_related: {
                    name: 'account_name_related',
                    dbFields: ['accounts.name'],
                    type: 'text',
                    vname: 'LBL_ACCOUNT_NAME'
                },
                date_modified: {
                    name: 'date_modified',
                    options: 'date_range_search_dom',
                    type: 'datetime',
                    vname: 'LBL_DATE_MODIFIED'
                },
                number: {
                    name: 'number',
                    type: 'varchar',
                    len: 100
                }
            };
            expect(fields).toEqual(expected);
            expect(fields.number['readonly']).not.toBe(true);
            metadataStub.restore();
        });
    });

    describe('createField', function() {
        it('should instanciate a field', function() {
            var def = { type: 'enum', options: { 'test': '' } };
            var field = view.createField(new Backbone.Model(), def);
            expect(field instanceof app.view.Field).toBeTruthy();
            expect(field.type).toEqual('enum');
            expect(field.def).toEqual(def);
        });
    });

    describe('addRow', function() {
        it('should add the row to the view with an enum field', function() {
            var createFieldSpy = sinon.spy(view, 'createField');
            view.formRowTemplate = function() {
                return '<div>';
            };
            view.filterFields = ['test_field'];
            var $row = view.addRow();
            expect($row.data('nameField')).toBeDefined();
            expect($row.data('nameField').type).toEqual('enum');
            expect($row.data('nameField').def.options).toEqual(['test_field']);
            createFieldSpy.restore();
        });
    });

    describe('removeRow', function() {
        var $event, addRowStub;
        beforeEach(function() {
            $event = $('<div>');
            addRowStub = sinon.stub(view, 'addRow', function() {
                $('<article>').addClass('filter-body').appendTo(view.$el);
            });
            $('<article>').addClass('filter-body').appendTo(view.$el);
            $('<article>').addClass('filter-body').appendTo(view.$el);
            $('<article>').addClass('filter-body').appendTo(view.$el);
        });
        afterEach(function() {
            addRowStub.restore();
        });
        it('should remove the row from the view', function() {
            $event.appendTo(view.$('article.filter-body:last'));
            view.removeRow({currentTarget: $event});
            expect(_.size(view.$('article.filter-body'))).toEqual(2);

            $event.appendTo(view.$('article.filter-body:last'));
            view.removeRow({currentTarget: $event});
            expect(_.size(view.$('article.filter-body'))).toEqual(1);

            //it should add another row when the form becomes empty
            $event.appendTo(view.$('article.filter-body:last'));
            view.removeRow({currentTarget: $event});
            expect(_.size(view.$('article.filter-body'))).toEqual(1);
        });
        it('should dispose fields', function() {
            var disposeStub = sinon.stub(view, '_disposeFields');
            view.removeRow({currentTarget: $event});
            expect(disposeStub).toHaveBeenCalled();
            expect(disposeStub.lastCall.args[1]).toEqual([
                {'field': 'nameField', 'value': 'name'},
                {'field': 'operatorField', 'value': 'operator'},
                {'field': 'valueField', 'value': 'value'}
            ]);
            disposeStub.restore();
        });
        it('should validate rows', function() {
            var validateStub = sinon.stub(view, 'validateRows');
            view.removeRow({currentTarget: $event});
            expect(validateStub).toHaveBeenCalled();
            validateStub.restore();
        });
    });

    describe('validateRows', function() {
        var triggerStub, $rows;
        beforeEach(function() {
            triggerStub = sinon.stub(view.layout, 'trigger');
            $rows = [];
        });
        afterEach(function() {
            triggerStub.restore();
        });
        it('should return true if all rows have a value set', function() {
            $rows.push($('<div>').data({ name: 'abc', value: 'ABC'}));
            $rows.push($('<div>').data({ name: '123', value: '123'}));
            view.validateRows($rows);
            expect(triggerStub.firstCall.args).toEqual(['filter:create:rowsValid', true]);
            expect(triggerStub.secondCall).toBeNull();
        });
        it('should return false if a row has a value not set', function() {
            $rows.push($('<div>').data({ name: 'abc', value: 'ABC'}));
            $rows.push($('<div>').data({ name: '123'}));
            view.validateRows($rows);
            expect(triggerStub.firstCall.args).toEqual(['filter:create:rowsValid', false]);
        });
        it('should return true if uses date range instead of value', function() {
            $rows.push($('<div>').data({ name: 'abc', isDateRange: true}));
            $rows.push($('<div>').data({ name: '123', value: '123'}));
            view.validateRows($rows);
            expect(triggerStub.firstCall.args).toEqual(['filter:create:rowsValid', true]);
            expect(triggerStub.secondCall).toBeNull();
        });
        it('should return true if predefined filter instead of value', function() {
            $rows.push($('<div>').data({ name: '$favorite', isPredefinedFilter: true}));
            $rows.push($('<div>').data({ name: '123', value: '123'}));
            view.validateRows($rows);
            expect(triggerStub.firstCall.args).toEqual(['filter:create:rowsValid', true]);
            expect(triggerStub.secondCall).toBeNull();
        });
        it('should return false if $dateBetween operator does not have 2 values', function() {
            $rows.push($('<div>').data({ name: 'abc', operator: '$dateBetween', value: ['12-12-12']}));
            $rows.push($('<div>').data({ name: '123', value: '123'}));
            view.validateRows($rows);
            expect(triggerStub.firstCall.args).toEqual(['filter:create:rowsValid', false]);
            expect(triggerStub.secondCall).toBeNull();
        });
        it('should return true if $dateBetween operator has 2 values', function() {
            $rows.push($('<div>').data({ name: 'abc', operator: '$dateBetween', value: ['12-12-12', '12-13-12']}));
            $rows.push($('<div>').data({ name: '123', value: '123'}));
            view.validateRows($rows);
            expect(triggerStub.firstCall.args).toEqual(['filter:create:rowsValid', true]);
            expect(triggerStub.secondCall).toBeNull();
        });
        it('should return false if $between operator does not have 2 values', function() {
            $rows.push($('<div>').data({ name: 'abc', operator: '$between', value: [11]}));
            $rows.push($('<div>').data({ name: '123', value: '123'}));
            view.validateRows($rows);
            expect(triggerStub.firstCall.args).toEqual(['filter:create:rowsValid', false]);
            expect(triggerStub.secondCall).toBeNull();
        });
        it('should return true if $between operator has 2 values', function() {
            $rows.push($('<div>').data({ name: 'abc', operator: '$between', value: [11, 22]}));
            $rows.push($('<div>').data({ name: '123', value: '123'}));
            view.validateRows($rows);
            expect(triggerStub.firstCall.args).toEqual(['filter:create:rowsValid', true]);
            expect(triggerStub.secondCall).toBeNull();
        });
    });

    describe('populateFilter', function() {
        it('should trigger filter:set:name and populate rows', function() {
            view.layout.editingFilter = new Backbone.Model({ name: 'Test',
                filter_definition: [
                    {
                        first_name: 'FirstName'
                    },
                    {
                        last_name: {
                            '$starts': 'LastName'
                        }
                    }
                ]
            });
            var triggerStub = sinon.stub(view.layout, 'trigger');
            var populateRowStub = sinon.stub(view, 'populateRow');
            view.populateFilter();
            expect(triggerStub).toHaveBeenCalledWith('filter:set:name', 'Test');
            expect(populateRowStub.secondCall).toBeDefined();
            populateRowStub.restore();
            triggerStub.restore();
        });
    });

    describe('populateRow', function() {
        var addRowStub, select2Stub, $triggerStub;
        beforeEach(function() {
            view.fieldList = {
                first_name: {
                },
                last_name: {
                }
            };
            addRowStub = sinon.stub(view, 'addRow', function() {
                return $('<article>').addClass('filter-body').appendTo(view.$el);
            });
            select2Stub = sinon.stub($.fn, 'select2', function(sel) {
                return $(sel);
            });
            $triggerStub = sinon.stub($.fn, 'trigger');
        });
        afterEach(function() {
            addRowStub.restore();
            select2Stub.restore();
            $triggerStub.restore();
        });
        it('should retrieve the field, the operator and the value from the filter object (1)', function() {
            view.populateRow({
                first_name: 'FirstName'
            });
            expect(select2Stub.firstCall.args).toEqual(['val', 'first_name']);
            expect(select2Stub.secondCall.args).toEqual(['val', '$equals']);
            expect(view.$('article.filter-body').data('value')).toEqual('FirstName');
        });
        it('should retrieve the field, the operator and the value from the filter object (2)', function() {
            view.populateRow({
                last_name: {
                    '$starts': 'LastName'
                }
            });
            expect(select2Stub.firstCall.args).toEqual(['val', 'last_name']);
            expect(select2Stub.secondCall.args).toEqual(['val', '$starts']);
            expect(view.$('article.filter-body').data('value')).toEqual('LastName');
        });
        it('should retrieve the field, the operator and the value from the filter object (3)', function() {
            view.fieldList = {
                address_state: {
                    dbFields: ['primary_address_state', 'alt_address_state']
                }
            };
            view.populateRow({
                "$or": [
                    {"primary_address_state": {"$equals": "12"}},
                    {"alt_address_state": {"$equals": "12"}}
                ]
            });
            expect(select2Stub.firstCall.args).toEqual(['val', 'address_state']);
            expect(select2Stub.secondCall.args).toEqual(['val', '$equals']);
            expect(view.$('article.filter-body').data('value')).toEqual('12');
        });
    });

    describe('handleFieldSelected', function() {
        var $row, $filterField, $operatorField;
        beforeEach(function() {
            view.fieldList = {
                test: {
                    type: 'enum'
                },
                $favorite: {
                    predefined_filter: true
                }
            };
            view.filterOperatorMap = { 'enum': {
                '$in': 'is',
                '$not_in': 'is not'
            }};
            $row = $('<div>').addClass('filter-body').appendTo(view.$el);
            $filterField = $('<div>').addClass('filter-field').val('test').appendTo($row);
            $operatorField = $('<div>').addClass('filter-operator').appendTo($row);
        });
        it('should create an enum field for operators', function() {
            var createFieldSpy = sinon.spy(view, 'createField');
            view.handleFieldSelected({currentTarget: $filterField});
            expect(createFieldSpy).toHaveBeenCalled();
            expect(createFieldSpy.lastCall.args[1]).toEqual({
                type: 'enum',
                options: {
                    '': '',
                    '$in': 'is',
                    '$not_in': 'is not'
                },
                searchBarThreshold: 9999
            });
            expect(_.isEmpty($operatorField.html())).toBeFalsy();
            createFieldSpy.restore();
        });
        it('should dispose previous operator and value fields', function() {
            var disposeStub = sinon.stub(view, '_disposeFields');
            view.handleFieldSelected({currentTarget: $filterField});
            expect(disposeStub).toHaveBeenCalled();
            expect(disposeStub.lastCall.args[1]).toEqual([
                {'field': 'operatorField', 'value': 'operator'},
                {'field': 'valueField', 'value': 'value'}
            ]);
            disposeStub.restore();
        });
        it('should set data attributes', function() {
            view.handleFieldSelected({currentTarget: $filterField});
            expect($row.data('name')).toBeDefined();
            expect($row.data('operatorField')).toBeDefined();
        });
        it('should not create an operator field for predefined filters', function() {
            var createFieldSpy = sinon.spy(view, 'createField');
            var applyFilterStub = sinon.stub(view, 'fireSearch');
            $filterField.val('$favorite');
            view.handleFieldSelected({currentTarget: $filterField});
            expect(createFieldSpy).not.toHaveBeenCalled();
            expect(_.isEmpty($operatorField.html())).toBeTruthy();
            expect(applyFilterStub).toHaveBeenCalled();
            expect($row.data('isPredefinedFilter')).toBeTruthy();
            applyFilterStub.restore();
            createFieldSpy.restore();
        });
    });

    describe('handleOperatorSelected', function() {
        var $row, $filterField, $operatorField, $valueField;
        beforeEach(function() {
            view.fieldList = {
                case_number: {
                    type: 'int'
                },
                status: {
                    type: 'enum',
                    options: 'status_dom'
                },
                priority: {
                    type: 'bool',
                    options: 'boolean_dom'
                },
                date_created: {
                    type: 'datetime'
                }
            };
            view.filterOperatorMap = { 'enum': {
                '$in': 'is',
                '$not_in': 'is not'
            }};
            view.moduleName = 'Cases';
            $row = $('<div>').addClass('filter-body').appendTo(view.$el);
            $filterField = $('<input type="hidden">');
            $('<div>').addClass('filter-field').html($filterField).appendTo($row);
            $operatorField = $('<div>').addClass('filter-operator').val('$in').appendTo($row);
            $valueField = $('<div>').addClass('filter-value').appendTo($row);
        });
        describe('creating fields for filter value', function() {
            var createFieldSpy;
            beforeEach(function() {
                createFieldSpy = sinon.spy(view, 'createField');
            })
            afterEach(function() {
                createFieldSpy.restore();
            });

            it('should make enum fields multi selectable', function() {
                spyOn($.fn, "select2").andReturn("status"); //return "status" as value
                view.handleOperatorSelected({currentTarget: $operatorField});
                expect(createFieldSpy).toHaveBeenCalled();
                expect(createFieldSpy.lastCall.args[1]).toEqual({
                    name: 'status',
                    type: 'enum',
                    options: 'status_dom',
                    isMultiSelect: true,
                    searchBarThreshold: 9999
                });
                expect(_.isEmpty($valueField.html())).toBeFalsy();
            });
            it('should convert a boolean field into an enum field', function() {
                spyOn($.fn, "select2").andReturn("priority"); //return "priority" as value
                view.handleOperatorSelected({currentTarget: $operatorField});
                expect(createFieldSpy).toHaveBeenCalled();
                expect(createFieldSpy.lastCall.args[1]).toEqual({
                    name: 'priority',
                    type: 'enum',
                    options: 'boolean_dom',
                    searchBarThreshold: 9999
                });
                expect(_.isEmpty($valueField.html())).toBeFalsy();
            });
            it('should set auto_increment to false for an integer field', function() {
                spyOn($.fn, "select2").andReturn("case_number"); //return "case_number" as value
                view.handleOperatorSelected({currentTarget: $operatorField});
                expect(createFieldSpy).toHaveBeenCalled();
                expect(createFieldSpy.lastCall.args[1]).toEqual({
                    name: 'case_number',
                    type: 'int',
                    auto_increment: false
                });
                expect(_.isEmpty($valueField.html())).toBeFalsy();
            });
            it('should create two inputs if the operator is in between', function() {
                spyOn($.fn, "select2").andReturn("case_number"); //return "case_number" as value
                $operatorField.val('$between');
                view.handleOperatorSelected({currentTarget: $operatorField});
                expect(createFieldSpy).toHaveBeenCalledTwice();
                expect(createFieldSpy.firstCall.args[1]).toEqual({
                    type: 'int',
                    name: 'case_number_min',
                    auto_increment: false
                });
                expect(createFieldSpy.lastCall.args[1]).toEqual({
                    type: 'int',
                    name: 'case_number_max',
                    auto_increment: false
                });
                expect(_.isEmpty($valueField.html())).toBeFalsy();
                expect(_.size($valueField.find('input'))).toEqual(2);
            });
            describe('date type fields', function() {
                it('should not create a value field for specific date operators', function() {
                    spyOn($.fn, "select2").andReturn("date_created"); //return "date_created" as value
                    var buildFilterDefStub = sinon.stub(view, 'buildFilterDef');
                    $filterField.val('date_created');
                    $operatorField.val('next_30_days');
                    view.handleOperatorSelected({currentTarget: $operatorField});
                    expect(createFieldSpy).not.toHaveBeenCalled();
                    expect(_.isEmpty($valueField.html())).toBeTruthy();
                    expect(buildFilterDefStub).toHaveBeenCalled();
                    buildFilterDefStub.restore();
                });
            });
        });

        it('should dispose previous value field', function() {
            var disposeStub = sinon.stub(view, '_disposeFields');
            spyOn($.fn, "select2").andReturn("case_number");
            view.handleOperatorSelected({currentTarget: $operatorField});
            expect(disposeStub).toHaveBeenCalled();
            expect(disposeStub.lastCall.args[1]).toEqual([
                {'field': 'valueField', 'value': 'value'}
            ]);
            disposeStub.restore();
        });

        it('should set data attributes', function() {
            spyOn($.fn, "select2").andReturn("case_number");
            view.handleOperatorSelected({currentTarget: $operatorField});
            expect($row.data('operator')).toBeDefined();
            expect($row.data('valueField')).toBeDefined();
        });
        it('should trigger filter:apply when value change', function() {
            spyOn($.fn, "select2").andReturn("case_number");
            var triggerStub = sinon.stub(view.layout, 'trigger');
            var renderStub = sinon.stub(app.view.Field.prototype, 'render', $.noop());
            view.handleOperatorSelected({currentTarget: $operatorField});
            $row.data('valueField').model.set('status', 'firesModelChangeEvent');
            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalledWith('filter:apply');
            triggerStub.restore();
            renderStub.restore();
        });
        it('should trigger filter:apply when keyup', function() {
            spyOn($.fn, "select2").andReturn("case_number");
            $filterField.val('case_number');
            $operatorField.val('$in');
            view.handleOperatorSelected({currentTarget: $operatorField});
            $row.data('valueField').model.set('case_number', 200);
            var triggerStub = sinon.stub(view.layout, 'trigger');
            $operatorField.parent('.filter-body').find('.filter-value input').trigger('keyup');
            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalledWith('filter:apply');
            triggerStub.restore();
        });
    });

    describe('buildRowFilterDef', function() {
        var $row, filter, expected;
        beforeEach(function() {
            view.fieldList = {
                description: {
                    name: 'description',
                    type: 'text'
                },
                address_street: {
                    name: 'address_street',
                    dbFields: ['primary_address_street', 'alt_address_street'],
                    type: 'text'
                },
                assigned_user_name: {
                    name: 'assigned_user_name',
                    id_name: 'assigned_user_id',
                    type: 'relate'
                },
                date_created: {
                    name: 'date_created',
                    type: 'datetimecombo'
                }
            };
        });

        it('should build a simple filter definition', function() {
            $row = $('<div>').data({
                name: 'description',
                operator: '$starts',
                value: 'abc'
            });
            filter = view.buildRowFilterDef($row);
            expected = {
                description: {
                    '$starts': 'abc'
                }
            };
            expect(filter).toEqual(expected);
        });
        it('should build a complex filter definition', function() {
            $row = $('<div>').data({
                name: 'address_street',
                operator: '$starts',
                value: 'abc'
            });
            filter = view.buildRowFilterDef($row);
            expected = {
                '$or': [
                    {
                        primary_address_street: {
                            '$starts': 'abc'
                        }
                    },
                    {
                        alt_address_street: {
                            '$starts': 'abc'
                        }
                    }

                ]
            };
            expect(filter).toEqual(expected);
        });
        it('should make an exception for predefined filters', function() {
            $row = $('<div>').data({
                name: '$favorite',
                isPredefinedFilter: true
            });
            filter = view.buildRowFilterDef($row);
            expected = {
                $favorite: ''
            };
            expect(filter).toEqual(expected);
        });

        it('should pick id_name for relate fields', function() {
            var filterModel = new Backbone.Model();
            filterModel.set("assigned_user_id", "seed_sarah_id");
            var fieldMock = {model: filterModel};
            $row = $('<div>').data({
                name: 'assigned_user_name',
                id_name: 'assigned_user_id',
                operator: '$equals',
                valueField: fieldMock
            });
            view._updateFilterData($row);
            filter = view.buildRowFilterDef($row);
            expected = {
                assigned_user_id: 'seed_sarah_id'
            };
            expect(filter).toEqual(expected);
        });

        it('should format date range filter', function() {
            SugarTest.loadComponent('base', 'field', 'date');
            SugarTest.loadComponent('base', 'field', 'datetimecombo');
            $row = $('<div>').data({
                isDate: true,
                isDateRange: true,
                name: 'date_created'
            });
            $row.data({
                operator: 'last_year'
            });
            filter = view.buildRowFilterDef($row);
            expected = { date_created: { $dateRange: 'last_year' } };
            expect(filter).toEqual(expected);
        });
    });

});
