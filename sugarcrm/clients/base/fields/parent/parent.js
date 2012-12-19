({
    events: {
        'keyup .chzn-select[name=parent_name] + .chzn-container .chzn-search input': 'throttleSearch'
    },
    minChars: 3,
    extendsFrom: 'RelateField',
    fieldTag: 'select.chzn-select[name=parent_name]',
    typeFieldTag: 'select.chzn-select[name=parent_type]',
    hiddenValue: '***',
    _render: function() {
        var result = app.view.fields.RelateField.prototype._render.call(this),
            self = this;
        if(this.tplName === 'edit') {
            this.checkAcl('access', this.model.get('parent_type'));
            this.$(this.typeFieldTag).not(".chzn-done").chosen().change(function(evt) {
                var selected = $(evt.currentTarget).find(':selected'),
                    module = selected.val();
                self.$(self.fieldTag).children().not(":first").not("[data-searchmore=true]").remove();
                self.checkAcl.call(self, 'edit', module);
                self.setValue({
                    id: '',
                    value: '',
                    module: module
                });
            });
            if(app.acl.hasAccessToModel('edit', this.model, this.name) === false) {
                this.$(this.typeFieldTag).attr("disabled", "disabled");
            } else {
                this.$(this.typeFieldTag).attr("disabled", false);
            }
            this.$(this.typeFieldTag).trigger("liszt:updated");
        }
        return result;
    },
    checkAcl: function(action, module) {
        if(app.acl.hasAccess(action, module) === false) {
            this.$(this.fieldTag).attr("disabled", "disabled");
        } else {
            this.$(this.fieldTag).attr("disabled", false);
        }
        this.$(this.fieldTag).trigger("liszt:updated");
    },
    setValue: function(model) {
        if(app.acl.hasAccess(this.action, this.model.module, this.model.get('assigned_user_id'), this.name)) {
            if(model.module) {
                this.model.set('parent_type', model.module);
            }
            this.model.set('parent_id', model.id);
            this.model.set('parent_name', model.value);
        }
    },
    getSearchModule: function() {
        return this.model.get('parent_type');
    },
    format: function(value) {
        //TODO: The label should be the parent module name
        this.def.module = this.model.get('parent_type');
        //check the user has the access to the current parent related module
        //TODO: Check hasAccessToModel for the parent related record
        if(app.acl.hasAccess('access', this.def.module, app.user.id) === false) {
            return this.hiddenValue;
        }
        return value;
    }
})