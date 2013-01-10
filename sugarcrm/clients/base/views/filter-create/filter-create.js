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
        'change .filter-value': 'modifyValue'
    },

    rowTemplate: Handlebars.compile('<article class="filter-body newRow">' +
'     <div class="row-fluid">' +
'       <div class="filter-field controls span3">' +
'         <select name="field" class="field_name chzn-select chzn-inherit-width" data-placeholder="Select field name..."> ' +
'             <option value=""></option> ' +
'             {{#each filterFields}}' +
'                 <option data-type="{{type}}" value="{{name}}">{{string}}</option>' +
'             {{/each}}' +
'         </select>' +
'       </div>' +
'       <div class="filter-operator hide controls span3"> ' +
'         <select name="operator" class="operator chzn-select chzn-inherit-width" data-placeholder="Select operator...">' +
'         </select>' +
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
        'is': 'equals',
        'is not': 'notEquals',
        'matches': 'equals',
        'does not match': 'notEquals',
        'contains': 'contains',
        'starts with': 'starts',
        'ends with': 'ends',
        'is equal to': 'equals',
        'is greater than': 'gt',
        'is greater than or equal to': 'gte',
        'is less than': 'lt',
        'is less than or equal to': 'lte',
        'on': 'equals',
        'before': 'lt',
        'on or before': 'lte',
        'after': 'gt',
        'on or after': 'gte'
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
                name: key,
                string: app.lang.getAppString(value.vname),
                type: value.type
            });
        });
        this.filterFields = _.filter(this.filterFields, function(el) {
            // Double-bang intended. Coerces values like 'undefined' to a bool.
            return !!self.filterOperatorMap[el.type];
        });
    },

    render: function() {
        app.view.View.prototype.render.call(this);
        this.addRow();

        // Render the filter widget by default with "name" "contains" for fast searching
        this.$("select.field_name option[value='name']").attr("selected", true);
        this.$(".field_name").trigger("liszt:updated").change();
        this.$("select.operator option[value='starts']").attr("selected", true);
        this.$(".operator").trigger("liszt:updated").change();
    },

    addRow: function(e) {
        var stuff = this.rowTemplate(this),
            target;
        this.$('.newRow').removeClass('newRow').find('.addme').addClass('hide');

        if(_.isUndefined(e)) {
            target = this.$(".filter-options");
        } else {
            target = this.$(e.currentTarget).parents('.filter-options');
        }
        target.append(stuff);
        this.$(".newRow select.field_name").chosen();
    },

    populateFilter: function(f) {
        var self = this;
        this.$(".filter-header input").val(f.name);
        _.each(this._applyJSON(f.filter_definition), function(row) {
            self.populateRow(row);
        });
    },

    populateRow: function(r) {
        this.addRow();
        var $row = this.$('.newRow');
        $row.data('value', r.value);
        $row.find("select.field_name option[value='" + r.field + "']").attr("selected", true);
        $row.find(".field_name").trigger("liszt:updated").change();
        $row.find("select.operator option[value='" + r.op + "']").attr("selected", true);
        $row.find(".operator").trigger("liszt:updated").change();
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
            self = this;
        $parent.find('.filter-operator').removeClass('hide').find('option').remove();
        $parent.find('.filter-value').addClass('hide').empty();
        var types = this.filterOperatorMap[fieldType] || this.filterOperatorMap['base'];
        if(types[0] !== '') types.unshift('');
        _.each(types, function(t) {
            $('<option />').appendTo($parent.find('select.operator'))
                .attr('value', self.filterMap[t] || '').text(t);
        });
        $parent.find("select.operator").chosen({
            allow_single_deselect: true,
            disable_search_threshold: 10
        });
        $parent.find("select.operator").trigger("liszt:updated");
        this._disposeField($parent);
    },

    chooseOperator: function(e) {
        var $el = this.$(e.currentTarget),
            $parent = $el.parents('.filter-body'),
            operation = $el.val(),
            fieldName = $parent.find('select.field_name').val(),
            fieldType = app.metadata.getModule(this.title).fields[fieldName].type;

        this._disposeField($parent);

        if(operation !== '') {
            if(fieldType == 'datetime') {
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
            if(fieldType == 'enum') {
                obj.def.options = fieldName + '_dom';
            }

            var field = app.view.createField(obj);

            $parent.find('.filter-value').removeClass('hide').find('input, select').remove();
            var fieldContainer = $(field.getPlaceholder().string).appendTo($parent.find('.filter-value'));
            this._renderField(field);
            $parent.data('value_field', field);
            fieldContainer.find('input, select').addClass('inherit-width');
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
            if($(i).val() !== '') modified = true;
        });
        $parent.find(kls).toggleClass('hide', !modified);
    },

    updateRow: function(e) {
        var $el = this.$(e.currentTarget),
            $parent = $el.parents('.filter-body');
        $parent.find('.updateme').addClass('hide');
    },

    triggerClose: function() {
        this.layout.trigger("filter:create:close:fire");
    },

    notSaved: function() {
        if(this.$(".filter-header").find('input').val() !== '') {
            this.$(".save_button").removeClass("disabled");
        }
        this.$(".delete_button").removeClass("hide");
    },

    save: function() {
        var val = this.$(".filter-header input").val();
        if(val) {
            this.$(".save_button").addClass("disabled");
            this.$(".delete_button").removeClass("hide");
            var obj = {
                filter_definition: this._getJSON(),
                name: val,
                default_filter: false,
                module_name: this.title,
                editable: true
            };
            var filter = app.data.createBean('Filters', obj);

            if(filter.get('editable')) {
                filter.save({success: this.setLastUsed});
            } else {
                this.setLastUsed(filter);
            }
        }
    },

    setLastUsed: function(model) {
        var url = app.api.buildURL('Filters/' + this.title + '/used', "update", model);
        app.api.call("update", url, null, {
            success: function() {
                // Fire event.
            }
        });
    },

    removeAll: function() {
        // TODO: Make a delete request to the server.
        var self = this;
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
            var field_name = $el.find("select.field_name").val();

            if(field_name) {
                if(_.isUndefined(fields[field_name])) {
                    fields[field_name] = [];
                }
                var field = $el.data('value_field'),
                    op = $el.find("select.operator").val(),
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
        _.each(fields, function(v, k) {
            if (v.length === 1) {
                v = v[0];
            }
            var foo = {};
            foo[k] = v;
            obj.filter[0]["$"+default_op].push(foo);
        });

        return obj;
    },

    _applyJSON: function(obj) {
        // TODO: Make this usable for OR filters too.
        var stuff = obj.filter[0]["$and"], ret = [];
        _.each(stuff, function(el) {
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
