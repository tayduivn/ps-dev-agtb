({
    minChars: 1,
    extendsFrom: 'RelateField',
    fieldTag: 'input.select2[name=parent_name]',
    typeFieldTag: 'select.select2[name=parent_type]',
    hiddenValue: '***',
    _render: function() {
        var result = app.view.fields.RelateField.prototype._render.call(this),
            self = this;
        if(this.tplName === 'edit') {
            this.checkAcl('access', this.model.get('parent_type'));
            this.$(this.typeFieldTag).select2().on("change", function(e) {
                var module = e.val;
                self.checkAcl.call(self, 'edit', module);
                self.setValue({
                    id: '',
                    value: '',
                    module: module
                });
                var plugin = self.$(self.fieldTag).data("select2"),
                    placeholderTemplate = Handlebars.compile(app.lang.getAppString("LBL_SEARCH_MODULE")),
                    moduleString = app.lang.getAppListStrings("moduleListSingular");
                plugin.container.find("span").text(placeholderTemplate({
                    module: moduleString[module]
                }));
            });
            if(app.acl.hasAccessToModel('edit', this.model, this.name) === false) {
                this.$(this.typeFieldTag).attr("disabled", "disabled");
            } else {
                this.$(this.typeFieldTag).attr("disabled", false);
            }
            this.$(this.typeFieldTag).trigger("liszt:updated");
        } else if(this.tplName === 'disabled'){
            this.$(this.typeFieldTag).attr("disabled", "disabled").not(".chzn-done").chosen();
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
        var silent = model.silent || false;
        if(app.acl.hasAccess(this.action, this.model.module, this.model.get('assigned_user_id'), this.name)) {
            if(model.module) {
                this.model.set('parent_type', model.module, {silent: silent});
            }
            this.model.set('parent_id', model.id, {silent: silent});
            this.model.set('parent_name', model.value, {silent: silent});
        }
    },
    getSearchModule: function() {
        return this.model.get('parent_type') || this.$(this.typeFieldTag).val();
    },
    format: function(value) {
        //TODO: The label should be the parent module name
        this.def.module = this.model.get('parent_type');

        this.context.set("record_label", {
            field: this.name,
            label: (this.tplName === 'detail') ? this.def.module : app.lang.get(this.def.label, this.module)
        });

        //check the user has the access to the current parent related module
        //TODO: Check hasAccessToModel for the parent related record
        if(app.acl.hasAccess('access', this.def.module, app.user.id) === false) {
            return this.hiddenValue;
        }
        return value;
    }
})
