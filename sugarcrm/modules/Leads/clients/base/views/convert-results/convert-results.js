({
    associatedModels: null,

    events:{
        'click .preview-list-item':'previewRecord'
    },

    initialize: function(options) {
        _.bindAll(this);
        app.view.View.prototype.initialize.call(this, options);
        app.events.on("list:preview:decorate", this.decorateRow);
        this.associatedModels = app.data.createMixedBeanCollection();
    },

    bindDataChange: function() {
        this.model.on("change", this.populateResults);
    },

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
    },

    /**
     * Handle firing of the preview render request for selected row
     *
     * @param e
     */
    previewRecord: function(e) {
        var $el = this.$(e.currentTarget),
            data = $el.data(),
            model = app.data.createBean(data.module, {id:data.id});

        model.fetch({
            success: _.bind(function(model) {
                model.set("_module", data.module);
                app.events.trigger("preview:render", model, this.associatedModels);
            }, this)
        });
    },

    /**
     * Decorate a row in the list that is being shown in Preview
     * @param model Model for row to be decorated.  Pass a falsy value to clear decoration.
     */
    decorateRow: function(model){
        this.$("tr.highlighted").removeClass("highlighted current above below");
        if(model){
            var rowName = model.module+"_"+ model.get("id");
            var curr = this.$("tr[name='"+rowName+"']");
            curr.addClass("current highlighted");
            curr.prev("tr").addClass("highlighted above");
            curr.next("tr").addClass("highlighted below");
        }
    }
})
