({
    minChars: 1,
    extendsFrom: 'RelateField',
    fieldTag: 'input.select2[name=parent_name]',
    typeFieldTag: 'select.select2[name=parent_type]',


    _render: function() {
        var result, self = this;
        app.view.invokeParent(this, {type: 'field', name: 'relate', method: '_render'});
        if(this.tplName === 'edit') {
            this.checkAcl('access', this.model.get('parent_type'));
            this.$(this.typeFieldTag).select2({
                width : '100%',
                minimumResultsForSearch: 5
            }).on("change", function(e) {
                var module = e.val;
                self.checkAcl.call(self, 'edit', module);
                self.setValue({
                    id: '',
                    value: '',
                    module: module
                });
            });

            if(this.model.get('parent_type') !== this.$(this.typeFieldTag).val()) {
                this.model.set('parent_type', this.$(this.typeFieldTag).val());
            }

            if(app.acl.hasAccessToModel('edit', this.model, this.name) === false) {
                this.$(this.typeFieldTag).attr("disabled", "disabled");
            } else {
                this.$(this.typeFieldTag).attr("disabled", false);
            }
            this.$(this.typeFieldTag).trigger("liszt:updated");
        } else if(this.tplName === 'disabled'){
            this.$(this.typeFieldTag).attr("disabled", "disabled").select2();
        }
        return result;
    },
    //Overriden methods to get related module and id and anything specific to parent.js
    _buildRoute: function() {
        var module, idName;
        if (!this.value || this.value !== this.hiddenValue) {
            module = this._getRelateModule();
            idName = this._getRelateId();
            app.view.invokeParent(this, {type: 'field', name: 'relate', method: 'buildRoute', args: [module, idName]});
        }
    },
    _getRelateModule: function() {
        return this.model.get("parent_type");
    },
    _getRelateId: function() {
        return this.model.get("parent_id");
    },
    format: function(value) {
        this.def.module = this.model.get('parent_type');

        this.context.set("record_label", {
            field: this.name,
            label: (this.tplName === 'detail') ? this.def.module : app.lang.get(this.def.label, this.module)
        });
        this._buildRoute();

        return value;
    },
    checkAcl: function(action, module) {
        if(app.acl.hasAccess(action, module) === false) {
            this.$(this.fieldTag).attr("disabled", "disabled");
        } else {
            this.$(this.fieldTag).attr("disabled", false);
        }
    },
    setValue: function(model) {
        if (model) {
            var silent = model.silent || false;
            if(app.acl.hasAccess(this.action, this.model.module, this.model.get('assigned_user_id'), this.name)) {
                if(model.module) {
                    this.model.set('parent_type', model.module, {silent: silent});
                }
                this.model.set('parent_id', model.id, {silent: silent});
                this.model.set('parent_name', model.value, {silent: silent});
            }
        }
    },
    getSearchModule: function() {
        return this.model.get('parent_type') || this.$(this.typeFieldTag).val();
    },
    getPlaceHolder: function() {
        return  app.lang.get('LBL_SEARCH_SELECT', this.module);
    },
    unbindDom: function() {
        this.$(this.typeFieldTag).select2('destroy');
        app.view.invokeParent(this, {type: 'field', name: 'relate', method: 'unbindDom'});
    }

})
