describe("BaseFilterRowsView", function() {
    var view, layout, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
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
                                    date_modified: {}
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
                }
            };
            expect(fields).toEqual(expected);
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
            expect(triggerStub.firstCall.args).toEqual(['filter:create:rowsValid', true]);
            expect(triggerStub.secondCall).toBeDefined();
            expect(triggerStub.secondCall.args).toEqual(['filter:create:rowsValid', false]);
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
                }
            };
            view.filterOperatorMap = { enum: {
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
            expect($operatorField.html()).not.toBeEmpty();
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
    });

    describe('handleOperatorSelected', function() {
        var $row, $filterField, $operatorField, $valueField;
        beforeEach(function() {
            view.fieldList = {
                case_number: {
                    type: 'int'
                },
                status: {
                    type: 'enum'
                },
                priority: {
                    type: 'bool'
                }
            };
            view.filterOperatorMap = { enum: {
                '$in': 'is',
                '$not_in': 'is not'
            }};
            view.moduleName = 'Cases';
            $row = $('<div>').addClass('filter-body').appendTo(view.$el);
            $filterField = $('<select>' +
                '<option value="case_number"></option>' +
                '<option value="status" selected></option>' +
                '<option value="priority"></option>' +
                '</select>');
            $('<div>').addClass('filter-field').html($filterField).appendTo($row);
            $operatorField = $('<div>').addClass('filter-operator').val('$in').appendTo($row);
            $valueField = $('<div>').addClass('filter-value').appendTo($row);
        });
        it('should create an enum field for the filter value', function() {
            var createFieldSpy = sinon.spy(view, 'createField');
            view.handleOperatorSelected({currentTarget: $operatorField});
            expect(createFieldSpy).toHaveBeenCalled();
            expect(createFieldSpy.lastCall.args[1]).toEqual({
                type: 'enum',
                isMultiSelect: true,
                searchBarThreshold: 9999
            });
            expect($valueField.html()).not.toBeEmpty();
            createFieldSpy.restore();
        });
        it('should convert a boolean field into an enum field', function() {
            $filterField.val('priority');
            var createFieldSpy = sinon.spy(view, 'createField');
            view.handleOperatorSelected({currentTarget: $operatorField});
            expect(createFieldSpy).toHaveBeenCalled();
            expect(createFieldSpy.lastCall.args[1]).toEqual({
                type: 'enum',
                searchBarThreshold: 9999
            });
            expect($valueField.html()).not.toBeEmpty();
            createFieldSpy.restore();
        });
        it('should set auto_increment to false for an integer field', function() {
            $filterField.val('case_number');
            var createFieldSpy = sinon.spy(view, 'createField');
            view.handleOperatorSelected({currentTarget: $operatorField});
            expect(createFieldSpy).toHaveBeenCalled();
            expect(createFieldSpy.lastCall.args[1]).toEqual({
                type: 'int',
                auto_increment: false
            });
            expect($valueField.html()).not.toBeEmpty();
            createFieldSpy.restore();
        });
        it('should dispose previous value field', function() {
            var disposeStub = sinon.stub(view, '_disposeFields');
            view.handleOperatorSelected({currentTarget: $operatorField});
            expect(disposeStub).toHaveBeenCalled();
            expect(disposeStub.lastCall.args[1]).toEqual([
                {'field': 'valueField', 'value': 'value'}
            ]);
            disposeStub.restore();
        });

        it('should set data attributes', function() {
            view.handleOperatorSelected({currentTarget: $operatorField});
            expect($row.data('operator')).toBeDefined();
            expect($row.data('valueField')).toBeDefined();
        });
        it('should trigger filter:apply when value change', function() {
            var triggerStub = sinon.stub(view.layout, 'trigger');
            view.handleOperatorSelected({currentTarget: $operatorField});
            $row.data('valueField').model.set('status', 'firesModelChangeEvent');
            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalledWith('filter:apply');
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
        it('should make an exception for specific $ filters', function() {
            $row = $('<div>').data({
                name: '$favorite',
                value: 'true'
            });
            filter = view.buildRowFilterDef($row);
            expected = {
                $favorite: ''
            };
            expect(filter).toEqual(expected);
        });
    });

});
