({
    extendsFrom: 'ConvertResultsView',

    /**
     * Build a collection of associated models and re-render the view
     */
    populateResults: function() {
        var model;

        this.associatedModels.reset();

        model = this.buildAssociatedModel('Contacts', 'contact_id', 'contact_name');
        if (model) {
            this.associatedModels.push(model);
        }
        model = this.buildAssociatedModel('Accounts', 'account_id', 'account_name');
        if (model) {
            this.associatedModels.push(model);
        }
        model = this.buildAssociatedModel('Opportunities', 'opportunity_id', 'opportunity_name');
        if (model) {
            this.associatedModels.push(model);
        }
        app.view.View.prototype.render.call(this);
    },

    /**
     * Build an associated model based on given id & name fields on the Lead record
     *
     * @param moduleName
     * @param idField
     * @param nameField
     * @return {*} model or false if id field is not set on the lead
     */
    buildAssociatedModel: function(moduleName, idField, nameField) {
        var moduleSingular = app.lang.getAppListStrings("moduleListSingular"),
            rowTitle,
            model;

        if (_.isEmpty(this.model.get(idField))) {
            return false;
        }

        rowTitle = app.lang.get(
            'LBL_CONVERT_MODULE_ASSOCIATED',
            this.module,
            {'moduleName': moduleSingular[moduleName]}
        );

        model = app.data.createBean(moduleName, {
            id: this.model.get(idField),
            name: this.model.get(nameField),
            row_title: rowTitle,
            _module: moduleName,
            target_module: moduleName
        });
        model.module = moduleName;
        return model;
    }
})
