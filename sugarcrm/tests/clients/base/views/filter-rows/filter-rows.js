describe('Base.View.FilterRows', function() {
    var view, layout, app;

    beforeEach(function() {
        app = SUGAR.App;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadComponent('base', 'layout', 'filter');
        SugarTest.loadComponent('base', 'layout', 'togglepanel');
        SugarTest.loadComponent('base', 'layout', 'filterpanel');
        SugarTest.loadComponent('base', 'view', 'filter-rows');
        SugarTest.testMetadata.set();

        layout = SugarTest.createLayout('base', "Cases", "filterpanel", {}, null, null, { layout: new Backbone.View() });
        layout._components.push(SugarTest.createLayout('base', "Cases", "filter", {}, null, null, { layout: new Backbone.View() }));
        view = SugarTest.createView("base", "Cases", "filter-rows", null, null, null, layout);
        view.layout = layout;
        view.context.editingFilter = new Backbone.Model();
    });

    afterEach(function() {
        sinon.collection.restore();
        SugarTest.testMetadata.dispose();
        view.dispose();
        layout.dispose();
        app.cache.cutAll();
        Handlebars.templates = {};
    });

    describe('handleFilterChange', function() {
        it('should return undefined if there is no module metadata', function() {
            view.handleFilterChange('CustomModule');
            expect(view.fieldList).toBeUndefined();
        });
    });

    describe('openForm', function() {
        var renderStub, addRowStub, populateFilterStub, saveEditStateStub,
            filterModel;
        beforeEach(function() {
            renderStub = sinon.collection.stub(view, 'render');
            addRowStub = sinon.collection.stub(view, 'addRow').returns($('<div></div>').data('nameField', {}));
            saveEditStateStub = sinon.collection.stub(view, 'saveFilterEditState');
            populateFilterStub = sinon.collection.stub(view, 'populateFilter');
            filterModel = new Backbone.Model();
        });
        it('should render the view and add a row', function() {
            view.openForm(filterModel);
            expect(renderStub).toHaveBeenCalled();
            expect(addRowStub).toHaveBeenCalled();
            expect(populateFilterStub).not.toHaveBeenCalled();
            expect(saveEditStateStub).toHaveBeenCalled();
        });
        it('should populate filter', function() {
            filterModel.set('filter_definition', [{ /* ... */ }]);
            view.openForm(filterModel);
            expect(renderStub).not.toHaveBeenCalled();
            expect(addRowStub).not.toHaveBeenCalled();
            expect(populateFilterStub).toHaveBeenCalled();
            expect(saveEditStateStub).toHaveBeenCalled();
        });
    });

    describe('saveFilter', function() {
        it('should trigger events', function() {
            var layoutTriggerStub = sinon.collection.stub(view.layout, 'trigger'),
                ctxTriggerStub = sinon.collection.stub(view.context, 'trigger');
            sinon.collection.stub(view.context.editingFilter, 'sync', function(method, model, options) {
                if (options.success) options.success(model, {}, options);
            });
            view.saveFilter();
            expect(ctxTriggerStub).toHaveBeenCalledWith('filter:add', view.context.editingFilter);
            expect(layoutTriggerStub).toHaveBeenCalledWith('filter:toggle:savestate', false);
        });
    });

    describe('deleteFilter', function() {
        it('should trigger events', function() {
            var triggerStub = sinon.collection.stub(view.layout, 'trigger');
            view.deleteFilter();
            expect(triggerStub).toHaveBeenCalledWith('filter:remove', view.context.editingFilter);
            expect(triggerStub).toHaveBeenCalledWith('filter:create:close');
        });
    });

    describe('getFilterableFields', function() {

        it('should return the list of filterable fields with fields definition', function() {
            sinon.collection.stub(app.metadata, 'getModule').returns(
                {
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
                }
            );
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
            sinon.collection.spy(view, 'createField');
            view.formRowTemplate = function() {
                return '<div>';
            };
            view.filterFields = ['test_field'];
            var $row = view.addRow();
            expect($row.data('nameField')).toBeDefined();
            expect($row.data('nameField').type).toEqual('enum');
            expect($row.data('nameField').def.options).toEqual(['test_field']);
        });
    });

    describe('removeRow', function() {
        var $event;
        beforeEach(function() {
            $event = $('<div>');
            sinon.collection.stub(view, 'addRow', function() {
                $('<div data-filter="row">').appendTo(view.$el);
            });
            $('<div data-filter="row">').appendTo(view.$el);
            $('<div data-filter="row">').appendTo(view.$el);
            $('<div data-filter="row">').appendTo(view.$el);
        });
        it('should remove the row from the view', function() {
            $event.appendTo(view.$('[data-filter=row]:last'));
            view.removeRow({currentTarget: $event});
            expect(_.size(view.$('[data-filter=row]'))).toEqual(2);

            $event.appendTo(view.$('[data-filter=row]:last'));
            view.removeRow({currentTarget: $event});
            expect(_.size(view.$('[data-filter=row]'))).toEqual(1);

            //it should add another row when the form becomes empty
            $event.appendTo(view.$('[data-filter=row]:last'));
            view.removeRow({currentTarget: $event});
            expect(_.size(view.$('[data-filter=row]'))).toEqual(1);
        });
        it('should dispose fields', function() {
            var disposeStub = sinon.collection.stub(view, '_disposeRowFields');
            view.removeRow({currentTarget: $event});
            expect(disposeStub).toHaveBeenCalled();
            expect(disposeStub.lastCall.args[1]).toEqual([
                {'field': 'nameField', 'value': 'name'},
                {'field': 'operatorField', 'value': 'operator'},
                {'field': 'valueField', 'value': 'value'}
            ]);
        });
    });

    describe('rows validation', function() {

        it('should return true if all rows have a value set', function() {
            var $rows = [
                $('<div>').data({ name: 'abc', value: 'ABC'}),
                $('<div>').data({ name: '123', value: '123'})
            ];
            expect(view.validateRows($rows)).toBe(true);
        });

        it('should return false if one row has a value not set', function() {
            var $rows = [
                $('<div>').data({ name: 'abc', value: 'ABC'}),
                $('<div>').data({ name: '123'})
            ];
            expect(view.validateRows($rows)).toBe(false);
        });

        using('possible filters', [{
            filter: $('<div>').data({ name: 'abc', isDateRange: true}),
            expected: true
        },{
            filter: $('<div>').data({ name: '$favorite', isPredefinedFilter: true}),
            expected: true
        },{
            filter: $('<div>').data({ name: 'abc', operator: '$dateBetween', value: ['12-12-12']}),
            expected: false
        },{
            filter: $('<div>').data({ name: 'abc', operator: '$dateBetween', value: ['', '12-12-12']}),
            expected: false
        },{
            filter: $('<div>').data({ name: 'abc', operator: '$dateBetween', value: ['12-12-12', '12-13-12']}),
            expected: true
        },{
            filter: $('<div>').data({ name: 'abc', operator: '$between', value: [11]}),
            expected: false
        },{
            filter: $('<div>').data({ name: 'abc', operator: '$between', value: [11, 22]}),
            expected: true
        },{
            filter: $('<div>').data({ name: 'abc', operator: '$between', value: ['11', 22]}),
            expected: false
        }], function(value) {
            it('should validate a filter correctly', function() {
                expect(view.validateRows(value.filter)).toBe(value.expected);
            });
        });

    });

    describe('populateFilter', function() {
        it('should trigger filter:set:name and populate rows', function() {
            view.context.editingFilter = new Backbone.Model({ name: 'Test',
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
            var triggerStub = sinon.collection.stub(view.layout, 'trigger');
            var populateRowStub = sinon.collection.stub(view, 'populateRow');
            view.populateFilter();
            expect(triggerStub).toHaveBeenCalledWith('filter:set:name', 'Test');
            expect(populateRowStub.secondCall).toBeDefined();
        });
    });

    describe('populateRow', function() {
        var addRowStub, _select2Obj, _rowObj;
        beforeEach(function() {
            view.fieldList = {
                name: {},
                account_name: {
                    name: 'account_name',
                    id_name: 'account_id'
                }
            };
            _select2Obj = {
                select2: sinon.collection.stub($.fn, 'select2', function(sel) {
                    return $(sel);
                })
            };
            _rowObj = {
                remove: sinon.collection.stub(),
                data: sinon.collection.stub(),
                find: sinon.collection.stub().returns(_select2Obj),
            };
            addRowStub = sinon.collection.stub(view, 'addRow').returns(_rowObj);
        });
        it('should remove the row if the field does not exist in the metadata', function() {
            view.populateRow({
                first_name: 'FirstName'
            });
            expect(_select2Obj.select2).not.toHaveBeenCalled();
            expect(_rowObj.remove).toHaveBeenCalled();
        });
        it('should remove the row if the field does not exist in the `fieldList`', function() {
            view.populateRow({
                case_number: '123456'
            });
            expect(_select2Obj.select2).not.toHaveBeenCalled();
            expect(_rowObj.remove).toHaveBeenCalled();
        });
        it('should retrieve the field, the operator and the value from the filter object', function() {
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

            expect(_select2Obj.select2.firstCall.args).toEqual(['val', 'address_state']);
            expect(_select2Obj.select2.secondCall.args).toEqual(['val', '$equals']);
            expect(_rowObj.data.firstCall.args).toEqual(['value', '12']);
        });

        it('should populate because it is a valid predefined filter', function() {
            view.fieldList = {
                '$favorite': {
                    'predefined_filter': true
                }
            };
            view.populateRow({
                '$favorite': ''
            });

            expect(_select2Obj.select2.firstCall.args).toEqual(['val', '$favorite']);
        });

        using('normal field, relate field', [
            {
                filter_populate: {
                    name: 'Sugar'
                },
                filterDef: {
                    'name': ''
                },
                expected: {
                    field: 'name',
                    operator: '$equals',
                    value: 'Sugar'
                }
            },
            {
                filter_populate: {
                    account_id: '1234-5678'
                },
                filterDef: {
                    'account_id': ''
                },
                expected: {
                    field: 'account_name',
                    operator: '$equals',
                    value: '1234-5678'
                }
            }
        ], function(option) {

            it('should populate the template with values passed in options', function() {
                view.context.editingFilter.set('is_template', true);
                view.context.set('filterOptions', {
                    filter_populate: option.filter_populate
                });
                view.populateRow(option.filterDef);

                expect(_select2Obj.select2.firstCall.args).toEqual(['val', option.expected.field]);
                expect(_select2Obj.select2.secondCall.args).toEqual(['val', option.expected.operator]);
                expect(_rowObj.data.firstCall.args).toEqual(['value', option.expected.value]);
            });
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
            $row = $('<div data-filter="row">').appendTo(view.$el);
            $filterField = $('<div data-filter="field">').val('test').appendTo($row);
            $operatorField = $('<div data-filter="operator">').appendTo($row);
        });
        it('should create an enum field for operators', function() {
            var createFieldSpy = sinon.collection.spy(view, 'createField');
            view.handleFieldSelected({currentTarget: $filterField});
            expect(createFieldSpy).toHaveBeenCalled();
            expect(createFieldSpy.lastCall.args[1]).toEqual({
                type: 'enum',
                options: {
                    '$in': 'is',
                    '$not_in': 'is not'
                },
                searchBarThreshold: 9999
            });
            expect(_.isEmpty($operatorField.html())).toBeFalsy();
        });
        it('should dispose previous operator and value fields', function() {
            var disposeStub = sinon.collection.stub(view, '_disposeRowFields');
            view.handleFieldSelected({currentTarget: $filterField});
            expect(disposeStub).toHaveBeenCalled();
            expect(disposeStub.lastCall.args[1]).toEqual([
                {'field': 'operatorField', 'value': 'operator'},
                {'field': 'valueField', 'value': 'value'}
            ]);
        });
        it('should set data attributes', function() {
            view.handleFieldSelected({currentTarget: $filterField});
            expect($row.data('name')).toBeDefined();
            expect($row.data('operatorField')).toBeDefined();
        });
        it('should not create an operator field for predefined filters', function() {
            var createFieldSpy = sinon.collection.spy(view, 'createField');
            var applyFilterStub = sinon.collection.stub(view, 'fireSearch');
            $filterField.val('$favorite');
            view.handleFieldSelected({currentTarget: $filterField});
            expect(createFieldSpy).not.toHaveBeenCalled();
            expect(_.isEmpty($operatorField.html())).toBeTruthy();
            expect(applyFilterStub).toHaveBeenCalled();
            expect($row.data('isPredefinedFilter')).toBeTruthy();
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
                test_bool_field: {
                    type: 'bool'
                },
                date_created: {
                    type: 'datetime'
                },
                team_name: {
                    type: 'teamset',
                    'id_name': 'team_id'
                }
            };
            view.moduleName = 'Cases';
            $row = $('<div data-filter="row">').appendTo(view.$el);
            $filterField = $('<input type="hidden">');
            $('<div data-filter="field">').html($filterField).appendTo($row);
            $operatorField = $('<div data-filter="operator">').val('$in').appendTo($row);
            $valueField = $('<div data-filter="value">').appendTo($row);
        });
        describe('creating fields for filter value', function() {
            var createFieldSpy;
            beforeEach(function() {
                createFieldSpy = sinon.collection.spy(view, 'createField');
            });

            it('should make enum fields multi selectable', function() {
                sinon.collection.stub($.fn, 'select2').returns('status'); //return `status` as field
                view.handleOperatorSelected({currentTarget: $operatorField});
                expect(createFieldSpy).toHaveBeenCalled();
                expect(createFieldSpy.lastCall.args[1]).toEqual({
                    name: 'status',
                    type: 'enum',
                    options: 'status_dom',
                    isMultiSelect: true,
                    searchBarThreshold: -1,
                    required: false,
                    readonly: false
                });
                expect(_.isEmpty($valueField.html())).toBeFalsy();
                expect($row.data('valueField').action).toEqual('detail');
            });
            it('should convert a boolean field into an enum field', function() {
                sinon.collection.stub($.fn, 'select2').returns('priority'); //return `priority` as field
                view.handleOperatorSelected({currentTarget: $operatorField});
                expect(createFieldSpy).toHaveBeenCalled();
                expect(createFieldSpy.lastCall.args[1]).toEqual({
                    name: 'priority',
                    type: 'enum',
                    options: 'boolean_dom',
                    required: false,
                    readonly: false
                });
                expect(_.isEmpty($valueField.html())).toBeFalsy();
            });
            it('should use filter_checkbox_dom by default for bools', function() {
                sinon.collection.stub($.fn, 'select2').returns('test_bool_field'); //return `test_bool_field` as field
                view.handleOperatorSelected({currentTarget: $operatorField});
                expect(createFieldSpy).toHaveBeenCalled();
                expect(createFieldSpy.lastCall.args[1]).toEqual({
                    name: 'test_bool_field',
                    type: 'enum',
                    options: 'filter_checkbox_dom',
                    required: false,
                    readonly: false
                });
                expect(_.isEmpty($valueField.html())).toBeFalsy();
            });
            it('should set auto_increment to false for an integer field', function() {
                $operatorField.val('$equals');
                sinon.collection.stub($.fn, 'select2').returns('case_number'); //return `case_number` as field
                view.handleOperatorSelected({currentTarget: $operatorField});
                expect(createFieldSpy).toHaveBeenCalled();
                expect(createFieldSpy.lastCall.args[1]).toEqual({
                    name: 'case_number',
                    type: 'int',
                    auto_increment: false,
                    required: false,
                    readonly: false
                });
                expect(_.isEmpty($valueField.html())).toBeFalsy();
            });
            it('should convert to varchar and join values for an integer field when operator is $in', function() {
                $operatorField.val('$in');
                $row.data('value', [1,20,35]);
                sinon.collection.stub($.fn, 'select2').returns('case_number'); //return `case_number` as field
                view.handleOperatorSelected({currentTarget: $operatorField});
                expect(createFieldSpy).toHaveBeenCalled();
                expect(createFieldSpy.lastCall.args[1]).toEqual({
                    name: 'case_number',
                    type: 'varchar',
                    auto_increment: false,
                    len: 200,
                    required: false,
                    readonly: false
                });
                expect(_.isEmpty($valueField.html())).toBeFalsy();
                expect($row.data('value')).toEqual('1,20,35');
            });
            it('should create two inputs if the operator is in between', function() {
                sinon.collection.stub($.fn, 'select2').returns('case_number'); //return `case_number` as field
                $operatorField.val('$between');
                view.handleOperatorSelected({currentTarget: $operatorField});
                expect(createFieldSpy).toHaveBeenCalledTwice();
                expect(createFieldSpy.firstCall.args[1]).toEqual({
                    type: 'int',
                    name: 'case_number_min',
                    auto_increment: false,
                    required: false,
                    readonly: false
                });
                expect(createFieldSpy.lastCall.args[1]).toEqual({
                    type: 'int',
                    name: 'case_number_max',
                    auto_increment: false,
                    required: false,
                    readonly: false
                });
                expect(_.isEmpty($valueField.html())).toBeFalsy();
                expect(_.size($valueField.find('input'))).toEqual(2);
                _.each($row.data('valueField'), function(data) {
                    expect(data.action).toEqual('detail');
                });
            });
            describe('teamset and relate field', function() {
                var fetchStub;
                beforeEach(function() {
                    sinon.collection.stub($.fn, 'select2').returns('team_name'); //return `team_name` as field
                    fetchStub = sinon.collection.stub(Backbone.Collection.prototype, 'fetch');
                });
                it('should convert teamset field to a relate field and fetch name like other relate fields', function() {
                    $row.data('value', 'West');
                    view.handleOperatorSelected({currentTarget: $operatorField});
                    expect(createFieldSpy).toHaveBeenCalled();
                    expect(createFieldSpy.lastCall.args[1]).toEqual({
                        name: 'team_name',
                        type: 'relate',
                        id_name: 'team_id',
                        required: false,
                        readonly: false
                    });
                    expect(_.isEmpty($valueField.html())).toBeFalsy();
                    expect(fetchStub).toHaveBeenCalled();
                });
                it('should convert teamset field to a relate field but not fetch because no value set', function() {
                    view.handleOperatorSelected({currentTarget: $operatorField});
                    expect(createFieldSpy).toHaveBeenCalled();
                    expect(createFieldSpy.lastCall.args[1]).toEqual({
                        name: 'team_name',
                        type: 'relate',
                        id_name: 'team_id',
                        required: false,
                        readonly: false
                    });
                    expect(_.isEmpty($valueField.html())).toBeFalsy();
                    expect(fetchStub).not.toHaveBeenCalled();
                });
            });
            describe('date type fields', function() {
                it('should not create a value field for specific date operators', function() {
                    sinon.collection.stub($.fn, 'select2').returns('date_created'); //return `date_created` as field
                    var buildFilterDefStub = sinon.collection.stub(view, 'buildFilterDef');
                    $filterField.val('date_created');
                    $operatorField.val('next_30_days');
                    view.handleOperatorSelected({currentTarget: $operatorField});
                    expect(createFieldSpy).not.toHaveBeenCalled();
                    expect(_.isEmpty($valueField.html())).toBeTruthy();
                    expect(buildFilterDefStub).toHaveBeenCalled();
                });
            });
        });

        it('should dispose previous value field', function() {
            var disposeStub = sinon.collection.stub(view, '_disposeRowFields');
            sinon.collection.stub($.fn, 'select2').returns('case_number'); //return `case_number` as field
            view.handleOperatorSelected({currentTarget: $operatorField});
            expect(disposeStub).toHaveBeenCalled();
            expect(disposeStub.lastCall.args[1]).toEqual([
                {'field': 'valueField', 'value': 'value'}
            ]);
        });

        it('should set data attributes', function() {
            sinon.collection.stub($.fn, 'select2').returns('case_number'); //return `case_number` as field
            view.handleOperatorSelected({currentTarget: $operatorField});
            expect($row.data('operator')).toBeDefined();
            expect($row.data('valueField')).toBeDefined();
        });
        it('should trigger filter:apply when value change', function() {
            sinon.collection.stub($.fn, 'select2').returns('case_number'); //return `case_number` as field
            var triggerStub = sinon.collection.stub(view.layout, 'trigger');
            sinon.collection.stub(app.view.Field.prototype, 'render');
            view.handleOperatorSelected({currentTarget: $operatorField});
            view.lastFilterDef = undefined;
            $row.data('valueField').model.set('status', 'firesModelChangeEvent');
            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalledWith('filter:apply');
        });
        it('should trigger filter:apply when keyup', function() {
            sinon.collection.stub($.fn, 'select2').returns('case_number'); //return `case_number` as field
            $filterField.val('case_number');
            $operatorField.val('$in');
            view.handleOperatorSelected({currentTarget: $operatorField});
            $row.data('valueField').model.set('case_number', 200);
            var triggerStub = sinon.collection.stub(view.layout, 'trigger');
            view.lastFilterDef = undefined;
            $operatorField.closest('[data-filter="row"]').find('[data-filter=value] input').trigger('keyup');
            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalledWith('filter:apply');
        });
    });

    describe('buildRowFilterDef', function() {
        var $row, filter, expected;
        beforeEach(function() {
            view.fieldList = {
                case_number: {
                    name: 'case_number',
                    type: 'int'
                },
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
            filter = view.buildRowFilterDef($row, true);
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
            filter = view.buildRowFilterDef($row, true);
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

        it('should build empty filter definition if the displaying column is invalid', function() {
            $row = $('<div>').data({
                name: 'address_street'
            });
            filter = view.buildRowFilterDef($row, true);
            expect(filter).toBeUndefined();

            var validate = view.validateRow($row);
            expect(validate).toBe(false);
        });

        describe('build an ad-hoc filter definition', function() {
            it('should have empty operator and value', function() {
                $row = $('<div>').data({
                    name: 'address_street'
                });
                var validate = view.validateRow($row);
                expect(validate).toBe(false);

                filter = view.buildRowFilterDef($row);
                //build ad-hoc filter
                expected = {
                    '$or': [
                        {
                            primary_address_street: {
                                'undefined': ''
                            }
                        },
                        {
                            alt_address_street: {
                                'undefined': ''
                            }
                        }

                    ]
                };
                expect(filter).toEqual(expected);
            });

            it('should have empty value when value is not unassigned', function() {
                $row = $('<div>').data({
                    name: 'address_street',
                    operator: '$starts'
                });
                var validate = view.validateRow($row);
                expect(validate).toBe(false);

                filter = view.buildRowFilterDef($row);
                //build ad-hoc filter
                expected = {
                    '$or': [
                        {
                            primary_address_street: {
                                '$starts': ''
                            }
                        },
                        {
                            alt_address_street: {
                                '$starts': ''
                            }
                        }

                    ]
                };
                expect(filter).toEqual(expected);
            });

            it('should return an empty array if operator is $in and value is an empty string', function() {
                $row = $('<div>').data({
                    name: 'case_number',
                    operator: '$in',
                    value: ''
                });
                filter = view.buildRowFilterDef($row);
                expected = {
                    case_number: {
                        '$in': []
                    }
                };
                expect(filter).toEqual(expected);
            });
        });

        it('should split values if operator is $in and value is a string', function() {
            $row = $('<div>').data({
                name: 'case_number',
                operator: '$in',
                value: '1,20,35'
            });
            filter = view.buildRowFilterDef($row, true);
            expected = {
                case_number: {
                    '$in': ['1','20','35']
                }
            };
            expect(filter).toEqual(expected);
        });

        it('should make an exception for predefined filters', function() {
            $row = $('<div>').data({
                name: '$favorite',
                isPredefinedFilter: true
            });
            filter = view.buildRowFilterDef($row, true);
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
            filter = view.buildRowFilterDef($row, true);
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
            filter = view.buildRowFilterDef($row, true);
            expected = { date_created: { $dateRange: 'last_year' } };
            expect(filter).toEqual(expected);
        });
    });

    describe('saveFilterEditState', function() {
        var component,
            buildFilterDefStub, saveFilterEditStateStub;
        beforeEach(function() {
            component = {
                '$': function(sel) { return [sel]; },
                getFilterName: $.noop,
                saveFilterEditState: $.noop
            };
            view.layout.getComponent = function() { return component; };
            buildFilterDefStub = sinon.collection.stub(view, 'buildFilterDef').returns(
                [{'$favorites': ''}]
            );
            sinon.collection.stub(component, 'getFilterName').returns('AwesomeName');
            saveFilterEditStateStub = sinon.collection.stub(component, 'saveFilterEditState');
        });
        it('should build filter def when no param', function() {
            view.saveFilterEditState();
            expect(buildFilterDefStub).toHaveBeenCalled();
            expect(saveFilterEditStateStub).toHaveBeenCalled();
            var expectedFilter = {
                'filter_definition': [
                    {'$favorites': ''}
                ],
                'filter_template': [
                    {'$favorites': ''}
                ],
                'name': 'AwesomeName'
            };
            expect(saveFilterEditStateStub).toHaveBeenCalledWith(expectedFilter);
        });
        it('should get the filter def passed in params', function() {
            view.saveFilterEditState([{'my_filter': {'is': 'cool'}}], [{'my_filter': {'is': 'cool'}}]);
            expect(buildFilterDefStub).not.toHaveBeenCalled();
            expect(saveFilterEditStateStub).toHaveBeenCalled();
            var expectedFilter = {
                'filter_definition': [
                    {'my_filter': {'is': 'cool'}}
                ],
                'filter_template': [
                    {'my_filter': {'is': 'cool'}}
                ],
                'name': 'AwesomeName'
            };
            expect(saveFilterEditStateStub).toHaveBeenCalledWith(expectedFilter);
        });
    });

    describe('resetFilterValues', function() {
        it('should call clear on value field models so all value fields are cleared', function() {
            var model1 = new Backbone.Model();
            var model2 = new Backbone.Model();
            var stubs = [sinon.collection.stub(model1, 'clear'), sinon.collection.stub(model2, 'clear')];
            $('<div data-filter="row">').data('valueField', {model: model1 }).appendTo(view.$el);
            $('<div data-filter="row">').data('valueField', {model: model2 }).appendTo(view.$el);
            view.resetFilterValues();
            expect(stubs[0]).toHaveBeenCalled();
            expect(stubs[1]).toHaveBeenCalled();
        });
        it('should call clear on each field model if valueField is an array', function() {
            var model1 = new Backbone.Model();
            var model2 = new Backbone.Model();
            var stubs = [sinon.collection.stub(model1, 'clear'), sinon.collection.stub(model2, 'clear')];
            $('<div data-filter="row">')
                .data('valueField', [{model: model1 }, {model: model2 }])
                .appendTo(view.$el);
            view.resetFilterValues();
            expect(stubs[0]).toHaveBeenCalled();
            expect(stubs[1]).toHaveBeenCalled();
        });
    });

});
