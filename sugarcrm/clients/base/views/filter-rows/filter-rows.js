({
    events: {
        'click a.addme': 'addRow',
        'click a.removeme': 'removeRow',
        'change .filter-field select': 'chooseField',
        'change .filter-operator select': 'chooseOperator'
    },

    className: 'filter-definition-container',

    filterFields: [],

    initialize: function(opts) {
        // Remove the next line later:
        this.isSaved = false;
        this.filterOperatorMap = app.lang.getAppListStrings("filter_operators_dom");
        app.view.View.prototype.initialize.call(this, opts);

        this.layout.on("filter:change", function(moduleName) {
            var moduleMeta = app.metadata.getModule(moduleName);
            if (!moduleMeta) {
                return;
            }
            this.fieldList = this.getFilterableFields(moduleName);

            // This is the Select2 data for the enum field. 1st value must be blank.
            this.filterFields = {"": ""};
            this.moduleName = moduleName;

            _.each(this.fieldList, function(value, key) {
                var text = app.lang.get(value.vname, moduleName);
                // Check if we support this field type.
                if (this.filterOperatorMap[value.type] && !_.isUndefined(text)) {
                    this.filterFields[key] = text;
                }
            }, this);
        }, this);

        this.layout.on("filter:create:open", function(filterModel) {
            if (!filterModel.get('filter_definition')) {
                this.render();
                this.addRow();
            } else {
                this.populateFilter();
            }
        }, this);

        this.layout.on("filter:create:close", function() {
            this.render();
        }, this);

        this.listenTo(this.layout, "filter:create:save", this.saveFilter);
        this.listenTo(this.layout, "filter:create:delete", this.deleteFilter);
    },

    getFilterableFields: function(moduleName) {
        var moduleMeta = app.metadata.getModule(moduleName),
            fieldMeta = moduleMeta.fields,
            fields = {};
        if (moduleMeta.filters) {
            _.each(moduleMeta.filters, function(templateMeta) {
                if (templateMeta.meta && templateMeta.meta.fields) {
                    fields = _.extend(fields, templateMeta.meta.fields);
                }
            });
        }

        _.each(fields, function(fieldFilterDef, fieldName) {
            if (_.isEmpty(fieldFilterDef)) {
                fields[fieldName] = fieldMeta[fieldName];
            } else {
                fields[fieldName] = _.extend({name: fieldName}, fieldFilterDef, fieldMeta[fieldName]);
            }
        });

        return fields;
    },

    addRow: function(e) {
        var $row,
            tpl = app.template.get("filter-rows.filter-row-partial");

        if (e) {
            // Triggered by clicking the plus sign. Add the row to that point.
            $row = this.$(e.currentTarget).parents('.filter-body');
            $row.after(tpl({}));
            $row = $row.next();
        } else {
            // Add the initial row.
            $row = $(tpl({})).appendTo(this.$el);
        }

        var model = app.data.createBean(this.moduleName);
        // minimumResultsForSearch set to 9999 to hide the search field,
        // See: https://github.com/ivaynberg/select2/issues/414
        var obj = {
                meta: {
                    view: "edit"
                },
                def: {
                    type: "enum",
                    searchBarThreshold: 9999,
                    options: this.filterFields
                },
                model: model,
                context: app.controller.context,
                viewName: "edit",
                view: this
            },
            field = app.view.createField(obj),
            $fieldValue = $row.find('.filter-field'),
            $fieldContainer = $(field.getPlaceholder().string);

        $fieldContainer.appendTo($fieldValue);
        $row.data('nameField', field);

        this._renderField(field);
        this.layout.trigger("filter:create:rowsValid", false);

        return $row;
    },

    removeRow: function(e) {
        var $row = this.$(e.currentTarget).parents('article.filter-body'),
            $rows = this.$('article.filter-body'),
            fieldOpts = [
                {'field': 'nameField', 'value': 'name'},
                {'field': 'operatorField', 'value': 'operator'},
                {'field': 'valueField', 'value': 'value'}
            ];

        this._disposeFields($row, fieldOpts);
        $row.remove();
        this.validateRows($rows.not($row));
        if ($rows.length === 1) {
            this.addRow();
        }
    },

    validateRows: function($rows) {
        $rows = $rows ? $rows : this.$('article.filter-body');
        this.layout.trigger("filter:create:rowsValid", true);
        _.each($rows, function(row) {
            var data = $(row).data();
            if (!data.value) {
                this.layout.trigger("filter:create:rowsValid", false);
            }
        }, this);
    },

    populateFilter: function() {
        var self = this,
            filterDef = this.layout.editingFilter.get("filter_definition");

        this.render();
        this.layout.trigger("filter:set:name", this.layout.editingFilter.get("name"));

        _.each(filterDef, function(row) {
            self.populateRow(row);
        });
    },

    populateRow: function(rowObj) {
        var $row = this.addRow();
        _.each(rowObj, function(value, key) {
            if (key === "$or") {
                keys = _.reduce(value, function(memo, obj) {
                    return memo.concat(_.keys(obj));
                }, []);

                key = _.find(_.keys(this.fieldList), function(key) {
                    if (_.has(this.fieldList[key], 'dbFields')) {
                        return _.isEqual(this.fieldList[key].dbFields.sort(), keys.sort());
                    }
                }, this);

                // Predicates are identical, so we just use the first.
                value = _.values(value[0])[0];
            }

            $row.find('.filter-field select').select2('val', key).trigger('change');
            if (_.isString(value)) {
                value = {"$equals": value};
            }
            _.each(value, function(value, operator) {
                $row.data('value', value);
                $row.find('.filter-operator select').select2('val', operator).trigger('change');
            });
        }, this);
    },

    editName: function(e) {
        if(this.$(e.currentTarget).find('input').val() === '') {
            this.$(".save_button").addClass("disabled");
        }
    },

    chooseField: function(e) {
        var $el = this.$(e.currentTarget),
            $row = $el.parents('.filter-body'),
            $fieldWrapper = $row.find('.filter-operator'),
            data = $row.data(),
            fieldName = $el.val(),
            fieldOpts = [{'field': 'operatorField', 'value': 'operator'},
                         {'field': 'valueField', 'value': 'value'}];

        this._disposeFields($row, fieldOpts);

        if (!fieldName) {
            data['name'] = "";
            return;
        }

        var fieldType = this.fieldList[fieldName].type,
            payload = {"": ""},
            types = _.keys(this.filterOperatorMap[fieldType]);

        $fieldWrapper.removeClass('hide').empty();
        $row.find('.filter-value').addClass('hide').empty();

        data['name'] = fieldName;

        // If the user is editing a filter, clear the operator.
        //$row.find('.field-operator select').select2('val', '');

        _.each(types, function(operand) {
            payload[operand] = this.filterOperatorMap[fieldType][operand];
        }, this);

        var model = app.data.createBean(this.moduleName);
        // minimumResultsForSearch set to 9999 to hide the search field,
        // See: https://github.com/ivaynberg/select2/issues/414
        var obj = {
                meta: {
                    view: "edit"
                },
                def: {
                    type: "enum",
                    searchBarThreshold: 9999,
                    options: payload
                },
                model: model,
                context: app.controller.context,
                viewName: "edit",
                view: this
            },
            field = app.view.createField(obj),
            $field = $(field.getPlaceholder().string);

        $field.appendTo($fieldWrapper);
        data['operatorField'] = field;

        this._renderField(field);
    },

    setField: function(fieldName) {

    },

    chooseOperator: function(e) {
        var $el = this.$(e.currentTarget),
            $row = $el.parents('.filter-body'),
            $fieldWrapper = $row.find('.filter-value'),
            data = $row.data(),
            operation = $el.val(),
            fieldOpts = [{'field': 'valueField', 'value': 'value'}];

        this._disposeFields($row, fieldOpts);

        if (!operation) {
            data['operator'] = "";
            return;
        }

        data['operator'] = operation;

        var module = this.moduleName,
            fieldName = $row.find('.filter-field select').val(),
            fieldType = this.fieldList[fieldName].type;

        // Patching metadata
        var fields = app.metadata._patchFields(module, app.metadata.getModule(module), app.utils.deepCopy(this.fieldList));

        switch (fieldType) {
        case 'enum':
            fields[fieldName].isMultiSelect = true;
            break;
        case 'bool':
            fields[fieldName].type = 'enum';
            break;
        case 'int':
            fields[fieldName].auto_increment = false;
            break;
        }

        var model = app.data.createBean(module);
        model.set(fieldName, $row.data('value') || '');
        var obj = {
                meta: {
                    view: "edit"
                },
                def: fields[fieldName],
                model: model,
                context: app.controller.context,
                viewName: "edit",
                view: this
            };

        var field = app.view.createField(obj),
            $fieldValue = $row.find('.filter-value'),
            fieldContainer = $(field.getPlaceholder().string);

        $fieldValue.removeClass('hide').find('input, select').remove();
        fieldContainer.appendTo($fieldValue);
        data['valueField'] = field;

        this._renderField(field);

        fieldContainer.find('input, select, textarea').addClass('inherit-width');

        model.on("change", (function($row) {
            return function() {
                // We use _.result here to prevent an undefined method error
                // in case the val method is not defined on the field.
                var result = $row.data("valueField").value || _.result($row.data("valueField"), 'val') || '';
                $row.data("value", result);
                // check each row for a valid filter, add to dynamic filter def
                var dynamicFilterDef = this.buildFilterDef();
                // trigger the filtering here.
                this.layout.trigger('filter:change:quicksearch', null, dynamicFilterDef);
            };
        })($row), this);
    },

    buildFilterDef: function() {
        var $rows = this.$('article.filter-body'),
            filter = [];

        _.each($rows, function(row) {
            var rowFilter = this.buildRowFilterDef($(row));

            if (rowFilter) {
                filter.push(rowFilter);
            }
        }, this);

        this.validateRows($rows);

        return filter;
    },

    buildRowFilterDef: function($row) {
        var data = $row.data(),
            name = data['name'],
            operator = data['operator'],
            value = data['value'],
            filter = {};

        if (value) {
            if (name.indexOf("$") === 0 && value === "true") {
                filter[name] = "";
            } else {
                if (_.has(this.fieldList[name], 'dbFields')) {
                    var subfilters = [];
                    _.each(this.fieldList[name].dbFields, function(dbField) {
                        var filter = {};
                        filter[dbField] = {};
                        filter[dbField][operator] = value;
                        subfilters.push(filter);
                    });
                    filter["$or"] = subfilters;
                } else {
                    if (operator === "$equals") {
                        filter[name] = value;
                    } else {
                        filter[name] = {};
                        filter[name][operator] = value;
                    }
                }

            }

            return filter;
        }
    },

    saveFilter: function(name) {
        var self = this,
            obj = {
                filter_definition: this.buildFilterDef(),
                name: name,
                module_name: this.moduleName
            };

        this.layout.editingFilter.save(obj, {
            success: function(model) {
                self.layout.trigger("filter:add", model);
                self.layout.trigger("filter:create:rowsValid", false);
            },
            alerts: {
                'success': {
                    title: app.lang.get("LBL_EMAIL_SUCCESS") + ":",
                    messages: app.lang.get("LBL_FILTER_SAVE") + " " + name
                }
            }
        });

        this.layout.trigger('filter:create:close');
    },

    deleteFilter: function() {
        var self = this,
            name = this.layout.editingFilter.get('name');
        this.layout.editingFilter.destroy({
            success: function(model) {
                self.layout.trigger("filter:remove", model);
            },
            alerts: {
                'success': {
                    title: app.lang.get('LBL_EMAIL_SUCCESS') + ':',
                    message: app.lang.get('LBL_DELETED') + ' ' + name
                }
            }
        });
        this.layout.trigger('filter:create:close');
    },

    /**
     * Internal function that disposes fields stored in the data attribute of the row el.
     * @param  {jQuery el} $row The row which fields are to be disposed.
     * @param  {array} opts An array of objects, corresponding with the data obj of the row.
     * Example: opts = [{'field': 'nameField', 'value': 'name'},
                        {'field': 'operatorField', 'value': 'operator'},
                        {'field': 'valueField', 'value': 'value'}]
     */
    _disposeFields: function($row, opts) {
        var data = $row.data(), trigger = false, model;
        if (_.isObject(data) && _.isArray(opts)) {
            _.each(opts, function(val) {
                if (data[val.field]) {
                    data[val.value] = "";
                    model = data[val.field].model;
                    if (val.field === "valueField" && model) {
                        model.unset(data.valueField.name, {silent: true});
                        trigger = true;
                    }
                    data[val.field].dispose();
                    data[val.field] = "";
                }
            }, this);
        }
        $row.data(data);

        if (trigger) {
            model.trigger('change');
        }
    }
})
