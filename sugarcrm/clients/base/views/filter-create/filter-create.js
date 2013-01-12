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
        'change .operator': 'chooseOperator',
        'blur .filter-value': 'modifyValue'
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
        'enum': ['is', 'is not'],
        'varchar': ['matches', 'does not match', 'contains', 'starts with', 'ends with'],
        'name': ['matches', 'does not match', 'contains', 'starts with', 'ends with'],
        'currency': ['is equal to', 'is greater than', 'is greater than or equal to', 'is less than', 'is less than or equal to'],
        'int': ['is equal to', 'is greater than', 'is greater than or equal to', 'is less than', 'is less than or equal to'],
        'double': ['is equal to', 'is greater than', 'is greater than or equal to', 'is less than', 'is less than or equal to'],
        'datetime': ['on', 'before', 'on or before', 'after', 'on or after'],
        'base': ['fall through to this case']
    },

    filterMap: {
        'is': '$equals',
        'is not': '$not_equals',
        'matches': '$equals',
        'does not match': '$not_equals',
        'contains': '$contains',
        'starts with': '$starts',
        'ends with': '$ends',
        'is equal to': '$equals',
        'is greater than': '$gt',
        'is greater than or equal to': '$gte',
        'is less than': '$lt',
        'is less than or equal to': '$lte',
        'on': '$equals',
        'before': '$lt',
        'on or before': '$lte',
        'after': '$gt',
        'on or after': '$gte'
    },

    initialize: function(opts) {
        // Remove the next line later:
        this.isSaved = false;

        var self = this;
        this.title = app.controller.context.get('module');
        app.view.View.prototype.initialize.call(this, opts);
        this.filterFields = [];
        _.each(app.metadata.getModule(this.title).fields, function(value, key) {
            self.filterFields.push({
                id: key,
                text: app.lang.getAppString(value.vname),
                type: value.type
            });
        });
        this.filterFields = _.filter(this.filterFields, function(el) {
            // Double-bang intended. Coerces values like 'undefined' to a bool.
            return !!self.filterOperatorMap[el.type] && !_.isUndefined(el.text);
        });

        this.layout.off("filter:populate");
        this.layout.on("filter:populate", function(filter) {
            self.populateFilter(filter);
        });
    },

    render: function(model) {
        app.view.View.prototype.render.call(this);
        this.addRow();
    },

    purgeAllRows: function() {
        this.$(".filter-body").filter(":not(:first-child)").remove();
    },

    addRow: function(e) {
        var tpl = this.rowTemplate(this),
            self = this;

        this.$('.newRow').removeClass('newRow').find('.addme').addClass('hide');
        this.$(".filter-options").append(tpl);
        this.$(".newRow .field_name").select2({
            data: this.filterFields,
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
        var $el = this.$(e.currentTarget),
            $parent = $el.parents('.filter-body'),
            fieldName = $el.val(),
            fieldType = app.metadata.getModule(this.title).fields[fieldName].type,
            payload = [],
            self = this;
        $parent.find('.filter-operator').removeClass('hide').find('option').remove();
        $parent.find('.filter-value').addClass('hide').empty();
        var types = this.filterOperatorMap[fieldType] || this.filterOperatorMap['base'];
        _.each(types, function(t) {
            payload.push({
                id: self.filterMap[t],
                text: t
            });
        });
        $parent.find(".operator").select2({
            data: payload,
            placeholder: app.lang.getAppString("LBL_FILTER_SELECT_OPERATOR"),
            initSelection: function(el, callback) {
                var data = _(payload).find(function(i) {
                    return i.id === el.val();
                });
                callback(data);
            }
        });
        this._disposeField($parent);
    },

    chooseOperator: function(e) {
        var $el = this.$(e.currentTarget),
            $parent = $el.parents('.filter-body'),
            operation = $el.val(),
            fieldName = $parent.find('input.field_name').val(),
            fieldType = app.metadata.getModule(this.title).fields[fieldName].type;

        this._disposeField($parent);

        if(operation !== '') {
            if(fieldType === 'datetime') {
                fieldType = 'datetimecombo';
            }

            var model = app.data.createBean(this.title);
            model.set(fieldName, $parent.data('value') || '');
            var obj = {
                view: this,
                viewName: 'edit',
                model: model,
                def: {
                    name: fieldName,
                    type: fieldType
                }
            };
            if(fieldType === 'enum') {
                obj.def.options = fieldName + '_dom';
            }

            var field = app.view.createField(obj);

            $parent.find('.filter-value').removeClass('hide').find('input, input').remove();
            var fieldContainer = $(field.getPlaceholder().string).appendTo($parent.find('.filter-value'));
            this._renderField(field);
            $parent.data('value_field', field);
            fieldContainer.find('input, input').addClass('inherit-width');
            model.change();
        }
    },

    modifyValue: function(e) {
        var $el = this.$(e.currentTarget),
            $parent = $el.parents('.filter-body'),
            modified = false,
            fieldTag = $parent.data('value_field').fieldTag,
            kls = $parent.hasClass('newRow') ? '.addme' : '.updateme';

        _.each($el.find(fieldTag), function(i) {
            if( $(i).val() !== '') {
                modified = true;
            }
        });
        $parent.find(kls).toggleClass('hide', !modified);
    },

    updateRow: function(e) {
        var $el = this.$(e.currentTarget),
            $parent = $el.parents('.filter-body');
        $parent.find('.updateme').addClass('hide');
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
                module_name: this.title
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
        var url = app.api.buildURL('Filters/' + this.title + '/used', "update", model);
        app.api.call("update", url, null, {
            success: function() {
                self.layout.trigger("filter:refresh");
            }
        });
    },

    removeAll: function() {
        // TODO: Make a delete request to the server.
        var self = this;
        if(this.$(".filter-header").data("model")) {
            this.$(".filter-header").data("model").destroy({
                success: function() {
                    self.$(".filter-header").data("model", null);
                    self.layout.trigger("filter:refresh");
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
        }
        $parent.find('.addme').addClass('hide');
    },

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
