({
    events: {
        'click a.filter-close': 'triggerClose',
        'click a.addme': 'addRow',
        'click a.removeme': 'removeRow',
        'click a.updateme': 'updateRow',
        'click .save_button': 'save',
        'click .delete_button': 'removeAll',
        'change .filter-header': 'editName',
        'change .field_name': 'chooseField',
        'change .operator': 'chooseOperator'
    },

    rowTemplate: Handlebars.compile('<article class="filter-body newRow">' +
'     <div class="row-fluid">' +
'       <div class="filter-field controls span3">' +
'         <input type="hidden" name="field" class="field_name" />' +
'       </div>' +
'       <div class="filter-operator hide controls span3"> ' +
'         <input name="operator" type="hidden" class="operator" />' +
'       </div>' +
'       <div class="filter-value hide controls span5">' +
'       </div>' +
'       <div class="filter-actions span1">' +
'         <a class="removeme btn btn-invisible btn-dark"><i class="icon-minus"></i></a>' +
'         <a class="updateme btn btn-invisible hide btn-dark"><i class="icon-refresh"></i></a>' +
'         <a class="addme btn btn-invisible hide btn-dark"><i class="icon-plus"></i></a>' +
'       </div>' +
'     </div>' +
'   </article>'),

    filterOperatorMap: {
        'enum': ['$equals', '$not_equals'],
        'varchar': ['$equals', '$not_equals', '$contains', '$starts', '$ends'],
        'name': ['$equals', '$not_equals', '$contains', '$starts', '$ends'],
        'text': ['$equals', '$not_equals', '$contains', '$starts', '$ends'],
        'currency': ['$equals', '$gt', '$gte', '$lt', '$lte'],
        'int': ['$equals', '$gt', '$gte', '$lt', '$lte'],
        'double': ['$equals', '$gt', '$gte', '$lt', '$lte'],
        'datetime': ['$equals', '$lt', '$lte', '$gt', '$gte'],
        'base': []
    },

    initialize: function(opts) {
        // Remove the next line later:
        this.isSaved = false;

        app.view.View.prototype.initialize.call(this, opts);
        this.filterFields = [];
        _.each(app.metadata.getModule(this.module).fields, function(value, key) {
            this.filterFields.push({
                id: key,
                text: app.lang.get(value.vname, this.module),
                type: value.type
            });
        }, this);
        this.filterFields = _.filter(this.filterFields, function(el) {
            // Double-bang intended. Coerces values like 'undefined' to a bool.
            return !!this.filterOperatorMap[el.type] && !_.isUndefined(el.text);
        }, this);

        this.layout.off("filter:create:new");
        this.layout.off("filter:create:close");
        this.layout.on("filter:create:new", function(filter) {
            if(_.isUndefined(filter)) {
                this.render();
            } else {
                this.populateFilter(filter);
            }
            this.$('.filter-options').removeClass('hide');

        }, this);
        this.layout.on("filter:create:close", function() {
            this.$('.filter-options').addClass('hide');
        }, this);
    },

    render: function(model) {
        app.view.View.prototype.render.call(this);
        this.addRow();
    },

    purgeAllRows: function() {
        this.$(".filter-header input").val("");
        this.$(".filter-body").filter(":not(:first-child)").remove();
    },

    addRow: function() {
        var tpl = this.rowTemplate(this),
            self = this;

        this.$('.newRow').removeClass('newRow').find('.addme').addClass('hide');
        this.$(".filter-options").append(tpl);

        // minimumResultsForSearch set to 9999 to hide the search field,
        // See: https://github.com/ivaynberg/select2/issues/414
        this.$(".newRow .field_name").select2({
            data: this.filterFields,
            width: "element",
            minimumResultsForSearch: 9999,
            placeholder: app.lang.getAppString("LBL_FILTER_SELECT_FIELD"),
            initSelection: function(el, callback) {
                var data = _(self.filterFields).find(function(i) {
                    return i.id === el.val();
                });
                callback(data);
            }
        });
    },

    populateFilter: function(f) {
        var self = this;
        this.purgeAllRows();
        this.$(".filter-header").data("model", f);
        this.$(".filter-header input").val(f.get("name"));
        _.each(this._applyJSON(f.get("filter_definition")), function(row) {
            self.populateRow(row);
        });
        this.addRow();
    },

    populateRow: function(r) {
        this.addRow();
        var $row = this.$('.newRow');
        $row.data('value', r.value);
        $row.find("input.field_name").val(r.field).trigger("change");
        $row.find("input.operator").val(r.op).trigger("change");
    },

    editName: function(e) {
        if(this.$(e.currentTarget).find('input').val() === '') {
            this.$(".save_button").addClass("disabled");
        }
    },

    removeRow: function(e) {
        var $parent = this.$(e.currentTarget).parents('.filter-body');
        var newRow = $parent.hasClass('newRow');
        this.notSaved();
        this._disposeField($parent);
        $parent.remove();
        if(newRow) {
            this.addRow();
        }
    },

    chooseField: function(e) {
        this.notSaved();
        var self = this,
            $el = this.$(e.currentTarget),
            $parent = $el.parents('.filter-body'),
            fieldName = $el.val(),
            fieldType = app.metadata.getModule(this.module).fields[fieldName || 'name'].type,
            payload = [],
            types = this.filterOperatorMap[fieldType] || this.filterOperatorMap['base'],
            filterStrings = app.lang.getAppListStrings('filter_operators_dom');

        $parent.find('.filter-operator').removeClass('hide').find('option').remove();
        $parent.find('.filter-value').addClass('hide').empty();

        _.each(types, function(operand) {
            payload.push({
                id: operand,
                text: filterStrings[fieldType][operand]
            });
        });

        // minimumResultsForSearch set to 9999 to hide the search field,
        // See: https://github.com/ivaynberg/select2/issues/414
        $parent.find(".operator").select2({
            data: payload,
            width: "element",
            minimumResultsForSearch: 9999,
            placeholder: app.lang.getAppString("LBL_FILTER_SELECT_OPERATOR"),
            initSelection: function(el, callback) {
                var data = _(payload).find(function(i) {
                    return i.id === el.val();
                });
                callback(data);
            }
        }).focus();
        this._disposeField($parent);
    },

    chooseOperator: function(e) {
        var self = this,
            $el = this.$(e.currentTarget),
            $parent = $el.parents('.filter-body'),
            operation = $el.val(),
            fieldName = $parent.find('input.field_name').val(),
            fieldType = app.metadata.getModule(this.module).fields[fieldName].type;

        // Patching metadata
        var fields = app.metadata._patchFields(this.module, app.metadata.getModule(this.module), JSON.parse(JSON.stringify(app.metadata.getModule(this.module).fields)));

        this._disposeField($parent);

        if(operation !== '') {
            var model = app.data.createBean(this.module);
            model.set(fieldName, $parent.data('value') || '');
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
                $fieldValue = $parent.find('.filter-value');

            $fieldValue.removeClass('hide').find('input, select').remove();
            var fieldContainer = $(field.getPlaceholder().string).appendTo($parent.find('.filter-value'));
            this._renderField(field);
            $parent.data('value_field', field);
            fieldContainer.find('input, select').addClass('inherit-width');
            model.on("change", this.modifyValue($fieldValue));
        }
    },

    modifyValue: function($el) {
        var self = this,
            $parent = $el.parents('.filter-body'),
            fieldTag = $parent.data('value_field').fieldTag;

        return function() {
            var modified = false,
                kls = $parent.hasClass('newRow') ? '.addme' : '.updateme';
            _.each($el.find(fieldTag), function(i) {
                if( $(i).val() !== '' ) {
                    modified = true;
                }
            });

            $parent.find(kls).toggleClass('hide', !modified);

            if($parent.hasClass('newRow')) {
                self.addRow();
            }
        };
    },

    updateRow: function(e) {
        var $el = this.$(e.currentTarget),
            $parent = $el.parents('.filter-body');
        $parent.find('.updateme').addClass('hide');

        this.save();
    },

    triggerClose: function() {
        this.layout.trigger("filter:create:close");
    },

    notSaved: function() {
        if(this.$(".filter-header").find('input').val() !== '') {
            this.$(".save_button").removeClass("disabled");
        }
        this.$(".delete_button").removeClass("hide");
    },

    save: function() {
        var self = this;
        var val = this.$(".filter-header input").val();
        if(val) {
            this.$(".save_button").addClass("disabled");
            this.$(".delete_button").removeClass("hide");
            var obj = {
                filter_definition: this._getJSON(),
                name: val,
                default_filter: false,
                module_name: this.module
            };

            var filter = this.$(".filter-header").data("model");

            if(!filter || !filter.get("default_filter")) {
                filter = app.data.createBean('Filters');
            }

            filter.save(obj, {success: function(model) {
                self.setLastUsed(model);
                self.$(".filter-header").data("model", model);
            }});

            this.triggerClose();
        }
    },

    setLastUsed: function(model) {
        var self = this;
        var url = app.api.buildURL('Filters/' + this.module + '/used', "update");
        app.api.call("update", url, {filters: [model.id]}, {
            success: function() {
                self.layout.trigger("filter:refresh", model.id);
            }
        });
    },

    removeAll: function() {
        var self = this;
        if(this.$(".filter-header").data("model")) {
            this.$(".filter-header").data("model").destroy({
                success: function() {
                    self.$(".filter-header").data("model", null);
                    self.layout.trigger("filter:refresh", "");
                }
            });
        }
        _.each(this.$(".filter-body"), function(el) {
            self._disposeField($(el));
        });
        this.render();
    },

    _disposeField: function($parent) {
        if(_($parent.data('value_field')).isObject()) {
            $parent.data('value_field').dispose();
            $parent.data('value_field', '');
            $parent.data('value', '');
        }
        $parent.find('.addme').addClass('hide');
    },

    /**
     * This function converts the filter-create DOM to a JSON object
     * that the Filter API can understand.
     * @return {object} Filter object ready to pass up to the Filter API.
     */
    _getJSON: function() {
        var obj = {
            filter: [{}]
        }, default_op = "and";
        obj.filter[0]["$"+default_op] = [];
        var fields = {};
        _.each(this.$el.find('.filter-body'), function(el) {
            var $el = $(el);
            var field_name = $el.find("input.field_name").val();

            if(field_name) {
                if(_.isUndefined(fields[field_name])) {
                    fields[field_name] = [];
                }
                var field = $el.data('value_field'),
                    op = $el.find("input.operator").val(),
                    fieldTag = field.fieldTag,
                    str, o = {};
                if(_.has(field, 'val')) {
                    str = field.val();
                } else {
                    str = $el.find(".filter-value " + fieldTag).first().val();
                }

                o[op] = str;
                fields[field_name].push(o);
            }
        });
        _.each(fields, function(value, key) {
            if (value.length === 1) {
                value = value[0];
            }
            var fieldParams = {};
            fieldParams[key] = value;
            obj.filter[0]["$"+default_op].push(fieldParams);
        });

        return obj;
    },

    /**
     * Converts a Filter API filter object to the filter-create DOM-representation.
     * Essentially performs the inverse of _getJSON.
     * @param  {object} obj The Filter API filter object.
     * @return {array}     An array of filter-create row elements to be inserted in the DOM.
     */
    _applyJSON: function(obj) {
        // TODO: Make this usable for OR filters too.
        var existingFilters = obj.filter[0]["$and"], ret = [];
        _.each(existingFilters, function(el) {
            _.each(el, function(val, field_name) {
                if(!_.isArray(val)) {
                    val = [val];
                }
                _.each(val, function(o) {
                    _.each(o, function(value, operator) {
                        ret.push({
                            field: field_name,
                            op: operator,
                            value: value
                        });
                    });
                });
            });
        });
        return ret;
    }
})
